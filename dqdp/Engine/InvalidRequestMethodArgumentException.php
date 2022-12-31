<?php declare(strict_types = 1);

namespace dqdp\Engine;

use BackedEnum;

class InvalidRequestMethodArgumentException extends \InvalidArgumentException {
	function __construct(BackedEnum $en, string $required){
		parent::__construct(get_class($en)."::$en->value requires $required");
	}
}
