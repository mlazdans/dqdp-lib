<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class RelationField extends Field implements DDL
{
	protected $relation;

	function __construct(Relation $relation, $name){
		$this->relation = $relation;
		parent::__construct($relation->getDb(), $name);
	}

	static function getSQL(): Select {
		return parent::getSQL()
		->Select('relation_fields.*, fields.*, collations.*, character_sets.*')
		->Join('RDB$RELATION_FIELDS AS relation_fields', 'relation_fields.RDB$FIELD_SOURCE = fields.RDB$FIELD_NAME')
		->OrderBy('relation_fields.RDB$FIELD_POSITION')
		;
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()
		->Where(['relation_fields.RDB$RELATION_NAME = ?', $this->relation->name])
		->Where(['relation_fields.RDB$FIELD_NAME = ?', $this->name])
		;
	}

	function getRelation(){
		return $this->relation;
	}

	# TODO: col_constraint
	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		// $DBMD = $this->getDb()->getMetadata();

		$ddl = ["$this"];
		$ddl[] = $parts['datatype'];
		// if(isset($parts['domainname'])){
		// 	$ddl[] = $parts['domainname'];
		// } else {
		// 	$ddl[] = $parts['datatype'];
		// }

		if($parts['col_def'] == 'regular_col_def'){
			// $collate = "";
			// if(isset($parts['collation_name'])){
			// 	if($parts['charset'] != $parts['collation_name']){
			// 		$collate = "COLLATE $parts[collation_name]";
			// 	}
			// }
			// if($collate || isset($parts['charset'])){
			// 	if($collate || ($DBMD->CHARACTER_SET_NAME != $parts['charset'])){
			// 		$ddl[] = "CHARACTER SET $parts[charset]";
			// 	}
			// }

			if(isset($parts['default'])){
				$ddl[] = $parts['default'];
			}

			# TODO: constraints

			if(!empty($parts['null_flag'])){
				$ddl[] = "NOT NULL";
			}

			if(isset($parts['collation_name'])){
				$ddl[] = "COLLATE $parts[collation_name]";
			}
		}

		if($parts['col_def'] == 'computed_col_def'){
			$ddl[] = "COMPUTED BY $parts[expression]";
		}

		return join(" ", $ddl);
	}
}
