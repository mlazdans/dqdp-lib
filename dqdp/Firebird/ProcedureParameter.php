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

class ProcedureParameter extends Field implements DDL
{
	const TYPE_INPUT                = 0;
	const TYPE_RETURN               = 1;

	protected $proc;

	function __construct(Procedure $proc, $name){
		$this->proc = $proc;
		parent::__construct($proc->getDb(), "$name");
	}

	static function getSQL(): Select {
		return parent::getSQL()
		->Select('procedure_parameters.*, fields.*, collations.*, character_sets.*')
		->Join('RDB$PROCEDURE_PARAMETERS AS procedure_parameters', 'procedure_parameters.RDB$FIELD_SOURCE = fields.RDB$FIELD_NAME')
		->OrderBy('procedure_parameters.RDB$PARAMETER_NUMBER')
		;
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()
		->Where(['procedure_parameters.RDB$PROCEDURE_NAME = ?', $this->proc->name])
		->Where(['procedure_parameters.RDB$PARAMETER_NAME = ?', $this->name])
		;
	}

	//, param2 VARCHAR(101) CHARACTER SET win1257 COLLATE WIN1257_LV
	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		// $DBMD = $this->getDb()->getMetadata();

		$ddl = ["$this"];
		$ddl[] = $parts['datatype'];
		// if(isset($parts['domainname'])){
		// 	$ddl[] = $parts['domainname'];
		// } else {
		// 	$ddl[] = $parts['datatype'];
		// }

		// $collate = "";
		// if(isset($parts['collation_name'])){
		// 	if($parts['charset'] != $parts['collation_name']){
		// 		$collate = "COLLATE $parts[collation_name]";
		// 	}
		// }

		// if($collate || isset($parts['charset'])){
		// 	if($collate || ($DBMD->CHARACTER_SET_NAME != $parts['charset'])){
		// 		$ddl[] = "CHARACTER SET $parts[charset]";
		// 	}
		// }

		if(isset($parts['default'])){
			$ddl[] = $parts['default'];
		}

		if(!empty($parts['null_flag'])){
			$ddl[] = "NOT NULL";
		}

		if(isset($parts['collation_name'])){
			$ddl[] = "COLLATE $parts[collation_name]";
		}

		return join(" ", $ddl);
	}
}
