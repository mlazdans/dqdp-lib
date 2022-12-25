<?php declare(strict_types = 1);

namespace dqdp\DBA\driver;

require_once('stdlib.php');

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\Types\IbaseConnectParams;

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
				$q = ibase_query($this->conn, $this->tr, (string)$args[0], ...$args[0]->vars());
			} else {
				$q = ibase_query($this->conn, (string)$args[0], ...$args[0]->vars());
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

	function new_trans(): self {
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

	// private function get_sql_fields(iterable $DATA, TableInterface $Table){
	// 	$sql_fields = (array)merge_only($Table->getFields(), $DATA);

	// 	$PK = $Table->getPK();
	// 	if(is_array($PK)){
	// 	} else {
	// 		if($this->fetch_case === 'lower'){
	// 			$PK = strtolower($PK);
	// 		}
	// 		$PK_val = get_prop($DATA, $PK);
	// 		if(is_null($PK_val)){
	// 			if($Gen = $Table->getGen()){
	// 				$sql_fields[$PK] = function() use ($Gen) {
	// 					return "NEXT VALUE FOR $Gen";
	// 				};
	// 			}
	// 		} else {
	// 			$sql_fields[$PK] = $PK_val;
	// 		}
	// 	}

	// 	return $sql_fields;
	// }

	# TODO:
	// UPDATE target [[AS] alias]
	// SET col = <value> [, col = <value> ...]
	// [WHERE {<search-conditions> | CURRENT OF cursorname}]
	// [PLAN <plan_items>]
	// [ORDER BY <sort_items>]
	// [ROWS m [TO n]]
	// [RETURNING <returning_list> [INTO <variables>]]
	// <returning_list> ::=
	// <ret_value> [[AS] ret_alias] [, <ret_value> [[AS] ret_alias] ...]
	// <ret_value> ::=
	// colname
	// | table_or_alias.colname
	// | NEW.colname
	// | OLD.colname
	// | <value>
	// <variables> ::= [:]varname [, [:]varname ...]
	// function update($ID, iterable $DATA, TableInterface $Table){
	// 	$sql_fields = $this->get_sql_fields($DATA, $Table);
	// 	$sql = (new Update($Table->getName()))
	// 		->Set($sql_fields)
	// 		->Where([$Table->getPK().' = ?', $ID])
	// 	;

	// 	return $this->query($sql);
	// }

	// Returns PK or null
	// private function _insert_query(iterable $DATA, TableInterface $Table, $update = false): mixed {
	// 	$PK = $Table->getPK();
	// 	$PK_fields_str = is_array($PK) ? join(",", $PK) : $PK;

	// 	$sql_fields = $this->get_sql_fields($DATA, $Table);

	// 	$sql = (new Insert)
	// 	->Into($Table->getName())
	// 	->Values($sql_fields);

	// 	if($update){
	// 		if(!is_null($sql_fields[$PK])){
	// 			$sql->Update()->after("values", "matching", "MATCHING ($PK_fields_str)");
	// 		}
	// 	}

	// 	$sql->after("values", "returning", "RETURNING $PK_fields_str");

	// 	if($q = $this->query($sql)){
	// 		$retPK = $this->fetch_object($q);
	// 		if(is_array($PK)){
	// 			return $retPK;
	// 		} else {
	// 			return get_prop($retPK, $PK);
	// 		}
	// 	}

	// 	return null;
	// }


	// function insert(iterable $DATA, TableInterface $Table): mixed {
	// 	return $this->_insert_query($DATA, $Table, false);
	// }

	// function save(iterable $DATA, TableInterface $Table): mixed {
	// 	return $this->_insert_query($DATA, $Table, true);
	// }

	function with_new_trans(callable $func, ...$args): mixed {
		// printr("New trans!", $this->tr);
		$tr = $this->tr;
		// if($this->tr){
		// 	trigger_error("Transaction in use, rollback", E_USER_WARNING);
		// 	$this->rollback();
		// }

		// $tr = $this->tr;

		$this->new_trans(...$args);
		try {
			// register_shutdown_function(function(DBA $db){
			// 	printr("Shut!", $this->tr);
			// 	if($db->tr){
			// 		printr("Shut - rollback!", $this->tr);
			// 		$db->rollback();
			// 	}
			// 	// dumpr($db);
			// }, $this);

			if($result = $func($this)){
				// printr("Commit!", $this->tr);
				$this->commit();
			// } else {
			// 	$this->rollback();
			}
		} finally {
			// printr("Finally!", $this->tr);
			if(empty($result)){
				// printr("Finally - rollback!", $this->tr);
				$this->rollback();
			}
			$this->tr = $tr;
		}

		return $result;
	}
}
