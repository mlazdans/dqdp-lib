<?php

namespace dqdp\FireBird;

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

		# Argument $name is realy an integer - ARGUMENT_POSITION
		$sql = '
		SELECT
			RDB$ARGUMENT_POSITION AS NAME
		FROM
			RDB$FUNCTION_ARGUMENTS
		WHERE
			RDB$FUNCTION_NAME = \''.$this->func.'\'
		ORDER BY
			RDB$ARGUMENT_POSITION
		';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new FunArgument($this->func, $r->NAME);
		}

		return $this->list;
	}
}
