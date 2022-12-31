<?php declare(strict_types = 1);

namespace dqdp\Engine;

class UnsupportedRequestMethodException extends \InvalidArgumentException {
	function __construct(string $m){
		parent::__construct($m);
	}
}
