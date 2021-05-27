<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class RelationField extends Field
{
	protected $relation;

	static function getSQL(): Select {
		return (new Select('rf.*, f.*, c.*, cs.*'))
		->From('RDB$FIELDS f')
		->Join('RDB$RELATION_FIELDS rf', 'rf.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME')
		->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = rf.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
		->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID')
		->Where('f.RDB$SYSTEM_FLAG = 0')
		->OrderBy('rf.RDB$FIELD_POSITION');
	}

	function __construct(Relation $relation, $name){
		$this->relation = $relation;
		parent::__construct($relation->getDb(), $name);
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['rf.RDB$RELATION_NAME = ?', $this->relation])
		->Where(['rf.RDB$FIELD_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function getRelation(){
		return $this->relation;
	}

	# TODO: col_constraint
	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		$DBMD = $this->getDb()->getMetadata();

		$ddl = ["$this"];
		if(isset($parts['domainname'])){
			$ddl[] = $parts['domainname'];
		} else {
			$ddl[] = $parts['datatype'];
		}

		if($parts['col_def'] == 'regular_col_def'){
			$collate = "";
			if(isset($parts['collation_name'])){
				if($parts['charset'] != $parts['collation_name']){
					$collate = "COLLATE $parts[collation_name]";
				}
			}

			if($collate || isset($parts['charset'])){
				if($collate || ($DBMD->CHARACTER_SET_NAME != $parts['charset'])){
					$ddl[] = "CHARACTER SET $parts[charset]";
				}
			}

			if(isset($parts['default'])){
				$ddl[] = $parts['default'];
			}

			# TODO: constraints

			if(!empty($parts['null_flag'])){
				$ddl[] = "NOT NULL";
			}

			if($collate){
				$ddl[] = $collate;
			}
		}

		if($parts['col_def'] == 'computed_col_def'){
			$ddl[] = "COMPUTED BY $parts[expression]";
		}

		return join(" ", $ddl);
	}
}
