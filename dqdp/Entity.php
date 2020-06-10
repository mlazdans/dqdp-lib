<?php

namespace dqdp;

use dqdp\SQL\Select;
use TypeError;

class Entity {
	var $Table;
	var $PK;
	protected $dba;

	// function __construct(DBLayer $dba){
	// 	$this->dba = $dba;
	// }

	//abstract function fetch();
	// abstract function search();
	// function insert();
	// function update();
	// abstract function save();
	// abstract function delete($IDS);

	// abstract function new_trans();
	// abstract function commit();
	// abstract function rollback();

	function select(){
		return (new Select("*"))->From($this->Table);
	}

	function search($params = null){
		$params = eoe($params);
		$sq = $this->set_filters($this->select(), $params);
		$q = $this->get_trans()->Query($sq);
		//sqlr($sq, $q);
		// die;
		return $q;
	}

	function fetch(...$args){
		return $this->get_trans()->fetch(...$args);
	}

	function fetch_all(...$args){
		while($r = $this->fetch(...$args)){
			$ret[] = $r;
		}
		return $ret??[];
	}

	function get($ID, $params = null){
		$params = eoe($params);
		$params->{$this->PK} = $ID;
		return $this->get_single($params);
	}

	function get_all($params = null){
		if($q = $this->search($params)){
			return $this->fetch_all($q);
		}

		return [];
	}

	function get_single($params = null){
		$params = eo($params);
		if($q = $this->search($params)){
			return $this->fetch($q);
		}
	}

	function set_default_filters(Select $sql, $DATA, $fields, $prefix = ''){
		$DATA = eoe($DATA);
		foreach($fields as $field=>$default){
			if($DATA->isset($field)){
				if(!is_null($DATA->{$field})){
					$sql->Where(["$field = ?", $DATA->{$field}]);
				}
			} else {
				$sql->Where(["$prefix$field = ?", $default]);
			}
		}

		return $sql;
	}

	function set_null_filters(Select $sql, $DATA, $fields, $prefix = ''){
		$DATA = eoe($DATA);
		$fields = array_wrap($fields);
		foreach($fields as $k){
			if($DATA->isset($k)){
				if(is_null($DATA->{$k})){
					$sql->Where(["$prefix$k IS NULL"]);
				} else {
					$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
				}
			}
		}

		return $sql;
	}

	function set_filters(Select $sql, $DATA = null){
		if(is_array($this->PK)){
		} else {
			if($DATA->isset($this->PK) && is_empty($DATA->{$this->PK})){
				trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
				return $sql;
			}
		}

		$pks = array_wrap($this->PK);
		foreach($pks as $k){
			if($DATA->{$k}){
				$sql->Where(["$this->Table.$k = ?", $DATA->{$k}]);
			}
		}

		# TODO: multi field PK
		if(!is_array($this->PK)){
			$k = $this->PK."S";
			if($DATA->isset($k)){
				if(is_array($DATA->{$k})){
					$IDS = $DATA->{$k};
				} elseif(is_string($DATA->{$k})){
					$IDS = explode(',',$DATA->{$k});
				} else {
					trigger_error("Illegal multiple PRIMARY KEY value for $this->PKS", E_USER_ERROR);
				}
				$sql->Where(sql_create_int_filter("$this->Table.{$this->PK}", $IDS));
			}
		}

		$Order = $DATA->order_by??($DATA->ORDER_BY??'');
		if($Order){
			$sql->ResetOrderBy()->OrderBy($Order);
		}

		if($DATA->fields){
			if(is_array($DATA->fields)){
				$sql->ResetSelect()->Select(join(", ", $DATA->fields));
			} else {
				$sql->ResetSelect()->Select($DATA->fields);
			}
		}

		/*
		if(!isset($DATA->limit)){
			if($DATA->page && $DATA->items_per_page){
				$DATA->limit = sprintf("%d,%d", ($DATA->page - 1) * $DATA->items_per_page, $DATA->items_per_page);
			}
		}

		if($DATA->limit){
			$sql->limit($DATA->limit);
		}
		*/

		return $sql;
	}

	function save(){
		return $this->get_trans()->insert_update($this, ...func_get_args());
	}

	function delete(){
		# TODO: multi field PK
		return $this->ids_process("DELETE FROM $this->Table WHERE $this->PK = ?", ...func_get_args());
	}

	protected function ids_process(...$args){
		$sql = array_shift($args);
		$IDS = array_shift($args);
		if(!is_array($IDS)){
			$IDS = [$IDS];
		}

		if(!($smt = $this->get_trans()->prepare($sql))){
			return false;
		}

		$ret = true;
		foreach($IDS as $ID){
			$ret = $ret && $this->get_trans()->execute($smt, $ID);
			// $params = array_merge([$smt], $args, [$ID]);
			// $ret = $ret && call_user_func_array('ibase_execute', $params);
		}
		return $ret;
	}

	// function set_trans($tr){
	// 	if($tr instanceof \dqdp\Entity\Entity){
	// 		$this->TR = $tr->get_trans();
	// 	} elseif($tr) {
	// 		$this->TR = $tr;
	// 	}

	// 	return $this;
	// }
	// Uncaught Exception(TypeError): Argument 1 passed to dqdp\Entity::set_trans() must be an instance of dqdp\DBLayer\DBLayer, resource given, called in D:\vienpatis\vienpatis\modules\journal\accounting.php on line 19 in dqdp\Entity.php on line 188
	// set_trans(...) in modules\journal\accounting.php on line 19
	// include(modules\journal\accounting.php) in dqdp\PHPTemplate.php on line 39
	// include(...) in modules\journal.php on line 34
	// include(modules\journal.php) in dqdp\PHPTemplate.php on line 39
	// include(...) in public\main.php on line 10

	function set_trans(...$args){
		list($dba) = $args;
		if(!($dba instanceof \dqdp\DBLayer\DBLayer)){
			//$msg = sprintf("Argument 1 passed to set_trans() must be an instance of dqdp\DBLayer\DBLayer, %s given, called in %s on line %d");
			$msg = sprintf("Argument 1 passed to set_trans() must be an instance of dqdp\DBLayer\DBLayer, %s given", gettype($dba));
			throw new TypeError($msg);
			//trigger_error("asdad", E_USER_ERROR);
		}
		//DBLayer $dba
		$this->dba = $dba;
		return $this;
	}

	function get_trans(){
		return $this->dba;
	}

	function new_trans(){
		$this->dba = $this->dba->trans();
		return $this;
	}

	function commit(...$args){
		return $this->dba->commit(...$args);
	}

	function rollback(...$args){
		return $this->dba->rollback(...$args);
	}

	// static function get_multi_filter($DATA, $k){
	// 	if(is_array($DATA->{$k})){
	// 		$IDS = $DATA->{$k};
	// 	} elseif(is_string($DATA->{$k})){
	// 		$IDS = explode(',',$DATA->{$k});
	// 	} else {
	// 		$IDS = [$IDS];
	// 	}

	// 	return $IDS;
	// }
}
