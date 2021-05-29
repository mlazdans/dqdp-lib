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

class RelationConstraintCheck extends FirebirdObject implements DDL
{
	protected $relation;

	function __construct(Relation $relation, $name){
		$this->relation = $relation;
		parent::__construct($relation->getDb(), $name);
	}

	static function getSQL(): Select {
		return (new Select())
		// ->Select('cc.*, t.*')
		->Select('relation_constraints.*, check_constraints.*, triggers.*')
		->From('RDB$RELATION_CONSTRAINTS AS relation_constraints')
		->Join('RDB$CHECK_CONSTRAINTS check_constraints', 'check_constraints.RDB$CONSTRAINT_NAME = relation_constraints.RDB$CONSTRAINT_NAME')
		->Join('RDB$TRIGGERS triggers', 'triggers.RDB$TRIGGER_NAME = check_constraints.RDB$TRIGGER_NAME AND triggers.RDB$TRIGGER_TYPE = 1')
		->Where('relation_constraints.RDB$CONSTRAINT_TYPE = \'CHECK\'')
		;
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()
		->Where(['relation_constraints.RDB$RELATION_NAME = ?', $this->getRelation()->name])
		->Where(['relation_constraints.RDB$CONSTRAINT_NAME = ?', $this->name]);
	}

	function getRelation(){
		return $this->relation;
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		// $PARTS = parent::ddlParts();
		$PARTS = [];
		$PARTS['constr_type'] = 'CHECK';
		$PARTS['check_condition'] = $MD->TRIGGER_SOURCE;
		if($MD->INDEX_NAME == $MD->CONSTRAINT_NAME){
			$PARTS['constr_name'] = $MD->CONSTRAINT_NAME;
		}

		return $PARTS;
	}

	function ddl($PARTS = null): string {
		if(is_null($PARTS)){
			$PARTS = $this->ddlParts();
		}

		$PARTS = $this->ddlParts();

		if(isset($PARTS['constr_name'])){
			$ddl[] = "CONSTRAINT $PARTS[constr_name]";
		}

		$ddl[] = $PARTS['check_condition'];

		return join(" ", $ddl);
	}
}
