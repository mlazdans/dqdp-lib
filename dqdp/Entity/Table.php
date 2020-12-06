<?php

declare(strict_types = 1);

namespace dqdp\Entity;

class Table {
	protected string $Name;
	protected $PK;

	function getPK(){
		return $this->PK;
	}

	function getName(){
		return $this->Name;
	}

}
