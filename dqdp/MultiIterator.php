<?php declare(strict_types = 1);

namespace dqdp;

use ArrayAccess;
use Countable;
use Generator;
use Iterator;

class MultiIterator implements Iterator, ArrayAccess, Countable {
	private $data = [];

	function __construct(array|object|null $data = null){
		if(is_null($data)){
			$this->data = [];
		} elseif(is_array($data) || $data instanceof ArrayAccess){
			foreach($data as $k=>$v){
				$this[$k] = $v;
			}
			// $this->data = $data;
		// } elseif($data instanceof ArrayAccess){
		// 	$this->data = clone $data;
		} elseif(is_object($this->data)){
			// $this->data = get_object_vars($data);
			foreach(get_object_vars($data) as $k=>$v){
				$this[$k] = $v;
			}
		} elseif($this->data instanceof Generator) {
			new TODO("Implement and test");
		} else {
			throw new InvalidTypeException($data);
		}
	}

	// function getIterator(): Traversable {
	// 	return new ArrayIterator($this->data);
	// }

	function offsetExists(mixed $k): bool {
		return prop_exists($this->data, $k);
		// if(is_array($this->data)){
		// 	return array_key_exists($offset, $this->data);
		// } elseif($this->data instanceof Generator) {
		// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
		// }
	}

	function offsetGet(mixed $k): mixed {
		return get_prop_ref($this->data, $k);
		// if(is_array($this->data)){
		// 	return $this->data[$offset];
		// } elseif($this->data instanceof Generator) {
		// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
		// }
	}

	function offsetSet(mixed $k, mixed $v): void {
		if (is_null($k)) {
			$this->data[] = $v;
		} elseif(is_int($k)) {
			if($this->offsetExists($k)){
				$this->data[$k] = $v;
			} else {
				$this->data[] = $v;
			}
		} else {
			$this->data[$k] = $v;
		}

	// 	set_prop($this->data, $k, $value);
	// 	// if(is_array($this->data)){
	// 	// 	if (is_null($offset)) {
	// 	// 		$this->data[] = $value;
	// 	// 	} else {
	// 	// 		$this->data[$offset] = $value;
	// 	// 	}
	// 	// } elseif($this->data instanceof Generator) {
	// 	// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
	// 	// }
	}

	function offsetUnset(mixed $k): void {
		unset($this->data[$k]);
		// if(is_string($k) || is_int($k)){
		// 	unset_prop($this->data, $k);
		// } else {
		// 	throw new InvalidTypeException($k);
		// }
		// if(is_array($this->data)){
		// 	unset($this->data[$offset]);
		// } elseif($this->data instanceof Generator) {
		// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
		// }
	}

	function current(): mixed {
		return current($this->data);
		// if(is_array($this->data)){
		// 	return current($this->data);
		// } elseif($this->data instanceof Generator) {
		// 	return $this->data->current();
		// }
	}

	function key(): mixed {
		return key($this->data);
		// if(is_array($this->data)){
		// 	return key($this->data);
		// } elseif($this->data instanceof Generator) {
		// 	return $this->data->key();
		// }
	}

	function next(): void {
		next($this->data);
		// if(is_array($this->data)){
		// 	next($this->data);
		// } elseif($this->data instanceof Generator) {
		// 	$this->data->next();
		// }
	}

	function rewind(): void {
		reset($this->data);
		// if(is_array($this->data)){
		// 	reset($this->data);
		// } elseif($this->data instanceof Generator) {
		// 	$this->data->rewind();
		// }
	}

	function valid(): bool {
		return !is_null(key($this->data));
		// if(is_array($this->data)){
		// 	return !is_null(key($this->data));
		// } elseif($this->data instanceof Generator) {
		// 	return $this->data->valid();
		// }
	}

	function count(): int {
		return count($this->data);
	}

	function toArray(): array {
		return $this->data;
	}

	function has(mixed $e){
		return in_array($e, $this->data);
	}
}
