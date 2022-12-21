<?php declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\interfaces\EntityInterface;
use dqdp\DBA\interfaces\ORMInterface;
use dqdp\SQL\Insert;
use dqdp\SQL\Select;
use dqdp\SQL\Statement;

abstract class AbstractEntity implements EntityInterface {
	protected DBAInterface $dba;
	protected $Table;
	protected $PK;

	function __construct(){
		$this->Table = $this->getTableName();
		$this->PK = $this->getPK();
	}

	protected abstract function getTableName(): string;
	protected abstract function getPK(): array|string|null;
	protected abstract function getGen(): ?string;

	protected function select(): Select {
		return (new Select($this->getTableName().".*"))->From($this->getTableName());
	}

	function get($ID, ?iterable $filters = null): mixed {
		$filters = eoe($filters);

		$filters->{$this->PK} = $ID;

		return $this->getSingle($filters);
	}

	// function get_all(string $CollectionClass, ?iterable $filters = null): DataCollection {
	function getAll(?iterable $filters = null): mixed {
		if(!($q = $this->query($filters))){
			return null;
		}

		if($this instanceof ORMInterface){
			$col = new ($this->getCollectionType());
		} else {
			$col = [];
		}
		// while($r = $this->get_trans()->fetch_object($q)){
		while($r = $this->fetch($q)){
			// $ret[] = $r;
			$col[] = $r;
			// $ret[] = $r;
			// if($this->Table instanceof DataMapperInterface){
				// $ret[] = $this->Table->fromDBObject($r);
			// } else {
			// 	$ret[] = $r;
			// }
		}

		return $col;
		// return $ret;
		// return new ($this->getCollectionType())($ret);
		// return $this->get_trans()->fetch_all($q);
	}

	# TODO: QueryClass
	function fetch(): mixed {
		$q = func_get_arg(0);
		// return $this->get_trans()->fetch_assoc($q);
		// return $data ? ($this->getDataType())::fromDBObject($data) : null;
		if($data = $this->get_trans()->fetch_object($q)){
			if($this instanceof ORMInterface){
				return $this->fromDBObject($data);
				// return ($this->getDataType())::fromDBObject($data);
			} else {
				return $data;
			}
		} else {
			return null;
		}
		// return $data ? ($this->getDataType())::fromDBObject($data) : null;
		// return (new $this->getDataType())($this->fromDBObject($this->get_trans()->fetch_object($q)));
		// if($this->Table instanceof DataMapperInterface){
			// return $this->Table->fromDBObject($this->get_trans()->fetch_object($q));
		// } else {
		// 	return $this->get_trans()->fetch_object($q);
		// }
	}

	function getSingle(?iterable $filters = null): mixed {
		if($q = $this->query($filters)){
			return $this->fetch($q);
			// if(isset($this->Mapper)){
			// 	return $this->Mapper->fromDBObject($this->get_trans()->fetch_object($q));
			// } else {
			// 	return $this->get_trans()->fetch_object($q);
			// }
		}

		return null;
	}

	function query(?iterable $filters = null){
		return $this->get_trans()->query($this->set_filters($this->select(), $filters));
	}

	function count(?iterable $filters = null): int {
		$sql = $this->set_filters($this->select(), $filters)
		->ResetFields()
		->ResetOrderBy()
		->ResetJoinLast() // Reset LEFT JOINS
		->Select("COUNT(*) sk")
		->Rows(1);

		return (int)($this->get_trans()->execute_single($sql)['sk']??0);
	}

	# TODO: insert un update
	function save(array|object $DATA){
		return $this->_insert_query($DATA, true);
		// return $this->get_trans()->save($DATA, $this->Table);
	}

	function update($ID, array|object $DATA){
		return $this->get_trans()->update($ID, $DATA, $this->Table);
	}

	function insert(array|object $DATA){
		return $this->get_trans()->insert($DATA, $this->Table);
	}

	private function _insert_query(array|object $DATA, $update = false): mixed {
		$PK = $this->getPK();
		$TableName = $this->getTableName();
		$PK_fields_str = is_array($PK) ? join(",", $PK) : $PK;

		if($this instanceof ORMInterface){
			$sql_fields = $this->get_sql_fields($this->toDBObject($DATA));
		} else {
			$sql_fields = $this->get_sql_fields($DATA);
		}

		$sql = (new Insert)
		->Into($TableName)
		->Values($sql_fields);

		if($update && $PK_fields_str){
			$sql->Update()->after("values", "matching", "MATCHING ($PK_fields_str)");
		}

		# TODO: refactor out
		$sql->after("values", "returning", "RETURNING $PK_fields_str");

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

	# TODO: refactor out, rename
	private function get_sql_fields(object|array $DATA){
		$PK = $this->getPK();
		if(is_array($PK)){
		} else {
			if(prop_exists($DATA, $PK)){
				$DATA[$PK] = get_prop($DATA, $PK);
			} else {
				if($Gen = $this->getGen()){
					set_prop($DATA, $PK, function() use ($Gen) {
						return "NEXT VALUE FOR $Gen";
					});
				}
			}
		}

		return $DATA;
	}


	function delete($ID){
		// $ID = func_get_arg(0);
		# TODO: multi field PK
		# TODO: dqdp\SQL\Statement
		$prep = $this->get_trans()->prepare("DELETE FROM $this->Table WHERE $this->PK = ?");
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

	protected function set_default_filters(Statement $sql, $DATA, array $defaults, $prefix = null): Statement {
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($defaults as $field=>$value){
			if(is_int($field)){
				$sql->Where($value);
			} elseif($DATA->exists($field)){
				if(!is_null($DATA->{$field})){
					# TODO: f-ija, kā build_sql
					$sql->Where(["$prefix$field = ?", $DATA->{$field}]);
				}
			} else {
				$sql->Where(["$prefix$field = ?", $value]);
			}
		}

		return $sql;
	}

	# TODO: abstract out filters funkcionālo daļu
	# TODO: uz Select???
	# TODO: vai vispār vajag atdalīt NULL filters? Varbūt visiem vajag NULL check?
	protected function set_null_filters(Statement $sql, $DATA, array $fields, string $prefix = null): Statement {
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($fields as $k){
			if($DATA->exists($k)){
				if(is_null($DATA->{$k})){
					$sql->Where(["$prefix$k IS NULL"]);
				} else {
					# TODO: f-ija, kā build_sql
					$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
				}
			}
		}

		return $sql;
	}

	protected function set_non_null_filters(Statement $sql, $DATA, array $fields, string $prefix = null): Statement {
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($fields as $k){
			if($DATA->exists($k)){
				# TODO: f-ija, kā build_sql
				$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
			}
		}

		return $sql;
	}

	protected function set_field_filters(Statement $sql, $DATA, array $fields, string $prefix = null): Statement {
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($fields as $k){
			if($DATA->$k){
				# TODO: f-ija, kā build_sql
				$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
			}
		}

		return $sql;
	}

	protected function set_filters(Statement $sql, ?iterable $filters = null): Statement {
		$filters = eoe($filters);
		if($this->PK){
			if(is_array($this->PK)){
			} else {
				//if($filters->exists($this->PK) && is_empty($filters->{$this->PK})){
				if($filters->exists($this->PK) && is_null($filters->{$this->PK})){
					trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
					return $sql;
				}
			}
		}

		if($this->PK){
			foreach(array_enfold($this->PK) as $k){
				if($filters->exists($k) && !is_null($filters->{$k})){
					$sql->Where(["$this->Table.$k = ?", $filters->{$k}]);
				}
			}
		}

		# TODO: multi field PK
		if($this->PK && !is_array($this->PK)){
			$k = $this->PK."S";
			if($filters->exists($k)){
				if(is_array($filters->{$k})){
					$IDS = $filters->{$k};
				} elseif(is_string($filters->{$k})){
					$IDS = explode(',',$filters->{$k});
				} else {
					trigger_error("Illegal multiple PRIMARY KEY value for $this->PKS", E_USER_ERROR);
				}
				$sql->Where(qb_filter_in("$this->Table.{$this->PK}", $IDS));
			}
		}

		# TODO: unify
		$Order = $filters->order_by??($filters->ORDER_BY??'');
		if($Order){
			$sql->ResetOrderBy()->OrderBy($Order);
		}

		if($filters->limit){
			$sql->Rows($filters->limit);
		}

		if($filters->rows){
			$sql->Rows($filters->rows);
		}

		if($filters->offset){
			$sql->Offset($filters->offset);
		}

		if($filters->fields){
			if(is_array($filters->fields)){
				$sql->ResetFields()->Select(join(", ", $filters->fields));
			} else {
				$sql->ResetFields()->Select($filters->fields);
			}
		}

		return $sql;
	}
}
