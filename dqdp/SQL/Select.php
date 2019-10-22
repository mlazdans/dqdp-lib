<?php

namespace dqdp\SQL;

class Select extends Statement
{
	var $parts = null;
	var $first = null;
	var $skip = null;
	var $distinct = false;

	function __construct(string $fields = ""){
		$this->parts = (object)[];
		$this->parts->select = [];
		$this->parts->from = [];
		$this->parts->join = [];
		$this->parts->where = new Condition;
		$this->parts->groupby = [];
		$this->parts->having = [];
		$this->parts->orderby = [];

		if($fields){
			$this->Select($fields);
		}
	}

	function __call(string $name, array $arguments){
		# Reset parts, e.g. select, joins, etc
		if(strpos($name, 'Reset') === 0){
			$part = strtolower(substr($name, strlen('Reset')));
			if(isset($this->parts->{$part})){
				$this->parts->{$part} = [];
				return $this;
			}
		}
		trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
	}

	function ResetDistinct(){
		$this->parts->where = new Condition;
		return $this;
	}

	function ResetWhere(){
		$this->distinct = false;
		return $this;
	}

	function Distinct(){
		$this->distinct = true;
		return $this;
	}

	function Skip(int $rows){
		$this->skip = $rows;
		return $this;
	}

	function First(int $rows){
		$this->first = $rows;
		return $this;
	}

	function Select(string $fields){
		$this->parts->select[] = $fields;
		return $this;
	}

	function From($arg){
		//if(is_object($v) && (get_class($v) == 'dqdp\SQL\Select')){
		if(is_array($arg) && is_object($arg[0]) && (get_class($arg[0]) == 'dqdp\SQL\Select')){
			list($sql, $alias) = $arg;
			$this->parts->from[] = "($sql) $alias";
			//foreach($sql->vars() as $v){
				$this->parts->where->add_vars($sql->vars());
			//}
			//$this->add_vars($sql->vars());
		} else {
			$this->parts->from[] = $arg;
		}

		return $this;
	}

	function Join(string $table, $condition){
		$this->parts->join[] = new Join($table, $condition, Join::INNER_JOIN);
		return $this;
	}

	function LeftJoin(string $table, $condition){
		$this->parts->join[] = new Join($table, $condition, Join::LEFT_OUTER_JOIN);
		return $this;
	}

	function Where($condition){
		$this->parts->where->add_condition($condition);
		return $this;
	}

	function Between($Col, $v1 = NULL, $v2 = NULL){
		if($v1 && $v2){
			$this->Where(["$Col BETWEEN ? AND ?", $v1, $v2]);
		} elseif($v1){
			$this->Where(["$Col >= ?", $v1]);
		} elseif($v2){
			$this->Where(["$Col <= ?", $v2]);
		}
}

	function OrderBy($order){
		$this->parts->orderby[] = Order::factory($order);
		return $this;
	}

	function GroupBy($group){
		$this->parts->groupby[] = $group;
		return $this;
	}

	function parse(){
		$lines = ['SELECT'];

		if($this->distinct){
			$lines[] = 'DISTINCT';
		}

		if($this->first){
			$lines[] = "FIRST $this->first";
		}

		if($this->skip){
			$lines[] = "SKIP $this->skip";
		}

		$lines[] = $this->parts->select ? join(",\n", $this->parts->select) : '*';


		if(empty($this->parts->from)){
			trigger_error("FROM part not set", E_USER_WARNING);
		} else {
			$lines[] = 'FROM';
			$lines[] = join(', ', $this->parts->from);
		}

		if($this->parts->join){
			$lines[] = join("\n", $this->parts->join);
		}

		if($where = (string)$this->parts->where){
			$lines[] = 'WHERE';
			$lines[] = $where;
		}

		if($this->parts->groupby){
			$lines[] = 'GROUP BY';
			$lines[] = join(', ', $this->parts->groupby);
		}

		if($this->parts->orderby){
			$lines[] = 'ORDER BY';
			$lines[] = join(', ', $this->parts->orderby);
		}

		return join("\n", $lines);
	}

	function vars(){
		$vars = [];
		foreach($this->parts->join as $j){
			$vars = array_merge($vars, $j->vars());
		}
		$vars = array_merge($vars, $this->parts->where->vars());
		return $vars;
	}

}
