<?php

namespace dqdp;

class EmptyObject
{
	static $debug = false;

	function __construct($initValues = null){
		$this->merge($initValues);
		return $this;
	}

	function __get($v){
		if(EmptyObject::$debug){
			if(!isset($this->{$v})){
				trigger_error("$v not set");
			}
		}
		return isset($this->{$v}) ? $this->{$v} : null;
	}

	/*
	function isset($v){
		return property_exists($this, $v);
	}
	*/

	function merge($o){
		return merge2($this, $o);
	}
/*
	function merge($o){
		if(is_array($o)){
			$a = $o;
		} elseif(is_object($o)){
			$a = get_object_vars($o);
		} else {
			return $this;
		}
		foreach($a as $k=>$v){
			$this->{$k} = $v;
		}

		return $this;
	}
*/
/*
	function is_empty(){
		return is_empty($this);
	}
	*/
}
