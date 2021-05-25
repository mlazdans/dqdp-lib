<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

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
		return (new Select('f.*, pp.*'))
		->From('RDB$PROCEDURE_PARAMETERS pp')
		->LeftJoin('RDB$FIELDS f', 'f.RDB$FIELD_NAME = pp.RDB$FIELD_SOURCE')
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

	function ddl(): string {
		return "$this ".parent::ddl();
	}
}
