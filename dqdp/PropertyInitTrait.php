<?php declare(strict_types = 1);

namespace dqdp;

use Error;
use InvalidArgumentException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use TypeError;

trait PropertyInitTrait {
	function initPoperty(string|int $k, mixed $v, bool $is_dirty): void {
		// Skip setting non null-able properties to null
		if($is_dirty && is_null($v = $this->initValue($k, $v))){
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

	static function initValueByType(string $TypeName, mixed $v): mixed {
		switch($TypeName){
			case "int": {
				if(is_int($v)){
					return $v;
				}
				# TODO: cast tikai vajadzētu piemērot, kad iziets cauri visiem ReflectionUnionType tipiem
				# piemēram, var būt tips int|false|string
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
			case "bool":
			case "false":
			case "true": {
				return (bool)$v;
			}
			case "double":
			case "float": {
				return (float)$v;
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

		if(is_array($v) || is_object($v) || is_null($v)){
			if(is_subclass_of($TypeName, PropertyInitInterface::class)){
				return ($TypeName)::initFrom($v);
			}

			if(is_subclass_of($TypeName, Collection::class)){
				return new $TypeName($v);
			}
		}

		throw new InvalidTypeException($v);
	}

	static function initValue(string|int $k, mixed $v): mixed {
		if(is_null($v)){
			return null;
		}

		if(method_exists(static::class, "init$k")){
			throw new Error("Deprecated use of init$k()");
			// return static::{"init$k"}($v);
		}

		$Reflection = new ReflectionProperty(static::class, $k);
		$Type = $Reflection->getType();

		if($Type instanceof ReflectionNamedType) {
			return static::initValueByType($Type->getName(), $v);
		} elseif($Type instanceof ReflectionUnionType) {
			foreach($Type->getTypes() as $T){
				try {
					return static::initValueByType($T->getName(), $v);
				} catch(InvalidTypeException) {
				} catch(TypeError){
				}
			}
		} else {
			throw new InvalidArgumentException("Unsupported Reflection type: ".get_class($Type));
		}
	}

	static function initFrom(array|object|null $data = null, array|object|null $defaults = null): static {
		return static::initiator($data, $defaults, false);
	}

	static function initFromDirty(array|object|null $data = null, array|object|null $defaults = null): static {
		return static::initiator($data, $defaults, true);
	}

	private static function initiator(array|object|null $data, array|object|null $defaults, bool $is_dirty): static {
		$o = new static();
		$properties = get_class_public_vars(static::class);

		if($is_dirty){
			foreach($properties as $k=>$class_default){
				if(prop_isset($data, $k)){
					$o->$k = static::initValue($k, get_prop($data, $k));
				} elseif(prop_isset($defaults, $k)){
					$o->$k = static::initValue($k, get_prop($defaults, $k));
				}
			}
		} else {
			foreach($properties as $k=>$class_default){
				if(prop_isset($data, $k)){
					$o->$k = get_prop($data, $k);
				} elseif(prop_isset($defaults, $k)){
					$o->$k = get_prop($defaults, $k);
				}
			}
		}

		return $o;

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
