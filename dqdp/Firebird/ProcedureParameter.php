<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// CREATE PROCEDURE procname [ ( [ <in_params> ] ) ]
//   [RETURNS (<out_params>)]
//   <module-body>

// <module-body> ::=
//   !! See Syntax of Module Body !!

// <in_params> ::= <inparam> [, <inparam> ...]

// <inparam> ::= <param_decl> [{= | DEFAULT} <value>]

// <out_params> ::= <outparam> [, <outparam> ...]

// <outparam> ::= <param_decl>

// <value> ::= {<literal> | NULL | <context_var>}

// <param_decl> ::= paramname <domain_or_non_array_type> [NOT NULL]
//   [COLLATE collation]

// <type> ::=
//     <datatype>
//   | [TYPE OF] domain
//   | TYPE OF COLUMN rel.col

// <domain_or_non_array_type> ::=
//   !! See Scalar Data Types Syntax !!

class ProcedureParameter extends Field
{
	const TYPE_INPUT                = 0;
	const TYPE_RETURN               = 1;

	protected $proc;

	function __construct(Procedure $proc, $name){
		$this->proc = $proc;
		parent::__construct($proc->getDb(), $name);
	}

	static function getSQL(): Select {
		return (new Select('pp.*, f.*, c.*, cs.*'))
		->From('RDB$PROCEDURE_PARAMETERS pp')
		->LeftJoin('RDB$FIELDS f', 'f.RDB$FIELD_NAME = pp.RDB$FIELD_SOURCE')
		->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = f.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
		->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID')
		->Where('pp.RDB$SYSTEM_FLAG = 0')
		->OrderBy('RDB$PARAMETER_NUMBER')
		;
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['pp.RDB$PROCEDURE_NAME = ?', $this->proc->name])
		->Where(['pp.RDB$PARAMETER_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	//, param2 VARCHAR(101) CHARACTER SET win1257 COLLATE WIN1257_LV
	function ddl(): string {
		$DBMD = $this->getDb()->getMetadata();
		$parts = $this->ddlParts();

		$ddl = ["$this"];
		if(isset($parts['domainname'])){
			$ddl[] = $parts['domainname'];
		} else {
			$ddl[] = $parts['datatype'];
		}

		$collate = "";
		if(isset($parts['collation_name'])){
			if($parts['charset'] != $parts['collation_name']){
				$collate = "COLLATE $parts[collation_name]";
			}
		}

		if($collate || isset($parts['charset'])){
			if($collate || ($DBMD->CHARACTER_SET_NAME != $parts['charset'])){
				$ddl[] = "CHARACTER SET $parts[charset]";
			}
		}

		if(isset($parts['default'])){
			$ddl[] = $parts['default'];
		}

		if(!empty($parts['null_flag'])){
			$ddl[] = "NOT NULL";
		}

		if($collate){
			$ddl[] = $collate;
		}

		return join(" ", $ddl);
	}
}
