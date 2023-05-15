<?php declare(strict_types = 1);

namespace dqdp\TypeGenerator;

use dqdp\DBA\interfaces\DBAInterface;

abstract class AbstractTypeGenerator
{
	function __construct(
		public string $name,
		public bool $is_relation,
		public bool $is_proc,
		public ?string $namespace = null
	) {}

	// Returns name of sequence or null
	abstract function getSequenceName(): ?string;
	abstract function getDB(): DBAInterface;

	// Converts field name to PHP class property
	abstract function field2prop(string $name): string;

	// Converts table, view or procedure name to PHP class name
	abstract function self2class(): string;

	// Returns table, view or procedure field information. See more @FieldInfoType
	abstract function getFields(): FieldInfoCollection;

	// Returns procedure args information. See more @FieldInfoType
	abstract function getProcArgs(): FieldInfoCollection;
}
