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

class ConstraintPK extends ConstraintIndex
{
	static function getSQL(): Select {
		return parent::getSQL()
		->Select('relation_constraints.*, indices.*')
		->Where('relation_constraints.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'')
		;
	}

	function ddlParts(): array {
		$PARTS = parent::ddlParts();
		$PARTS['constr_type'] = 'PRIMARY KEY';

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
