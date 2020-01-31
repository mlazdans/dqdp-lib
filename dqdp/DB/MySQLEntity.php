<?php

namespace dqdp\DB;

use dqdp\SQL\Select;

abstract class MySQLEntity implements Entity {
	var $Table;
	var $PK;

	protected $TR;

	function sql_select(){
		return (new Select("*"))->From($this->Table);
	}

	function fetch(){
		return call_user_func_array([$this->get_trans(), 'FetchAssoc'], func_get_args());
	}

	function fetch_all(){
		while($r = call_user_func_array([$this, 'fetch'], func_get_args())){
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

		return false;
	}

	function get_all($params = null){
		if($q = $this->search($params)){
			return $this->fetch_all($q);
		}

		return false;
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

	function set_default_filters($sql, $DATA, $fields, $prefix = ''){
		$DATA = eoe($DATA);
		foreach($fields as $field=>$default){
			if($DATA->isset($field)){
				if(!is_null($DATA->{$field}))$sql->Where(["`$field` = ?", $DATA->{$field}]);
			} else {
				$sql->Where(["$prefix$field = ?", $default]);
			}
		}
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
	}

	function set_filters($sql, $DATA = null){
		$DATA = eoe($DATA);
		//print "mysqlentity->set_filters()\n";
		if(is_array($this->PK)){
		} else {
			if($DATA->isset($this->PK) && is_empty($DATA->{$this->PK})){
				trigger_error("Illegal PRIMARY KEY value for ".$this->PK, E_USER_ERROR);
				return false;
			}
		}

		$pks = array_wrap($this->PK);
		foreach($pks as $k){
			if($DATA->{$k}){
				$sql->Where([$this->Table.".".$this->PK." = ?", $DATA->{$k}]);
			}
		}

		if(!is_array($this->PK)){
			$k = $this->PK."s";
			if($DATA->isset($k)){
				$IDS = $this->get_multi_filter($DATA, $k);
				call_user_func([$sql, 'Where'], sql_create_int_filter($this->Table.".".$this->PK, $IDS));
			}
		}

		if($DATA->order_by){
			$sql->ResetOrderBy()->OrderBy($DATA->order_by);
		}

		if($DATA->fields){
			if(is_array($DATA->fields)){
				$sql->ResetSelect()->Select(join(", ", $DATA->fields));
			} else {
				$sql->ResetSelect()->Select($DATA->fields);
			}
		}

		if(!isset($DATA->limit)){
			if($DATA->page && $DATA->items_per_page){
				$DATA->limit = sprintf("%d,%d", ($DATA->page - 1) * $DATA->items_per_page, $DATA->items_per_page);
			}
		}

		if($DATA->limit){
			$sql->limit($DATA->limit);
		}
	}

	function search($DATA = null){
		$DATA = eoe($DATA);
		$sql = $this->sql_select();
		$this->set_filters($sql, $DATA);
		// printr($DATA);
		// sqlr($sql);
		// printr($this);
		// die;
		//sqlr($sql);
		//return $this->get_trans()->PrepareAndExecute($sql, $sql->vars());
		$q = $this->get_trans()->Prepare($sql);
		return ($q ? $this->get_trans()->Execute($q, $sql->vars()) : false);
	}

	function save(){
		list($fields, $DATA) = func_get_args();

		$PK_fields_str = $this->PK;
		$Gen_value_str = $Gen_field_str = '';

		if(is_array($this->PK)){
			$PK_fields_str = join(",", $this->PK);
		} else {
			if(empty($DATA->{$this->PK})){
				if(isset($this->Gen)){
					$Gen_field_str = $this->PK.",";
					$Gen_value_str = "NULL,";
				}
			} else {
				if(!in_array($this->PK, $fields)){
					$fields[] = $this->PK;
				}
			}
		}

		//list($fieldSQL, $valuesSQL, $values, $fields) = build_sql($fields, $DATA, true);
		list($fields, $holders, $values) = build_sql_raw($fields, $DATA, true);
		$fieldSQL = join(",", $fields);
		$insertSQL = join(",", $holders);

		$updateSQL = [];
		foreach($fields as $i=>$field){
			$updateSQL[] = "$field = ".$holders[$i];
		}
		$updateSQL = join(", ",$updateSQL);


		$sql = "INSERT INTO `$this->Table` ($Gen_field_str$fieldSQL) VALUES ($Gen_value_str$insertSQL) ON DUPLICATE KEY UPDATE $updateSQL";

		$res = $this->get_trans()->PrepareAndExecute($sql, array_merge($values, $values));
		if($res !== false){
			return empty($DATA->{$this->PK}) ? $this->get_trans()->LastID() : $DATA->{$this->PK};
		} else {
			return false;
		}
	}

	function delete($IDS){
		return $this->ids_process("DELETE FROM $this->Table WHERE $this->PK = ?", $IDS);
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

	function commit(){
		return ibase_commit($this->get_trans());
	}

	function commit_ret(){
		return ibase_commit_ret($this->get_trans());
	}

	function rollback(){
		return ibase_rollback($this->get_trans());
	}

	function rollback_ret(){
		return ibase_rollback_ret($this->get_trans());
	}

	function new_trans(){
		return $this->set_trans(ibase_trans());
	}

	protected function ids_process(...$args){
		$sql = array_shift($args);
		$IDS = array_shift($args);
		if(!is_array($IDS)){
			$IDS = [$IDS];
		}

		if(!($smt = ibase_prepare($this->get_trans(), $sql))){
			return false;
		}

		$ret = true;
		foreach($IDS as $ID){
			$params = array_merge([$smt], $args, [$ID]);
			$ret = $ret && call_user_func_array('ibase_execute', $params);
		}
		return $ret;
	}
}
