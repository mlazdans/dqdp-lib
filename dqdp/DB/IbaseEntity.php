<?php

namespace dqdp\DB;

class IbaseEntity extends Entity {
	var $Gen;

	function fetch(){
		return call_user_func_array('ibase_fetch', func_get_args());
	}

	function get_all_single($params = null){
		$params['FIRST'] = 1;
		if($data = $this->get_all($params)){
			return $data[0];
		} else {
			return false;
		}
	}

	function set_filters($sql, $DATA = null){
		parent::set_filters($sql, $DATA);

		if($DATA->ORDER_BY){
			$sql->ResetOrderBy()->OrderBy($DATA->ORDER_BY);
		}

		if($DATA->FIRST){
			$sql->first($DATA->FIRST);
		}

		return $sql;
	}

	function search($DATA = null){
		$DATA = eoe($DATA);
		$sql = $this->sql_select();
		if($this->set_filters($sql, $DATA)){
			return ibase_query_array($this->get_trans(), $sql, $sql->vars());
		}
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
					$Gen_field_str = "$this->PK,";
					$Gen_value_str = "NEXT VALUE FOR $this->Gen,";
				}
			} else {
				if(!in_array($this->PK, $fields)){
					$fields[] = $this->PK;
				}
			}
		}

		list($fieldSQL, $valuesSQL, $values) = build_sql($fields, $DATA, true);

		# TODO: ja vajadzēs nodalīt GRANT tiesības pa INSERT/UPDATE, tad jāatdala UPDATE OR INSERT atsevišķos pieprasījumos
		$sql = "UPDATE OR INSERT INTO $this->Table ($Gen_field_str$fieldSQL) VALUES ($Gen_value_str$valuesSQL) MATCHING ($PK_fields_str) RETURNING $PK_fields_str";

		if($q = ibase_query_array($this->get_trans(), $sql, $values)){
			$retPK = ibase_fetch($q);
			if(is_array($this->PK)){
				return $retPK;
			} else {
				return $retPK->{$this->PK};
			}
		} else {
			return false;
		}
	}

	function delete($IDS){
		return $this->ids_process("DELETE FROM $this->Table WHERE $this->PK = ?", $IDS);
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
