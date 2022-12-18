<?php declare(strict_types = 1);

namespace dqdp\DBA;

use ArrayIterator;
use IteratorAggregate;
use stdClass;
use Traversable;

abstract class AbstractDataObject implements IteratorAggregate {
	# NOTE: must be defined in child, to be in scope
	abstract function initPoperty(string|int $k, mixed $v): void;
	// abstract function toDBObject(): stdClass;
	// abstract static function fromDBObject(array|object $o): AbstractDataObject;

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

	function getIterator(): Traversable {
		return new ArrayIterator($this);
	}

	// static function withDefaults(): static {
	// 	return new static(get_class_public_vars(static::class));
	// }

	// static function fromDBObjectFactory(iterable $map, array|object $o): AbstractDataObject {
	// 	$params = [];
	// 	foreach($map as $k=>$v){
	// 		if(prop_exists($o, $k)){
	// 			$params[$v] = get_prop($o, $k);
	// 		}
	// 	}

	// 	return new static($params);
	// }

	// function toDBObjectFactory(iterable $map): stdClass {
	// 	$ret = new stdClass;
	// 	foreach($map as $k=>$v){
	// 		if(isset($this->{$k})){
	// 			$ret->{$v} = $this->{$k};
	// 		}
	// 	}

	// 	return $ret;
	// }
}
