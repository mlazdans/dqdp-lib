<?php declare(strict_types = 1);

namespace dqdp\SQL;

class Condition extends Statement
{
	var ConditionType $Type;
	var $Condition = '';
	var $Conditions = [];

	function __construct(array|string $C = null, ConditionType $Type = new ConditionAnd){
		$this->Type = $Type;

		if(is_null($C)){
			return;
		}

		if(is_array($C)){
			$this->Condition = array_shift($C);
			$this->addVar($C);
		} else {
			$this->Condition = $C;
		}
	}

	static function ao($type){
		return $type instanceof ConditionOr ? " OR " : " AND ";
	}

	static function parse_conditions(array $conditions): string {
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

	function parse(): string {
		$lines = [];
		if($this->Condition){
			$lines[] = "$this->Condition";
		}

		if($extra = Condition::parse_conditions($this->Conditions)){
			$lines[] = $extra;
		}

		return join(Condition::ao($this->Type), $lines);
	}

	function add_condition($C, ...$args): static {
		if($C instanceof Condition){
			$this->Conditions[] = $C;
		} else {
			$this->Conditions[] = new Condition($C, ...$args);
		}

		return $this;
	}

	function getVars(): array {
		$vars = parent::getVars();
		foreach($this->Conditions as $cond){
			$vars = array_merge($vars, $cond->getVars());
		}
		return $vars;
	}

	function non_empty(){
		return (count($this->Conditions) > 0) || $this->Condition;
	}

	function is_empty(){
		return $this->non_empty();
	}
}
