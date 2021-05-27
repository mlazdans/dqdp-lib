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

class RelationConstraintUniq extends RelationConstraint
{
	static function getSQL(): Select {
		return parent::getSQL()
		->Select('i.*')
		->Join('RDB$INDICES i', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		->Where('i.RDB$SYSTEM_FLAG = 0')
		->Where('rc.RDB$CONSTRAINT_TYPE = \'UNIQUE\'');
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		$PARTS = parent::ddlParts();
		$PARTS['constr_type'] = 'UNIQUE';

		if($MD->SEGMENT_COUNT){
			$PARTS['col_list'] = $this->getSegments();
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
