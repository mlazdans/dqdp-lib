<?php

namespace dqdp;

class StdObject
{
	static $debug = false;

	function __construct($initValues = null){
		$this->merge($initValues);
		return $this;
	}

	function __get($v){
		if(StdObject::$debug){
			if(!isset($this->{$v})){
				trigger_error("$v not set");
			}
		}
		return isset($this->{$v}) ? $this->{$v} : null;
	}

	function exists($v){
		return property_exists($this, $v);
	}

	function merge($o){
		return merge($this, $o);
	}
}
