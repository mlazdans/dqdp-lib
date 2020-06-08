<?php

namespace dqdp\DB;

use dqdp\SQL\Select;

abstract class Entity {
	var $Table;
	var $PK;
	protected $TR;

	abstract function fetch();
	abstract function search();
	// function insert();
	// function update();
	abstract function save();
	abstract function delete($IDS);

	abstract function new_trans();
	abstract function commit();
	abstract function rollback();

	function sql_select(){
		return (new Select("*"))->From($this->Table);
	}

	function fetch_all(...$args){
		while($r = $this->fetch(...$args)){
			$ret[] = $r;
		}
		return $ret??[];
	}

	function get($ID, $params = []){
		$params = eoe($params);
		$params->{$this->PK} = $ID;
		if($q = $this->search($params)){
			return $this->fetch($q);
		}

		return [];
	}

	function get_all($params = null){
		if($q = $this->search($params)){
			return $this->fetch_all($q);
		}

		return [];
	}

	function set_default_filters($sql, $DATA, $fields, $prefix = ''){
		$DATA = eoe($DATA);
		foreach($fields as $field=>$default){
			if($DATA->isset($field)){
				if(!is_null($DATA->{$field}))$sql->Where(["`$field` = ?", $DATA->{$field}]);
			} else {
				$sql->Where(["$prefix$field = ?", $default]);
			}
		}

		return $sql;
	}

	function set_null_filters($sql, $DATA, $fields, $prefix = ''){
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

		if(!is_array($this->PK)){
			$k = $this->PK."S";
			if($DATA->isset($k)){
				if(is_array($DATA->{$k})){
					$IDS = $DATA->{$k};
				} elseif(is_string($DATA->{$k})){
					$IDS = explode(',',$DATA->{$k});
				} else {
					$IDS = [$IDS];
				}
				call_user_func([$sql, 'Where'], sql_create_int_filter("$this->Table.{$this->PK}", $IDS));
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

		return $sql;
	}

	function set_trans($tr){
		if($tr instanceof \dqdp\DB\Entity){
			$this->TR = $tr->get_trans();
		} elseif($tr) {
			$this->TR = $tr;
		}

		return $this;
	}

	function get_trans(){
		return $this->TR;
	}

	static function get_multi_filter($DATA, $k){
		if(is_array($DATA->{$k})){
			$IDS = $DATA->{$k};
		} elseif(is_string($DATA->{$k})){
			$IDS = explode(',',$DATA->{$k});
		} else {
			$IDS = [$IDS];
		}

		return $IDS;
	}
}
