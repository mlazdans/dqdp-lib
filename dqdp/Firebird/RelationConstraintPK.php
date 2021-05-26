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

class RelationConstraintPK extends RelationConstraint
{
	static function getSQL(): Select {
		return parent::getSQL()
		->Select('i.*')
		->Join('RDB$INDICES i', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		->Where('i.RDB$SYSTEM_FLAG = 0')
		->Where('rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'');
	}

	function ddl(): string {
		$MD = $this->getMetadata();

		if($MD->CONSTRAINT_NAME){
			$ddl[] = "CONSTRAINT $MD->CONSTRAINT_NAME";
		}

		$ddl[] = 'PRIMARY KEY';

		if($MD->SEGMENT_COUNT){
			$segments = $this->getSegments();
			$ddl[] = "(".join(",", $segments).")";
		}

		return join(" ", $ddl);
	}
}
