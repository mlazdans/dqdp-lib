<?php declare(strict_types = 1);

namespace dqdp\TypeGenerator;

class FieldInfoType
{
	# TODO: remove null from mandatory fields
	function __construct(
		public ?string $name     = null,
		public ?FieldType $type  = null,
		public ?string $php_type = null,
		public ?bool $nullable   = null,
		public ?string $nullflag = null,
		public ?bool $readonly   = null,

		public ?int $precision   = null, // for decimal
		public ?int $scale       = null,
		public ?int $len         = null, // for chars
		public mixed $default    = null,
	) {}
}
