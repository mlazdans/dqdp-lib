<?php

declare(strict_types = 1);

namespace dqdp\DBA\driver;

use dqdp\DBA\AbstractDBA;
use dqdp\DBA\AbstractTable;
use dqdp\SQL\Insert;

require_once('ibaselib.php');

class IBase extends AbstractDBA
{
	var $conn;
	var $tr;

	static public $FETCH_FLAGS = IBASE_TEXT;

	function connect_params($params){
		$database = $params['database'] ?? '';
		$username = $params['username'] ?? '';
		$password = $params['password'] ?? '';
		$charset = $params['charset'] ?? 'utf8';
		$buffers = $params['buffers'] ?? 0;
		$dialect = $params['dialect'] ?? 0;
		$role = $params['role'] ?? '';

		return $this->connect($database, $username, $password, $charset, $buffers, $dialect, $role);
	}

	function connect(){
		$this->conn = ibase_connect(...func_get_args());

		return $this;
	}

	private function __execute($f, ...$args){
		$q = $this->query(...$args);

		if($q && is_resource($q)){
			$data = $this->$f($q);
		}

		return isset($data) ? $data : $q;
	}

	function execute(){
		return $this->__execute("fetch_all", ...func_get_args());
	}

	function execute_single(){
		return $this->__execute("fetch", ...func_get_args());
	}

	function query() {
		$args = func_get_args();

		if($this->is_dqdp_statement($args)){
			$q = ibase_query($this->tr??$this->conn, (string)$args[0], ...$args[0]->vars());
			// if(!$q){
			// 	sqlr($args[0]);
			// }
		} elseif((count($args) == 2) && is_resource($args[0]) && is_array($args[1])) {
			//$q = ibase_execute(...$args);
			$q = ibase_execute($args[0], ...$args[1]);
			// if(!$q){
			// 	sqlr($args[0]);
			// 	printr(...$args[1]);
			// }
			//$q = ibase_execute($this->tr??$this->conn, $args[0], ...$args[1]);
		} elseif((count($args) == 2) && is_array($args[1])) {
			//printr($args);
			if(($q2 = $this->prepare($args[0])) && ($q = ibase_execute($q2, ...$args[1]))){
			}
			// if(!$q){
			// 	printr(...$args[1]);
			// }
			//$q = ibase_query($this->tr??$this->conn, $args[0], ...$args[1]);
		} else {
			$q = ibase_query($this->tr??$this->conn, ...$args);
			// if(!$q){
			// 	printr($args);
			// }
		}

		// if($this->is_dqdp_statement($args)){
		// 	$q = ibase_query($this->tr??$this->conn, $args[0], ...$args[0]->vars());
		// } elseif(is_resource($args[0])) {
		// 	$q = ibase_execute(...$args);
		// 	//$q = ibase_execute($args[0], ...$args[1]);
		// } elseif(count($args) == 2) {
		// 	$q = ibase_query($this->tr??$this->conn, $args[0], ...$args[1]);
		// } else {
		// 	$q = ibase_query($this->tr??$this->conn, ...$args);
		// }

		return $q;
	}

	private function __fetch(callable $func, ...$args){
		if(!isset($args[1])){
			$args[1] = self::$FETCH_FLAGS;
		}

		// if($d = $func(...$args)){
		// 	if(is_array($d)){
		// 		return array_change_key_case($d, CASE_LOWER);
		// 	}
		// }

		// return $d;
		return $func(...$args);
	}

	function fetch_assoc(){
		return $this->__fetch('ibase_fetch_assoc', ...func_get_args());
	}

	function fetch_object(){
		return $this->__fetch('ibase_fetch_object', ...func_get_args());
	}

	function trans(){
		$tr = ibase_trans($this->conn, ...func_get_args());

		$o = clone $this;
		$o->tr = $tr;

		return $o;
	}

	function commit(): bool {
		$ret = ibase_commit($this->tr);
		$this->tr = null;

		return $ret;
	}

	function rollback(): bool {
		$ret = ibase_rollback($this->tr);
		$this->tr = null;

		return $ret;
	}

	function affected_rows(): int {
		return ibase_affected_rows($this->tr??$this->conn);
	}

	function close(): bool {
		return ibase_close($this->conn);
	}

	function prepare(){
		$args = func_get_args();

		if($this->is_dqdp_statement($args)){
			return ibase_prepare($this->tr??$this->conn, (string)$args[0]);
		}

		return ibase_prepare($this->tr??$this->conn, ...$args);
	}

	function escape($v): string {
		return ibase_escape($v);
	}

	function save(iterable $DATA, AbstractTable $Table){
		$sql_fields = (array)merge_only($Table->getFields(), $DATA);

		$PK = $Table->getPK();
		if(is_array($PK)){
		} else {
			// $PK = strtolower($PK);
			$PK_val = get_prop($DATA, $PK);
			if(is_null($PK_val)){
				if($Gen = $Table->getGen()){
					$sql_fields[$PK] = function() use ($Gen) {
						return "NEXT VALUE FOR $Gen";
					};
				}
			} else {
				$sql_fields[$PK] = $PK_val;
			}
		}

		$sql = (new Insert)
		->Into($Table->getName())
		->Values($sql_fields);
		// ->Update();

		$PK_fields_str = is_array($PK) ? join(",", $PK) : $PK;

		if(!is_null($PK_val)){
			$sql->Update()->after("values", "matching", "MATCHING ($PK_fields_str)");
		}

		$sql
		// ->after("values", "matching", "MATCHING ($PK_fields_str)")
		->after("values", "returning", "RETURNING $PK_fields_str");

		if($q = $this->query($sql)){
			$retPK = $this->fetch($q);
			if(is_array($PK)){
				return $retPK;
			} else {
				return get_prop($retPK, $PK);
			}
		} else {
			return false;
		}
	}
}
