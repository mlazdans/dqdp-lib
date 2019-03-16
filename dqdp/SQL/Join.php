<?php

namespace dqdp\SQL;

class Join extends Statement
{
	const INNER_JOIN = 1;
	const LEFT_OUTER_JOIN = 2;
	const RIGHT_OUTER_JOIN = 3;

	var $type;
	var $table;
	var $conditions = array();

	function __construct(string $table, $condition = null, $type = Join::INNER_JOIN){
		$this->table = $table;
		$this->type = $type;
		if($condition){
			$this->add_condition($condition);
		}
	}

	function parse(){
		switch($this->type)
		{
			case Join::INNER_JOIN:
				$line = "JOIN $this->table";
				break;
			case Join::LEFT_OUTER_JOIN:
				$line = "LEFT JOIN $this->table";
				break;
			case Join::RIGHT_OUTER_JOIN:
				$line = "RIGHT JOIN $this->table";
				break;
			default:
				return false;
		}

		if($cond_line = Condition::parse_conditions($this->conditions))
			$line .= " ON $cond_line";

		return $line;
	}

	function add_condition($condition){
		$this->conditions[] = Condition::factory($condition);
		return $this;
	}
}
