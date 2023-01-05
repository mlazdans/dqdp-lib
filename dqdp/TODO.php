<?php declare(strict_types = 1);

namespace dqdp;

use Exception;

class TODO {
	function __construct(?string $m = ""){
		throw new Exception("TODO".($m ? ": $m" : ""));
	}
}
