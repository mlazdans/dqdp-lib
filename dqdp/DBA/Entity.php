<?php declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\SQL\Select;
use dqdp\SQL\Statement;

abstract class Entity implements EntityInterface {
	public Table $Table;
	protected DBA $dba;
	protected $PK;

	function getTable() {
		return $this->Table;
	}

	function __construct(){
		$this->PK = $this->Table->getPK();
	}

	protected function select(): Select {
		return (new Select("$this->Table.*"))->From($this->Table);
	}

	function get($ID, ?iterable $filters = null){
		$filters = eoe($filters);

		$filters->{$this->PK} = $ID;

		return $this->get_single($filters);
	}

	function get_all(?iterable $filters = null): array {
		if($q = $this->search($filters)){
			return $this->get_trans()->fetch_all($q);
		}
	}

	function get_single(?iterable $filters = null){
		if($q = $this->search($filters)){
			return $this->get_trans()->fetch($q);
		}
	}

	function search(?iterable $filters = null){
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
	function save(iterable $DATA){
		return $this->get_trans()->save($DATA, $this->getTable());
	}

	function update($ID, iterable $DATA){
		return $this->get_trans()->update($ID, $DATA, $this->getTable());
	}

	function insert(iterable $DATA){
		return $this->get_trans()->insert($DATA, $this->getTable());
	}

	function delete(){
		$ID = func_get_arg(0);
		# TODO: multi field PK
		# TODO: dqdp\SQL\Statement
		$prep = $this->get_trans()->prepare("DELETE FROM $this->Table WHERE $this->PK = ?");
		$ret = true;
		foreach(array_enfold($ID) as $id){
			$ret = $ret && $this->get_trans()->execute_prepared($prep, $id);
		}

		return $ret;
	}

	function set_trans(DBA $dba){
		$this->dba = $dba;

		return $this;
	}

	function get_trans(): DBA {
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
		if(is_array($this->PK)){
		} else {
			//if($filters->exists($this->PK) && is_empty($filters->{$this->PK})){
			if($filters->exists($this->PK) && is_null($filters->{$this->PK})){
				trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
				return $sql;
			}
		}

		foreach(array_enfold($this->PK) as $k){
			if($filters->exists($k) && !is_null($filters->{$k})){
				$sql->Where(["$this->Table.$k = ?", $filters->{$k}]);
			}
		}

		# TODO: multi field PK
		if(!is_array($this->PK)){
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
