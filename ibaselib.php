<?php declare(strict_types = 1);

# TODO: in future use dqdp\FireBird\*; !!

use dqdp\DBA\driver\IBase;
use dqdp\SQL\Select;

require_once("stdlib.php");

function ibase_field_types(){
	return [
		7=>'SMALLINT',
		8=>'INTEGER',
		9=>'QUAD',
		10=>'FLOAT',
		11=>'D_FLOAT',
		12=>'DATE',
		13=>'TIME',
		14=>'CHAR',
		16=>'INT64',
		27=>'DOUBLE',
		35=>'TIMESTAMP',
		37=>'VARCHAR',
		40=>'CSTRING',
		261=>'BLOB'
	];
}

function ibase_db_create($db_name, $db_user, $db_password, $body = ''){
	$sql = sprintf(
		"CREATE DATABASE '%s' USER '%s' PASSWORD '%s' PAGE_SIZE 8192 DEFAULT CHARACTER SET UTF8;\n",
		ibase_escape($db_name),
		ibase_escape($db_user),
		ibase_escape($db_password),
	);

	if($body){
		$sql .= $body."\n";
	}

	return ibase_isql($sql, [
		'USER'=>$db_user,
		'PASS'=>$db_password,
	]);
}

function ibase_db_drop($db_name, $db_user, $db_password){
	$sql = "DROP DATABASE;\n";

	return ibase_isql($sql, [
		'USER'=>$db_user,
		'PASS'=>$db_password,
		'DB'=>$db_name
	]);
}

function ibase_field_type($r){
	$FIELD_TYPES = ibase_field_types();

	$type = isset($FIELD_TYPES[$r->{'RDB$FIELD_TYPE'}]) ? $FIELD_TYPES[$r->{'RDB$FIELD_TYPE'}] : false;

	# Integer
	if($r->{'RDB$FIELD_TYPE'} == 8){
		if($r->{'RDB$FIELD_SUB_TYPE'} == 1){
			$type = 'NUMERIC';
		}
		if($r->{'RDB$FIELD_SUB_TYPE'} == 2){
			$type = 'DECIMAL';
		}
	}

	/*
	if(trim($r->{'RDB$FIELD_SOURCE'}) == 'BOOL')
	{
		$type = 'BOOLEAN';
	}
	if(trim($r->{'RDB$FIELD_PRECISION'}) > 0)
	{
		$type = 'FLOAT';
	}
	*/

	return $type;
}

function ibase_bool($php_bool){
	return $php_bool ? 1 : 0;
}

function ibase_type2php_type($type){
	$js_types = [
		'SMALLINT'=>'int',
		'INTEGER'=>'int',
		'QUAD'=>'int',
		'FLOAT'=>'float',
		'D_FLOAT'=>'float',
		'DATE'=>'date',
		'TIME'=>'date',
		'CHAR'=>'string',
		'INT64'=>'int',
		'DOUBLE'=>'float',
		'TIMESTAMP'=>'date',
		'VARCHAR'=>'string',
		'CSTRING'=>'string',
		'BLOB'=>'string',
		'BOOLEAN'=>'boolean',
	];

	return $js_types[$type]??'auto';
}

function ibase_type2json_type($type){
	$js_types = [
		'SMALLINT'=>'integer',
		'INTEGER'=>'integer',
		'QUAD'=>'integer',
		'FLOAT'=>'number',
		'D_FLOAT'=>'number',
		'DATE'=>'string',
		'TIME'=>'string',
		'CHAR'=>'string',
		'INT64'=>'integer',
		'DOUBLE'=>'number',
		'TIMESTAMP'=>'string',
		'VARCHAR'=>'string',
		'CSTRING'=>'string',
		'BLOB'=>'string',
		'BOOLEAN'=>'boolean',
	];

	return $js_types[$type]??'auto';
}

function ibase_escape($data){
	return str_replace("'", "''", $data);
}

function ibase_isql_exec(array $args = [], string $input = ''){
	if(defined('STDOUT')){
		$args[] = '-o';
		# TODO: -o CON only on Windows, need test on linux
		$args[] = is_windows() ? 'CON' : '/dev/stdout';
	} else {
		$args[] = '-o';
		$tmpfname = tempnam(sys_get_temp_dir(), 'isql');
		$args[] = $tmpfname;
	}

	$cmd = '"'.prepend_path(constant('IBASE_BIN'), "isql").'"';
	// Wrapper
	// https://github.com/cubiclesoft/createprocess-windows
	// if(is_windows() && !is_climode()){
	// 	$args = array_merge(['/w=5000', '/term', $cmd], $args);
	// 	$cmd = 'C:\bin\createprocess.exe';
	// }

	# Capture isql output. isql tends to keep isql in interactive mode if no -i or -o specified
	if($exe = proc_exec($cmd, $args, $input)){
		if(isset($tmpfname)){
			if($outp = file_get_contents($tmpfname)){
				$exe[1] = $outp;
			}
			//unlink($tmpfname);
		}
	}

	return $exe;
}

function __ibase_isql_args($params = null, $args = []){
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

// args = ['DB', 'USER', 'PASS'];
function ibase_isql($SQL, $params = null){
	$args = __ibase_isql_args($params, ['-e', '-noautocommit', '-bail', '-q']);

	return ibase_isql_exec($args, $SQL);
}

function ibase_get_meta($database, $params = null){
	$params = eoe($params);
	$params->DB = $database;

	return ibase_isql_exec(__ibase_isql_args($params, ['-x']));
}

function ibase_strip_rdb(array|object &$data) {
	__object_walk($data, function(&$item, &$k, &$parent){
		if(!(is_string($k) || $k instanceof Stringable)){
			return;
		}

		if((strpos($k, 'RDB$') !== 0) && (strpos($k, 'SEC$') !== 0)){
			return;
		}

		unset_prop($parent, $k);

		$k = trim(substr($k, 4));

		if(is_string($item) || $item instanceof Stringable){
			$item = trim($item);
		}

		set_prop($parent, $k, $item);
	});
}

function ibase_pathinfo(string $DB_PATH){
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

function ibase_db_exists(string $db_path, string $db_user, string $db_password){
	if(
		($pi = ibase_pathinfo($db_path)) &&
		($service = ibase_service_attach($pi['host'], $db_user, $db_password)) &&
		ibase_db_info($service, $pi['path'], IBASE_STS_HDR_PAGES)
	){
		return true;
	}
	return false;
}

function ibase_quote($data){
	return __object_map($data, function($item){
		return str_replace("'", "''", $item);
	});
}

# TODO: Zemāk esošajām f-ijām lietot Firebird lib
function ibase_get_current_user(Ibase $db){
	return ($u = ibase_get_users($db, ["CURRENT_USER"=>true])) ? $u[0] : $u;
}

function ibase_get_users(Ibase $db, ?iterable $F = null): array {
	$F = eoe($F);

	$sql = (new Select)
	->From('SEC$USERS')
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

	if(!($q = $db->query($sql))){
		return null;
	}

	while($r = $db->fetch_object($q)){
		ibase_strip_rdb($r);
		$ret[] = $r;
	}

	// $ret2 = ibase_strip_rdb($ret);
	// return ibase_strip_rdb($ret??[]);
	// return ibase_strip_rdb($db->fetch_all($q));
	return $ret??[];
}

function ibase_get_current_role(Ibase $db): string {
	return trim(get_prop(
		$db->fetch_assoc($db->query('SELECT CURRENT_ROLE AS RLE FROM RDB$DATABASE'))
	, 'RLE'));
}

function ibase_get_object_types(Ibase $db): array {
	$sql = 'SELECT RDB$TYPE, RDB$TYPE_NAME FROM RDB$TYPES WHERE RDB$FIELD_NAME=\'RDB$OBJECT_TYPE\'';
	// $data = ibase_strip_rdb($db->execute($sql));
	$q = $db->query($sql);
	// foreach($data as $r){
	while($r = $db->fetch_object($q)){
		ibase_strip_rdb($r);
		$ret[$r->TYPE] = $r->TYPE_NAME;
	}

	return $ret??[];
}

function ibase_get_privileges(Ibase $db, $PARAMS = null): array {
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

	if(!($q = $db->query($sql))){
		return $ret;
	}

	while($r = $db->fetch_object($q)){
		ibase_strip_rdb($r);

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

function get_relation_fields(IBase $db, string $table): mixed {
	$sql = 'SELECT rf.*,
		f.RDB$FIELD_SUB_TYPE,
		f.RDB$FIELD_TYPE,
		f.RDB$FIELD_LENGTH,
		f.RDB$CHARACTER_LENGTH,
		f.RDB$FIELD_PRECISION,
		f.RDB$FIELD_SCALE,
		f.RDB$NULL_FLAG,
		rf.RDB$FIELD_NAME AS RDB$ITEM_NAME
	FROM RDB$RELATION_FIELDS rf
	JOIN RDB$FIELDS f ON f.RDB$FIELD_NAME = rf.RDB$FIELD_SOURCE
	WHERE rf.RDB$RELATION_NAME = ?
	ORDER BY rf.RDB$FIELD_POSITION';

	if(!($q = $db->query($sql, $table))){
		return null;
	}

	while($r = $db->fetch_object($q)){
		ibase_strip_rdb($r);
		$ret[] = $r;
	}

	return $ret??[];
}

function get_proc_fields(IBase $db, string $proc): mixed {
	$sql = 'SELECT pp.*,
	f.RDB$FIELD_SUB_TYPE,
	f.RDB$FIELD_TYPE,
	f.RDB$FIELD_LENGTH,
	f.RDB$CHARACTER_LENGTH,
	f.RDB$FIELD_PRECISION,
	f.RDB$FIELD_SCALE,
	f.RDB$NULL_FLAG,
	pp.RDB$PARAMETER_NAME AS RDB$ITEM_NAME
FROM RDB$PROCEDURE_PARAMETERS pp
JOIN RDB$FIELDS f ON f.RDB$FIELD_NAME = pp.RDB$FIELD_SOURCE
WHERE pp.RDB$PROCEDURE_NAME = ? AND pp.RDB$PARAMETER_TYPE = 1
ORDER BY pp.RDB$PARAMETER_NUMBER';

	if(!($q = $db->query($sql, $proc))){
		return null;
	}

	while($r = $db->fetch_object($q)){
		ibase_strip_rdb($r);
		$ret[] = $r;
	}

	return $ret??[];
}

function ibase_get_pk(IBase $db, string $table): string|array|null {
	$sql ='SELECT
		ix.RDB$INDEX_NAME AS INDEX_NAME,
		sg.RDB$FIELD_NAME AS FIELD_NAME,
		rc.RDB$RELATION_NAME AS TABLE_NAME
	FROM
		RDB$INDICES ix
		JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = ix.RDB$INDEX_NAME
		LEFT JOIN RDB$INDEX_SEGMENTS sg ON ix.RDB$INDEX_NAME = sg.RDB$INDEX_NAME
	WHERE
		rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\' AND rc.RDB$RELATION_NAME = ?';

	if(!($q = $db->query($sql, $table))){
		return null;
	}

	while($r = $db->fetch_object($q)){
		$ret[] = trim($r->FIELD_NAME);
	}

	if(!isset($ret)){
		return null;
	} elseif(count($ret) == 1){
		return $ret[0];
	} else {
		return $ret;
	}
}

function ibase_get_table_info(IBase $db, string $table): ?stdClass {
	$sql = 'SELECT * FROM RDB$RELATIONS AS relations WHERE relations.RDB$SYSTEM_FLAG = 0 AND relations.RDB$RELATION_NAME = ?';

	if(!($q = $db->query($sql, $table))){
		return null;
	}

	if($r = $db->fetch_object($q)){
		ibase_strip_rdb($r);
		return $r;
	}

	return null;
}
