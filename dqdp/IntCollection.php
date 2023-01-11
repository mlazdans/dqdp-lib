<?php declare(strict_types = 1);

namespace dqdp;

class IntCollection extends Collection {
	function current(): int {
		return parent::current();
	}

	function offsetGet(mixed $k): int {
		return parent::offsetGet($k);
	}

	function offsetSet(mixed $k, mixed $v): void {
		parent::offsetSet($k, (int)$v);
	}
}
