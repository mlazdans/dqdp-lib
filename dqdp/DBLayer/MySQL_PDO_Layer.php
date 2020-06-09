<?php

namespace dqdp\DBLayer;

use Exception;
use PDO;
use PDOStatement;

class MySQL_PDO_Layer extends Layer
{
	var $conn;
	var $charset;
	protected $transactionCounter = 0;
	protected $row_count;

	protected function handle_err($e){
		if($this->use_exception){
			throw new DBException($e);
		} else {
			trigger_error($e->getMessage());
			return false;
		}
	}

	function connect($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '', $int_port = 3306){
		$dsn = "mysql:dbname=$str_db_name;host=$str_db_host;port=$int_port";
		if($this->charset){
			$dsn .= ";charset=$this->charset";
		}

		try {
			$this->conn = new PDO($dsn, $str_db_user, $str_db_password);
			//$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			//$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
			return $this->conn;
		} catch (Exception $e) {
			return $this->handle_err($e);
		}
	}

	function execute(...$args){
		$q = $this->query(...$args);
		if($q && $q->columnCount()){
			$data = [];
			while($row = $this->{$this->execute_fetch_function}($q)){
				$data[] = $row;
			}
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return isset($data) ? $data : $q;
	}

	protected function is_dqdp_select($args){
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Select;
	}

	function query(...$args){
		try {
			if($this->is_dqdp_select($args)){
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
			$this->row_count = $q->rowCount();
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

	function last_id(){
		return $this->conn->lastInsertId();
	}

	function trans(){
		return $this->conn->beginTransaction();
	}

	function commit() {
		return $this->conn->commit();
	}

	function rollback(){
		return $this->conn->rollBack();
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

	function auto_commit(...$args){
		list($b) = $args;
		return $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $b);
	}

	function affected_rows(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function close(){
		$this->conn = null;
	}

	function prepare(...$args){
		if($this->is_dqdp_select($args)){
			return $this->conn->prepare((string)$args[0]);
		}
		return $this->conn->prepare(...$args);
	}
}
