<?php

namespace dqdp\DBLayer;

use PDO;

class MySQL_PDO_Layer implements Layer
{
	var $db_type;
	var $db_info;
	var $conn;
	var $charset;
	var $suff;

	function Connect($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '', $int_port = 0){
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

	function Execute(...$args){
		$q = $this->Query(...$args);
		if($this->argIsDqdpSelect($args)){
			$q->execute($args[0]->vars());
		}

		if($q && $q->columnCount()){
			$data = [];
			while($row = $this->FetchAssoc($q)){
				$data[] = $row;
			}
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return isset($data) ? $data : $q;
	}

	function argIsDqdpSelect($args){
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Select;
	}

	function ExecuteSingle(...$args){
		$data = $this->Execute(...$args);
		if(is_array($data) && isset($data[0])){
			return $data[0];
		}

		return [];
	}

	function Now(){
		return 'NOW()';
	}

	function Query(...$args){
		if($this->argIsDqdpSelect($args)){
			return $this->Prepare($args[0]);
		}
		return $this->conn->Query(...$args);
	}

	function FetchAssoc(...$args){
		list($q) = $args;
		return $q->Fetch(PDO::FETCH_ASSOC);
	}

	function FetchObject(...$args){
		list($q) = $args;
		return $q->Fetch(PDO::FETCH_OBJ);
	}

	function LastID(){
		return $this->conn->lastInsertId();
	}

	function BeginTransaction(){
		return $this->conn->beginTransaction();
	}

	function Commit(){
		return $this->conn->Commit();
	}

	function Rollback(){
		return $this->conn->Rollback();
	}

	function Quote(...$args){
		list($p) = $args;
		return $p;
		// return __object_map($data, function($item){
		// 	return $this->conn->Quote($item);
		// });
	}

	function AutoCommit(...$args){
		list($b) = $args;
		return $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $b);
	}

	function AffectedRows(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function Close(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function Prepare(...$args){
		if($this->argIsDqdpSelect($args)){
			return $this->conn->prepare($args[0]);
		}

		return $this->conn->prepare(...$args);
	}

	// function PrepareAndExecute(...$args){
	// 	trigger_error("Not implemented", E_USER_ERROR);
	// 	// list($sql, $data) = $args;
	// 	// if($q = $this->Prepare($sql)){
	// 	// 	if($q->Execute($data)){
	// 	// 		if($q->columnCount()){
	// 	// 			# TODO: kāpēc tieši assoc??
	// 	// 			return $q->FetchAll(PDO::FETCH_ASSOC);
	// 	// 		} else {
	// 	// 			return $q;
	// 	// 		}
	// 	// 	}
	// 	// }

	// 	// return false;
	// }
}
