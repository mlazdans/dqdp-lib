<?php declare(strict_types = 1);

namespace dqdp\DBA\Types;

use InvalidArgumentException;

class Decimal {
	readonly string $value;
	readonly int $precision;
	readonly int $scale;

	function __construct(mixed $value, int $precision, int $scale){
		$this->precision = $precision;
		$this->scale = $scale;

		if(isset($value)){
			$this->value = number_format((float)$value, $scale, '.', '');
			if($this->value != $value){
				throw new InvalidArgumentException("Expected decimal, found: ".gettype($value)." with value $value");
			}
		}
	}

	function __toString(): string {
		return $this->value;
	}
}
