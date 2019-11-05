<?php

use dqdp\SQL\Condition;

require_once("stdlib.php");

final class Ibase {
	static public $FETCH_FLAGS = IBASE_TEXT;
	static public $FIELD_TYPES = [ 7=>'SMALLINT', 8=>'INTEGER', 9=>'QUAD', 10=>'FLOAT', 11=>'D_FLOAT', 12=>'DATE', 13=>'TIME',
	14=>'CHAR', 16=>'INT64', 27=>'DOUBLE', 35=>'TIMESTAMP', 37=>'VARCHAR', 40=>'CSTRING', 261=>'BLOB' ];
}

function __ibase_params($args){
	foreach($args as $i=>$a){
		if(is_array($a)){
			return array_merge(array_slice($args, 0, $i), $a);
		}
	}
	return $args;
}

function ibase_fetch_flags_set($flags){
	Ibase::$FETCH_FLAGS = $flags;
}

function ibase_fetch_flags_get(){
	return Ibase::$FETCH_FLAGS;
}

function ibase_fetch($q){
	return $q ? ibase_fetch_object($q, ibase_fetch_flags_get()) : false;
}

function ibase_fetcha($q){
	return $q ? ibase_fetch_assoc($q, ibase_fetch_flags_get()) : false;
}

function ibase_fetch_all(...$args){
	if(count($args) == 1 && gettype($args[0]) == 'resource'){
		$q = $args[0];
	} else {
		if(!($q = call_user_func_array('ibase_query', $args))){
			sqlr($args);
			return false;
		}
	}
	while($r = ibase_fetch($q)){
		$ret[] = $r;
	};
	return $ret??[];
}

/*
function ibase_fetch($q){
	return ibase_fetch_object($q, ibase_get_fetch_flags());
}

function ibase_fetcha($q){
	return ibase_fetch_assoc($q, ibase_get_fetch_flags());
}

function ibase_fetch_all($q){
	while($r = ibase_fetch($q)){
		$ret[] = $r;
	};
	return $ret??[];
}
*/

# ibase($sql[, $bind1, $bind2....])
function ibase(){
	$values = __ibase_params(func_get_args());
	if($q = call_user_func_array('ibase_query', $values)){
		return ibase_fetch($q);
	} else {
		sqlr($values);
		return false;
	}
}

function ibasea() {
	$values = __ibase_params(func_get_args());
	if($q = call_user_func_array('ibase_query', $values)){
		return ibase_fetcha($q);
	} else {
		sqlr($values);
		return false;
	}
}

# TODO: add transaction param
function ibase_execute_array($q, $values){
	array_unshift($values, $q);
	if(!($ret = call_user_func_array('ibase_execute', $values))){
		sqlr($values);
	}
	return $ret;
}

function ibase_query_array(){
	$values = __ibase_params(func_get_args());
	if(!($ret = call_user_func_array('ibase_query', $values))){
		sqlr($values);
	}
	return $ret;
}

function ibase_build_sql($struct, $data){
	$sql = '';
	if(isset($struct['char'])){
		foreach($struct['char'] as $s_char){
			if(isset($data[$s_char])){
				$sql .= " $s_char = '$data[$s_char]', ";
			}
		}
	}

	if(isset($struct['int'])){
		foreach($struct['int'] as $s_int){
			if(isset($data[$s_int])){
				$sql .= " $s_int = ".intval($data[$s_int]).", ";
			}
		}
	}

	if(isset($struct['float'])){
		foreach($struct['float'] as $s_float){
			if(isset($data[$s_float])){
				$sql .= " $s_float = ".to_float($data[$s_float]).", ";
			}
		}
	}

	if(isset($struct['func'])){
		foreach($struct['func'] as $s_func){
			if(isset($data[$s_func])){
				$sql .= " $s_func = ".$data[$s_func].", ";
			}
		}
	}

	if(isset($struct['blob'])){
		foreach($struct['blob'] as $s_func)	{
			if(isset($data[$s_func])){
				$sql .= " $s_func = ?, ";
			}
		}
	}

	$sql = substr($sql, 0, -2);

	return $sql;
}

function parse_search_q($q, $minWordLen = 3){
	$q = preg_replace('/[%,\'\.]/', ' ', $q);
	$words = explode(' ', $q);

	foreach($words as $k=>$word){
		if(($word = trim($word)) && (mb_strlen($word) >= $minWordLen)){
			$words[$k] = mb_strtoupper($word);
		} else {
			unset($words[$k]);
		}
	}
	return array_unique($words);
}

function search_to_sql_cond($q, $fields, $minWordLen = 3){
	$words = parse_search_q($q, $minWordLen);
	if(!is_array($fields)){
		$fields = array($fields);
	}

	$MainCond = new Condition();
	foreach($words as $word){
		$Cond = new Condition();
		foreach($fields as $field){
			$Cond->add_condition(["UPPER($field) LIKE ?", "%".$word."%"], Condition::OR);
		}
		$MainCond->add_condition($Cond, Condition::AND);
	}

	return $MainCond;
}

/**
 * @return array
 */
function search_to_sql($q, $fields, $minWordLen = 3){
	$words = parse_search_q($q, $minWordLen);
	if(!is_array($fields)){
		$fields = array($fields);
	}

	$match = '';
	$values = [];
	foreach($words as $word){
		$tmp = '';
		foreach($fields as $field){
			//$tmp .= "UPPER($field) LIKE ? COLLATE UNICODE_CI_AI ESCAPE '\\' OR ";
			$tmp .= "UPPER($field) LIKE ? OR ";
			$values[] = "%".$word."%";
		}
		$tmp = substr($tmp, 0, -4);
		if($tmp)
			$match .= "($tmp) AND ";
	}
	$match = substr($match, 0, -5);
	if($match){
		return ["($match)", $values];
	}
	return ["", []];
}

# TODO: abstract out config!
/*
function ibase_user_add($new_user, $new_password){
	//extract($GLOBALS, EXTR_SKIP);
	# NOTE: check PHP5 for syntax
	//return ibase_add_user($config['db_server'], $config['db_user'], $config['db_password'], $new_user, $new_password);
	/*
	extract($GLOBALS['config'], EXTR_SKIP);
	$cmd = "gsec -user $db_user -password $db_password -add $new_user -pw $new_password";
	print "$cmd<br>\n";
	return my_exec($cmd);
	/
}

function ibase_user_del($user){
	extract($GLOBALS['config'], EXTR_SKIP);
	$cmd = "gsec -user $db_user -password $db_password -delete $user";
	return my_exec($cmd);
}

function ibase_db_create($db_name, $db_user, $db_password, $add_sql = array()){
	$db_root = $GLOBALS['config']['db_root'];
	$db_schema = $GLOBALS['config']['db_schema'];

	$ret = ibase_user_add($db_user, $db_password);

	$schema_data = join('', file($db_schema));
	$sql_file = tempnam('', 'onlinetrader');
	$data = "CREATE DATABASE '$db_root$db_name.gdb' USER '$db_user' PASSWORD '$db_password';\r\n";
	$data .= "CONNECT '$db_root$db_name.gdb' USER '$db_user' PASSWORD '$db_password';\r\n";
	$data .= $schema_data;
	$data .= join("\r\n", $add_sql)."\r\n";
	$data .= "COMMIT;\r\n";

	if(!($f = fopen($sql_file, 'w'))){
		user_error("Cannot create '$sql_file'\r\n");
		return false;
	}

	fputs($f, $data);
	fclose($f);

	$ret = exec("isql -m -i ".escapeshellarg($sql_file), $output, $retval);
	return file_exists("$db_root$db_name.gdb");
}
*/

function ibase_db_restore($db_backup_file, $db_file, $db_user, $db_password){
	$cmd = "gbak -USER $db_user -PASSWORD $db_password -R $db_backup_file $db_file";
	my_exec($cmd);
}

function ibase_db_backup($db_file, $db_backup_file, $db_user, $db_password){
	$cmd = "gbak -USER $db_user -PASSWORD $db_password -B $db_file $db_backup_file";
	my_exec($cmd);
}

function ibase_table_info($table, $tr = null){
	$table = strtoupper($table);
	$sql = sprintf('
SELECT
	rf.*,
	f.RDB$FIELD_SUB_TYPE,
	f.RDB$FIELD_TYPE,
	f.RDB$FIELD_LENGTH,
	f.RDB$CHARACTER_LENGTH,
	f.RDB$FIELD_PRECISION
FROM
	RDB$RELATION_FIELDS rf
JOIN
	RDB$FIELDS f ON f.RDB$FIELD_NAME = rf.RDB$FIELD_SOURCE
WHERE
	rf.RDB$RELATION_NAME = UPPER(\'%s\')
ORDER BY
	rf.RDB$FIELD_POSITION
', $table);

	$q = ibase_query(__tr($tr), $sql);


	$fields = array();
	while($r = ibase_fetch($q)){
		$tmp = new stdclass;
		$tmp->name = trim($r->{'RDB$FIELD_NAME'});
		$tmp->relationName = trim($r->{'RDB$RELATION_NAME'});
		$tmp->domain = trim($r->{'RDB$FIELD_SOURCE'});
		$tmp->notNull = $r->{'RDB$NULL_FLAG'} == 1 ? true : false;
		$tmp->position = $r->{'RDB$FIELD_POSITION'};
		$tmp->clength = $r->{'RDB$CHARACTER_LENGTH'};
		$tmp->length = $r->{'RDB$FIELD_LENGTH'};
		$tmp->updateFlag = $r->{'RDB$UPDATE_FLAG'};
		$tmp->type = ibase_field_type($r);

		$fields[] = $tmp;
	}

	return $fields;
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
	$js_types = array(
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
	);

	return isset($js_types[$type]) ? $js_types[$type] : 'auto';
}

function ibase_type2json_type($type){
	$js_types = array(
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
	);

	return isset($js_types[$type]) ? $js_types[$type] : 'auto';
}

function ibase_quote($data){
	return __object_map($data, function($item){
		return str_replace("'", "''", $item);
	});
}

function ibase_get_pk(){
	$args = func_get_args();
	$table = array_shift($args);

	$args[] = 'SELECT iseg.RDB$FIELD_NAME
	FROM RDB$INDICES i
	JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME
	JOIN RDB$INDEX_SEGMENTS iseg ON iseg.RDB$INDEX_NAME = i.RDB$INDEX_NAME
	WHERE i.RDB$RELATION_NAME = \''.$table.'\' AND rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'
	';

	if($r = call_user_func_array('ibase', $args)){
		return trim($r->{'RDB$FIELD_NAME'});
	} else {
		return false;
	}
}

function ibase_replace_sql($replaces, $field){
	$sql = $field;
	foreach($replaces as $f=>$r){
		$sql = "REPLACE($sql, '$f', '$r')";
	}
	return $sql;
}

function __ibase_get($tr, $sql){
	$q = ibase_query(__tr($tr), $sql);
	while($r = ibase_fetch($q)){
		$ret[] = trim($r->NAME);
	}
	return $ret??[];
}

function ibase_get_generators($tr = null){
	return __ibase_get($tr, 'SELECT RDB$GENERATOR_NAME AS NAME
	FROM RDB$GENERATORS
	WHERE RDB$SYSTEM_FLAG = 0
	ORDER BY RDB$GENERATOR_NAME');
}

function ibase_get_tables($tr = null){
	return __ibase_get($tr, 'SELECT r.RDB$RELATION_NAME AS NAME FROM RDB$RELATIONS AS r
	LEFT JOIN RDB$VIEW_RELATIONS v ON v.RDB$VIEW_NAME = r.RDB$RELATION_NAME
	WHERE v.RDB$VIEW_NAME IS NULL AND r.RDB$SYSTEM_FLAG = 0
	ORDER BY r.RDB$RELATION_NAME');
}

// args = ['DB', 'ISQL', 'USER', 'PASS', 'SQL', 'OUTPUT'];
// returns exit code and output
function ibase_isql($DATA){
	$DEFAULTS = [
		'ISQL'=>is_windows() ? "isql.exe" : "isql",
		'USER'=>"sysdba",
		'PASS'=>"masterkey",
		'STDIN'=>["pipe", "r"],
		'STDOUT'=>["pipe", "w"],
		'STDERR'=>["file", getenv('TMPDIR')."./isql-output.txt", "a"],
	];
	$DATA = eo($DEFAULTS)->merge($DATA);

	if(empty($DATA->DB)){
		trigger_error("DB parameter not set", E_USER_WARNING);
		return false;
	}

	$args = ['-e', '-noautocommit', '-m2', '-merge', '-bail'];
	if($DATA->USER){
		$args[] = '-user';
		$args[] = "'$DATA->USER'";
	}
	if($DATA->PASS){
		$args[] = '-pass';
		$args[] = "'$DATA->PASS'";
	}
	if($DATA->DB)$args[] = $DATA->DB;

	$descriptorspec = [$DATA->STDIN, $DATA->STDOUT, $DATA->STDERR];

	$process_cmd = '"'.$DATA->ISQL.'"'.' '.join(" ", escape_shell($args));
	$process = proc_open($process_cmd, $descriptorspec, $pipes);
	if(is_resource($process)){
		fwrite($pipes[0], $DATA->SQL);
		fclose($pipes[0]);

		$output = null;
		if(isset($pipes[1])){
			$output = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}

		return [proc_close($process), $output];
	}
	return false;
}

function ibase_current_role($tr = null){
	return ibase(__tr($tr), 'SELECT CURRENT_ROLE AS RLE FROM RDB$DATABASE')->RLE;
}
