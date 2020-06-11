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

	function non_empty(){
		return (count($this->Conditions) > 0) || $this->Condition;
	}

	function is_empty(){
		return $this->non_empty();
	}

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
		$acount = func_num_args();
		$aval = func_get_args();

		if($acount == 0) {
		} elseif(gettype($aval[0]) == 'array'){
			$this->Condition = $aval[0][0];
			for($i=1; $i<count($aval[0]);$i++){
				$this->add_vars($aval[0][$i]);
				//$this->Vars[] = $aval[0][$i];
			}
			if(isset($aval[1])){
				$this->Type = $aval[1];
			}
		} elseif(($acount == 1) && (($aval[0] === Condition::AND) || ($aval[0] === Condition::OR))){
			# __construct($type = Condition::AND)
			$this->Type = $aval[0];
		} elseif(($acount == 1)){
			# __construct($str = '')
			$this->Condition = $aval[0];
		} elseif($acount == 2) {
			$this->Condition = $aval[0];
			$this->Type = $aval[1];
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
