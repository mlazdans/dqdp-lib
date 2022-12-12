<?php

declare(strict_types = 1);

namespace dqdp\DBA;

abstract class AbstractTable {
	abstract function getPK();
	abstract function getName(): string;
	abstract function getGen(): ?string;
	abstract function getFields(): array;
	function __toString(){
		return $this->getName();
	}
}
