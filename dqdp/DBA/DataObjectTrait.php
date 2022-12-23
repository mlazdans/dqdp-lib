<?php declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\PropertyInitTrait;

trait DataObjectTrait {
	use PropertyInitTrait;
	function initPoperty(string|int $k, mixed $v): void {
		$this->{$k} = $this->initValue($k, $v);
	}

	// function initPoperty(string|int $k, mixed $v): void {
	// 	if(is_null($v)){
	// 		$this->{$k} = null;
	// 		return;
	// 	}

	// 	$Reflection = new ReflectionProperty(static::class, $k);
	// 	$Type = $Reflection->getType();

	// 	# TODO: implement union type checks
	// 	// if($Type instanceof ReflectionUnionType) {

	// 	// } else
	// 	if($Type instanceof ReflectionNamedType) {
	// 		switch($Type){
	// 			case "int":
	// 				if((int)$v != $v){
	// 					throw new InvalidArgumentException("Expected $k to be int, found: ".gettype($v)." ($v)");
	// 				}
	// 				$this->{$k} = (int)$v;
	// 				return;
	// 			case "string":
	// 				$this->{$k} = (string)$v;
	// 				return;
	// 			default:
	// 				$this->{$k} = $v;
	// 				return;
	// 		};
	// 	} else {
	// 		throw new InvalidArgumentException("Unsupported Reflection type: ".get_class($Type));
	// 	}
	// }

	static function withDefaults(): static {
		return new static(get_class_public_vars(static::class));
	}

	static function fromDBObjectFactory(iterable $map, array|object|null $o): ?AbstractDataObject {
		if(is_null($o)){
			return null;
		}

		$params = [];
		foreach($map as $k=>$v){
			if(prop_exists($o, $k)){
				$params[$v] = get_prop($o, $k);
			}
		}

		return new static($params);
	}

	static function toDBObjectFactory(AbstractDataObject $self, iterable $map): \stdClass {
		$ret = new \stdClass;
		foreach($map as $k=>$v){
			if(prop_exists($self, $k) && prop_initialized($self, $k)){
				$ret->{$v} = $self->{$k};
			}
		}

		return $ret;
	}

}
