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

class UDF extends FirebirdType
{
	const TYPE_VALUE      = 0;
	const TYPE_BOOLEAN    = 1;

	protected $arguments;

	// function __construct(Database $db, $name){
	// 	$this->type = FirebirdObject::TYPE_FUNCTION;
	// 	parent::__construct($db, $name);
	// }

	static function getSQL(): Select {
		return (new Select())
		->From('RDB$FUNCTIONS')
		->Where('RDB$SYSTEM_FLAG = 0')
		->OrderBy('RDB$FUNCTION_NAME');
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['RDB$FUNCTION_NAME = ?', "$this"])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function getArguments(): array {
		$sql = UDFArgument::getSQL()
		->Where(['fa.RDB$FUNCTION_NAME = ?', $this->name])
		;

		foreach($this->getList($sql) as $r){
			$list[] = new UDFArgument($this, $r->ARGUMENT_POSITION);
		}

		return $list??[];
	}

	function ddl(): string {
		$ddl = array();
		$MT = $this->getMetadata();

		$ddl[] = sprintf('DECLARE EXTERNAL FUNCTION "%s"', $this);

		$in_args = array();
		$out_arg = false;
		$args = $this->getArguments();
		foreach($args as $arg){
			$AMT = $arg->getMetadata();
			# Returning argument
			if($AMT->ARGUMENT_POSITION == $MT->RETURN_ARGUMENT){
				$out_arg = $arg->ddl();
			} else {
				$in_args[] = $arg->ddl();
			}
		}

		if($in_args){
			$ddl[] = join(",", $in_args);
		}

		if($out_arg){
			$ddl[] = $out_arg;
		}

		$ddl[] = sprintf("ENTRY_POINT '%s'\nMODULE_NAME '%s'", $MT->ENTRYPOINT, $MT->MODULE_NAME);

		return join("\n", $ddl);
	}
}
