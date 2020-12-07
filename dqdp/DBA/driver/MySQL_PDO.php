<?php

declare(strict_types = 1);

namespace dqdp\DBA\driver;

use dqdp\DBA\AbstractDBA;
use dqdp\DBA\AbstractTable;
use dqdp\DBA\DBAException;
use dqdp\SQL\Insert;
use Exception;
use PDO;
use PDOStatement;

class MySQL_PDO extends AbstractDBA
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

	function save(iterable $DATA, AbstractTable $Table){
		$sql_fields = (array)merge_only($Table->getFields(), $DATA);

		$PK = $Table->getPK();
		if(!is_array($PK)){
			$PK_val = get_prop($DATA, $PK);
			if(is_null($PK_val)){
			} else {
				$sql_fields[$PK] = $PK_val;
			}
		}

		$sql = (new Insert)->Into($Table->getName())
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
