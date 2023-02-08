<?php declare(strict_types = 1);

namespace dqdp;

class StringCollection extends Collection {
	function current(): string {
		return parent::current();
	}

	function offsetGet(mixed $k): string {
		return parent::offsetGet($k);
	}

	function offsetSet(mixed $k, mixed $v): void {
		parent::offsetSet($k, (string)$v);
	}
}
