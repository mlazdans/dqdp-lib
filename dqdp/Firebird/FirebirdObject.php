<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;
use stdClass;

abstract class FirebirdObject
{
	const RESERVERD_WORDS = [
		'ADD', 'ADMIN', 'ALL', 'ALTER', 'AND', 'ANY', 'AS', 'AT', 'AVG', 'BEGIN', 'BETWEEN', 'BIGINT', 'BIT_LENGTH',
		'BLOB', 'BOOLEAN', 'BOTH', 'BY', 'CASE', 'CAST', 'CHAR', 'CHARACTER', 'CHARACTER_LENGTH', 'CHAR_LENGTH',
		'CHECK', 'CLOSE', 'COLLATE', 'COLUMN', 'COMMIT', 'CONNECT', 'CONSTRAINT', 'CORR', 'COUNT', 'COVAR_POP',
		'COVAR_SAMP', 'CREATE', 'CROSS', 'CURRENT', 'CURRENT_CONNECTION', 'CURRENT_DATE', 'CURRENT_ROLE', 'CURRENT_TIME',
		'CURRENT_TIMESTAMP', 'CURRENT_TRANSACTION', 'CURRENT_USER', 'CURSOR', 'DATE', 'DAY', 'DEC', 'DECIMAL',
		'DECLARE', 'DEFAULT', 'DELETE', 'DELETING', 'DETERMINISTIC', 'DISCONNECT', 'DISTINCT', 'DOUBLE', 'DROP',
		'ELSE', 'END', 'ESCAPE', 'EXECUTE', 'EXISTS', 'EXTERNAL', 'EXTRACT', 'FALSE', 'FETCH', 'FILTER', 'FLOAT', 'FOR',
		'FOREIGN', 'FROM', 'FULL', 'FUNCTION', 'GDSCODE', 'GLOBAL', 'GRANT', 'GROUP', 'HAVING', 'HOUR', 'IN', 'INDEX', 'INNER',
		'INSENSITIVE', 'INSERT', 'INSERTING', 'INT', 'INTEGER', 'INTO', 'IS', 'JOIN', 'LEADING', 'LEFT', 'LIKE', 'LONG', 'LOWER',
		'MAX', 'MERGE', 'MIN', 'MINUTE', 'MONTH', 'NATIONAL', 'NATURAL', 'NCHAR', 'NO', 'NOT', 'NULL', 'NUMERIC', 'OCTET_LENGTH',
		'OF', 'OFFSET', 'ON', 'ONLY', 'OPEN', 'OR', 'ORDER', 'OUTER', 'OVER', 'PARAMETER', 'PLAN', 'POSITION', 'POST_EVENT',
		'PRECISION', 'PRIMARY', 'PROCEDURE', 'RDB$DB_KEY', 'RDB$RECORD_VERSION', 'REAL', 'RECORD_VERSION', 'RECREATE', 'RECURSIVE',
		'REFERENCES', 'REGR_AVGX', 'REGR_AVGY', 'REGR_COUNT', 'REGR_INTERCEPT', 'REGR_R2', 'REGR_SLOPE', 'REGR_SXX', 'REGR_SXY',
		'REGR_SYY', 'RELEASE', 'RETURN', 'RETURNING_VALUES', 'RETURNS', 'REVOKE', 'RIGHT', 'ROLLBACK', 'ROW', 'ROWS', 'ROW_COUNT',
		'SAVEPOINT', 'SCROLL', 'SECOND', 'SELECT', 'SENSITIVE', 'SET', 'SIMILAR', 'SMALLINT', 'SOME', 'SQLCODE', 'SQLSTATE', 'START',
		'STDDEV_POP', 'STDDEV_SAMP', 'SUM', 'TABLE', 'THEN', 'TIME', 'TIMESTAMP', 'TO', 'TRAILING', 'TRIGGER', 'TRIM', 'TRUE', 'UNION',
		'UNIQUE', 'UNKNOWN', 'UPDATE', 'UPDATING', 'UPPER', 'USER', 'USING', 'VALUE', 'VALUES', 'VARCHAR', 'VARIABLE', 'VARYING', 'VAR_POP',
		'VAR_SAMP', 'VIEW', 'WHEN', 'WHERE', 'WHILE', 'WITH', 'YEAR'
	];

	// protected $type;
	protected $name;
	// protected $dependencies;
	// protected $dependents;
	protected $db;
	protected $metadata;

	private static $discardFields = ['RDB$RUNTIME', 'RDB$COMPUTED_BLR'];

	abstract static function getSQL(): Select;
	abstract function getMetadataSQL(): Select;
	abstract function ddlParts(): array;
	// abstract function ddl($PARTS = null): string;

	// private function loadMetadata(){
	// 	return $this->loadMetadataBySQL($this->getSQL());
	// }

	function __construct(Database $db, $name){
		$this->name = $name;
		$this->setDb($db);

		return $this;
	}

	function setDb(Database $db){
		$this->db = $db;

		return $this;
	}

	static function isNameQuotable($name) {
		$uname = strtoupper($name);

		return ($uname !== $name) || in_array($uname, FirebirdObject::RESERVERD_WORDS) || !preg_match("/^[A-Z][A-Z0-9\$_]*$/", $uname);
	}

	function __toString(){
		if($this->isNameQuotable($this->name)){
			return "\"$this->name\"";
		} else {
			return $this->name;
		}
	}

	static function strquote(string $str): string {
		return str_replace("'", "''", $str);
	}

	// function getDependencies(){
	// 	if($this->dependencies !== null){
	// 		return $this->dependencies;
	// 	}

	// 	$this->dependencies = array();
	// 	$sql = (new Select('RDB$DEPENDED_ON_TYPE, RDB$DEPENDED_ON_NAME'))
	// 	->From('RDB$DEPENDENCIES')
	// 	->Where(['RDB$DEPENDENT_TYPE = ?', $this->type])
	// 	->Where(['RDB$DEPENDENT_NAME = ?', $this->name])
	// 	;
	// 	// $sql = sprintf('SELECT RDB$DEPENDED_ON_TYPE, RDB$DEPENDED_ON_NAME
	// 	// 	FROM RDB$DEPENDENCIES
	// 	// 	WHERE RDB$DEPENDENT_TYPE = %d AND RDB$DEPENDENT_NAME = \'%s\'
	// 	// 	GROUP BY RDB$DEPENDED_ON_TYPE, RDB$DEPENDED_ON_NAME',
	// 	// 	$this->type,
	// 	// 	addslashes($this->name)
	// 	// );

	// 	//print_r($sql);
	// 	//print "----------\n\n";

	// 	$conn = $this->getDb()->getConnection();
	// 	$q = $conn->Query($sql);
	// 	while($r = $conn->fetch_object($q)){
	// 		$type = (int)$r->{'RDB$DEPENDED_ON_TYPE'};
	// 		$name = trim($r->{'RDB$DEPENDED_ON_NAME'});
	// 		if($type == FirebirdObject::TYPE_COLLATION){
	// 			continue;
	// 		}

	// 		print "$this depends on ($name, $type)\n";
	// 		$o = FirebirdObject::create($this->getDb(), $name, $type);
	// 		$o->getDependencies();
	// 		$this->dependencies[] = $o;
	// 	}

	// 	# Table foreign keys
	// 	if($this->type == FirebirdObject::TYPE_TABLE){
	// 		if($FKs = $this->getFK()){
	// 			foreach($FKs as $fk){
	// 				$i = new Index($this->getDb(), $fk->getMetadata()->FOREIGN_KEY);
	// 				//print "FK:start\n";
	// 				//print_r($i);
	// 				//print "FK:end\n\n\n\n";

	// 				//print sprintf("Depend on FK (%s)\n", $i->getMetadata()->RELATION_NAME);
	// 				$this->dependencies[] = new Table($this->getDb(), $i->getMetadata()->RELATION_NAME);
	// 				//$this->dependencies = array_merge($this->dependencies, $fk->getDependencies());
	// 				//$index = new IbaseIndex($this->db, $fk->getMetadata()->FOREIGN_KEY);
	// 				//print_r($fk->getMetadata());
	// 				//printf("\t%s->%s\n", $fk, $index->getMetadata()->RELATION_NAME);

	// 				//$o = $this->db->getObjectList()->get($index->getMetadata()->RELATION_NAME, FirebirdObject::TYPE_INDEX);
	// 				//$this->dependencies[] = $o;
	// 			}
	// 		}
	// 	}

	// 	return $this->dependencies;
	// }

	// function getDependents(){
	// 	if($this->dependents !== null){
	// 		return $this->dependents;
	// 	}

	// 	$this->dependents = array();
	// 	if($this->type == FirebirdObject::TYPE_FIELD){
	// 		$name = $this->getTable();
	// 	} else {
	// 		$name = $this->name;
	// 	}

	// 	$sql = (new Select('RDB$DEPENDENT_TYPE, RDB$DEPENDENT_NAME'))
	// 	->From('RDB$DEPENDENCIES')
	// 	->Where(['RDB$DEPENDED_ON_NAME = ?', $name])
	// 	;

	// 	// $sql = sprintf('
	// 	// 	SELECT
	// 	// 		RDB$DEPENDENT_TYPE, RDB$DEPENDENT_NAME
	// 	// 	FROM
	// 	// 		RDB$DEPENDENCIES
	// 	// 	WHERE
	// 	// 		RDB$DEPENDED_ON_NAME = \'%s\'
	// 	// 	',
	// 	// 	addslashes($name)
	// 	// );
	// 	if($this->type == FirebirdObject::TYPE_FIELD){
	// 		$sql->Where(['RDB$FIELD_NAME = ?', $this]);
	// 		// $sql .= sprintf(' AND RDB$FIELD_NAME = \'%s\'', $this);
	// 	}
	// 	// print_r($sql);
	// 	// print "\n----------\n\n";

	// 	$conn = $this->getDb()->getConnection();
	// 	$q = $conn->Query($sql);
	// 	while($r = $conn->fetch_object($q)){
	// 		$type = (int)$r->{'RDB$DEPENDENT_TYPE'};
	// 		$name = trim($r->{'RDB$DEPENDENT_NAME'});
	// 		if($type == FirebirdObject::TYPE_COLLATION){
	// 			continue;
	// 		}

	// 		print "$this is dependent of ($name, $type)\n";
	// 		$o = FirebirdObject::create($this->getDb(), $name, $type);
	// 		$o->getDependents();
	// 		$this->dependencies[] = $o;
	// 	}

	// 	# Table foreign keys
	// 	/*
	// 	if($this->type == FirebirdObject::TYPE_TABLE){
	// 		if($FKs = $this->getFK()){
	// 			foreach($FKs as $fk){
	// 				$i = new IbaseIndex($this->getDb(), $fk->getMetadata()->FOREIGN_KEY);
	// 				//print sprintf("Depend on FK (%s)\n", $i->getMetadata()->RELATION_NAME);
	// 				$this->dependencies[] = new IbaseTable($this->getDb(), $i->getMetadata()->RELATION_NAME);
	// 				//$this->dependencies = array_merge($this->dependencies, $fk->getDependencies());
	// 				//$index = new IbaseIndex($this->db, $fk->getMetadata()->FOREIGN_KEY);
	// 				//print_r($fk->getMetadata());
	// 				//printf("\t%s->%s\n", $fk, $index->getMetadata()->RELATION_NAME);

	// 				//$o = $this->db->getObjectList()->get($index->getMetadata()->RELATION_NAME, FirebirdObject::TYPE_INDEX);
	// 				//$this->dependencies[] = $o;
	// 			}
	// 		}
	// 	}
	// 	*/

	// 	return $this->dependents;
	// }

	# TODO: configurable
	function setMetadata($metadata){
		$this->metadata = $metadata;

		return $this;
	}

	function getMetadata(){
		# TODO: check if load once
		if(!$this->metadata){
			$this->loadMetadataBySQL($this->getMetadataSQL());
		}

		return $this->metadata;
	}

	static function rdbs2human($r){
		$r = str_replace('RDB$', '', trim($r));
		$r = str_replace('MON$', '', $r);
		return $r;
	}

	function getDb(){
		return $this->db;
	}

	protected function getList($sql){
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_assoc($q))
			$list[] = FirebirdObject::process_rdb($r);

		return $list??[];
	}

	private function loadMetadataBySQL(Select $sql){
		if($this->metadata !== null){
			return $this->metadata;
		}

		$c = 0;
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_assoc($q)){
			if($c){
				ob_start();
				debug_print_backtrace();
				$trace = ob_get_clean();
				trigger_error("More than one row for metadata\n$sql\nTrace:\n$trace\n", E_USER_ERROR);
				break;
			}

			$this->metadata = FirebirdObject::process_rdb($r);
			// foreach($r as $k=>$v){
			// 	# Skip RUNTIME binary field
			// 	if(in_array($k, self::$discardFields)){
			// 		continue;
			// 	}

			// 	if(is_string($v)){
			// 		$v = trim($v);
			// 	}

			// 	$this->metadata->{FirebirdObject::rdbs2human($k)} = $v;
			// }
			$c++;
		}

		return $this->metadata;
	}

	protected static function process_rdb(Array $data){
		$o = new stdClass;
		foreach($data as $k=>$v){
			# Skip RUNTIME binary field
			if(in_array($k, self::$discardFields)){
				continue;
			}

			if(is_string($v)){
				$v = trim($v);
			}

			$o->{FirebirdObject::rdbs2human($k)} = $v;
		}

		return $o;
	}

	// static function create($db, $name, $type){
	// 	if($type == FirebirdObject::TYPE_TABLE)
	// 		$class = "dqdp\FireBird\Table";
	// 	elseif($type == FirebirdObject::TYPE_VIEW)
	// 		$class = "dqdp\FireBird\View";
	// 	elseif($type == FirebirdObject::TYPE_TRIGGER)
	// 		$class = "dqdp\FireBird\Trigger";
	// 	elseif($type == FirebirdObject::TYPE_DOMAIN)
	// 		$class = "dqdp\FireBird\Domain";
	// 	elseif($type == FirebirdObject::TYPE_COMPUTED_FIELD)
	// 		$class = "dqdp\FireBird\Field";
	// 	// elseif($type == FirebirdObject::TYPE_VALIDATION)
	// 	elseif($type == FirebirdObject::TYPE_PROCEDURE)
	// 		$class = "dqdp\FireBird\Procedure";
	// 	// elseif($type == FirebirdObject::TYPE_EXPRESSION_INDEX)
	// 	elseif($type == FirebirdObject::TYPE_EXCEPTION)
	// 		$class = "dqdp\FireBird\Exception";
	// 	// elseif($type == FirebirdObject::TYPE_USER)
	// 	// elseif($type == FirebirdObject::TYPE_FIELD)
	// 	elseif($type == FirebirdObject::TYPE_INDEX)
	// 		$class = "dqdp\FireBird\Index";
	// 	// elseif($type == FirebirdObject::TYPE_USER_GROUP)
	// 	// elseif($type == FirebirdObject::TYPE_ROLE)
	// 	elseif($type == FirebirdObject::TYPE_GENERATOR)
	// 		$class = "dqdp\FireBird\Generator";
	// 	elseif($type == FirebirdObject::TYPE_FUNCTION)
	// 		$class = "dqdp\FireBird\Function";
	// 	// elseif($type == FirebirdObject::TYPE_BLOB_FILTER)
	// 	// elseif($type == FirebirdObject::TYPE_COLLATION)
	// 	else
	// 		trigger_error("Unsupported FirebirdObject type=$type for name=$name", E_USER_ERROR);

	// 	return new $class($db, $name);
	// }
}
