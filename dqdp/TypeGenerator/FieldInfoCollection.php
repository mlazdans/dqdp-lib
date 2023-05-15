<?php declare(strict_types = 1);

namespace dqdp\TypeGenerator;

use InvalidArgumentException;

class FieldInfoCollection extends \dqdp\Collection {
	function current(): FieldInfoType {
		return parent::current();
	}

	function offsetGet(mixed $k): FieldInfoType {
		return parent::offsetGet($k);
	}

	function offsetSet(mixed $k, mixed $v): void {
		if($v instanceof FieldInfoType){
			parent::offsetSet($k, $v);
		} else {
			throw new InvalidArgumentException("Expected FieldInfoType but found: ".get_multitype($v));
		}
	}
}