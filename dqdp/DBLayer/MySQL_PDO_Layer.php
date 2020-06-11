<?php

namespace dqdp\DBLayer;

use dqdp\SQL\Insert;
use Exception;
use PDO;
use PDOStatement;

class MySQL_PDO_Layer extends DBLayer
{
	var $conn;
	protected $transactionCounter = 0;
	protected $row_count;

	protected function handle_err($e){
		if($this->use_exceptions){
			throw new DBException($e);
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
		//$dsn = "mysql:dbname=$database;host=$host;port=$port";
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
				// if($args[0] instanceof Insert){
				// 	sqlr($args);
				// 	$q = $this->prepare($args[0]);
				// 	$q2 = $q->execute($args[0]->vars());
				// 	dumpr($q, $q2);
				// 	die;
				// }
				//debug2file(printrr($args));
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

	function last_id(){
		return $this->conn->lastInsertId();
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
		return $this->conn->beginTransaction();
	}

	function commit() {
		return $this->conn->commit();
	}

	function rollback(){
		return $this->conn->rollBack();
	}

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
		if($this->is_dqdp_statement($args)){
			return $this->conn->prepare((string)$args[0]);
		}
		return $this->conn->prepare(...$args);
	}

	# TODO: bez Ent
	# TODO: pārvietot uz Entity
	/*
	function insert_update($Ent, $fields, $DATA){
		//list($Ent, $fields, $DATA) = func_get_args();

		$Gen_value_str = $Gen_field_str = '';

		if(is_array($Ent->PK)){
			//$PK_fields_str = join(",", $Ent->PK);
		} else {
			//$PK_fields_str = $Ent->PK;
			if(empty($DATA->{$Ent->PK})){
				// if(isset($this->Gen)){
				// 	$Gen_field_str = $Ent->PK.",";
				// 	$Gen_value_str = "NULL,";
				// }
			} else {
				if(!in_array($Ent->PK, $fields)){
					$fields[] = $Ent->PK;
				}
			}
		}

		//list($fieldSQL, $valuesSQL, $values, $fields) = build_sql($fields, $DATA, true);
		list($fields, $holders, $values) = build_sql_raw($fields, $DATA, true);
		//printr($fields, $holders, $values);
		$fieldSQL = join(",", $fields);
		$insertSQL = join(",", $holders);

		$updateSQL = [];
		foreach($fields as $i=>$field){
			$updateSQL[] = "$field = ".$holders[$i];
		}
		$updateSQL = join(", ",$updateSQL);

		$sql = "INSERT INTO `$Ent->Table` ($Gen_field_str$fieldSQL) VALUES ($Gen_value_str$insertSQL) ON DUPLICATE KEY UPDATE $updateSQL";

		$res = $this->query($sql, array_merge($values, $values));
		if($res !== false){
			if(is_array($Ent->PK)){
				foreach($Ent->PK as $k){
					$ret[] = $DATA->{$k};
				}
				return $ret??[];
			} else {
				return $this->last_id();
			}
			//return empty($DATA->{$Ent->PK}) ? $this->last_id() : $DATA->{$Ent->PK};
		} else {
			return false;
		}
	}
	*/
}
