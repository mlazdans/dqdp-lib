<?php

namespace dqdp\SQL;

class Select extends Statement
{
	var $parts = null;
	var $first = null;
	var $skip = null;

	function __construct(string $fields = ""){
		$this->parts = (object)[];
		$this->parts->select = [];
		$this->parts->from = [];
		$this->parts->join = [];
		$this->parts->where = new Condition;
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
				return $this->parts->{$part} = [];
			}
		}
		trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
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

	function From(string $table){
		$this->parts->from[] = $table;
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

	function OrderBy($order){
		$this->parts->orderby[] = Order::factory($order);
		return $this;
	}

	function parse(){
		$lines = ['SELECT'];

		if($this->first){
			$lines[] = "FIRST $this->first";
		}

		if($this->skip){
			$lines[] = "SKIP $this->skip";
		}

		$lines[] = $this->parts->select ? join(',', $this->parts->select) : '*';


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

		if($this->parts->orderby){
			$lines[] = 'ORDER BY';
			$lines[] = join(', ', $this->parts->orderby);
		}

		return join("\n", $lines);
	}
}
