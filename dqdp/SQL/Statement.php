<?php declare(strict_types = 1);

namespace dqdp\SQL;

use dqdp\InvalidTypeException;
use Traversable;

abstract class Statement
{
	// protected $beforeTriggers = [];
	// protected $afterTriggers = [];
	private $Vars = [];

	abstract function parse(): string ;

	function __toString(): string {
		return $this->parse();
	}

	function addVar(mixed $v): int {
		$c = 0;
		if(is_scalar($v)){
			$c = 1;
			$this->Vars[] = $v;
		} elseif(is_array($v) || $v instanceof Traversable ){
			foreach($v as $i){
				$c += $this->addVar($i);
			}
		} else {
			throw new InvalidTypeException($v);
		}

		return $c;
	}

	function getVars(): array {
		return $this->Vars;
	}

	// function lex(){
	// 	return $this->lex??SQL::$lex;
	// }

	// function setLex($lex){
	// 	return $this->lex = $lex;
	// }

	// private function __add_trigger($trigger, $clause, $k, $v = null){
	// 	$this->{$trigger}[$clause][$k] = $v;
	// 	return $this;
	// }

	// function addAfterTrigger(string $clause, string $k, mixed $v = null){
	// 	$this->afterTriggers[$clause][$k] = $v;
	// }

	// function addBeforeTrigger($clause, $k, $v = null){
	// 	$this->afterTriggers[$clause][$k] = $v;
	// }

	protected function merge_lines(&$lines, $parts){
		$lines = array_merge($lines, $parts);
	}

	// private function __get_lines($trigger, $clause){
	// 	return array_values($this->{$trigger}[$clause]??[]);
	// }

	// protected function before_lines($clause){
	// 	return $this->__get_lines('before', $clause);
	// }

	// protected function after_lines($clause){
	// 	return $this->__get_lines('after', $clause);
	// }

	// function __call(string $name, array $arguments){
	// 	$section = strtolower(substr($name, 0, -7));
	// 	if(str_ends($name, '_parser') && method_exists($this, "_$section")){
	// 		$lines = [];
	// 		$this->merge_lines($lines, $this->before_lines($section));
	// 		$this->merge_lines($lines, $this->{"_$section"}(...$arguments));
	// 		$this->merge_lines($lines, $this->after_lines($section));

	// 		return $lines;
	// 	}

	// 	throw new BadMethodCallException(sprintf("Call to undefined method %s::%s", __CLASS__, $name));
	// }
}
