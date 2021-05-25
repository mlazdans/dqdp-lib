<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Procedure extends FirebirdType
{
	const TYPE_LEGACY          = 0;
	const TYPE_SELECTABLE      = 1;
	const TYPE_EXECUTABLE      = 2;

	protected $parameters;

	static function getSQL(): Select {
		return (new Select())
		->From('RDB$PROCEDURES')
		->Where('RDB$SYSTEM_FLAG = 0')
		->OrderBy('RDB$PROCEDURE_NAME');
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['RDB$PROCEDURE_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function getParameters(){
		$sql = ProcedureParameter::getSQL()
		->Where(['pp.RDB$PROCEDURE_NAME = ?', $this->name])
		;

		foreach($this->getList($sql) as $r){
			$list[] = new ProcedureParameter($this, $r->PARAMETER_NAME);
		}

		return $list??[];
	}

	function ddl(): string {
		$ddl = array();
		// $ddl[] = "SET TERM ^ ;";

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
		// $ddl[] = "SET TERM ; ^";

		return join("\n", $ddl);
	}
}
