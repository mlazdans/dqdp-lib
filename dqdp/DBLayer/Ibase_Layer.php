<?php

namespace dqdp\DBLayer;

class Ibase_Layer extends DBLayer
{
	var $conn;
	var $tr;

	static public $FETCH_FLAGS = IBASE_TEXT;

	function connect_params($params){
		$database = $params['database'] ?? '';
		$username = $params['username'] ?? '';
		$password = $params['password'] ?? '';
		$charset = $params['charset'] ?? 'utf8';
		$buffers = $params['buffers'] ?? null;
		$dialect = $params['dialect'] ?? null;
		$role = $params['role'] ?? '';
		return $this->connect($database, $username, $password, $charset, $buffers, $dialect, $role);
	}

	function connect(...$args){
		// $argc = count($args);
		// if($argc == 1) {
		// 	if(is_object($args[0])){
		// 		$params = get_object_vars($args[0]);
		// 	} elseif(is_array($args[0])) {
		// 		$params = $args[0];
		// 	} else {
		// 		$params = $args;
		// 	}
		// } else {
		// 	$params = $args;
		// }
		$this->conn = ibase_connect(...$args);
		return $this->conn;
		//return ibase_connect($database, $username, $password, $charset, $buffers, $dialect, $role);
	}

	function execute(...$args){
		$q = $this->query(...$args);
		if($q && is_resource($q)){
			$data = [];
			while($row = $this->fetch($q)){
				$data[] = $row;
			}
		}

		return isset($data) ? $data : $q;
	}

	function query(...$args){
		if($this->is_dqdp_select($args)){
			return ibase_query($this->tr??$this->conn, $args[0], ...$args[0]->vars());
		} elseif(is_resource($args[0])) {
			return ibase_execute(...$args);
		} elseif(count($args) == 2) {
			return ibase_query($this->tr??$this->conn, $args[0], ...$args[1]);
			// if(($q = $this->prepare($args[0])) && $q->execute($args[1])){
			// 	return $q;
			// }
		}
		// if($this->is_dqdp_select($args)){
		// 	if(($q = $this->prepare($args[0])) && $q->execute($args[0]->vars())){
		// 		return $q;
		// 	}
		// 	return false;
		// } elseif($args[0] instanceof PDOStatement) {
		// 	if($args[0]->execute($args[1])){
		// 		return $args[0];
		// 	}
		// 	return false;
		// } elseif(count($args) == 2) {
		// 	if(($q = $this->prepare($args[0])) && $q->execute($args[1])){
		// 		return $q;
		// 	}
		// 	return false;
		// }

		return ibase_query($this->tr??$this->conn, ...$args);
	}

	private function __fetch($func, ...$args){
		if(!isset($args[1])){
			$args[1] = self::$FETCH_FLAGS;
		}
		return $func(...$args);
	}

	function fetch_assoc(...$args){
		return $this->__fetch('ibase_fetch_assoc', ...$args);
	}

	function fetch_object(...$args){
		return $this->__fetch('ibase_fetch_object', ...$args);
	}

	function trans(...$args){
		$tr = ibase_trans($this->conn, ...$args);
		$o = clone $this;
		$o->tr = $tr;
		return $o;
	}

	function commit(){
		return ibase_commit($this->tr);
	}

	function rollback(){
		printr($this);
		die;
		return ibase_rollback($this->tr);
	}

	function affected_rows(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function close(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function prepare(...$args){
		if($this->is_dqdp_select($args)){
			return ibase_prepare($this->tr??$this->conn, (string)$args[0]);
			//return $this->conn->prepare((string)$args[0]);
		}
		return ibase_prepare($this->tr??$this->conn, ...$args);
	}

	function save($Ent, $fields, $DATA){
		//list($fields, $DATA) = func_get_args();

		$Gen_value_str = $Gen_field_str = '';
		if(is_array($Ent->PK)){
			$PK_fields_str = join(",", $Ent->PK);
		} else {
			$PK_fields_str = $Ent->PK;
			if(empty($DATA->{$Ent->PK})){
				if(isset($Ent->Gen)){
					$Gen_field_str = "$Ent->PK,";
					$Gen_value_str = "NEXT VALUE FOR $Ent->Gen,";
				}
			} else {
				if(!in_array($Ent->PK, $fields)){
					$fields[] = $Ent->PK;
				}
			}
		}

		list($fieldSQL, $valuesSQL, $values) = build_sql($fields, $DATA, true);

		# TODO: ja vajadzēs nodalīt GRANT tiesības pa INSERT/UPDATE, tad jāatdala UPDATE OR INSERT atsevišķos pieprasījumos
		$sql = "UPDATE OR INSERT INTO $Ent->Table ($Gen_field_str$fieldSQL) VALUES ($Gen_value_str$valuesSQL) MATCHING ($PK_fields_str) RETURNING $PK_fields_str";

		if($q = $this->query($sql, $values)){
			$retPK = $this->fetch($q);
			if(is_array($Ent->PK)){
				return $retPK;
			} else {
				return $retPK->{$Ent->PK};
			}
		} else {
			return false;
		}
	}
}
