<?php

declare(strict_types = 1);

namespace dqdp\FireBird\Relation;

use dqdp\SQL\Select;

// <tconstraint> ::=
//   [CONSTRAINT constr_name]
//     { PRIMARY KEY (<col_list>) [<using_index>]
//     | UNIQUE      (<col_list>) [<using_index>]
//     | FOREIGN KEY (<col_list>)
//         REFERENCES other_table [(<col_list>)] [<using_index>]
//         [ON DELETE {NO ACTION | CASCADE | SET DEFAULT | SET NULL}]
//         [ON UPDATE {NO ACTION | CASCADE | SET DEFAULT | SET NULL}]
//     | CHECK (<check_condition>) }

class ConstraintPK extends Index
{
	static function getSQL(): Select {
		return Index::getSQL()
		->Select('relation_constraints.*, indices.*')
		->Join('RDB$RELATION_CONSTRAINTS AS relation_constraints', 'relation_constraints.RDB$INDEX_NAME = indices.RDB$INDEX_NAME')
		->Where('relation_constraints.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'')
		;
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		$PARTS = parent::ddlParts();
		$PARTS['constr_type'] = 'PRIMARY KEY';

		if($MD->SEGMENT_COUNT){
			$PARTS['col_list'] = $this->getSegments();
		}

		if($MD->INDEX_NAME == $MD->CONSTRAINT_NAME){
			$PARTS['constr_name'] = $MD->CONSTRAINT_NAME;
		}

		return $PARTS;
	}

	function ddl($PARTS = null): string {
		if(is_null($PARTS)){
			$PARTS = $this->ddlParts();
		}

		if(isset($PARTS['constr_name'])){
			$ddl[] = "CONSTRAINT $PARTS[constr_name]";
		}

		$ddl[] = $PARTS['constr_type'];

		if(isset($PARTS['col_list'])){
			$ddl[] = "(".join(",", $PARTS['col_list']).")";
		}

		return join(" ", $ddl);
	}
}
