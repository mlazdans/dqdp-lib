<?php declare(strict_types = 1);

namespace dqdp;

class StdObject extends \stdClass implements \Countable, \ArrayAccess, \IteratorAggregate
{
	function __construct(array|object|null $initValues = null) {
		$this->merge($initValues);
	}

	function __unset($k): void {
		unset($this->{$k});
	}

	function __get($k){
		return $this->{$k}??null;
	}

	function __set($k, $v){
		$this->{$k} = $v;
	}

	function getIterator(): \Traversable {
		return new \ArrayIterator(get_object_public_vars($this));
	}

	function exists(string $k): bool {
		return $this->offsetExists($k);
	}

	function merge($o){
		return merge($this, $o);
	}

	function merge_only(array $only, $o){
		return merge_only($only, $this, $o);
	}

	function count(): int {
		return count(get_object_public_vars($this));
	}

	// function current(): mixed {
	// 	return current($this->__data);
	// }

	// function key(): mixed {
	// 	return key($this->__data);
	// }

	// function next(): void {
	// 	next($this->__data);
	// }

	// function rewind(): void {
	// 	reset($this->__data);
	// }

	// function valid(): bool {
	// 	return !is_null(key($this->__data));
	// }

	function offsetExists(mixed $k): bool {
		if(is_int($k)){
			return property_exists($this, (string)$k);
		} else {
			return property_exists($this, $k);
		}
	}

	function offsetGet(mixed $k): mixed {
		return $this->{$k};
	}

	function offsetSet(mixed $k, mixed $v): void {
		if (is_null($k)) {
			$this->{$this->count() + 1} = $v;
		} elseif(is_int($k)) {
			if($this->offsetExists($k)){
				$this->{$k} = $v;
			} else {
				$this->{max($this->count(), $k)} = $v;
			}
		} else {
			$this->{$k} = $v;
		}
	}

	function offsetUnset(mixed $k): void {
		unset($this->{$k});
	}

}
