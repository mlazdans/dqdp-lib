<?php declare(strict_types = 1);

namespace dqdp\SQL;

class Join extends Statement
{
	const INNER_JOIN = 1;
	const LEFT_OUTER_JOIN = 2;
	const RIGHT_OUTER_JOIN = 3;

	var $Type;
	var $Table;
	var Condition $Conditions;

	function __construct(string $Table, mixed $Condition = null, int $Type = Join::INNER_JOIN){
		$this->Table = $Table;
		$this->Type = $Type;
		$this->Conditions = new Condition;
		if($Condition){
			$this->Conditions->add_condition($Condition);
		}
	}

	function parse(): string {
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

		if($cond_line = (string)$this->Conditions){
			$line .= " ON $cond_line";
		}

		return $line;
	}

	function getVars(): array {
		return $this->Conditions->getVars();
	}
}
