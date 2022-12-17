<?php declare(strict_types = 1);

namespace dqdp\DBA\Types;

use Stringable;

class Varchar {
	readonly string $value;
	readonly int $length;

	function __construct(string|Stringable|null $value, int $length){
		$this->length = $length;

		if(isset($value)){
			$this->value = (string)$value;
			if(mb_strlen($this->value) > $length){
				throw new VarcharLengthException($length, mb_strlen($this->value));
			}
		}
	}

	function __toString(){
		return $this->value;
	}
}
