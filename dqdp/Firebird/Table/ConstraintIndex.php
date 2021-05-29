<?php

declare(strict_types = 1);

namespace dqdp\FireBird\Table;

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

abstract class ConstraintIndex extends Index
{
	static function getSQL(): Select {
		return \dqdp\FireBird\Index::getSQL()
		->Select('relation_constraints.*, indices.*')
		->Join('RDB$RELATION_CONSTRAINTS AS relation_constraints', 'relation_constraints.RDB$INDEX_NAME = indices.RDB$INDEX_NAME')
		;
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();
		$PARTS = parent::ddlParts();

		if($MD->SEGMENT_COUNT){
			$PARTS['col_list'] = $this->getSegments();
		}

		if($MD->INDEX_NAME == $MD->CONSTRAINT_NAME){
			$PARTS['constr_name'] = $MD->CONSTRAINT_NAME;
		}

		return $PARTS;
	}
}
