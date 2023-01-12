<?php declare(strict_types = 1);

namespace dqdp\DBA\driver;

require_once('stdlib.php');

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\Types\IbaseConnectParams;
use Error;

require_once('ibaselib.php');

class IBase implements DBAInterface
{
	private $conn;
	private $tr;

	static public $FETCH_FLAGS = IBASE_TEXT;

	function __construct(private IbaseConnectParams $params){
	}

	function get_conn(): mixed {
		return $this->conn;
	}

	function connect(): static {
		$this->conn = ibase_connect(
			$this->params->database,
			$this->params->username,
			$this->params->password,
			$this->params->charset,
			$this->params->buffers,
			$this->params->dialect,
			$this->params->role,
			$this->params->sync
		);

		return $this;
	}

	// private function __execute($f, ...$args){
	// 	$q = $this->query(...$args);

	// 	if($q && is_resource($q)){
	// 		$data = $this->$f($q);
	// 	}

	// 	return isset($data) ? $data : $q;
	// }

	// function execute(){
	// 	return $this->__execute("fetch_all", ...func_get_args());
	// }

	// function execute_single(){
	// 	return $this->__execute("fetch_object", ...func_get_args());
	// }

	function execute_prepared(){
		return ibase_execute(...func_get_args());
	}

	function query() {
		$args = func_get_args();

		if(is_dqdp_statement($args)){
			# NOTE: ibase_query() fatals when more arguments specified with notice.
			# shutdown, error nor exception handlers get called
			# PHP Version 8.2.0
			# (Windows NT 10.0 build 19045 (Windows 10) AMD64)
			# CGI/FastCGI or CLI, API420220829,TS,VS16
			if($this->tr){
				$q = ibase_query($this->conn, $this->tr, (string)$args[0], ...$args[0]->getVars());
			} else {
				$q = ibase_query($this->conn, (string)$args[0], ...$args[0]->getVars());
			}

			if(!$q){
				sqlr($args[0]);
			}
		// } elseif((count($args) == 2) && is_resource($args[0]) && is_array($args[1])) {
		// 	//$q = ibase_execute(...$args);
		// 	$q = ibase_execute($args[0], ...$args[1]);
		// 	// if(!$q){
		// 	// 	sqlr($args[0]);
		// 	// 	printr(...$args[1]);
		// 	// }
		// 	//$q = ibase_execute($this->tr??$this->conn, $args[0], ...$args[1]);
		// } elseif((count($args) == 2) && is_array($args[1])) {
		// 	//printr($args);
		// 	if(($q2 = $this->prepare($args[0])) && ($q = ibase_execute($q2, ...$args[1]))){
		// 	}
		// 	// if(!$q){
		// 	// 	printr(...$args[1]);
		// 	// }
		// 	//$q = ibase_query($this->tr??$this->conn, $args[0], ...$args[1]);
		} else {
			if($this->tr){
				$q = ibase_query($this->conn, $this->tr, ...$args);
			} else {
				$q = ibase_query($this->conn, ...$args);
			}

			if(!$q){
				sqlr($args);
			}
		}

		return $q;
	}

	private function __fetch(callable $func, ...$args): mixed {
		if(!isset($args[1])){
			$args[1] = self::$FETCH_FLAGS;
		}

		// if($this->fetch_case === 'lower'){
		// 	if($d = $func(...$args)){
		// 		if(is_array($d)){
		// 			return array_change_key_case($d, CASE_LOWER);
		// 		}
		// 	}

		// 	return $d;
		// } else {
		// 	return $func(...$args);
		// }

		if($r = $func(...$args)){
			return $r;
		}

		return null;
	}

	function fetch_array(...$args): array|null {
		return $this->__fetch("ibase_fetch_row", ...$args);
	}

	function fetch_assoc(...$args): array|null {
		return $this->__fetch("ibase_fetch_assoc", ...$args);
	}

	function fetch_object(...$args): object|null {
		return $this->__fetch("ibase_fetch_object", ...$args);
	}

	function new_trans(): static {
		$this->tr = ibase_trans(...[...func_get_args(), $this->conn]);

		return $this;
	}

	function commit(): bool {
		$ret = ibase_commit($this->tr);
		$this->tr = null;

		return $ret;
	}

	function commit_ret(): bool {
		return ibase_commit_ret($this->tr);
	}

	function rollback(): bool {
		$ret = ibase_rollback($this->tr);
		$this->tr = null;

		return $ret;
	}

	function rollback_ret(): bool {
		return ibase_rollback_ret($this->tr);
	}

	function affected_rows(): int {
		return ibase_affected_rows($this->tr??$this->conn);
	}

	function close(): bool {
		return $this->conn ? ibase_close($this->conn) : false;
	}

	function prepare(){
		$args = func_get_args();

		if(is_dqdp_statement($args)){
			if($this->tr){
				return ibase_prepare($this->conn, $this->tr, (string)$args[0]);
			} else {
				return ibase_prepare($this->conn, (string)$args[0]);
			}
		}

		if($this->tr){
			return ibase_prepare($this->conn, $this->tr, ...$args);
		} else {
			return ibase_prepare($this->conn, ...$args);
		}
	}

	function escape($v): string {
		return ibase_escape($v);
	}

	function withNewTrans(callable $func, ...$args): mixed {
		$old_tr = $this->tr;
		$this->new_trans(...$args);
		try {
			if(!$this->tr){
				throw new Error("Could not initiate transaction");
			}

			if($result = $func($this)){
				$this->commit();
			}
		} finally {
			if(empty($result)){
				$this->rollback();
			}
			$this->tr = $old_tr;
		}

		return $result;
	}

}
