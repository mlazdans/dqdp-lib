<?php

namespace dqdp\SQL;

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

	function after($clause, $k, $v = null){
		$this->after[$clause][$k] = $v;
		return $this;
	}

	protected function merge_lines(&$lines, $parts){
		$lines = array_merge($lines, $parts);
	}

	protected function _lines($k, $clause){
		return array_values($this->{$k}[$clause]??[]);
	}

	protected function before_lines($clause){
		return $this->_lines('before', $clause);
	}

	protected function after_lines($clause){
		return $this->_lines('after', $clause);
	}

	function __call(string $name, array $arguments){
		if(!str_ends($name, '_parser')){
			return;
		}

		$section = strtolower(substr($name, 0, -7));

		if(!method_exists($this, "_$section")){
			trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
			return;
		}

		$lines = [];
		$this->merge_lines($lines, $this->before_lines($section));
		$this->merge_lines($lines, $this->{"_$section"}(...$arguments));
		$this->merge_lines($lines, $this->after_lines($section));

		return $lines;
	}
}
