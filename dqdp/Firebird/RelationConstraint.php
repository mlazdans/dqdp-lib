<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

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

abstract class RelationConstraint extends RelationIndex
{
	static function getSQL(): Select {
		return (new Select('rc.*'))->From('RDB$RELATION_CONSTRAINTS rc');
	}

	function getDefinedName(){
		$MD = $this->getMetadata();

		return ($MD->INDEX_NAME == $MD->CONSTRAINT_NAME ? $MD->CONSTRAINT_NAME : '');
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		if($MD->INDEX_NAME == $MD->CONSTRAINT_NAME){
			$PARTS['constr_name'] = $MD->CONSTRAINT_NAME;
		}

		return $PARTS??[];
	}
}
