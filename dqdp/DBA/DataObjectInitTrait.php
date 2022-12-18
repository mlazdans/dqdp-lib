<?php declare(strict_types = 1);

namespace dqdp\DBA;

use InvalidArgumentException;
use ReflectionProperty;

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
}
