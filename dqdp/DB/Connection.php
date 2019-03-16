<?php

namespace dqdp\DB;

class Connection
{
	private $trans;
	private $prep;
	private $db;

	function __construct($db){
		$this->db = $db;
	}

	# query($sql, [$param1, $param2, ...]){
	function query(){
		$argv = func_get_args();
		$sql = (string)$argv[0];
		$values = $argv[1];
		if(
			array_unshift($values, $sql) &&
			array_unshift($values, $this->get_trans()) &&
			($q = call_user_func_array('ibase_query', $values))
		) {
			return $q;
		}
		return false;
	}

	function prepare($sql){
		return $this->prep = ibase_prepare($this->get_trans(), $sql);
	}

	function execute($values){
		if(
			$this->prep &&
			array_unshift($values, $this->prep) &&
			($q = call_user_func_array('ibase_execute', $values))
		) {
			return $q;
		}
		return false;
	}

	function fetch($q){
		return ibase_fetch_object($q, IBASE_TEXT);
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
		return $this->trans = ibase_trans($this->db);
	}

	function set_trans($tr){
		return $this->trans = $tr;
	}

	function get_trans(){
		return $this->trans ? $this->trans : $this->db;
	}

	function gen_id($gen, $inc = 1){
		return ibase_gen_id($gen, $inc, $this->get_trans());
	}
}
