<?php

namespace dqdp\SQL;

class Condition extends Statement
{
	const AND = 1;
	const OR = 2;

	var $Type = Condition::AND;
	var $Condition = '';
	var $Conditions = [];
	var $Vars = [];

	# __construct($type = Condition::AND)
	# __construct($str = '', $type = Condition::AND)
	function __construct(){
		$argc = func_num_args();
		$argv = func_get_args();

		if($argc == 0) {
		} elseif(gettype($argv[0]) == 'array'){
			$this->Condition = $argv[0][0];
			$this->Vars[] = $argv[0][1];
			if(isset($argv[1])){
				$this->Type = $argv[1];
			}
		} elseif(($argc == 1) && (($argv[0] === Condition::AND) || ($argv[0] === Condition::OR))){
			# __construct($type = Condition::AND)
			$this->Type = $argv[0];
		} elseif(($argc == 1)){
			# __construct($str = '')
			$this->Condition = $argv[0];
		} elseif($argc == 2) {
			$this->Condition = $argv[0];
			$this->Type = $argv[1];
		} else {
			trigger_error("Wrong parameter count", E_USER_WARNING);
		}
	}

	static function ao($type){
		return $type === Condition::OR ? " OR " : " AND ";
	}

	static function factory($condition, $type = Condition::AND){
		if((gettype($condition) != 'object') || (get_class($condition) != 'dqdp\SQL\Condition')){
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
				$line .= $scond.($c ? Condition::ao($cond->Type) : '');
			}
		}

		return $line ? "($line)" : '';
	}

	function parse(){
		$lines = [];
		if($this->Condition){
			$lines[] = "$this->Condition";
		}

		if($extra = Condition::parse_conditions($this->Conditions)){
			$lines[] = $extra;
		}

		return join(Condition::ao($this->Type), $lines);
	}

	function add_condition($condition){
		$this->Conditions[] = Condition::factory($condition, $this->Type);
		return $this;
	}

	function vars(){
		$vars = $this->Vars;
		foreach($this->Conditions as $cond){
			$vars = array_merge($vars, $cond->vars());
		}
		return $vars;
	}
}
