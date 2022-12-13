<?php declare(strict_types = 1);

namespace dqdp;

use Generator;
use InvalidArgumentException;
use Iterator;

abstract class Collection implements Iterator {
	abstract function current();

	function __construct(private iterable $data){
		if(is_array($this->data)){
		} elseif($this->data instanceof Generator) {
		} else {
			throw new InvalidArgumentException("Unsupported iterable");
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
