<?php declare(strict_types = 1);

namespace dqdp;

use InvalidArgumentException;
use ReflectionNamedType;
use ReflectionProperty;

trait PropertyInitTrait {
	function initPoperty(string|int $k, mixed $v): void {
		// Skip setting non null-able properties to null
		if(is_null($v = $this->initValue($k, $v))){
			if(prop_is_nullable($this, $k)){
				$this->$k = $v;
			}
		} else {
			$this->$k = $v;
		}
	}

	static function initFromDefaults(array|object|null $defaults = null): static {
		return static::initFrom($defaults, get_class_public_vars(static::class));
	}

	static function initValue(string|int $k, mixed $v): mixed {
		if(is_null($v)){
			return null;
		}

		if(method_exists(static::class, "init$k")){
			return static::{"init$k"}($v);
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
					return $TypeName::tryFrom($v);
				}
			}

			if(is_subclass_of($TypeName, PropertyInitInterface::class)){
				return ($TypeName)::initFrom($v);
			}

			if(is_subclass_of($TypeName, Collection::class)){
				return new $TypeName($v);
			}

			return $v;
		} else {
			throw new InvalidArgumentException("Unsupported Reflection type: ".get_class($Type));
		}
	}

	static function initFrom(array|object|null $data = null, array|object|null $defaults = null): static {
		if(is_subclass_of(static::class, '\dqdp\ParametersConstructor')){
			return static::initiator(1, $data, $defaults);
		} elseif(is_subclass_of(static::class, '\dqdp\TraversableConstructor')){
			return static::initiator(2, $data, $defaults);
		} else {
			# TODO: could relax this, defaulting to ParametersConstructor?
			throw new \Exception("Not a valid constructor interface for ".static::class.", must implement one of: \dqdp\ParametersConstructor, \dqdp\TraversableConstructor");
		}
	}

	// static function initWithObjConstructorFrom(array|object|null $data = null, array|object|null $defaults = null): static {
	// 	return static::initiator(2, $data, $defaults);
	// }

	private static function initiator(int $way, array|object|null $data = null, array|object|null $defaults = null): static {
		// if(empty($data)){
		// 	return new static;
		// }

		$params = [];
		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			if($data !== null && prop_exists($data, $k) && prop_initialized($data, $k)){
				$params[$k] = static::initValue($k, get_prop($data, $k));
			} elseif($defaults !== null && prop_exists($defaults, $k) && prop_initialized($defaults, $k)){
				$params[$k] = static::initValue($k, get_prop($defaults, $k));
			}
		}

		if($way == 1){
			return new static(...$params);
		} elseif($way == 2){
			return new static($params);
		} else {
			throw new InvalidArgumentException("Invalid value for \$way: $way");
		}
	}
}
