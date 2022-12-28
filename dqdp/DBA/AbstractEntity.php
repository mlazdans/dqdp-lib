<?php declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\interfaces\EntityInterface;
use dqdp\DBA\interfaces\TransactionInterface;
use dqdp\SQL\Condition;
use dqdp\SQL\Insert;
use dqdp\SQL\Select;
use dqdp\SQL\Update;
use InvalidArgumentException;

// TODO: maybe do separate classes? ProcEntity, ReadOnlyEntity, etc
abstract class AbstractEntity implements EntityInterface, TransactionInterface {
	protected DBAInterface $dba;

	function __construct(){
	}

	// Select can be made from tabe,view or procedure
	// Update,insert can be made to table or view
	protected abstract function getTableName(): ?string;
	protected abstract function getProcName(): ?string;
	protected abstract function getPK(): array|string|null;
	protected abstract function getGen(): ?string;
	protected abstract function getProcArgs(): ?array;

	protected function select(): Select {
		if($TableName = $this->getTableName()){
			return (new Select("$TableName.*"))->From($TableName);
		} elseif($TableName = $this->getProcName()){
			return (new Select("$TableName.*"))->From($TableName)->withArgs($this->getProcArgs());
		} else {
			throw new InvalidArgumentException("Table not found");
		}
	}

	function fetch(mixed $q): mixed {
		if($data = $this->get_trans()->fetch_object($q)){
			return $data;
		} else {
			return null;
		}
	}

	function getSingle(?AbstractFilter $filters = null): mixed {
		if($q = $this->query($filters)){
			return $this->fetch($q);
		}

		return null;
	}

	function query(?AbstractFilter $filter = null): mixed {
		return $this->get_trans()->query($filter ? $filter->apply($this->select()) : $this->select());
	}

	// function count(?iterable $filters = null): int {
	// 	$sql = $this->set_filters($this->select(), $filters)
	// 	->ResetFields()
	// 	->ResetOrderBy()
	// 	->ResetJoinLast() // Reset LEFT JOINS
	// 	->Select("COUNT(*) sk")
	// 	->Rows(1);

	// 	return (int)($this->get_trans()->execute_single($sql)['sk']??0);
	// }

	function save(array|object $DATA): mixed {
		return $this->_insert_query($DATA, true);
	}

	function insert(array|object $DATA): mixed {
		return $this->_insert_query($DATA, false);
	}

	function update(int|string|array $ID, array|object $DATA): bool {
		if(is_null($TableName = $this->getTableName())){
			throw new InvalidArgumentException("Table not found");
		}

		if(is_null($PK = $this->getPK())){
			throw new InvalidArgumentException("Primary key not set");
		}

		$Where = new Condition();
		if(is_array($PK)){
			foreach($PK as $i=>$k){
				$Where->add_condition(["$k = ?", $ID[$i]]);
			}
		} else {
			$Where->add_condition(["$PK = ?", $ID]);
		}

		$sql = (new Update($TableName))->Set($DATA)->Where($Where);

		if($this->get_trans()->query($sql)){
			return true;
		}

		return false;
	}

	private function _pk_in_data(array|object $DATA): bool {
		$PK = $this->getPK();
		if(is_null($PK)){
			return false;
		} elseif(is_array($PK)){
			foreach($PK as $k){
				if(!prop_exists($DATA, $k) || !prop_initialized($DATA, $k)){
					return false;
				}
			}
		} elseif(!prop_exists($DATA, $PK) || !prop_initialized($DATA, $PK)){
			return false;
		}

		return true;
	}

	private function _insert_query(array|object $DATA, $update = false): mixed {
		if(is_null($TableName = $this->getTableName())){
			throw new InvalidArgumentException("Table not found");
		}

		$PKSetInData = $this->_pk_in_data($DATA);

		if($update && !$PKSetInData){
			throw new InvalidArgumentException("Primary key not set");
		}

		$PK = $this->getPK();
		if(is_array($PK)){
		} else {
			if(!$PKSetInData && $Gen = $this->getGen()){
				set_prop($DATA, $PK, function() use ($Gen) {
					return "NEXT VALUE FOR $Gen";
				});
			}
		}

		$sql = (new Insert)->Into($TableName)->Values($DATA);

		if($update){
			$sql->Update()->Matching($PK);
		}

		$sql->Returning($PK);

		if($q = $this->get_trans()->query($sql)){
			$retPK = $this->get_trans()->fetch_object($q);
			if(is_array($PK)){
				return $retPK;
			} else {
				return get_prop($retPK, $PK);
			}
		}

		return null;
	}

	function delete($ID){
		if(is_null($TableName = $this->getTableName())){
			throw new InvalidArgumentException("Table not found");
		}

		// $ID = func_get_arg(0);
		# TODO: multi field PK
		# TODO: dqdp\SQL\Statement
		$prep = $this->get_trans()->prepare("DELETE FROM $TableName WHERE $this->PK = ?");
		$ret = true;
		foreach(array_enfold($ID) as $id){
			$ret = $ret && $this->get_trans()->execute_prepared($prep, $id);
		}

		return $ret;
	}

	function set_trans(DBAInterface $dba){
		$this->dba = $dba;

		return $this;
	}

	function get_trans(): DBAInterface {
		return $this->dba;
	}

	// protected function set_default_filters(Statement $sql, $DATA, array $defaults, $prefix = null): Statement {
	// 	$DATA = eoe($DATA);

	// 	if(is_null($prefix)){
	// 		$prefix = "$this->Table.";
	// 	}

	// 	foreach($defaults as $field=>$value){
	// 		if(is_int($field)){
	// 			$sql->Where($value);
	// 		} elseif($DATA->exists($field)){
	// 			if(!is_null($DATA->{$field})){
	// 				# TODO: f-ija, kā build_sql
	// 				$sql->Where(["$prefix$field = ?", $DATA->{$field}]);
	// 			}
	// 		} else {
	// 			$sql->Where(["$prefix$field = ?", $value]);
	// 		}
	// 	}

	// 	return $sql;
	// }

	// # TODO: abstract out filters funkcionālo daļu
	// # TODO: uz Select???
	// # TODO: vai vispār vajag atdalīt NULL filters? Varbūt visiem vajag NULL check?
	// protected function set_null_filters(Statement $sql, $DATA, array $fields, string $prefix = null): Statement {
	// 	$DATA = eoe($DATA);

	// 	if(is_null($prefix)){
	// 		$prefix = "$this->Table.";
	// 	}

	// 	foreach($fields as $k){
	// 		if($DATA->exists($k)){
	// 			if(is_null($DATA->{$k})){
	// 				$sql->Where(["$prefix$k IS NULL"]);
	// 			} else {
	// 				# TODO: f-ija, kā build_sql
	// 				$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
	// 			}
	// 		}
	// 	}

	// 	return $sql;
	// }

	// protected function set_non_null_filters(Statement $sql, $DATA, array $fields, string $prefix = null): Statement {
	// 	$DATA = eoe($DATA);

	// 	if(is_null($prefix)){
	// 		$prefix = "$this->Table.";
	// 	}

	// 	foreach($fields as $k){
	// 		if($DATA->exists($k)){
	// 			# TODO: f-ija, kā build_sql
	// 			$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
	// 		}
	// 	}

	// 	return $sql;
	// }

	// protected function set_field_filters(Statement $sql, $DATA, array $fields, string $prefix = null): Statement {
	// 	$DATA = eoe($DATA);

	// 	if(is_null($prefix)){
	// 		$prefix = "$this->Table.";
	// 	}

	// 	foreach($fields as $k){
	// 		if($DATA->$k){
	// 			# TODO: f-ija, kā build_sql
	// 			$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
	// 		}
	// 	}

	// 	return $sql;
	// }

	// protected function set_base_filters(Statement $sql, ?iterable $filters = null): Statement {
	// 	$filters = eoe($filters);
	// 	if($this->PK){
	// 		if(is_array($this->PK)){
	// 		} else {
	// 			//if($filters->exists($this->PK) && is_empty($filters->{$this->PK})){
	// 			if($filters->exists($this->PK) && is_null($filters->{$this->PK})){
	// 				trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
	// 				return $sql;
	// 			}
	// 		}
	// 	}

	// 	if($this->PK){
	// 		foreach(array_enfold($this->PK) as $k){
	// 			if($filters->exists($k) && !is_null($filters->{$k})){
	// 				$sql->Where(["$this->Table.$k = ?", $filters->{$k}]);
	// 			}
	// 		}
	// 	}

	// 	# TODO: multi field PK
	// 	if($this->PK && !is_array($this->PK)){
	// 		$k = $this->PK."S";
	// 		if($filters->exists($k)){
	// 			if(is_array($filters->{$k})){
	// 				$IDS = $filters->{$k};
	// 			} elseif(is_string($filters->{$k})){
	// 				$IDS = explode(',',$filters->{$k});
	// 			} else {
	// 				trigger_error("Illegal multiple PRIMARY KEY value for $this->PKS", E_USER_ERROR);
	// 			}
	// 			$sql->Where(qb_filter_in("$this->Table.{$this->PK}", $IDS));
	// 		}
	// 	}

	// 	# TODO: unify
	// 	$Order = $filters->order_by??($filters->ORDER_BY??'');
	// 	if($Order){
	// 		$sql->ResetOrderBy()->OrderBy($Order);
	// 	}

	// 	if($filters->limit){
	// 		$sql->Rows($filters->limit);
	// 	}

	// 	if($filters->rows){
	// 		$sql->Rows($filters->rows);
	// 	}

	// 	if($filters->offset){
	// 		$sql->Offset($filters->offset);
	// 	}

	// 	if($filters->fields){
	// 		if(is_array($filters->fields)){
	// 			$sql->ResetFields()->Select(join(", ", $filters->fields));
	// 		} else {
	// 			$sql->ResetFields()->Select($filters->fields);
	// 		}
	// 	}

	// 	return $sql;
	// }
}
