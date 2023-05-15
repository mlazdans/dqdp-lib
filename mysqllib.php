<?php declare(strict_types = 1);

use dqdp\DBA\driver\MySQL_PDO;

# https://mariadb.com/kb/en/information-schema-columns-table/
function mysql_get_relation_fields(MySQL_PDO $db, string $table): ?array
{
	$params = $db->get_params();

	$sql = "SELECT *
	FROM information_schema.columns
	WHERE table_schema = ? AND table_name = ?
	ORDER BY ordinal_position";

	if(!($q = $db->query($sql, $params->database, $table))){
		return null;
	}

	while($r = $db->fetch_object($q)){
		$ret[] = $r;
	}

	return $ret??[];
}

# https://mariadb.com/kb/en/data-types/
function mysql_field_types(): array {
	return [
		// Numeric Data Types
		'tinyint',
		'smallint',
		'mediumint',
		'int',
		'bigint',

		'decimal',

		'float',
		'double',

		// String Data Types
		// 'binary',
		'char',
		'varchar',
		// 'varbinary',

		'tinyblob',
		'blob',
		'mediumblob',
		'longblob',

		'tinytext',
		'text',
		'mediumtext',
		'longtext',

		// Date and Time Data Types
		'date',
		'time',
		'datetime',
		'timestamp',
		// 'year',

		// 'uuid',
		// 'set',
		// 'inet4',
		// 'inet6',
		// 'enum',

	];
}

function mysql_get_pk(MySQL_PDO $db, string $table): string|array|null {
	$params = $db->get_params();

	$sql = "SELECT table_schema, table_name, column_name
	FROM information_schema.columns
	WHERE table_schema = ? AND table_name = ? AND column_key = 'PRI'";

	if(!($q = $db->query($sql, $params->database, $table))){
		return null;
	}

	while($r = $db->fetch_object($q)){
		$ret[] = trim($r->column_name);
	}

	if(!isset($ret)){
		return null;
	} elseif(count($ret) == 1){
		return $ret[0];
	} else {
		return $ret;
	}
}
