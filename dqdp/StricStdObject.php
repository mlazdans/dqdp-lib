<?php declare(strict_types = 1);

namespace dqdp;

class StricStdObject implements \Countable, \ArrayAccess, \IteratorAggregate
{
	use PropertyInitTrait;

	static function initFrom(array|object|null $data = null, array|object|null $defaults = null): static {
		if(empty($data)){
			return new static;
		}

		$params = [];
		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			if(prop_exists($data, $k)){
				$params[$k] = static::initValue($k, get_prop($data, $k));
			} elseif(prop_exists($defaults, $k)){
				$params[$k] = static::initValue($k, get_prop($defaults, $k));
			}
		}

		return new static(...$params);
	}

	function getIterator(): \Traversable {
		return new \ArrayIterator(get_object_public_vars($this));
	}

	function count(): int {
		return count(get_object_public_vars($this));
	}

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
