<?php

namespace dqdp\SQL;

use BadMethodCallException;
use dqdp\SQL;

abstract class Statement
{
	protected $lex;
	protected $before;
	protected $after;

	abstract function parse();

	function __toString(){
		return $this->parse();
	}

	function lex(){
		return $this->lex??SQL::$lex;
	}

	function setLex($lex){
		return $this->lex = $lex;
	}

	private function __add_trigger($trigger, $clause, $k, $v = null){
		$this->{$trigger}[$clause][$k] = $v;
		return $this;
	}

	function after($clause, $k, $v = null){
		return $this->__add_trigger('after', $clause, $k, $v);
	}

	function before($clause, $k, $v = null){
		return $this->__add_trigger('before', $clause, $k, $v);
	}

	protected function merge_lines(&$lines, $parts){
		$lines = array_merge($lines, $parts);
	}

	private function __get_lines($trigger, $clause){
		return array_values($this->{$trigger}[$clause]??[]);
	}

	protected function before_lines($clause){
		return $this->__get_lines('before', $clause);
	}

	protected function after_lines($clause){
		return $this->__get_lines('after', $clause);
	}

	function __call(string $name, array $arguments){
		$section = strtolower(substr($name, 0, -7));
		if(str_ends($name, '_parser') && method_exists($this, "_$section")){
			$lines = [];
			$this->merge_lines($lines, $this->before_lines($section));
			$this->merge_lines($lines, $this->{"_$section"}(...$arguments));
			$this->merge_lines($lines, $this->after_lines($section));

			return $lines;
		}

		throw new BadMethodCallException(sprintf("Call to undefined method %s::%s", __CLASS__, $name));
	}
}
