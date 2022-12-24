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

		# TODO: implement union and intersaction type checks
		if($Type instanceof ReflectionNamedType) {
			$TypeName = $Type->getName();

			switch($TypeName){
				case "int": {
					if(is_int($v)){
						return $v;
					}
					if(strlen($v)){
						return (int)$v;
					} else {
						return null;
					}
					// if((int)$v != $v){
					// 	throw new InvalidArgumentException("Expected $k to be int, found: ".gettype($v)." '$v'");
					// }
					// return (int)$v;
				}
				case "string": {
					return (string)$v;
				}
				case "bool": {
					return (bool)$v;
				}
				// case "array": {
				// 	if(is_array($v)){
				// 		return $v;
				// 	} else {
				// 	}
				// }
			};

			if(enum_exists($TypeName)){
				if($v instanceof $TypeName || !method_exists($TypeName, "from")){
					return $v;
				} else {
					return $TypeName::from($v);
				}
			}

			if(method_exists($TypeName, "initFrom")){
				return ($TypeName)::initFrom($v);
			}

			return $v;
		} else {
			throw new InvalidArgumentException("Unsupported Reflection type: ".get_class($Type));
		}
	}
}
