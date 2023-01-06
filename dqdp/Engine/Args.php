<?php declare(strict_types = 1);

namespace dqdp\Engine;

use dqdp\TraversableConstructor;
use stdClass;

class Args extends stdClass implements TraversableConstructor {
	function __construct(array|object|null $data = null, array|object|null $defaults = null){
		$looper = function(array|object|null $o = null): void {
			if(is_null($o)) {
				return;
			}

			$o_data = is_array($o) ? $o : get_object_public_vars($o);
			foreach($o_data as $k=>$v){
				if(!isset($this->$k)){
					$this->$k = $v;
				}
			}
		};

		$looper($data);
		$looper($defaults);
	}

	static function initFrom(array|object|null $data = null, array|object|null $defaults = null): static {
		return new static($data, $defaults);
	}

	// function set(int|string $k, mixed $v): static {
	// 	$this->$k = $v;
	// 	return $this;
	// }

	// function get(int|string $k): mixed {
	// 	return $this->$k??null;
	// }
}
