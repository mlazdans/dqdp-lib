<?php declare(strict_types = 1);

namespace dqdp\SQL;

class WrapSelect extends Statement
{
	function __construct(protected string|Select $select, protected string $wrapper){
	}

	function parse(): string {
		$this->addVar($this->select->getVars());
		return "$this->wrapper(($this->select))";
	}
}
