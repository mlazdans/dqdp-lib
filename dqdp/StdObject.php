<?php

declare(strict_types = 1);

namespace dqdp;

use Countable;
use Iterator;

class StdObject implements Iterator, Countable
{
	private $__stdo_debug = false;
	// private $__stdo_keys = [];
	private $__stdo_i = 0;

	function __construct($initValues = null) {
		$this->merge($initValues);

		return $this;
	}

	private function is_protected($k): bool {
		return strpos($k, '__stdo_') === 0;
	}

	private function debug_msg($msg){
		if($this->__stdo_debug){
			trigger_error($msg);
		}
	}

	function set_debug(bool $mode){
		$this->__stdo_debug = $mode;
	}

	function __get($k){
		if($this->is_protected($k)){
			$this->debug_msg("Can not access private property $k");
		// } elseif($this->exists($k)){
		// 	return $this->{$k};
		} else{
			$this->debug_msg("$k not set");
		}
	}

	function __set($k, $v){
		if($this->is_protected($k)){
			$this->debug_msg("Can not access private property $k");
		} else {
			// if(!$this->exists($k)){
			// 	$this->__stdo_keys[] = $k;
			// }
			$this->{$k} = $v;
		}
	}

	function exists($k): bool {
		return property_exists($this, $k);
	}

	function merge($o){
		return merge($this, $o);
	}

	function merge_only(array $only, $o){
		return merge_only($only, $this, $o);
	}

	function count() : int {
		return count(get_object_vars($this));
		//return count($this->__stdo_keys);
	}

	function current() {
		return $this->{$this->key()};
	}

	function key() {
		return get_object_vars($this)[$this->__stdo_i]??null;
		//return $this->__stdo_keys[$this->__stdo_i]??null;
	}

	function next(): void {
		++$this->__stdo_i;
	}

	function rewind(): void {
		$this->__stdo_i = 0;
	}

	function valid(): bool {
		return $this->exists($this->key());
	}
}
