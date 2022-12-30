<?php declare(strict_types = 1);

namespace dqdp;

class UnsupportedRequestMethodException extends \InvalidArgumentException {
	function __construct(string $m){
		parent::__construct($m);
	}
}
