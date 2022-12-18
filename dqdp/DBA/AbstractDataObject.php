<?php declare(strict_types = 1);

namespace dqdp\DBA;

use ArrayIterator;
use dqdp\DBA\interfaces\DataMapperInterface;
use IteratorAggregate;
use stdClass;
use Traversable;

abstract class DataObject implements DataMapperInterface, IteratorAggregate {
	function __construct(?iterable $data = null, ?iterable $defaults = null){
		if(empty($data)){
			return $this;
		}

		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			if(prop_exists($data, $k)){
				$this->initPoperty(get_prop($data, $k), $k);
			} elseif($defaults !== null && prop_exists($defaults, $k)){
				$this->initPoperty(get_prop($defaults, $k), $k);
			}
		}
	}

	function getIterator(): Traversable {
		return new ArrayIterator($this);
	}

	protected static function fromDBObjectFactory(string $class, iterable $map, stdClass $o): DataObject {
		$params = [];
		foreach($map as $k=>$v){
			if(isset($o->{$k})){
				$params[$map[$k]] = $o->{$k};
			}
		}

		return new $class($params);
	}

	protected function toDBObjectFactory(iterable $map): stdClass {
		$ret = new stdClass;
		foreach($map as $k=>$v){
			if(isset($this->{$k})){
				$ret->{$v} = $this->{$k};
			}
		}

		return $ret;
	}
}
