<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class Fun extends FirebirdObject
{
	const TYPE_VALUE      = 0;
	const TYPE_BOOLEAN    = 1;

	protected $arguments;

	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_FUNCTION;
		parent::__construct($db, $name);
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('RDB$FUNCTION_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			*
		FROM
			RDB$FUNCTIONS
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

	function getArguments(){
		if(empty($this->arguments)){
			$a = new FunArgumentList($this);
			$this->arguments = $a->get();
		}
		return $this->arguments;
	}

	function ddl(){
		$ddl = array();
		$MT = $this->getMetadata();

		$ddl[] = sprintf(
			'DECLARE EXTERNAL FUNCTION "%s"',
			$this
			);

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

		$ddl[] = sprintf(
			"ENTRY_POINT '%s' MODULE_NAME '%s'",
			$MT->ENTRYPOINT,
			$MT->MODULE_NAME
			);

		return join("\n", $ddl);
	}
}

