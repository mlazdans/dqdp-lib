<?php

namespace dqdp;

use InvalidArgumentException;

class Params {
	function __construct(array $params = []){
		foreach($params as $k=>$v){
			if(property_exists($this, $k)){
				$this->{$k} = $v;
			} else {
				throw new InvalidArgumentException("Undefined property: $k");
			}
		}
	}
}
