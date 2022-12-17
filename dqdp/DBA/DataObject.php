<?php declare(strict_types = 1);

namespace dqdp\DBA;

use ArrayIterator;
use dqdp\DBA\interfaces\DataMapperInterface;
use IteratorAggregate;
use stdClass;
use Traversable;

abstract class DataObject implements DataMapperInterface, IteratorAggregate {
	// abstract function initFromIterable(array|object $O, array|object $DO = null): void;
	function getIterator(): Traversable {
		return new ArrayIterator($this);
	}

	protected static function fromDBObjectFactory(string $class, array $map, stdClass $o): DataObject {
		$params = [];
		foreach($map as $k=>$v){
			if(isset($o->{$k})){
				$params[$map[$k]] = $o->{$k};
			}
		}

		return $class::fromIterable($params)->init();
	}

	protected function toDBObjectFactory(array $map): stdClass {
		$ret = new stdClass;
		foreach($map as $k=>$v){
			if(isset($this->{$k})){
				$ret->{$v} = $this->{$k};
			}
		}

		return $ret;

		// $ret = new stdClass;
		// foreach($map as $k=>$v){
		// 	if(isset($this->{$k})){
		// 		$ret->{$map[$k]} = $v;
		// 	}
		// }

		// return $ret;
	}
}
