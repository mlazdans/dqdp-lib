<?php

namespace dqdp\DBLayer;

use dqdp\SQL\Select;

class Ibase_Layer extends DBLayer
{
	var $conn;
	var $tr;

	static public $FETCH_FLAGS = IBASE_TEXT;

	function connect_params($params){
		$database = $params['database'] ?? '';
		$username = $params['username'] ?? '';
		$password = $params['password'] ?? '';
		$charset = $params['charset'] ?? 'utf8';
		$buffers = $params['buffers'] ?? null;
		$dialect = $params['dialect'] ?? null;
		$role = $params['role'] ?? '';
		return $this->connect($database, $username, $password, $charset, $buffers, $dialect, $role);
	}

	function connect(...$args){
		// $argc = count($args);
		// if($argc == 1) {
		// 	if(is_object($args[0])){
		// 		$params = get_object_vars($args[0]);
		// 	} elseif(is_array($args[0])) {
		// 		$params = $args[0];
		// 	} else {
		// 		$params = $args;
		// 	}
		// } else {
		// 	$params = $args;
		// }
		$this->conn = ibase_connect(...$args);
		return $this;
		//return ibase_connect($database, $username, $password, $charset, $buffers, $dialect, $role);
	}

	function execute(...$args){
		$q = $this->query(...$args);
		if($q && is_resource($q)){
			$data = $this->fetch_all($q);
			// $data = [];
			// while($row = $this->fetch($q)){
			// 	$data[] = $row;
			// }
		}

		return isset($data) ? $data : $q;
	}

	function query(...$args){
		if($this->is_dqdp_select($args)){
			return ibase_query($this->tr??$this->conn, $args[0], ...$args[0]->vars());
		} elseif(is_resource($args[0])) {
			return ibase_execute(...$args);
		} elseif(count($args) == 2) {
			return ibase_query($this->tr??$this->conn, $args[0], ...$args[1]);
			// if(($q = $this->prepare($args[0])) && $q->execute($args[1])){
			// 	return $q;
			// }
		}
		// if($this->is_dqdp_select($args)){
		// 	if(($q = $this->prepare($args[0])) && $q->execute($args[0]->vars())){
		// 		return $q;
		// 	}
		// 	return false;
		// } elseif($args[0] instanceof PDOStatement) {
		// 	if($args[0]->execute($args[1])){
		// 		return $args[0];
		// 	}
		// 	return false;
		// } elseif(count($args) == 2) {
		// 	if(($q = $this->prepare($args[0])) && $q->execute($args[1])){
		// 		return $q;
		// 	}
		// 	return false;
		// }

		return ibase_query($this->tr??$this->conn, ...$args);
	}

	private function __fetch($func, ...$args){
		if(!isset($args[1])){
			$args[1] = self::$FETCH_FLAGS;
		}
		return $func(...$args);
	}

	function fetch_assoc(...$args){
		return $this->__fetch('ibase_fetch_assoc', ...$args);
	}

	function fetch_object(...$args){
		return $this->__fetch('ibase_fetch_object', ...$args);
	}

	function trans(...$args){
		$tr = ibase_trans($this->conn, ...$args);
		$o = clone $this;
		$o->tr = $tr;
		return $o;
	}

	function commit(){
		return ibase_commit($this->tr);
	}

	function rollback(){
		printr($this);
		die;
		return ibase_rollback($this->tr);
	}

	function affected_rows(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function close(){
		trigger_error("Not implemented", E_USER_ERROR);
	}

	function prepare(...$args){
		if($this->is_dqdp_select($args)){
			return ibase_prepare($this->tr??$this->conn, (string)$args[0]);
			//return $this->conn->prepare((string)$args[0]);
		}
		return ibase_prepare($this->tr??$this->conn, ...$args);
	}

	function insert_update($Ent, $fields, $DATA){
		//list($fields, $DATA) = func_get_args();

		$Gen_value_str = $Gen_field_str = '';
		if(is_array($Ent->PK)){
			$PK_fields_str = join(",", $Ent->PK);
		} else {
			$PK_fields_str = $Ent->PK;
			if(empty($DATA->{$Ent->PK})){
				if(isset($Ent->Gen)){
					$Gen_field_str = "$Ent->PK,";
					$Gen_value_str = "NEXT VALUE FOR $Ent->Gen,";
				}
			} else {
				if(!in_array($Ent->PK, $fields)){
					$fields[] = $Ent->PK;
				}
			}
		}

		list($fieldSQL, $valuesSQL, $values) = build_sql($fields, $DATA, true);

		# TODO: ja vajadzēs nodalīt GRANT tiesības pa INSERT/UPDATE, tad jāatdala UPDATE OR INSERT atsevišķos pieprasījumos
		$sql = "UPDATE OR INSERT INTO $Ent->Table ($Gen_field_str$fieldSQL) VALUES ($Gen_value_str$valuesSQL) MATCHING ($PK_fields_str) RETURNING $PK_fields_str";

		if($q = $this->query($sql, $values)){
			$retPK = $this->fetch($q);
			if(is_array($Ent->PK)){
				return $retPK;
			} else {
				return $retPK->{$Ent->PK};
			}
		} else {
			return false;
		}
	}

	function get_users(...$args){
		list($PARAMS) = $args;

		if(is_scalar($PARAMS)){
			$PARAMS = eo(['USER_NAME'=>$PARAMS]);
		} else {
			$PARAMS = eoe($PARAMS);
		}

		$sql = (new Select)->From('SEC$USERS')
		->Select('SEC$USER_NAME, SEC$FIRST_NAME, SEC$MIDDLE_NAME,SEC$LAST_NAME')
		->Select('SEC$DESCRIPTION, SEC$PLUGIN')
		->Select('IIF(SEC$ACTIVE, 1, 0) AS IS_ACTIVE')
		->Select('IIF(SEC$ADMIN, 1, 0) AS IS_ADMIN')
		->Where('SEC$PLUGIN = \'Srp\'');

		if($PARAMS->USER_NAME){
			$sql->Where(['SEC$USER_NAME = ?', $PARAMS->USER_NAME]);
		} else {
			$sql->Where('SEC$USER_NAME = CURRENT_USER');
			//(SEC$USER_NAME = CURRENT_USER OR CURRENT_USER = \'SYSDBA\') AND
		}

		if(!($q = $this->query($sql))){
			return false;
		}

		return self::strip_rdb($this->fetch_all($q));
	}

	function get_user($USER_NAME = null){
		return ($u = $this->get_users($USER_NAME)) ? $u[0] : $u;
	}

	static function strip_rdb($data){
		__object_walk_ref($data, function(&$item, &$k){
			if((strpos($k, 'RDB$') === 0) || (strpos($k, 'SEC$') === 0)){
				$k = substr($k, 4);
				$item = trim($item);
			}
		});
		return $data;
	}

	function table_info($table){
		$table = strtoupper($table);
		$sql = 'SELECT rf.*,
			f.RDB$FIELD_SUB_TYPE,
			f.RDB$FIELD_TYPE,
			f.RDB$FIELD_LENGTH,
			f.RDB$CHARACTER_LENGTH,
			f.RDB$FIELD_PRECISION
		FROM RDB$RELATION_FIELDS rf
		JOIN RDB$FIELDS f ON f.RDB$FIELD_NAME = rf.RDB$FIELD_SOURCE
		WHERE rf.RDB$RELATION_NAME = ?
		ORDER BY rf.RDB$FIELD_POSITION';

		$q = $this->query($sql, $table);
		while($r = $this->fetch($q)){
			$ret[] = self::strip_rdb($r);
		}

		return $ret??[];
	}

	function get_privileges($PARAMS = null){
		$ret = [];

		if(is_scalar($PARAMS)){
			$PARAMS = eo(['USER'=>$PARAMS]);
		} else {
			$PARAMS = eoe($PARAMS);
		}

		# TODO: trigeri, view, tables, proc var pārklāties nosaukumi, vai nevar? Hmm...
		$sql = (new Select)->From('RDB$USER_PRIVILEGES');
		if($PARAMS->USER){
			$sql->Where(['RDB$USER = ?', $PARAMS->USER]);
		} else {
			//if($PARAMS->EXCLUDE_SYSDBA)$sql->Where(['RDB$USER != ?', 'SYSDBA']);
			//if($PARAMS->EXCLUDE_PUBLIC)$sql->Where(['RDB$USER != ?', 'PUBLIC']);
			$sql->Where(['RDB$USER != ?', 'SYSDBA']);
			$sql->Where(['RDB$USER != ?', 'PUBLIC']);
		}

		if(!($q = $this->query($sql))){
			return false;
		}

		while($r = $this->fetch($q)){
			$r = self::strip_rdb($r);

			$k = $r->USER;
			if($r->USER_TYPE == 13){
				$k = "ROLE:$r->USER";
			}

			if(!isset($ret[$k][$r->RELATION_NAME])){
				$ret[$k][$r->RELATION_NAME] = (object)[
					'GRANTOR'=>$r->GRANTOR,
					'GRANT_OPTION'=>$r->GRANT_OPTION,
					'USER_TYPE'=>$r->USER_TYPE,
					'OBJECT_TYPE'=>$r->OBJECT_TYPE,
				];
			}

			$p = &$ret[$k][$r->RELATION_NAME];

			# UPDATE, REFERENCE
			if(($r->PRIVILEGE == 'U') || ($r->PRIVILEGE == 'R')){
				$p->PRIVILEGES = $p->PRIVILEGES ?? [];
				if(!in_array($r->PRIVILEGE, $p->PRIVILEGES)){
					array_push($p->PRIVILEGES, $r->PRIVILEGE);
				}
				if($r->FIELD_NAME){
					$k = $r->PRIVILEGE.'_FIELDS';
					$p->{$k} = $p->{$k} ?? [];
					array_push($p->{$k}, $r->FIELD_NAME);
				}
			# ROLE
			} elseif($r->PRIVILEGE == 'M'){
				//$ret = array_merge(ibase_get_privileges($r->RELATION_NAME, $tr), $ret);
			} else {
				$p->PRIVILEGES = $p->PRIVILEGES ?? [];
				if(!in_array($r->PRIVILEGE, $p->PRIVILEGES)){
					array_push($p->PRIVILEGES, $r->PRIVILEGE);
				}
			}
		}

		return $ret;
	}

	function get_object_types(){
		$sql = 'SELECT RDB$TYPE, RDB$TYPE_NAME FROM RDB$TYPES WHERE RDB$FIELD_NAME=\'RDB$OBJECT_TYPE\'';
		$data = self::strip_rdb($this->execute($sql));
		foreach($data as $r){
			$ret[$r->TYPE] = $r->TYPE_NAME;
		}
		return $ret??[];
	}

	static function path_info($DB_PATH){
		if(count($parts = explode(":", $DB_PATH)) > 1){
			$host = array_shift($parts);
			$path = join(":", $parts);
		} else {
			$path = $DB_PATH;
		}

		$pi = pathinfo($path);

		$pi['path'] = $path;
		if(isset($host)){
			$pi['host'] = $host;
		}

		return $pi;
	}

	function db_exists($db_path, $db_user, $db_password){
		if(
			($pi = self::path_info($db_path)) &&
			($service = ibase_service_attach($pi['host'], $db_user, $db_password)) &&
			ibase_db_info($service, $pi['path'], IBASE_STS_HDR_PAGES)
		){
			return true;
		}
		return false;
	}

	function current_role(){
		return trim($this->execute_single('SELECT CURRENT_ROLE AS RLE FROM RDB$DATABASE')->RLE);
	}

	static function isql_args($params = null, $args = []){
		$DEFAULTS = [
			'USER'=>"sysdba",
			'PASS'=>"masterkey",
		];
		$params = eoe($DEFAULTS)->merge($params);

		if($params->USER){
			$args[] = '-user';
			$args[] = "'$params->USER'";
		}
		if($params->PASS){
			$args[] = '-pass';
			$args[] = "'$params->PASS'";
		}
		if($params->DB){
			$args[] = $params->DB;
		}

		return $args??[];
	}

	static function isql_exec($args = [], $input = '', $descriptorspec = []){
		if(defined('STDOUT')){
			$args[] = '-o';
			# TODO: -o CON only on Windows, need test on linux
			$args[] = is_windows() ? 'CON' : '/dev/stdout';
		} else {
			$args[] = '-o';
			$tmpfname = tempnam(getenv('TMPDIR'), 'isql');
			$args[] = $tmpfname;
		}

		$cmd = '"'.prepend_path(getenv('IBASE_BIN', true), "isql").'"';
		// Wrapper
		// https://github.com/cubiclesoft/createprocess-windows
		if(is_windows() && !is_climode()){
			$args = array_merge(['/w=5000', '/term', $cmd], $args);
			$cmd = 'C:\bin\createprocess.exe';
		}

		# Capture isql output. isql tends to keep isql in interactive mode if no -i or -o specified
		if($exe = proc_exec($cmd, $args, $input, $descriptorspec)){
			if(isset($tmpfname)){
				if($outp = file_get_contents($tmpfname)){
					$exe[1] = $outp;
				}
				//unlink($tmpfname);
			}
		}
		return $exe;
	}

	// args = ['DB', 'USER', 'PASS'];
	# NOTE: Caur web karās pie kļūdas (nevar dabūt STDERR), tāpēc wrappers un killers.
	# NOTE: timeout jāmaina lielākiem/lēnākiem skriptiem :E
	static function isql($SQL, $params = null){
		$args = self::isql_args($params, ['-e', '-noautocommit', '-bail', '-q']);

		// $args[] = '-i';
		// $tmpfname = tempnam(getenv('TMPDIR'), 'isql');
		// file_put_contents($tmpfname, $SQL);
		// $args[] = $tmpfname;

		return self::isql_exec($args, $SQL);
	}

	static function isql_meta($database, $params = null){
		$params = eoe($params);
		$params->DB = $database;

		return self::isql_exec(self::isql_args($params, ['-x']));
	}

	static function db_drop($db_name, $db_user, $db_password){
		$sql = "DROP DATABASE;\n";

		return self::isql($sql, [
			'USER'=>$db_user,
			'PASS'=>$db_password,
			'DB'=>$db_name
		]);
	}

	static function db_create($db_name, $db_user, $db_password, $body = ''){
		$sql = sprintf(
			"CREATE DATABASE '%s' USER '%s' PASSWORD '%s' PAGE_SIZE 8192 DEFAULT CHARACTER SET UTF8;\n",
			ibase_quote($db_name),
			ibase_quote($db_user),
			ibase_quote($db_password),
		);

		if($body){
			$sql .= $body."\n";
		}

		return self::isql($sql, [
			'USER'=>$db_user,
			'PASS'=>$db_password,
		]);
	}

}
