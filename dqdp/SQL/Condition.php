<?php

namespace dqdp\SQL;

class Condition extends Statement
{
	const AND = 1;
	const OR = 2;

	var $type;
	var $condition = '';
	var $conditions = [];

	# __construct($type = Condition::AND)
	# __construct($str = '', $type = Condition::AND)
	function __construct(){
		$argc = func_num_args();
		$argv = func_get_args();

		$this->type = Condition::AND;

		if(($argc == 1) && (($argv[0] === Condition::AND) || ($argv[0] === Condition::OR))){
			# __construct($type = Condition::AND)
			$this->type = $argv[0];
		} elseif(($argc == 1)){
			# __construct($str = '')
			$this->condition = $argv[0];
		} elseif($argc == 2) {
			$this->condition = $argv[0];
			$this->type = $argv[1];
		} elseif($argc == 0) {
		} else {
			trigger_error("Wrong parameter count'", E_USER_WARNING);
		}
	}

	static function ao($type){
		return $type === Condition::OR ? " OR " : " AND ";
	}

	static function factory($condition, $type = Condition::AND){
		if(gettype($condition) == 'string'){
			return new Condition($condition, $type);
		} else {
			return $condition;
		}
	}

	static function parse_conditions($conditions){
		$line = '';
		$c = count($conditions);
		foreach($conditions as $cond){
			$c--;
			if($scond = (string)$cond){
				$line .= $scond.($c ? Condition::ao($cond->type) : '');
			}
		}

		return $line ? "($line)" : '';
	}

	function parse(){
		$lines = [];
		if($this->condition){
			$lines[] = "$this->condition";
		}

		if($extra = Condition::parse_conditions($this->conditions)){
			$lines[] = $extra;
		}

		return join(Condition::ao($this->type), $lines);
	}

	function add_condition($condition){
		$c = Condition::factory($condition, $this->type);
		$c->type = $this->type;
		$this->conditions[] = $c;
		return $this;
	}
}
