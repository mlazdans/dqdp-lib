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

	function add_vars($v){
		if(is_array($v)){
			foreach($v as $i){
				$this->add_vars($i);
			}
		} else {
			$this->Vars[] = $v;
		}
	}

	# __construct($type = Condition::AND)
	# __construct($str = '', $type = Condition::AND)
	function __construct(){
		$argc = func_num_args();
		$argv = func_get_args();

		if($argc == 0) {
		} elseif(gettype($argv[0]) == 'array'){
			$this->Condition = $argv[0][0];
			for($i=1; $i<count($argv[0]);$i++){
				$this->add_vars($argv[0][$i]);
				//$this->Vars[] = $argv[0][$i];
			}
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

	//static function factory($condition, $type = Condition::AND){
	static function factory(){
		$args = func_get_args();
		$condition = $args[0]??null;

		if((gettype($condition) != 'object') || (get_class($condition) != 'dqdp\SQL\Condition')){
			return new Condition(...$args);
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

	function add_condition(){
		$this->Conditions[] = Condition::factory(...func_get_args());
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
