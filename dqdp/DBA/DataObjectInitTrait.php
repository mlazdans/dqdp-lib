<?php declare(strict_types = 1);

namespace dqdp\DBA;

use InvalidArgumentException;
use ReflectionProperty;
use stdClass;

trait DataObjectInitTrait {
	function initPoperty(string|int $k, mixed $v): void {
		if(is_null($v)){
			$this->{$k} = null;
			return;
		}

		$Reflection = new ReflectionProperty(static::class, $k);
		switch($Reflection->getType()->getName()){
			case "int":
				if((int)$v != $v){
					throw new InvalidArgumentException("Expected int, found: ".gettype($v));
				}
				$this->{$k} = (int)$v;
				return;
			case "string":
				$this->{$k} = (string)$v;
				return;
			default:
				$this->{$k} = $v;
				return;
		};
	}

	static function withDefaults(): static {
		return new static(get_class_public_vars(static::class));
	}

	static function fromDBObjectFactory(iterable $map, array|object $o): AbstractDataObject {
		$params = [];
		foreach($map as $k=>$v){
			if(prop_exists($o, $k)){
				$params[$v] = get_prop($o, $k);
			}
		}

		return new static($params);
	}

	static function toDBObjectFactory(AbstractDataObject $self, iterable $map): stdClass {
		$ret = new stdClass;
		foreach($map as $k=>$v){
			if(isset($self->{$k})){
				$ret->{$v} = $self->{$k};
			}
		}

		return $ret;
	}

}
