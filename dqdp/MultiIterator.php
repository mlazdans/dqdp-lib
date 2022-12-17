<?php declare(strict_types = 1);

namespace dqdp;

use ArrayAccess;
use Generator;
use InvalidArgumentException;
use Iterator;

class MultiIterator implements Iterator, ArrayAccess {
	function __construct(private iterable $data){
		if(is_array($this->data)){
		} elseif($this->data instanceof Generator) {
		} else {
			throw new InvalidArgumentException("Unsupported iterable");
		}
	}

	function offsetExists(mixed $offset): bool {
		if(is_array($this->data)){
			return array_key_exists($offset, $this->data);
		} elseif($this->data instanceof Generator) {
			throw new InvalidArgumentException("Generators does not support ArrayAccess");
		}
	}

	function offsetGet(mixed $offset): mixed {
		if(is_array($this->data)){
			return $this->data[$offset];
		} elseif($this->data instanceof Generator) {
			throw new InvalidArgumentException("Generators does not support ArrayAccess");
		}
	}

	function offsetSet(mixed $offset, mixed $value): void {
		if(is_array($this->data)){
			if (is_null($offset)) {
				$this->data[] = $value;
			} else {
				$this->data[$offset] = $value;
			}
		} elseif($this->data instanceof Generator) {
			throw new InvalidArgumentException("Generators does not support ArrayAccess");
		}
	}

	function offsetUnset(mixed $offset): void {
		if(is_array($this->data)){
			unset($this->data[$offset]);
		} elseif($this->data instanceof Generator) {
			throw new InvalidArgumentException("Generators does not support ArrayAccess");
		}
	}

	function current(): mixed {
		if(is_array($this->data)){
			return current($this->data);
		} elseif($this->data instanceof Generator) {
			return $this->data->current();
		}
	}

	function key(): mixed {
		if(is_array($this->data)){
			return key($this->data);
		} elseif($this->data instanceof Generator) {
			return $this->data->key();
		}
	}

	function next(): void {
		if(is_array($this->data)){
			next($this->data);
		} elseif($this->data instanceof Generator) {
			$this->data->next();
		}
	}

	function rewind(): void {
		if(is_array($this->data)){
			reset($this->data);
		} elseif($this->data instanceof Generator) {
			$this->data->rewind();
		}
	}

	function valid(): bool {
		if(is_array($this->data)){
			return !is_null(key($this->data));
		} elseif($this->data instanceof Generator) {
			return $this->data->valid();
		}
	}
}
