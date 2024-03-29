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

class Procedure extends FirebirdObject implements DDL
{
	const TYPE_LEGACY          = 0;
	const TYPE_SELECTABLE      = 1;
	const TYPE_EXECUTABLE      = 2;

	protected $parameters;

	static function getSQL(): Select {
		return (new Select())->From('RDB$PROCEDURES AS procedures')->Where('procedures.RDB$SYSTEM_FLAG = 0');
		// ->OrderBy('RDB$PROCEDURE_NAME');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['procedures.RDB$PROCEDURE_NAME = ?', $this->name]);
	}

	function getParameters(){
		$sql = ProcedureParameter::getSQL()->Where(['procedure_parameters.RDB$PROCEDURE_NAME = ?', $this->name]);

		foreach($this->getList($sql) as $r){
			$list[] = (new ProcedureParameter($this, $r->PARAMETER_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	function ddlParts(): array {
		trigger_error("Not implemented yet");
		return [];
	}

	function ddl($PARTS = null): string {
		// if(is_null($PARTS)){
		// 	$PARTS = $this->ddlParts();
		// }

		$ddl = [];

		$MT = $this->getMetadata();

		$in_args = array();
		$out_args = array();

		$params = $this->getParameters();
		foreach($params as $param){
			$PMT = $param->getMetadata();
			if($PMT->PARAMETER_TYPE == ProcedureParameter::TYPE_RETURN){
				$out_args[] = $param->ddl();
			} else {
				$in_args[] = $param->ddl();
			}
		}

		if($in_args){
			$ddl[] = "CREATE PROCEDURE $this (";
			$ddl[] = "\t".join(",\n\t", $in_args)."\n)";
		} else {
			$ddl[] = "CREATE PROCEDURE $this";
		}

		if($out_args){
			$ddl[] = "RETURNS (\n\t".join(",\n\t", $out_args)."\n)";
		}

		$ddl[] = "AS";
		$ddl[] = $MT->PROCEDURE_SOURCE;

		return join("\n", $ddl);
	}
}
