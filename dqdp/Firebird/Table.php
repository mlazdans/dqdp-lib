<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\FireBird\Table\ConstraintCheck;
use dqdp\FireBird\Table\ConstraintFK;
use dqdp\FireBird\Table\ConstraintPK;
use dqdp\FireBird\Table\ConstraintUniq;
use dqdp\FireBird\Table\Index;

// CREATE [GLOBAL TEMPORARY] TABLE tablename
//   [EXTERNAL [FILE] 'filespec']
//   (<col_def> [, {<col_def> | <tconstraint>} ...])
//   [ON COMMIT {DELETE | PRESERVE} ROWS]

// <col_def> ::=
//     <regular_col_def>
//   | <computed_col_def>
//   | <identity_col_def>

// <regular_col_def> ::=
//   colname {<datatype> | domainname}
//   [DEFAULT {<literal> | NULL | <context_var>}]
//   [<col_constraint> ...]
//   [COLLATE collation_name]

// <computed_col_def> ::=
//   colname [{<datatype> | domainname}]
//   {COMPUTED [BY] | GENERATED ALWAYS AS} (<expression>)

// <identity_col_def> ::=
//   colname {<datatype> | domainname}
//   GENERATED BY DEFAULT AS IDENTITY [(START WITH startvalue)]
//   [<col_constraint> ...]

// <datatype> ::=
//     <scalar_datatype> | <blob_datatype> | <array_datatype>

// <scalar_datatype> ::=
//   !! See Scalar Data Types Syntax !!

// <blob_datatype> ::=
//   !! See BLOB Data Types Syntax !!

// <array_datatype> ::=
//   !! See Array Data Types Syntax !!

// <col_constraint> ::=
//   [CONSTRAINT constr_name]
//     { PRIMARY KEY [<using_index>]
//     | UNIQUE      [<using_index>]
//     | REFERENCES other_table [(colname)] [<using_index>]
//         [ON DELETE {NO ACTION | CASCADE | SET DEFAULT | SET NULL}]
//         [ON UPDATE {NO ACTION | CASCADE | SET DEFAULT | SET NULL}]
//     | CHECK (<check_condition>)
//     | NOT NULL }

// <tconstraint> ::=
//   [CONSTRAINT constr_name]
//     { PRIMARY KEY (<col_list>) [<using_index>]
//     | UNIQUE      (<col_list>) [<using_index>]
//     | FOREIGN KEY (<col_list>)
//         REFERENCES other_table [(<col_list>)] [<using_index>]
//         [ON DELETE {NO ACTION | CASCADE | SET DEFAULT | SET NULL}]
//         [ON UPDATE {NO ACTION | CASCADE | SET DEFAULT | SET NULL}]
//     | CHECK (<check_condition>) }

// <col_list> ::= colname [, colname ...]

// <using_index> ::= USING
//   [ASC[ENDING] | DESC[ENDING]] INDEX indexname

// <check_condition> ::=
//     <val> <operator> <val>
//   | <val> [NOT] BETWEEN <val> AND <val>
//   | <val> [NOT] IN (<val> [, <val> ...] | <select_list>)
//   | <val> IS [NOT] NULL
//   | <val> IS [NOT] DISTINCT FROM <val>
//   | <val> [NOT] CONTAINING <val>
//   | <val> [NOT] STARTING [WITH] <val>
//   | <val> [NOT] LIKE <val> [ESCAPE <val>]
//   | <val> [NOT] SIMILAR TO <val> [ESCAPE <val>]
//   | <val> <operator> {ALL | SOME | ANY} (<select_list>)
//   | [NOT] EXISTS (<select_expr>)
//   | [NOT] SINGULAR (<select_expr>)
//   | (<check_condition>)
//   | NOT <check_condition>
//   | <check_condition> OR <check_condition>
//   | <check_condition> AND <check_condition>

// <operator> ::=
//     <> | != | ^= | ~= | = | < | > | <= | >=
//   | !< | ^< | ~< | !> | ^> | ~>

// <val> ::=
//     colname ['['array_idx [, array_idx ...]']']
//   | <literal>
//   | <context_var>
//   | <expression>
//   | NULL
//   | NEXT VALUE FOR genname
//   | GEN_ID(genname, <val>)
//   | CAST(<val> AS <cast_type>)
//   | (<select_one>)
//   | func([<val> [, <val> ...]])

// <cast_type> ::= <domain_or_non_array_type> | <array_datatype>

// <domain_or_non_array_type> ::=
//   !! See Scalar Data Types Syntax !!

class Table extends Relation implements DDL
{
	/**
	 * @return Index[]
	 **/
	function getIndexes(): array {
		$sql = Index::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new Index($this, $r->INDEX_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return ConstraintFK[]
	 **/
	function getFKs(): array {
		$sql = ConstraintFK::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new ConstraintFK($this, $r->INDEX_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return ConstraintPK[]
	 **/
	function getPKs(): array {
		$sql = ConstraintPK::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new ConstraintPK($this, $r->INDEX_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return ConstraintUniq[]
	 **/
	function getUniqs(): array {
		$sql = ConstraintUniq::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new ConstraintUniq($this, $r->INDEX_NAME))->setMetadata($r);;
		}

		return $list??[];
	}

	/**
	 * @return ConstraintCheck[]
	 **/
	function getChecks(): array {
		$sql = ConstraintCheck::getSQL()->Where(['relation_constraints.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new ConstraintCheck($this, $r->CONSTRAINT_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return RelationTrigger[]
	 **/
	function getTriggers(): array {
		$sql = RelationTrigger::getSQL()->Where(['triggers.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new RelationTrigger($this, $r->TRIGGER_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	function ddlParts(): array {
		$parts = parent::ddlParts();

		if($constraint = $this->getPKs()){
			$parts['pks'] = $constraint;
		}

		if($constraint = $this->getFKs()){
			$parts['fks'] = $constraint;
		}

		if($constraint = $this->getUniqs()){
			$parts['uniqs'] = $constraint;
		}

		if($constraint = $this->getChecks()){
			$parts['checks'] = $constraint;
		}

		return $parts;
	}

	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		$ddl = [$parts['relation_name']];

		# col_def
		$col_defs = [$parts['col_def']];

		# Add tconstraints
		foreach(['pks', 'fks', 'uniqs', 'checks'] as $k){
			if(isset($parts[$k])){
				$col_defs[] = $parts[$k];
			}
		}

		foreach($col_defs as $col_def_items){
			foreach($col_def_items as $c){
				$fddl[] = $c->ddl();
			}
		}

		// $ddl[] = "(".join(",\n\t", $fddl).")";
		$ddl[] = "(";
		$ddl[] = "\t".join(",\n\t", $fddl);
		$ddl[] = ")";

		return join("\n", $ddl);
	}
}
