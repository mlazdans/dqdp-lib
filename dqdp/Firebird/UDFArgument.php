<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// DECLARE EXTERNAL FUNCTION funcname
//   [{ <arg_desc_list> | ( <arg_desc_list> ) }]
//   RETURNS { <return_value> | ( <return_value> ) }
//   ENTRY_POINT 'entry_point' MODULE_NAME 'library_name'

// <arg_desc_list> ::=
//   <arg_type_decl> [, <arg_type_decl> ...]

// <arg_type_decl> ::=
//   <udf_data_type> [BY {DESCRIPTOR | SCALAR_ARRAY} | NULL]

// <udf_data_type> ::=
//     <scalar_datatype>
//   | BLOB
//   | CSTRING(length) [ CHARACTER SET charset ]

// <scalar_datatype> ::=
//   !! See Scalar Data Types Syntax !!

// <return_value> ::=
//   { <udf_data_type> | PARAMETER param_num }
//   [{ BY VALUE | BY DESCRIPTOR [FREE_IT] | FREE_IT }]

class UDFArgument extends Field
{
	const MECHANISM_VALUE                = 0;
	const MECHANISM_REFERENCE            = 1;
	const MECHANISM_DESCRIPTOR           = 2;
	const MECHANISM_BLOB_DESCRIPTOR      = 3;
	const MECHANISM_ARRAY_DESCRIPTOR     = 4;
	const MECHANISM_NULL                 = 5;

	protected $UDF;

	function __construct(UDF $UDF, $name){
		$this->UDF = $UDF;
		parent::__construct($UDF->getDb(), "$name");
	}

	static function getSQL(): Select {
		return (new Select('fa.*, c.*, cs.*, NULL AS COMPUTED_SOURCE'))
		->From('RDB$FUNCTION_ARGUMENTS fa')
		->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = fa.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = fa.RDB$CHARACTER_SET_ID)')
		->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = fa.RDB$CHARACTER_SET_ID')
		->OrderBy('fa.RDB$ARGUMENT_POSITION')
		;
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['fa.RDB$FUNCTION_NAME = ?', $this->UDF->name])
		->Where(['fa.RDB$ARGUMENT_POSITION = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(): string {
		$ddl = '';

		$MD = $this->getMetadata();
		$UDFMD = $this->UDF->getMetadata();
		$parts = $this->ddlParts();

		if(isset($parts['domainname'])){
			$ddl = $parts['domainname'];
		} else {
			$ddl = $parts['datatype'];
		}

		$paramType = "";
		if($MD->MECHANISM == UDFArgument::MECHANISM_VALUE){
			$paramType = "BY VALUE";
		} elseif($MD->MECHANISM == UDFArgument::MECHANISM_REFERENCE){
			// $paramType = " BY REFERENCE";
		} elseif($MD->MECHANISM == UDFArgument::MECHANISM_DESCRIPTOR){
			$paramType = "BY DESCRIPTOR";
		} elseif($MD->MECHANISM == UDFArgument::MECHANISM_ARRAY_DESCRIPTOR){
			$paramType = "BY SCALAR_ARRAY";
		} elseif($MD->MECHANISM == UDFArgument::MECHANISM_NULL){
			$paramType = "NULL";
		} else {
			trigger_error("Unknown MECHANISM: $MD->MECHANISM");
		}

		# Returning argument
		if($MD->ARGUMENT_POSITION == $UDFMD->RETURN_ARGUMENT){
			if($UDFMD->RETURN_ARGUMENT){
				$ddl = "RETURNS PARAMETER {$UDFMD->RETURN_ARGUMENT}";
			} else {
				$ddl = "RETURNS $ddl $paramType";

				if($MD->MECHANISM < 0){
					$ddl .= " FREE_IT";
				}
			}
		} else {
			$ddl = "$ddl $paramType";
			// $ddl = "{$ddl}{$paramType}";
		}

		return $ddl;
	}
}
