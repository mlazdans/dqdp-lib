<?php declare(strict_types = 1);

namespace dqdp;

abstract class DataObject implements \IteratorAggregate, TraversableConstructor, PropertyInitInterface {
	# NOTE: must be defined in child, to be in scope
	// abstract function initPoperty(string|int $k, mixed $v): void;

	function __construct(array|object|null $data = null, array|object|null $defaults = null, bool $is_dirty = false){
		if(is_null($data) && is_null($defaults)){
			return $this;
		}

		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			if($data !== null && prop_isset($data, $k)){
				$this->initPoperty($k, get_prop($data, $k), $is_dirty);
			} elseif($defaults !== null && prop_isset($defaults, $k)){
				$this->initPoperty($k, get_prop($defaults, $k), $is_dirty);
			}
		}
	}

	function getIterator(): \Traversable {
		return new \ArrayIterator($this);
	}

	# TODO: varbūt kādreiz ieslēgt implements \ArrayAccess
	// function offsetExists(mixed $offset): bool
	// {
	// 	return property_exists($this, $offset);
	// }

	// function offsetGet(mixed $offset): mixed
	// {
	// 	return $this->{$offset};

	// }

	// function offsetSet(mixed $offset, mixed $value): void
	// {
	// 	$this->{$offset} = $value;
	// }

	// function offsetUnset(mixed $offset): void
	// {
	// 	unset($this->$offset);
	// }
}
