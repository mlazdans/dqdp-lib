<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class Procedure extends FirebirdObject
{
	const TYPE_LEGACY          = 0;
	const TYPE_SELECTABLE      = 1;
	const TYPE_EXECUTABLE      = 2;

	protected $parameters;

	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_PROCEDURE;
		parent::__construct($db, $name);
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('RDB$PROCEDURE_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			*
		FROM
			RDB$PROCEDURES
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

	function getParameters(){
		if(empty($this->parameters)){
			$a = new ProcedureParameterList($this);
			$this->parameters = $a->get();
		}
		return $this->parameters;
	}

	function ddl(){
		$ddl = array();
		$ddl[] = "SET TERM ^ ;";

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
		$ddl[] = $MT->PROCEDURE_SOURCE."^";
		$ddl[] = "SET TERM ; ^";

		return join("\n", $ddl);
	}

}
