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
	abstract function get_sequence_name(): ?string;
	abstract function get_db(): DBAInterface;

	// Converts field name to PHP class property
	abstract function field2prop(string $name): string;

	// Converts table, view or procedure name to PHP class name
	abstract function self2class(): string;

	// Returns table, view or procedure field information. See more @FieldInfoType
	abstract function get_fields(): FieldInfoCollection;

	// Returns procedure args information. See more @FieldInfoType
	abstract function get_proc_args(): ?FieldInfoCollection;

	// Returns primary key if any
	abstract function get_pk(): string|array|null;

	// Returns folder path to store generated code
	abstract function get_output_folder(): string;
}
