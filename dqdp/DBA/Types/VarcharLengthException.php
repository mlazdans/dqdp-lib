<?php declare(strict_types = 1);

namespace dqdp\DBA\Types;

use InvalidArgumentException;

class VarcharLengthException extends InvalidArgumentException {
	function __construct(int $expected_lenght, int $length){
		throw new InvalidArgumentException("expected length $expected_lenght, actual $length");
	}
}
