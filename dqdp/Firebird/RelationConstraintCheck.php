<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Join;
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

class RelationConstraintCheck extends RelationConstraint
{
	static function getSQL(): Select {
		return parent::getSQL()
		->Select('cc.*, t.*')
		->Join('RDB$CHECK_CONSTRAINTS cc', 'cc.RDB$CONSTRAINT_NAME = rc.RDB$CONSTRAINT_NAME')
		->Join('RDB$TRIGGERS t', 't.RDB$TRIGGER_NAME = cc.RDB$TRIGGER_NAME AND T.RDB$TRIGGER_TYPE = 1')
		->Where('rc.RDB$CONSTRAINT_TYPE = \'CHECK\'')
		;
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['rc.RDB$CONSTRAINT_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(): string {
		$MD = $this->getMetadata();

		if($MD->CONSTRAINT_NAME){
			$ddl[] = "CONSTRAINT $MD->CONSTRAINT_NAME";
		}

		$ddl[] = $MD->TRIGGER_SOURCE;

		return join(" ", $ddl);
	}
}
