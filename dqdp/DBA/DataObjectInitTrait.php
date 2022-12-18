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

	static function withDefaults(): static {
		$O = new static;
		$properties = get_class_public_vars(static::class);
		foreach($properties as $k=>$class_default){
			$O->initPoperty($class_default, $k);
		}

		return $O->init();
	}
}
