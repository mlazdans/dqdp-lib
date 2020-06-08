<?php

namespace dqdp\DB;

use dqdp\SQL\Select;

class MySQLEntity extends Entity {
	function fetch(...$args){
		return $this->get_trans()->fetch_assoc(...$args);
	}

	# TODO: limit - skip,first
	function get_all_single($params = null){
		$params = eo($params);
		$params->limit = 1;
		if($data = $this->get_all($params)){
			return $data[0];
		} else {
			return false;
		}
	}

	function set_filters(Select $sql, $DATA = null){
		parent::set_filters($sql, $DATA);

		if(!isset($DATA->limit)){
			if($DATA->page && $DATA->items_per_page){
				$DATA->limit = sprintf("%d,%d", ($DATA->page - 1) * $DATA->items_per_page, $DATA->items_per_page);
			}
		}

		if($DATA->limit){
			$sql->limit($DATA->limit);
		}

		return $sql;
	}

	function search($DATA = null){
		$DATA = eoe($DATA);
		return $this->get_trans()->Query($this->set_filters($this->sql_select(), $DATA));
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
		//printr($fields, $holders, $values);
		$fieldSQL = join(",", $fields);
		$insertSQL = join(",", $holders);

		$updateSQL = [];
		foreach($fields as $i=>$field){
			$updateSQL[] = "$field = ".$holders[$i];
		}
		$updateSQL = join(", ",$updateSQL);

		$sql = "INSERT INTO `$this->Table` ($Gen_field_str$fieldSQL) VALUES ($Gen_value_str$insertSQL) ON DUPLICATE KEY UPDATE $updateSQL";

		$res = $this->get_trans()->Execute($sql, array_merge($values, $values));
		if($res !== false){
			return empty($DATA->{$this->PK}) ? $this->get_trans()->last_id() : $DATA->{$this->PK};
		} else {
			return false;
		}
	}

	function delete($IDS){
		trigger_error("Not implemented", E_USER_ERROR);
		//return $this->ids_process("DELETE FROM $this->Table WHERE $this->PK = ?", $IDS);
	}

	function commit(){
		trigger_error("Not implemented", E_USER_ERROR);
		//return ibase_commit($this->get_trans());
	}

	function commit_ret(){
		trigger_error("Not implemented", E_USER_ERROR);
		//return ibase_commit_ret($this->get_trans());
	}

	function rollback(){
		trigger_error("Not implemented", E_USER_ERROR);
		//return ibase_rollback($this->get_trans());
	}

	function rollback_ret(){
		trigger_error("Not implemented", E_USER_ERROR);
		//return ibase_rollback_ret($this->get_trans());
	}

	function new_trans(){
		trigger_error("Not implemented", E_USER_ERROR);
		//return $this->set_trans(ibase_trans());
	}

	// protected function ids_process(...$args){
	// 	$sql = array_shift($args);
	// 	$IDS = array_shift($args);
	// 	if(!is_array($IDS)){
	// 		$IDS = [$IDS];
	// 	}

	// 	if(!($smt = ibase_prepare($this->get_trans(), $sql))){
	// 		return false;
	// 	}

	// 	$ret = true;
	// 	foreach($IDS as $ID){
	// 		$params = array_merge([$smt], $args, [$ID]);
	// 		$ret = $ret && call_user_func_array('ibase_execute', $params);
	// 	}
	// 	return $ret;
	// }
}
