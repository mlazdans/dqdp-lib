<?php

namespace dqdp;

use PDO;

// define('DB_MYSQL', 1);
// define('DB_PGSQL', 2);
// define('DB_MYSQLI', 3);
// define('DB_PDO_MYSQL', 4);

class DBLayer
{
	const MYSQL = 1;
	const PGSQL = 2;
	const MYSQLI = 3;
	const PDO_MYSQL = 4;

	var $db_type;
	var $db_info;
	var $conn;
	var $charset;
	var $suff;

	function __construct($db_type = DB_MYSQLI, $charset = 'utf8'){
		$this->db_info = 'none';
		$this->db_type = $db_type;
		$this->charset = $charset;
		if($this->db_type == DB_MYSQL){
			$this->suff = 'mysql';
		} elseif($this->db_type == DB_PGSQL){
			$this->suff = 'pgsql';
		} elseif($this->db_type == DB_MYSQLI){
			$this->suff = 'mysqli';
		} elseif($this->db_type == DB_PDO_MYSQL){
			$this->suff = 'pdo_mysql';
		}
	}

	function Connect($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '', $int_port = 0){
		if($this->db_type == DB_MYSQL){
			return $this->__connect_mysql($str_db_host, $str_db_user, $str_db_password, $str_db_name);
		} elseif($this->db_type == DB_PGSQL){
			return $this->__connect_pgsql($str_db_host, $str_db_user, $str_db_password, $str_db_name);
		} elseif($this->db_type == DB_MYSQLI){
			return $this->__connect_mysqli($str_db_host, $str_db_user, $str_db_password, $str_db_name, $int_port);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->__connect_pdo_mysql($str_db_host, $str_db_user, $str_db_password, $str_db_name, $int_port);
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function Execute(...$args){
		return call_user_func_array([$this, "__execute_$this->suff"], $args);
		/*
		$argc = func_num_args();
		if($argc == 2){
			list($q, $data) = func_get_args();
		} else {
			list($sql) = func_get_args();
			//debug2file("execute $sql");
		}

		if($this->db_type == DB_MYSQL){
			return $this->__execute_mysql($sql);
		} elseif($this->db_type == DB_PGSQL){
			return $this->__execute_pgsql($sql);
		} elseif($this->db_type == DB_MYSQLI){
			return $this->__execute_mysqli($sql);
		} elseif($this->db_type == DB_PDO_MYSQL){
			if($argc == 2){
				return $q->Execute($data) ? $q : false;
			} else {
				return $this->__execute_pdo_mysql($sql);
			}
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
		*/
	}

	function ExecuteSingle($sql){
		$data = $this->Execute($sql);
		if(is_array($data) && isset($data[0])){
			return $data[0];
		}

		return [];
	}

	function Now(){
		if($this->db_type == DB_MYSQL){
			return 'NOW()';
		} elseif($this->db_type == DB_PGSQL){
			return 'current_timestamp';
		} elseif($this->db_type == DB_MYSQLI){
			return 'NOW()';
		} elseif($this->db_type == DB_PDO_MYSQL){
			return 'NOW()';
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function Query($sql){
		if($this->db_type == DB_MYSQLI){
			return mysqli_query($this->conn, $sql);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->conn->Query($sql);
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function FetchAssoc($q){
		if($this->db_type == DB_MYSQLI){
			return mysqli_fetch_assoc($q);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $q->Fetch(PDO::FETCH_ASSOC);
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function FetchObject($q){
		if($this->db_type == DB_MYSQLI){
			return mysqli_fetch_object($q);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $q->Fetch(PDO::FETCH_OBJ);
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function LastID(){
		if($this->db_type == DB_MYSQLI){
			return mysqli_insert_id($this->conn);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->conn->lastInsertId();
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function BeginTransaction(){
		if($this->db_type == DB_MYSQLI){
			return mysqli_begin_transaction($this->conn);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->conn->beginTransaction();
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function Commit(){
		if($this->db_type == DB_MYSQLI){
			return mysqli_commit($this->conn);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->conn->Commit();
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function Rollback(){
		if($this->db_type == DB_MYSQLI){
			return mysqli_rollback($this->conn);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->conn->Rollback();
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function Quote($p){
		if($this->db_type == DB_MYSQLI){
			return $this->__mysqli_quote($p);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $p;
			//return $this->__pdo_mysql_quote($p);
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function AutoCommit($bool){
		if($this->db_type == DB_MYSQLI){
			return mysqli_autocommit($this->conn, $bool);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT,0);
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function AffectedRows(){
		if($this->db_type == DB_MYSQLI){
			return mysqli_affected_rows($this->conn);
		//} elseif($this->db_type == DB_PDO_MYSQL){
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function Close(){
		if($this->db_type == DB_MYSQLI){
			return mysqli_close($this->conn);
		//} elseif($this->db_type == DB_PDO_MYSQL){
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function Prepare($sql){
		//debug2file("prepare $sql");
		if($this->db_type == DB_MYSQLI){
			return mysqli_prepare($this->conn, $sql);
		} elseif($this->db_type == DB_PDO_MYSQL){
			return $this->conn->prepare($sql);
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	function PrepareAndExecute($sql, $data){
		if($this->db_type == DB_PDO_MYSQL){
			if($q = $this->Prepare($sql)){
				if($q->Execute($data)){
					if($q->columnCount()){
						return $q->FetchAll(PDO::FETCH_ASSOC);
					} else {
						return $q;
					}
					// printr($q->rowCount());
					// printr($q->columnCount());
					// dumpr($q);
					// die;
					// return $q->FetchAll(PDO::FETCH_ASSOC);
				}
			}
			return false;
		} else {
			trigger_error("Not implemented", E_USER_ERROR);
		}
	}

	protected function __connect_mysql($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '')
	{
		if(!extension_loaded('mysql'))
			user_error('Šī PHP versija neatbalsta MySQL funkcijas!', E_USER_ERROR);

		if( !($this->conn = mysql_connect($str_db_host, $str_db_user, $str_db_password)) )
			user_error(mysql_error(), E_USER_WARNING);

		if($str_db_name && $this->conn)
			if(mysql_select_db($str_db_name))
				$this->db_info = 'MySQL::'.$str_db_name;
			else
				user_error(mysql_error(), E_USER_WARNING);

		return $this->conn;
	} // __connect_mysql

	protected function __connect_pgsql($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '')
	{
		if(!extension_loaded('pgsql'))
			user_error('Šī PHP versija neatbalsta PostgreSQL funkcijas!', E_USER_ERROR);

		$str_connect = '';

		if($str_db_host)
			$str_connect .= ' host='.$str_db_host;

		if($str_db_user)
			$str_connect .= ' user='.$str_db_user;

		if($str_db_password)
			$str_connect .= ' password='.$str_db_password;

		if($str_db_name)
			$str_connect .= ' dbname='.$str_db_name;

		if( !($this->conn = pg_connect($str_connect)) )
			user_error(pg_last_error(), E_USER_WARNING);

		if($this->conn)
			$this->db_info = 'PgSQL::'.$str_db_name;

		return $this->conn;
	} // __connect_pgsql

	protected function __connect_mysqli($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '', $int_port = 3306)
	{
		if(!extension_loaded('mysqli'))
			user_error('Šī PHP versija neatbalsta MySQLi funkcijas!', E_USER_ERROR);

		if( !($this->conn = mysqli_connect($str_db_host, $str_db_user, $str_db_password, $str_db_name, $int_port)) )
			user_error(mysqli_connect_error(), E_USER_WARNING);

		if($str_db_name && $this->conn)
		{
			if(mysqli_select_db($this->conn, $str_db_name))
				$this->db_info = 'MySQLi::'.$str_db_name;
			else
				user_error(mysqli_error($this->conn), E_USER_WARNING);

			if($this->charset)
			{
				mysqli_set_charset($this->conn, $this->charset);
			}
		}

		$this->conn = $this->conn;

		return $this->conn;
	}

	protected function __connect_pdo_mysql($str_db_host = '', $str_db_user = '', $str_db_password = '', $str_db_name = '', $int_port = 3306){
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

	protected function __execute_mysql($str_sql){
		if( !($res_q = mysql_query($str_sql)) )
			user_error(mysql_error().($GLOBALS['sys_debug'] ? $str_sql : ''), E_USER_WARNING);

		$arr_data = array();

		if(is_resource($res_q)) {
			while($arr_row = mysql_fetch_array($res_q, MYSQL_ASSOC))
				$arr_data[] = $arr_row;
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return is_resource($res_q) ? $arr_data : $res_q;
	}

	protected function __execute_pgsql($str_sql)
	{
		if( !($res_q = pg_query($str_sql)) )
			user_error(pg_last_error(), E_USER_WARNING);

		$arr_data = array();

		if($res_q) {
			while($arr_row = pg_fetch_array($res_q))
				$arr_data[] = $arr_row;
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return is_resource($res_q) ? $arr_data : $res_q;
	} // __execute_pgsql

	protected function __execute_mysqli($str_sql)
	{
		if( !($res_q = mysqli_query($this->conn, $str_sql)) )
			user_error(mysqli_error($this->conn).($GLOBALS['sys_debug'] ? nl2br("\n$str_sql\n\n") : ''), E_USER_WARNING);

		$arr_data = array();
		if(is_object($res_q))
		{
			while($arr_row = mysqli_fetch_assoc($res_q))
			{
				$arr_data[] = $arr_row;
			}
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return is_object($res_q) ? $arr_data : $res_q;
	} // __execute_mysqli

	protected function __execute_pdo_mysql($sql){
		//if($sql instanceof $)
		//if(!($q = $this->conn->Query($sql))){
		if(!($q = $this->Query($sql))){
			$i = $this->conn->errorInfo();
			user_error($i[2].($GLOBALS['sys_debug'] ? nl2br("\n$sql\n\n") : ''), E_USER_WARNING);
			return false;
		}

		if($q->columnCount()){
			$data = [];
			while($row = $this->FetchAssoc($q)){
				$data[] = $row;
			}
			// $q->setFetchMode(PDO::FETCH_ASSOC);
			// $data = [];
			// foreach($q as $row){
			// 	$data[] = $row;
			// }
		}

		# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
		return isset($data) ? $data : $q;
	}

	protected function __mysqli_quote($data){
		return __object_map($data, function($item){
			return mysqli_real_escape_string($this->conn, $item);
		});
	}

	protected function __pdo_mysql_quote($data){
		return __object_map($data, function($item){
			return $this->conn->Quote($item);
		});
	}

	/***
	function Prepare($str_sql)
	{
		switch( $this->db_type ) {
			case DB_MYSQLI:
				$ret = mysqli_prepare($this->conn, $str_sql);
				if(!$ret)
				{
					user_error(mysqli_error($this->conn).($GLOBALS['sys_debug'] ? $str_sql : ''), E_USER_WARNING);
				}
				return $ret;
				break;
			default:
				return false;
				break;
		}
	} // Prepare

	function BindResult()
	{
		$args = func_get_args();
		if(count($args) < 2)
		{
			user_error("BindResult(): too few arguments", E_USER_ERROR);
			return;
		}

		$stmt = array_shift($args);
		$bind_params = '';
		$typecodes = '';
		foreach($params as $k=>$v)
		{
			$bind_params .= ', $params['.$k.']';
		}

		$f = '$ret = mysqli_stmt_bind_result($stmt'.$bind_params.');';

		switch( $this->db_type ) {
			case DB_MYSQLI:
				eval($f);
				return $ret;
				break;
			default:
				return false;
				break;
		}
	} // BindResult

	function BindParam()
	{
		$types = array(
			'integer'=>'i',
			'double'=>'d',
			'string'=>'s',
		);

		$args = func_get_args();
		if(count($args) < 2)
		{
			user_error("BindParam(): too few arguments", E_USER_ERROR);
			return;
		}

		$stmt = array_shift($args);
		$bind_params = '';
		$typecodes = '';
		foreach($params as $k=>$v)
		{
			$t = gettype($v);
			if(isset($types[$t]))
			{
				$typecodes .= $types[$t];
				$bind_params .= ', $params['.$k.']';
			}
		}

/
		foreach($args as $k=>$v)
		{
			$t = gettype($v);
			if(isset($types[$t]))
			{
				$typecodes .= $types[$t];
				$bind_params .= ', $args['.$k.']';
			}
		}
/
		$f = '$ret = mysqli_stmt_bind_param($stmt, $typecodes'.$bind_params.');';

		switch( $this->db_type ) {
			case DB_MYSQLI:
				eval($f);
				return $ret;
				break;
			default:
				return false;
				break;
		}
	} // Bind
*/

}
