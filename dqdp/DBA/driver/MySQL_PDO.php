<?php

namespace dqdp\DBA;

use Exception;
use PDO;
use PDOStatement;

class MySQL_PDO extends \dqdp\DBA
{
	var $conn;
	protected $transactionCounter = 0;
	protected $row_count;

	protected function handle_err($e){
		if($this->use_exceptions){
			throw new DBAException($e);
		} else {
			trigger_error($e->getMessage());
			return false;
		}
	}

	function connect_params($params){
		$host = $params['host'] ?? 'localhost';
		$username = $params['username'] ?? '';
		$password = $params['password'] ?? '';
		$database = $params['database'] ?? '';
		$charset = $params['charset'] ?? 'utf8';
		$port = $params['port'] ?? 3306;

		return $this->connect($host, $username, $password, $database, $charset, $port);
	}

	function connect($host = null, $username = null, $password = null, $database = null, $charset = null, $port = null){
		$dsn = [];
		if($host)$dsn[]= "host=$host";
		if($database)$dsn[]= "dbname=$database";
		if($charset)$dsn[]= "charset=$charset";
		if($port)$dsn[]= "port=$port";

		try {
			$this->conn = new PDO("mysql:".join(";", $dsn), $username, $password);
			//$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			//$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

			return $this;
		} catch (Exception $e) {
			return $this->handle_err($e);
		}
	}

	function execute(...$args){
		$q = $this->query(...$args);
		if($q && $q->columnCount()){
			$data = $this->fetch_all($q);
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return isset($data) ? $data : $q;
	}

	function query(...$args){
		try {
			if($this->is_dqdp_statement($args)){
				if(($q = $this->prepare($args[0])) && $q->execute($args[0]->vars())){
					return $q;
				}
			} elseif($args[0] instanceof PDOStatement) {
				$q = $args[0];
				if($q->execute($args[1])){
					return $q;
				}
			} elseif(count($args) == 2) {
				if(($q = $this->prepare($args[0])) && $q->execute($args[1])){
					return $q;
				}
			}

			return $q = $this->conn->query(...$args);
		} catch (Exception $e) {
			return $this->handle_err($e);
		} finally {
			if(isset($q)){
				$this->row_count = $q->rowCount();
			}
		}
	}

	function fetch_assoc(...$args){
		list($q) = $args;

		return $q->Fetch(PDO::FETCH_ASSOC);
	}

	function fetch_object(...$args){
		list($q) = $args;

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

	function prepare(...$args){
		if($this->is_dqdp_statement($args)){
			return $this->conn->prepare((string)$args[0]);
		} else {
			return $this->conn->prepare(...$args);
		}
	}

	function escape($v): string {
		return trim($this->conn->quote($v), "'");
	}
}
