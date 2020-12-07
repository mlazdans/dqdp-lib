<?php

declare(strict_types = 1);

namespace dqdp\DBA;

abstract class AbstractTable {
	// protected $PK;
	// protected string $Name;
	// protected ?string $Gen;

	// function getPK(){
	// 	return $this->PK;
	// }

	// function getName(){
	// 	return $this->Name;
	// }

	// function getGen(){
	// 	return $this->Gen;
	// }

	abstract function getPK();
	abstract function getName(): string;
	abstract function getGen(): ?string;
	abstract function getFields(): array;
}
