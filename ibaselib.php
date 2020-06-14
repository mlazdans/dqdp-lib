<?php

require_once("stdlib.php");

final class Ibase {
	static public $FIELD_TYPES = [
		7=>'SMALLINT', 8=>'INTEGER', 9=>'QUAD', 10=>'FLOAT', 11=>'D_FLOAT', 12=>'DATE', 13=>'TIME',
		14=>'CHAR', 16=>'INT64', 27=>'DOUBLE', 35=>'TIMESTAMP', 37=>'VARCHAR', 40=>'CSTRING', 261=>'BLOB'
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
	$FIELD_TYPES = Ibase::$FIELD_TYPES;

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

function ibase_isql_exec($args = [], $input = '', $descriptorspec = []){
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
# NOTE: Caur web karās pie kļūdas (nevar dabūt STDERR), tāpēc wrappers un killers.
# NOTE: timeout jāmaina lielākiem/lēnākiem skriptiem :E
function ibase_isql($SQL, $params = null){
	$args = __ibase_isql_args($params, ['-e', '-noautocommit', '-bail', '-q']);

	// $args[] = '-i';
	// $tmpfname = tempnam(getenv('TMPDIR'), 'isql');
	// file_put_contents($tmpfname, $SQL);
	// $args[] = $tmpfname;

	return ibase_isql_exec($args, $SQL);
}

function ibase_get_meta($database, $params = null){
	$params = eoe($params);
	$params->DB = $database;

	return ibase_isql_exec(__ibase_isql_args($params, ['-x']));
}

function ibase_strip_rdb($data){
	__object_walk_ref($data, function(&$item, &$k){
		if((strpos($k, 'RDB$') === 0) || (strpos($k, 'SEC$') === 0)){
			$k = substr($k, 4);
			$item = trim($item);
		}
	});
	return $data;
}

function ibase_pathinfo($DB_PATH){
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

function ibase_db_exists($db_path, $db_user, $db_password){
	if(
		($pi = ibase_pathinfo($db_path)) &&
		($service = ibase_service_attach($pi['host'], $db_user, $db_password)) &&
		ibase_db_info($service, $pi['path'], IBASE_STS_HDR_PAGES)
	){
		return true;
	}
	return false;
}
