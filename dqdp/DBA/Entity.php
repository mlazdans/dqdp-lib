<?php

declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\SQL\Select;
use dqdp\SQL\Statement;

abstract class Entity implements EntityInterface {
	public AbstractTable $Table;
	protected AbstractDBA $dba;
	// TODO: get rid off
	protected string $tableName;
	protected $PK;

	function getTable() {
		return $this->Table;
	}

	function __construct(){
		$this->tableName = $this->Table->getName();
		$this->PK = $this->Table->getPK();
	}

	protected function select(): Select {
		return (new Select("$this->tableName.*"))->From($this->tableName);
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
		return $this->get_trans()->Query($this->set_filters($this->select(), $filters));
	}

	function save(iterable $DATA){
		return $this->get_trans()->save($DATA, $this->getTable());
	}

	// function save(iterable $DATA){
	// 	$sql_fields = (array)merge_only($this->Table->getFields(), $DATA);

	// 	if(!is_array($this->PK)){
	// 		$PK_val = get_prop($DATA, $this->PK);
	// 		if(is_null($PK_val)){
	// 			if($this->lex == 'fbird'){
	// 				if($Gen = $this->Table->getGen()){
	// 					$sql_fields[$this->PK] = function() use ($Gen) {
	// 						return "NEXT VALUE FOR $Gen";
	// 					};
	// 				}
	// 			} elseif($this->lex == 'mysql'){
	// 			}
	// 		} else {
	// 			$sql_fields[$this->PK] = $PK_val;
	// 		}
	// 	}

	// 	$sql = (new Insert)->Into($this->tableName)
	// 		->Values($sql_fields)
	// 		->Update();

	// 	if($this->lex == 'fbird'){
	// 		$PK_fields_str = is_array($this->PK) ? join(",", $this->PK) : $this->PK;
	// 		$sql->after("values", "matching", "MATCHING ($PK_fields_str)")
	// 			->after("values", "returning", "RETURNING $PK_fields_str");
	// 	}

	// 	sqlr($sql);
	// 	if($q = $this->get_trans()->query($sql)){
	// 		if($this->lex == 'fbird'){
	// 			$retPK = $this->get_trans()->fetch($q);
	// 			if(is_array($this->PK)){
	// 				return $retPK;
	// 			} else {
	// 				return get_prop($retPK, $this->PK);
	// 			}
	// 		}

	// 		if($this->lex == 'mysql'){
	// 			if(is_array($this->PK)){
	// 				foreach($this->PK as $k){
	// 					$ret[] = get_prop($DATA, $k);
	// 				}
	// 				return $ret??[];
	// 			} else {
	// 				if(empty($sql_fields[$this->PK])){
	// 					return mysql_last_id($this->get_trans());
	// 				} else {
	// 					return $sql_fields[$this->PK];
	// 				}
	// 			}
	// 		}
	// 	} else {
	// 		return false;
	// 	}
	// }

	function delete(){
		$ID = func_get_arg(0);
		# TODO: multi field PK
		# TODO: dqdp\SQL\Statement
		$prep = $this->get_trans()->prepare("DELETE FROM $this->tableName WHERE $this->PK = ?");
		$ret = true;
		foreach(array_enfold($ID) as $id){
			$ret = $ret && $this->get_trans()->execute_prepared($prep, $id);
		}

		return $ret;
		// return $this->ids_process("DELETE FROM $this->tableName WHERE $this->PK = ?", ...func_get_args());
	}

	function set_trans(AbstractDBA $dba){
		$this->dba = $dba;

		// if($dba instanceof IBase){
		// 	$this->lex = 'fbird';
		// 	// if(!is_array($this->PK)){
		// 	// 	$this->PK = strtoupper($this->PK);
		// 	// }
		// } elseif($dba instanceof MySQL_PDO){
		// 	$this->lex = 'mysql';
		// }

		return $this;
	}

	function get_trans(): AbstractDBA {
		return $this->dba;
	}

	protected function set_default_filters(Statement $sql, $DATA, array $defaults, $prefix = null): Statement {
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->tableName.";
		}

		foreach($defaults as $field=>$value){
			if($DATA->exists($field)){
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

	# TODO: abstract out funkcionālo daļu
	# TODO: uz Select???
	protected function set_null_filters(Statement $sql, $DATA, array $fields, string $prefix = null): Statement {
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->tableName.";
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
			$prefix = "$this->tableName.";
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
			$prefix = "$this->tableName.";
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
				$sql->Where(["$this->tableName.$k = ?", $filters->{$k}]);
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
				$sql->Where(qb_filter_in("$this->tableName.{$this->PK}", $IDS));
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

	// function fetch($q){
	// 	return $this->get_trans()->fetch($q);
	// }

	# TODO: savest kārtībā
	// protected function ids_process(...$args){
	// 	$sql = array_shift($args);
	// 	$IDS = array_shift($args);
	// 	if(!is_array($IDS)){
	// 		$IDS = [$IDS];
	// 	}

	// 	if(!($smt = $this->get_trans()->prepare($sql))){
	// 		return false;
	// 	}

	// 	$ret = true;
	// 	foreach($IDS as $ID){
	// 		$ret = $ret && $this->get_trans()->execute($smt, [$ID]);
	// 		// $params = array_merge([$smt], $args, [$ID]);
	// 		// $ret = $ret && call_user_func_array('ibase_execute', $params);
	// 	}
	// 	return $ret;
	// }

	// # TODO: šiem trans() te nevajadzētu būt?
	// function new_trans(){
	// 	$this->dba = $this->dba->trans();

	// 	return $this;
	// }

	// function commit(...$args){
	// 	return $this->dba->commit(...$args);
	// }

	// function rollback(...$args){
	// 	return $this->dba->rollback(...$args);
	// }
	###
}
