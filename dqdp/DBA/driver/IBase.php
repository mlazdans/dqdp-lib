<?php

declare(strict_types = 1);

namespace dqdp\DBA\driver;

use dqdp\DBA\AbstractDBA;
use dqdp\DBA\AbstractTable;
use dqdp\SQL\Insert;
use dqdp\SQL\Select;

require_once('ibaselib.php');

class IBase extends AbstractDBA
{
	var $conn;
	var $tr;

	static public $FETCH_FLAGS = IBASE_TEXT;

	function connect_params($params){
		$database = $params['database'] ?? '';
		$username = $params['username'] ?? '';
		$password = $params['password'] ?? '';
		$charset = $params['charset'] ?? 'utf8';
		$buffers = $params['buffers'] ?? 0;
		$dialect = $params['dialect'] ?? 0;
		$role = $params['role'] ?? '';

		return $this->connect($database, $username, $password, $charset, $buffers, $dialect, $role);
	}

	function connect(...$args){
		$this->conn = ibase_connect(...$args);

		return $this;
	}

	function execute(...$args){
		$q = $this->query(...$args);

		if($q && is_resource($q)){
			$data = $this->fetch_all($q);
		}

		return isset($data) ? $data : $q;
	}

	function query(...$args) {
		if($this->is_dqdp_statement($args)){
			$q = ibase_query($this->tr??$this->conn, (string)$args[0], ...$args[0]->vars());
		} elseif((count($args) == 2) && is_resource($args[0]) && is_array($args[1])) {
			//$q = ibase_execute(...$args);
			$q = ibase_execute($args[0], ...$args[1]);
			//$q = ibase_execute($this->tr??$this->conn, $args[0], ...$args[1]);
		} elseif((count($args) == 2) && is_array($args[1])) {
			//printr($args);
			if(($q2 = $this->prepare($args[0])) && ($q = ibase_execute($q2, ...$args[1]))){
			}
			//$q = ibase_query($this->tr??$this->conn, $args[0], ...$args[1]);
		} else {
			$q = ibase_query($this->tr??$this->conn, ...$args);
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

	private function __fetch(callable $func, ...$args){
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

	function commit(): bool {
		$ret = ibase_commit($this->tr);
		$this->tr = null;

		return $ret;
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
		return ibase_close($this->conn);
	}

	function prepare(...$args){
		if($this->is_dqdp_statement($args)){
			return ibase_prepare($this->tr??$this->conn, (string)$args[0]);
		}

		return ibase_prepare($this->tr??$this->conn, ...$args);
	}

	function escape($v): string {
		return ibase_escape($v);
	}

	function get_users(?iterable $F = null): array {
		$F = eoe($F);

		// if(is_scalar($PARAMS)){
		// 	$PARAMS = eo(['USER_NAME'=>$PARAMS]);
		// } else {
		// 	$PARAMS = eoe($PARAMS);
		// }

		$sql = (new Select)->From('SEC$USERS')
		->Select('SEC$USER_NAME, SEC$FIRST_NAME, SEC$MIDDLE_NAME,SEC$LAST_NAME')
		->Select('SEC$DESCRIPTION, SEC$PLUGIN')
		->Select('IIF(SEC$ACTIVE, 1, 0) AS IS_ACTIVE')
		->Select('IIF(SEC$ADMIN, 1, 0) AS IS_ADMIN')
		->Where('SEC$PLUGIN = \'Srp\'');

		if($F->USER_NAME){
			$sql->Where(['SEC$USER_NAME = ?', $F->USER_NAME]);
		} elseif($F->CURRENT_USER) {
			$sql->Where('SEC$USER_NAME = CURRENT_USER');
			//(SEC$USER_NAME = CURRENT_USER OR CURRENT_USER = \'SYSDBA\') AND
		}

		if(!($q = $this->query($sql))){
			return false;
		}

		return ibase_strip_rdb($this->fetch_all($q));
	}

	function get_user($USER_NAME = null){
		return ($u = $this->get_users(['USER_NAME'=>$USER_NAME])) ? $u[0] : $u;
	}

	function get_current_user(){
		return ($u = $this->get_users(["CURRENT_USER"=>true])) ? $u[0] : $u;
	}

	function get_table_info($table){
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

		return ibase_strip_rdb($this->execute($sql, [$table]));
	}

	# TODO: principā return vajadzētu array|object atkarībā no default fetch
	function get_privileges($PARAMS = null): array {
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
			return $ret;
		}

		while($r = $this->fetch_object($q)){
			$r = ibase_strip_rdb($r);

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

	function get_object_types(): array {
		$sql = 'SELECT RDB$TYPE, RDB$TYPE_NAME FROM RDB$TYPES WHERE RDB$FIELD_NAME=\'RDB$OBJECT_TYPE\'';
		$data = ibase_strip_rdb($this->execute($sql));
		foreach($data as $r){
			$ret[$r->TYPE] = $r->TYPE_NAME;
		}

		return $ret??[];
	}

	function get_current_role(): string {
		return trim(get_prop($this->execute_single('SELECT CURRENT_ROLE AS RLE FROM RDB$DATABASE'), 'RLE'));
	}

	# TODO: params
	function get_generators(): array {
		return trimmer($this->execute('SELECT RDB$GENERATOR_NAME AS NAME
		FROM RDB$GENERATORS
		WHERE RDB$SYSTEM_FLAG = 0
		ORDER BY RDB$GENERATOR_NAME'));
	}

	function get_tables(): array {
		return trimmer($this->execute('SELECT r.RDB$RELATION_NAME AS NAME FROM RDB$RELATIONS AS r
		LEFT JOIN RDB$VIEW_RELATIONS v ON v.RDB$VIEW_NAME = r.RDB$RELATION_NAME
		WHERE v.RDB$VIEW_NAME IS NULL AND r.RDB$SYSTEM_FLAG = 0
		ORDER BY r.RDB$RELATION_NAME'));
	}

	function get_pk($table): ?string {
		$sql = 'SELECT iseg.RDB$FIELD_NAME
		FROM RDB$INDICES i
		JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME
		JOIN RDB$INDEX_SEGMENTS iseg ON iseg.RDB$INDEX_NAME = i.RDB$INDEX_NAME
		WHERE i.RDB$RELATION_NAME = ? AND rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'';

		if($r = $this->execute_single($sql, [$table])){
			return trim(get_prop($r, 'RDB$FIELD_NAME'));
		}

		return null;
	}

	// function get_fields($table){
	// 	$sql = 'SELECT F.RDB$RELATION_NAME, F.RDB$FIELD_NAME
	// 	FROM RDB$RELATION_FIELDS F
	// 	JOIN RDB$RELATIONS R ON F.RDB$RELATION_NAME = R.RDB$RELATION_NAME
	// 	AND R.RDB$VIEW_BLR IS NULL
	// 	AND (R.RDB$SYSTEM_FLAG IS NULL OR R.RDB$SYSTEM_FLAG = 0)
	// 	WHERE R.RDB$RELATION_NAME = ?
	// 	ORDER BY 1, F.RDB$FIELD_POSITION';
	// 	return ibase_strip_rdb($this->execute($sql, [$table]));
	// }

	function save(iterable $DATA, AbstractTable $Table){
		$sql_fields = (array)merge_only($Table->getFields(), $DATA);

		$PK = $Table->getPK();
		if(!is_array($PK)){
			$PK_val = get_prop($DATA, $PK);
			if(is_null($PK_val)){
				if($Gen = $Table->getGen()){
					$sql_fields[$PK] = function() use ($Gen) {
						return "NEXT VALUE FOR $Gen";
					};
				}
			} else {
				$sql_fields[$PK] = $PK_val;
			}
		}

		$sql = (new Insert)->Into($Table->getName())
			->Values($sql_fields)
			->Update();

		$PK_fields_str = is_array($PK) ? join(",", $PK) : $PK;
		$sql->after("values", "matching", "MATCHING ($PK_fields_str)")
			->after("values", "returning", "RETURNING $PK_fields_str");

		if($q = $this->query($sql)){
			$retPK = $this->fetch($q);
			if(is_array($PK)){
				return $retPK;
			} else {
				return get_prop($retPK, $PK);
			}
		} else {
			return false;
		}
	}
}
