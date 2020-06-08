<?php

namespace dqdp\DBLayer;

use PDO;
use PDOStatement;

class MySQL_PDO_Layer extends Layer
{
	var $conn;
	var $charset;

	function connect($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '', $int_port = 3306){
		$dsn = "mysql:dbname=$str_db_name;host=$str_db_host;port=$int_port";
		if($this->charset){
			$dsn .= ";charset=$this->charset";
		}

		try {
			$this->conn = new PDO($dsn, $str_db_user, $str_db_password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			//$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
			return $this->conn;
		} catch (PDOException $e) {
			user_error('Connection failed: ' . $e->getMessage());
			return false;
		}
	}

	function execute(...$args){
		$q = $this->Query(...$args);
		// if($this->is_dqdp_select($args)){
		// 	$q->execute($args[0]->vars());
		// }

		if($q && $q->columnCount()){
			$data = [];
			while($row = $this->fetch_assoc($q)){
				$data[] = $row;
			}
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return isset($data) ? $data : $q;
	}

	function query(...$args){
		if($this->is_dqdp_select($args)){
			if(($q = $this->prepare($args[0])) && $q->execute($args[0]->vars())){
				return $q;
			}
			return false;
		} elseif($args[0] instanceof PDOStatement) {
			if($args[0]->execute($args[1])){
				return $args[0];
			}
			return false;
		} elseif(count($args) == 2) {
			if(($q = $this->prepare($args[0])) && $q->execute($args[1])){
				return $q;
			}
			return false;
		}

		return $this->conn->query(...$args);
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

	function commit(){
		return $this->conn->Commit();
	}

	function rollback(){
		return $this->conn->Rollback();
	}

	function auto_commit(...$args){
		list($b) = $args;
		return $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $b);
	}

	function affected_rows(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function close(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function prepare(...$args){
		if($this->is_dqdp_select($args)){
			return $this->conn->prepare((string)$args[0]);
		}
		return $this->conn->prepare(...$args);
	}
}
