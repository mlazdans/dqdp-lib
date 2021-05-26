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

class RelationConstraintFK extends RelationConstraint
{
	static function getSQL(): Select {
		return parent::getSQL()
		->Select('i.*, refc.*')
		->Join('RDB$INDICES i', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		->LeftJoin('RDB$REF_CONSTRAINTS refc', 'refc.RDB$CONSTRAINT_NAME = rc.RDB$CONSTRAINT_NAME')
		->Where('i.RDB$SYSTEM_FLAG = 0')
		->Where('rc.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\'')
		;
	}

	function ddl(): string {
		$MD = $this->getMetadata();

		$ddl[] = "ALTER TABLE $MD->RELATION_NAME ADD";

		# TODO: skip auto generated names
		if($MD->CONSTRAINT_NAME){
			$ddl[] = "CONSTRAINT $MD->CONSTRAINT_NAME";
		}

		$ddl[] = 'FOREIGN KEY';

		if($MD->SEGMENT_COUNT){
			$segments = $this->getSegments();
			$ddl[] = "(".join(",", $segments).")";
		}

		# TODO: iekš getSQL()?
		$fk = new RelationConstraintPK($this->getDb(), $MD->FOREIGN_KEY);
		$fkMD = $fk->getMetadata();

		$ddl[] = "REFERENCES $fkMD->RELATION_NAME (".join(",", $fk->getSegments()).")";

		if($MD->UPDATE_RULE !== 'RESTRICT'){
			$ddl[] = "ON UPDATE $MD->UPDATE_RULE";
		}

		if($MD->DELETE_RULE !== 'RESTRICT'){
			$ddl[] = "ON DELETE $MD->DELETE_RULE";
		}

		return join(" ", $ddl);
	}
}
