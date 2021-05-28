<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// CREATE [UNIQUE] [ASC[ENDING] | DESC[ENDING]]
//   INDEX indexname ON tablename
//   {(col [, col …]) | COMPUTED BY (<expression>)}

class RelationIndex extends Index
{
	protected $relation;

	function __construct(Relation $relation, $name){
		$this->relation = $relation;
		parent::__construct($relation->getDb(), $name);
	}

	static function getSQL(): Select {
		return parent::getSQL()
		->LeftJoin('RDB$RELATION_CONSTRAINTS rc', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		->Where('rc.RDB$CONSTRAINT_TYPE IS NULL');
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['i.RDB$RELATION_NAME = ?', $this->getRelation()->name])
		->Where(['i.RDB$INDEX_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function getRelation(){
		return $this->relation;
	}

	function ddl($PARTS = null): string {
		if(is_null($PARTS)){
			$PARTS = $this->ddlParts();
		}

		if(isset($PARTS['unique'])){
			$ddl[] = $PARTS['unique'];
		}

		if($PARTS['type'] != "ASCENDING"){
			$ddl[] = $PARTS['type'];
		}

		$ddl[] = "INDEX $PARTS[indexname] ON $PARTS[tablename]";

		if(isset($PARTS['col_list'])){
			$ddl[] = "(".join(",", $PARTS['col_list']).")";
		} else {
			$ddl[] = $PARTS['expression'];
		}

		return join(" ", $ddl);
	}
}
