<?php

declare(strict_types = 1);

namespace dqdp\DBA\driver;

use dqdp\DBA\DBA;
use dqdp\DBA\Table;
use dqdp\DBA\DBAException;
use dqdp\SQL\Insert;
use PDO;
use PDOStatement;

class Firebird_PDO extends DBA
{
	var PDO $conn;
	protected $transactionCounter = 0;
	protected $row_count;

	function connect_params($params){
		$username = $params['username'] ?? '';
		$password = $params['password'] ?? '';
		$database = $params['database'] ?? '';
		$charset = $params['charset'] ?? 'utf8';
		$role = $params['role'] ?? '';

		return $this->connect($username, $password, $database, $charset, $role);
	}

	function connect($username = null, $password = null, $database = null, $charset = null, $role = null){
		$dsn = [];
		if($database)$dsn[]= "dbname=$database";
		if($charset)$dsn[]= "charset=$charset";
		if($role)$dsn[]= "role=$role";

		$this->conn = new PDO("firebird:".join(";", $dsn), $username, $password);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		// $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
		// $this->conn->exec("SET TRANSACTION READ ONLY ISOLATION LEVEL READ COMMITTED NO WAIT");

		return $this;
	}

	private function __execute($f, ...$args){
		$q = $this->query(...$args);
		if($q && $q->columnCount()){
			$data = $this->$f($q);
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return isset($data) ? $data : $q;
	}

	function execute(){
		return $this->__execute("fetch_all", ...func_get_args());
	}

	function execute_single(){
		return $this->__execute("fetch", ...func_get_args());
	}

	function query(){
		$args = func_get_args();

		try {
			if($this->is_dqdp_statement($args)){
				if($q = $this->prepare($args[0])){
					$q->execute($args[0]->vars());
				}
			} elseif($args[0] instanceof PDOStatement) {
				$q = $args[0];
				$q->execute($args[1]);
			} elseif(count($args) == 2) {
				if($q = $this->prepare($args[0])){
					$q->execute($args[1]);
				}
			} else {
				$q = $this->conn->query(...$args);
			}

			if($q){
				return $q;
			}

			throw new DBAException("Invalid query: $q->queryString");
		} finally {
			$this->row_count = $q->rowCount();
		}
	}

	function fetch_assoc(){
		list($q) = func_get_args();

		return $q->Fetch(PDO::FETCH_ASSOC);
	}

	function fetch_object(){
		list($q) = func_get_args();

		return $q->Fetch(PDO::FETCH_OBJ);
	}

	// function trans(){
	// 	if (!$this->transactionCounter++) {
	// 		return $this->conn->beginTransaction();
	// 	}
	// 	$this->execute('SAVEPOINT trans'.$this->transactionCounter);
	// 	return $this->transactionCounter >= 0;
	// }

	// function commit(){
	// 	if (!--$this->transactionCounter) {
	// 		return $this->conn->commit();
	// 	}
	// 	return $this->transactionCounter >= 0;
	// }

	// function rollback(){
	// 	if (--$this->transactionCounter) {
	// 		$this->execute('ROLLBACK TO trans'.($this->transactionCounter + 1));
	// 		return true;
	// 	}
	// 	return $this->conn->rollBack();
	// }

	# Broken
	function trans(){
		$this->conn->beginTransaction();
		$this->transactionCounter++;

		return $this;
	}

	function commit(): bool {
		return $this->conn->commit();
	}

	function rollback(): bool {
		return $this->conn->rollBack();
	}

	function auto_commit(...$args){
		list($b) = $args;

		return $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $b);
	}

	function affected_rows(): int {
		return $this->row_count;
	}

	function close(): bool {
		$this->conn = null;

		return true;
	}

	function prepare(){
		$args = func_get_args();

		if($this->is_dqdp_statement($args)){
			return $this->conn->prepare((string)$args[0]);
		} else {
			return $this->conn->prepare(...$args);
		}
	}

	function escape($v): string {
		return trim($this->conn->quote($v), "'");
	}

	function save(iterable $DATA, Table $Table){
		$sql_fields = (array)merge_only($Table->getFields(), $DATA);

		$PK = $Table->getPK();
		if(!is_array($PK)){
			$PK_val = get_prop($DATA, $PK);
			if(is_null($PK_val)){
			} else {
				$sql_fields[$PK] = $PK_val;
			}
		}

		$sql = (new Insert)
		->Into($Table->getName())
		->Values($sql_fields)
		->Update();

		if($this->query($sql)){
			if(is_array($PK)){
				foreach($PK as $k){
					$ret[] = get_prop($DATA, $k);
				}
				return $ret??[];
			} else {
				if(empty($sql_fields[$PK])){
					return $this->mysql_last_id();
				} else {
					return $sql_fields[$PK];
				}
			}
		} else {
			return false;
		}
	}

	private function mysql_last_id(){
		return get_prop($this->execute_single("SELECT LAST_INSERT_ID() AS last_id"), 'last_id');
	}
}
