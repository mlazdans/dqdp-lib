<?php declare(strict_types = 1);

namespace dqdp\Engine;

use dqdp\StricStdObject;
use dqdp\TraversableConstructor;

class Args extends StricStdObject implements TraversableConstructor {
	// function set(int|string $k, mixed $v): static {
	// 	$this->$k = $v;
	// 	return $this;
	// }

	// function get(int|string $k): mixed {
	// 	return $this->$k??null;
	// }
}
