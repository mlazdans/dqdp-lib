<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// DECLARE EXTERNAL FUNCTION funcname
// [<arg_type_decl> [, <arg_type_decl> ...]]
// RETURNS {
//   sqltype [BY {DESCRIPTOR | VALUE}] |
//   CSTRING(length) |
//   PARAMETER param_num }
// [FREE_IT]
// ENTRY_POINT 'entry_point' MODULE_NAME 'library_name';

// <arg_type_decl> ::=
//   sqltype [{BY DESCRIPTOR} | NULL] |
//   CSTRING(length) [NULL]

class UDF extends FirebirdObject implements DDL
{
	const TYPE_VALUE      = 0;
	const TYPE_BOOLEAN    = 1;

	protected $arguments;

	static function getSQL(): Select {
		return (new Select())->From('RDB$FUNCTIONS AS functions')->Where('functions.RDB$SYSTEM_FLAG = 0');
		// ->OrderBy('RDB$FUNCTION_NAME');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['functions.RDB$FUNCTION_NAME = ?', $this->name]);
	}

	/**
	 * @return UDFArgument[]
	 **/
	function getArguments(): array {
		$sql = UDFArgument::getSQL()->Where(['function_arguments.RDB$FUNCTION_NAME = ?', $this->name]);

		foreach($this->getList($sql) as $r){
			$list[] = (new UDFArgument($this, $r->ARGUMENT_POSITION))->setMetadata($r);
		}

		return $list??[];
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		$PARTS['funcname'] = "$this";
		$PARTS['entry_point'] = $MD->ENTRYPOINT;
		$PARTS['library_name'] = $MD->MODULE_NAME;

		$args = $this->getArguments();
		foreach($args as $arg){
			$AMD = $arg->getMetadata();
			# Returning argument
			if($AMD->ARGUMENT_POSITION == $MD->RETURN_ARGUMENT){
				$out_arg = $arg->ddl();
			} else {
				$in_args[] = $arg->ddl();
			}
		}

		if(isset($in_args)){
			$PARTS['arg_type_decl'] = $in_args;
		}

		if(isset($out_arg)){
			$PARTS['returns'] = $out_arg;
		}

		return $PARTS;
	}

	function ddl($PARTS = null): string {
		if(is_null($PARTS)){
			$PARTS = $this->ddlParts();
		}

		// $ddl[] = sprintf('DECLARE EXTERNAL FUNCTION "%s"', $this);
		$ddl = [$PARTS['funcname']];

		if(isset($PARTS['arg_type_decl'])){
			$ddl[] = join(", ", $PARTS['arg_type_decl']);
		}

		if(isset($PARTS['returns'])){
			$ddl[] = $PARTS['returns'];
		}

		$ddl[] = "ENTRY_POINT '$PARTS[entry_point]'";
		$ddl[] = "MODULE_NAME '$PARTS[library_name]'";

		return join("\n", $ddl);
	}
}
