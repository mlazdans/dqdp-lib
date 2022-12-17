<?php declare(strict_types = 1);

namespace dqdp\DBA;

use InvalidArgumentException;
use ReflectionProperty;

trait DataObjectInitTrait {
	function initPoperty($v, $k): void {
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

	// function initFromIterable(array|object $O, array|object $DO = null): void {
	// 	$properties = get_class_public_vars(static::class);
	// 	foreach($properties as $k=>$class_default){
	// 		if(prop_exists($O, $k)){
	// 			$this->initPoperty(get_prop($O, $k), $k);
	// 		} elseif($DO !== null && prop_exists($DO, $k)){
	// 			$this->initPoperty(get_prop($DO, $k), $k);
	// 		}
	// 	}
	// }
	static function fromIterable(array|object $DATA): static {
		$O = new static;
		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			if(prop_exists($DATA, $k)){
				$O->initPoperty(get_prop($DATA, $k), $k);
			// } elseif($DO !== null && prop_exists($DO, $k)){
			// 	$this->initPoperty(get_prop($DO, $k), $k);
			}
		}

		return $O->init();
	}

	static function withDefaults(): static {
		$O = new static;
		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			$O->initPoperty($class_default, $k);
		}

		return $O->init();
	}
}
