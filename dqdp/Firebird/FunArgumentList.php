<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class FunArgumentList extends ObjectList
{
	protected $func;

	function __construct(Fun $func){
		$this->func = $func;
		parent::__construct($func->getDb());
	}

	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = (new Select('RDB$ARGUMENT_POSITION AS NAME'))
		->From('RDB$FUNCTION_ARGUMENTS')
		->Where(['RDB$FUNCTION_NAME = ?', $this->func])
		->OrderBy('RDB$ARGUMENT_POSITION')
		;

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new FunArgument($this->func, $r->NAME);
		}

		return $this->list;
	}
}
