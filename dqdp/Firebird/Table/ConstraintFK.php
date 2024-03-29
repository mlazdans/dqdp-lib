<?php

declare(strict_types = 1);

namespace dqdp\FireBird\Table;

use dqdp\FireBird\Table;
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

class ConstraintFK extends ConstraintIndex
{
	static function getSQL(): Select {
		return parent::getSQL()
		->Select('indices.*, relation_constraints.*, ref_constraints.*')
		->Join('RDB$REF_CONSTRAINTS ref_constraints', 'ref_constraints.RDB$CONSTRAINT_NAME = relation_constraints.RDB$CONSTRAINT_NAME')
		->Where('relation_constraints.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\'');
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();
		$PARTS = parent::ddlParts();

		$PARTS['constr_type'] = 'FOREIGN KEY';

		$fk = new \dqdp\FireBird\Index($this->getDb(), $MD->FOREIGN_KEY);
		$fkMD = $fk->getMetadata();

		// $PARTS['other_table'] = $fkMD->RELATION_NAME;
		$PARTS['other_table'] = new Table($this->getDb(), $fkMD->RELATION_NAME);
		if($fkMD->SEGMENT_COUNT){
			$PARTS['other_table_col_list'] = $fk->getSegments();
		}

		$PARTS['update_rule'] = $MD->UPDATE_RULE;
		$PARTS['delete_rule'] = $MD->DELETE_RULE;

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

		$ddl[] = sprintf("REFERENCES %s", $PARTS['other_table']);

		if(isset($PARTS['other_table_col_list'])){
			$ddl[] = "(".join(",", $PARTS['other_table_col_list']).")";
		}

		if($PARTS['update_rule'] !== 'RESTRICT'){
			$ddl[] = "ON UPDATE $PARTS[update_rule]";
		}

		if($PARTS['delete_rule'] !== 'RESTRICT'){
			$ddl[] = "ON DELETE $PARTS[delete_rule]";
		}

		return join(" ", $ddl);
	}
}
