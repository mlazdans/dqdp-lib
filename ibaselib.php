<?php

use dqdp\SQL\Condition;

define('IBASE_DATE', "ibase.dateformat");
define('IBASE_TIME', "ibase.timeformat");
define('IBASE_TIMESTAMP', "ibase.timestampformat");

$IBASE_FIELD_TYPES = [ 7=>'SMALLINT', 8=>'INTEGER', 9=>'QUAD', 10=>'FLOAT', 11=>'D_FLOAT', 12=>'DATE', 13=>'TIME',
14=>'CHAR', 16=>'INT64', 27=>'DOUBLE', 35=>'TIMESTAMP', 37=>'VARCHAR', 40=>'CSTRING', 261=>'BLOB' ];

function ifetch($q, $flags = false){
	$flags = ($flags === false ? IBASE_TEXT : $flags);
	return ibase_fetch_object($q, $flags);
}

function ifetcha($q, $flags = false){
	$flags = ($flags === false ? IBASE_TEXT : $flags);
	return ibase_fetch_assoc($q, $flags);
}

# ibase($sql[, $bind1, $bind2....])
function ibase(){
	$values = __ibase_params(func_get_args());
	if($q = call_user_func_array('ibase_query', $values)){
		return ifetch($q);
	} else {
		sqlr($values);
		return false;
	}
}

function ibasea() {
	$values = __ibase_params(func_get_args());
	if($q = call_user_func_array('ibase_query', $values)){
		return ifetcha($q);
	} else {
		return false;
	}
}


# TODO: add transaction param
function ibase_execute_array($q, $values){
	array_unshift($values, $q);
	$ret = call_user_func_array('ibase_execute', $values);
	if(!$ret){
		sqlr($values);
	}
	return $ret;
}

function __ibase_params($args){
	foreach($args as $i=>$a){
		if(is_array($a)){
			return array_merge(array_slice($args, 0, $i), $a);
		}
	}
	return $args;
}

function ibase_query_array(){
	$values = __ibase_params(func_get_args());
	$ret = call_user_func_array('ibase_query', $values);
	if(!$ret){
		sqlr($values);
	}
	return $ret;
}

# ibase_fetch_all([$tr],$sql[, $bind1, $bind2....])
# ibase_fetch_all([$tr],$sql[arrray() $binds])
function ibase_fetch_all(){
	$ret = [];
	$values = __ibase_params(func_get_args());
	if(!($q = call_user_func_array('ibase_query', $values))){
		sqlr($values);
		return false;
	}
	//$q = call_user_func_array('ibase_query', func_get_args());
	while($r = ifetch($q)){
		$ret[] = $r;
	};
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

	$values = [];
	$MainCond = new Condition();
	foreach($words as $word){
		$Cond = new Condition(Condition::OR);
		foreach($fields as $field){
			$values[] = "%".$word."%";
			$Cond->add_condition("UPPER($field) LIKE ?");
		}
		$MainCond->add_condition($Cond);
	}

	return [$MainCond, $values];
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

function ibase_isql(){
}

function ibase_db_restore($db_backup_file, $db_file, $db_user, $db_password){
	$cmd = "gbak -USER $db_user -PASSWORD $db_password -R $db_backup_file $db_file";
	my_exec($cmd);
}

function ibase_db_backup($db_file, $db_backup_file, $db_user, $db_password){
	$cmd = "gbak -USER $db_user -PASSWORD $db_password -B $db_file $db_backup_file";
	my_exec($cmd);
}

function ibase_genpass(){
	return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
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

	$q = $tr ? ibase_query($tr, $sql) : ibase_query($sql);


	$fields = array();
	while($r = ifetch($q)){
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
	global $IBASE_FIELD_TYPES;

	$type = isset($IBASE_FIELD_TYPES[$r->{'RDB$FIELD_TYPE'}]) ? $IBASE_FIELD_TYPES[$r->{'RDB$FIELD_TYPE'}] : false;

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
