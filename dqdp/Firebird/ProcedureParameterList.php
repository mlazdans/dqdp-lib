<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class ProcedureParameterList extends ObjectList
{
	protected $proc;

	function __construct(Procedure $proc){
		$this->proc = $proc;
		parent::__construct($proc->getDb());
	}

	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		# Argument $name is realy an integer - ARGUMENT_POSITION
		$sql = (new Select('RDB$PARAMETER_NAME AS NAME'))
		->From('RDB$PROCEDURE_PARAMETERS')
		->Where(['RDB$PROCEDURE_NAME = ?', $this->proc])
		->OrderBy('RDB$PARAMETER_NUMBER')
		;
		// $sql = '
		// SELECT
		// 	RDB$PARAMETER_NAME AS NAME
		// FROM
		// 	RDB$PROCEDURE_PARAMETERS
		// WHERE
		// 	RDB$PROCEDURE_NAME = \''.$this->proc.'\'
		// ORDER BY
		// 	RDB$PARAMETER_NUMBER
		// ';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new ProcedureParameter($this->proc, $r->NAME);
		}

		return $this->list;
	}
}
