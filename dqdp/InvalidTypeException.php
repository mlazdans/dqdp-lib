<?php declare(strict_types = 1);

namespace dqdp;

class InvalidTypeException extends \InvalidArgumentException {
	function __construct(mixed $o, string $msg = null){
		$s = "Invalid type: ".get_multitype($o);

		if($msg){
			$s .= " ".$msg;
		}

		throw new \InvalidArgumentException($s);
	}
}
