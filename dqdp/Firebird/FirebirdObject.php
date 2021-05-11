<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;
use stdClass;

abstract class FirebirdObject
{
	const TYPE_TABLE                  = 0;
	const TYPE_VIEW                   = 1;
	const TYPE_TRIGGER                = 2;
	const TYPE_COMPUTED_FIELD         = 3;
	const TYPE_VALIDATION             = 4;
	const TYPE_PROCEDURE              = 5;
	const TYPE_EXPRESSION_INDEX       = 6;
	const TYPE_EXCEPTION              = 7;
	const TYPE_USER                   = 8;
	const TYPE_FIELD                  = 9;
	const TYPE_INDEX                  = 10;
	const TYPE_USER_GROUP             = 12;
	const TYPE_ROLE                   = 13;
	const TYPE_GENERATOR              = 14;
	const TYPE_FUNCTION               = 15;
	const TYPE_BLOB_FILTER            = 16;
	const TYPE_COLLATION              = 17;

	// Custom types
	const TYPE_FUNCTION_ARGUMENT      = 10001;
	const TYPE_PROCEDURE_PARAMETER    = 10002;
	const TYPE_DOMAIN                 = 10003;
	const TYPE_INDEX_SEGMENT          = 10004;

	protected $type;
	protected $name;
	protected $dependencies;
	protected $dependents;
	protected $db;
	protected $metadata;

	private static $discardFields = array(
		'RDB$RUNTIME',
		'RDB$COMPUTED_BLR',
		);

	abstract function loadMetadata();

	function __construct(Database $db, $name){
		if($this->type === null){
			trigger_error("Type not set", E_USER_WARNING);
		}

		$this->db = $db;
		$this->name = trim($name);
		# TODO: switch
		$this->loadMetadata();
		//$this->getDependencies();
	}

	function isNameQuotable() {
		$a = preg_match_all("/^[a-z][a-z0-9\$_]*$/i", $this->name, $m);
		return !($a > 0);
	}

	function __toString(){
		if($this->isNameQuotable()){
			return "\"$this->name\"";
		} else {
			return $this->name;
		}
	}

	function getDependencies(){
		if($this->dependencies !== null){
			return $this->dependencies;
		}

		$this->dependencies = array();
		$sql = (new Select('RDB$DEPENDED_ON_TYPE, RDB$DEPENDED_ON_NAME'))
		->From('RDB$DEPENDENCIES')
		->Where(['RDB$DEPENDENT_TYPE = ?', $this->type])
		->Where(['RDB$DEPENDENT_NAME = ?', $this->name])
		;
		// $sql = sprintf('SELECT RDB$DEPENDED_ON_TYPE, RDB$DEPENDED_ON_NAME
		// 	FROM RDB$DEPENDENCIES
		// 	WHERE RDB$DEPENDENT_TYPE = %d AND RDB$DEPENDENT_NAME = \'%s\'
		// 	GROUP BY RDB$DEPENDED_ON_TYPE, RDB$DEPENDED_ON_NAME',
		// 	$this->type,
		// 	addslashes($this->name)
		// );

		//print_r($sql);
		//print "----------\n\n";

		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$type = (int)$r->{'RDB$DEPENDED_ON_TYPE'};
			$name = trim($r->{'RDB$DEPENDED_ON_NAME'});
			if($type == FirebirdObject::TYPE_COLLATION){
				continue;
			}

			print "$this depends on ($name, $type)\n";
			$o = FirebirdObject::create($this->getDb(), $name, $type);
			$o->getDependencies();
			$this->dependencies[] = $o;
		}

		# Table foreign keys
		if($this->type == FirebirdObject::TYPE_TABLE){
			if($FKs = $this->getFK()){
				foreach($FKs as $fk){
					$i = new Index($this->getDb(), $fk->getMetadata()->FOREIGN_KEY);
					//print "FK:start\n";
					//print_r($i);
					//print "FK:end\n\n\n\n";

					//print sprintf("Depend on FK (%s)\n", $i->getMetadata()->RELATION_NAME);
					$this->dependencies[] = new Table($this->getDb(), $i->getMetadata()->RELATION_NAME);
					//$this->dependencies = array_merge($this->dependencies, $fk->getDependencies());
					//$index = new IbaseIndex($this->db, $fk->getMetadata()->FOREIGN_KEY);
					//print_r($fk->getMetadata());
					//printf("\t%s->%s\n", $fk, $index->getMetadata()->RELATION_NAME);

					//$o = $this->db->getObjectList()->get($index->getMetadata()->RELATION_NAME, FirebirdObject::TYPE_INDEX);
					//$this->dependencies[] = $o;
				}
			}
		}

		return $this->dependencies;
	}

	function getDependents(){
		if($this->dependents !== null){
			return $this->dependents;
		}

		$this->dependents = array();
		if($this->type == FirebirdObject::TYPE_FIELD){
			$name = $this->getTable();
		} else {
			$name = $this->name;
		}

		$sql = (new Select('RDB$DEPENDENT_TYPE, RDB$DEPENDENT_NAME'))
		->From('RDB$DEPENDENCIES')
		->Where(['RDB$DEPENDED_ON_NAME = ?', $name])
		;

		// $sql = sprintf('
		// 	SELECT
		// 		RDB$DEPENDENT_TYPE, RDB$DEPENDENT_NAME
		// 	FROM
		// 		RDB$DEPENDENCIES
		// 	WHERE
		// 		RDB$DEPENDED_ON_NAME = \'%s\'
		// 	',
		// 	addslashes($name)
		// );
		if($this->type == FirebirdObject::TYPE_FIELD){
			$sql->Where(['RDB$FIELD_NAME = ?', $this]);
			// $sql .= sprintf(' AND RDB$FIELD_NAME = \'%s\'', $this);
		}
		print_r($sql);
		print "\n----------\n\n";

		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$type = (int)$r->{'RDB$DEPENDENT_TYPE'};
			$name = trim($r->{'RDB$DEPENDENT_NAME'});
			if($type == FirebirdObject::TYPE_COLLATION){
				continue;
			}

			print "$this is dependent of ($name, $type)\n";
			$o = FirebirdObject::create($this->getDb(), $name, $type);
			$o->getDependents();
			$this->dependencies[] = $o;
		}

		# Table foreign keys
		/*
		if($this->type == FirebirdObject::TYPE_TABLE){
			if($FKs = $this->getFK()){
				foreach($FKs as $fk){
					$i = new IbaseIndex($this->getDb(), $fk->getMetadata()->FOREIGN_KEY);
					//print sprintf("Depend on FK (%s)\n", $i->getMetadata()->RELATION_NAME);
					$this->dependencies[] = new IbaseTable($this->getDb(), $i->getMetadata()->RELATION_NAME);
					//$this->dependencies = array_merge($this->dependencies, $fk->getDependencies());
					//$index = new IbaseIndex($this->db, $fk->getMetadata()->FOREIGN_KEY);
					//print_r($fk->getMetadata());
					//printf("\t%s->%s\n", $fk, $index->getMetadata()->RELATION_NAME);

					//$o = $this->db->getObjectList()->get($index->getMetadata()->RELATION_NAME, FirebirdObject::TYPE_INDEX);
					//$this->dependencies[] = $o;
				}
			}
		}
		*/

		return $this->dependents;
	}

	function getMetadata(){
		return $this->metadata;
	}

	static function rdbs2human($r){
		return str_replace('RDB$', '', trim($r));
	}

	function getDb(){
		return $this->db;
	}

	protected function loadMetadataBySQL($sql){
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
			$this->metadata = new stdClass;
			foreach($r as $k=>$v){
				# Skip RUNTIME binary field
				if(in_array($k, self::$discardFields)){
					continue;
				}
				$this->metadata->{FirebirdObject::rdbs2human($k)} = $v;
			}
			$c++;
		}

		return $this->metadata;
	}

	static function create($db, $name, $type){
		switch($type)
		{
			case FirebirdObject::TYPE_DOMAIN:
				$class = "IbaseDomain";
				break;
			case FirebirdObject::TYPE_EXCEPTION:
				$class = "IbaseException";
				break;
			case FirebirdObject::TYPE_FUNCTION:
				$class = "IbaseFunction";
				break;
			# TODO: FunctionArgument?
			case FirebirdObject::TYPE_GENERATOR:
				$class = "IbaseGenerator";
				break;
			case FirebirdObject::TYPE_INDEX:
				$class = "IbaseIndex";
				break;
			# TODO: IndexSegment?
			case FirebirdObject::TYPE_PROCEDURE:
				$class = "IbaseProcedure";
				break;
			# TODO: ProcedureParameter?
			case FirebirdObject::TYPE_TABLE:
				$class = "IbaseTable";
				break;
			# TODO: TableField?
			case FirebirdObject::TYPE_TRIGGER:
				$class = "IbaseTrigger";
				break;
			case FirebirdObject::TYPE_VIEW:
				$class = "IbaseView";
				break;
			default:
				trigger_error("Unsupported FirebirdObject type=$type for name=$name", E_USER_ERROR);
				break;
		}

		return new $class($db, $name);
	}
}
