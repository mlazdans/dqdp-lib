<?php

namespace dqdp\SQL;

class Join extends Condition
{
	const INNER_JOIN = 1;
	const LEFT_OUTER_JOIN = 2;
	const RIGHT_OUTER_JOIN = 3;

	var $Type;
	var $Table;

	function __construct(string $Table, $Condition = null, $Type = Join::INNER_JOIN){
		$this->Table = $Table;
		$this->Type = $Type;
		if($Condition){
			$this->add_condition($Condition);
		}
	}

	function parse(){
		switch($this->Type)
		{
			case Join::INNER_JOIN:
				$line = "JOIN $this->Table";
				break;
			case Join::LEFT_OUTER_JOIN:
				$line = "LEFT JOIN $this->Table";
				break;
			case Join::RIGHT_OUTER_JOIN:
				$line = "RIGHT JOIN $this->Table";
				break;
			default:
				return false;
		}

		if($cond_line = Condition::parse_conditions($this->Conditions))
			$line .= " ON $cond_line";

		return $line;
	}
}
