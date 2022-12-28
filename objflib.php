<?php declare(strict_types = 1);

use dqdp\InvalidTypeException;

/**
 * Nebūtu slikti izdomāt veidu, kā ērtāk apstrādāt obj un array pašā $func
 * Pagaidām $func dabū tikai ne-(obj|arr)
 * Tas palīdzētu tādām f-ijām, kas čeko [] vai empty object
 */

function __object_walk(mixed &$data, callable $func, mixed &$parent = null, &$parent_key = null): void {
	if(is_null($data) || is_scalar($data)){
		$func($data, $parent_key, $parent);
	} elseif(is_array($data) || $data instanceof ArrayAccess || $data instanceof stdClass || $data instanceof Traversable) {
		foreach($data as $k=>&$v){
			__object_walk($v, $func, $data, $k);
		}
	} else {
		throw new InvalidTypeException($data);
	}
}

# TODO: test if object does not get screwed over
function __object_map(mixed $data, callable $func, mixed $parent = null, $parent_key = null): mixed {
	if(is_null($data) || is_scalar($data)){
		return $func($data, $parent_key, $parent);
	} elseif(is_array($data) || $data instanceof ArrayAccess || $data instanceof stdClass || $data instanceof Traversable) {
		if(is_object($data)){
			$d = (object)(array)$data; // Funny way of cloning
		} else {
			$d = $data;
		}

		foreach($d as $k=>$v){
			set_prop($d, $k, __object_map($v, $func, $d, $k));
		}

		return $d;
	// } elseif(is_callable($data)) {
	} else {
		return $func($data, $parent_key, $parent);
		// throw new InvalidTypeException($data);
	}
}

function __object_filter(mixed $data, callable $func, mixed $parent = null, $parent_key = null): mixed {
	if(is_null($data) || is_scalar($data)){
		return $func($data, $parent_key, $parent);
	} elseif(is_array($data) || $data instanceof ArrayAccess || $data instanceof stdClass || $data instanceof Traversable) {
		if(is_object($data)){
			$d = clone $data;
		} else {
			$d = $data;
		}
		foreach($d as $k=>$v){
			$new_v = __object_filter($v, $func, $d, $k);
			if($new_v === false) {
				unset_prop($d, $k);
			} elseif($new_v !== true) {
				set_prop($d, $k, $new_v);
			}
		}
		return $d;
	} else {
		throw new InvalidTypeException($data);
	}
}

// TODO: bool carry should return immediately
function __object_reduce(mixed $data, callable $func, $carry = null, mixed $parent = null, $parent_key = null){
	if(is_null($data) || is_scalar($data)){
		$carry = $func($carry, $data, $parent_key, $parent);
	} elseif(is_array($data) || $data instanceof ArrayAccess || $data instanceof stdClass || $data instanceof Traversable) {
		foreach($data as $k=>$v){
			$carry = __object_reduce($v, $func, $carry, $k);
		}
	} else {
		throw new InvalidTypeException($data);
	}

	return $carry;
}
