<?php declare(strict_types = 1);

namespace dqdp;

class InvalidTypeException extends \InvalidArgumentException {
	function __construct(mixed $o){
		$t = gettype($o);
		$s = "Unsupported type: $t";
		if($t == "object"){
			$s .= "(".get_class($o).")";
		}

		throw new \InvalidArgumentException($s);
	}
}
