<?php declare(strict_types = 1);

namespace dqdp;

abstract class DataObject implements \IteratorAggregate, TraversableConstructor {
	# NOTE: must be defined in child, to be in scope
	abstract function initPoperty(string|int $k, mixed $v): void;

	function __construct(array|object|null $data = null, array|object|null $defaults = null){
		if(empty($data)){
			return $this;
		}

		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			if(prop_exists($data, $k)){
				$this->initPoperty($k, get_prop($data, $k));
			} elseif($defaults !== null && prop_exists($defaults, $k)){
				$this->initPoperty($k, get_prop($defaults, $k));
			}
		}
	}

	function getIterator(): \Traversable {
		return new \ArrayIterator($this);
	}
}