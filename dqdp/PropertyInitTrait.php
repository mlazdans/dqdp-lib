<?php declare(strict_types = 1);

namespace dqdp;

use InvalidArgumentException;
use ReflectionNamedType;
use ReflectionProperty;

trait PropertyInitTrait {
	static function initValue(string|int $k, mixed $v): mixed {
		if(is_null($v)){
			return null;
		}

		$Reflection = new ReflectionProperty(static::class, $k);
		$Type = $Reflection->getType();

		# TODO: implement union type checks
		// if($Type instanceof ReflectionUnionType) {

		// } else
		if($Type instanceof ReflectionNamedType) {
			switch($Type->getName()){
				case "int": {
					if((int)$v != $v){
						throw new InvalidArgumentException("Expected $k to be int, found: ".gettype($v)." ($v)");
					}
					return (int)$v;
				}
				case "string": {
					return (string)$v;
				}
				case "bool": {
					return (bool)$v;
				}
				default: {
					return $v;
				}
			};
		} else {
			throw new InvalidArgumentException("Unsupported Reflection type: ".get_class($Type));
		}
	}
}
