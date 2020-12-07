<?php

declare(strict_types = 1);

namespace dqdp\DBA;

abstract class AbstractTable {
	protected string $Name;
	protected $PK;

	function getPK(){
		return $this->PK;
	}

	function getName(){
		return $this->Name;
	}

	abstract function getFields() : array;
}
