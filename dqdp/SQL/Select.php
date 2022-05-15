<?php

namespace dqdp\SQL;

use BadMethodCallException;
use InvalidArgumentException;

class Select extends Statement
{
	protected $parts = null;
	protected $distinct;
	protected $offset;
	protected $rows;

	function __construct(string $fields = null){
		$this->parts = (object)[];
		$this->parts->fields = [];
		$this->parts->from = [];
		$this->parts->join = [];
		$this->parts->joinlast = [];
		$this->parts->where = new Condition;
		$this->parts->groupby = [];
		$this->parts->having = [];
		$this->parts->orderby = [];

		if($fields){
			$this->Select($fields);
		}
	}

	function __clone(){
		$pp = [];
		foreach((array)$this->parts as $k=>$v){
			$pp[$k] = is_object($v) ? clone $v : $v;
		}
		$this->parts = (object)$pp;
	}

	# TODO: make native methods
	function __call(string $name, array $arguments){
		if(strpos($name, 'Reset') === 0){
			$part = strtolower(substr($name, 5));
			if(isset($this->parts->{$part})){
				$this->parts->{$part} = [];
				return $this;
			}
			throw new BadMethodCallException($name);
		}

		return parent::__call($name, $arguments);
	}

	function ResetDistinct(){
		$this->distinct = false;
		return $this;
	}

	function ResetWhere(){
		$this->parts->where = new Condition;
		return $this;
	}

	function Distinct(){
		$this->distinct = true;
		return $this;
	}

	function Select($arg, $wrapper = ''){
		if(is_array($arg) && $arg[0] instanceof \dqdp\SQL\Select){
			list($sql, $alias) = $arg;
			if($wrapper){
				$this->parts->fields[] = "$wrapper(($sql)) $alias";
			} else {
				$this->parts->fields[] = "($sql) $alias";
			}
			$this->parts->where->add_vars($sql->vars());
		} else {
			$this->parts->fields[] = $arg;
		}

		return $this;
	}

	function Offset($offset = null){
		$this->offset = $offset;
		return $this;
	}

	function Rows($rows = null){
		$this->rows = $rows;
		return $this;
	}

	function From($arg){
		# TODO: alias optional
		if(is_array($arg) && $arg[0] instanceof \dqdp\SQL\Select){
			list($sql, $alias) = $arg;
			$this->parts->from[] = "($sql) $alias";
			$this->parts->where->add_vars($sql->vars());
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

	function LeftJoinLast(string $table, $condition){
		$this->parts->joinlast[] = new Join($table, $condition, Join::LEFT_OUTER_JOIN);
		return $this;
	}

	function Where($condition){
		$this->parts->where->add_condition($condition);
		return $this;
	}

	function WhereIn($field, $v){
		$this->parts->where->add_condition(qb_filter_in($field, $v));
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
		if($this->lex() == 'mysql'){
			$lines = $this->parse_mysql();
		} elseif($this->lex() == 'fbird'){
			$lines = $this->parse_fbird();
		} else {
			throw new InvalidArgumentException("Unknown SQL::\$lex: ".$this->lex());
		}
		return join("\n", $lines);
	}

	function vars(){
		$vars = [];
		foreach($this->parts->join as $j){
			$vars = array_merge($vars, $j->vars());
		}
		foreach($this->parts->joinlast as $j){
			$vars = array_merge($vars, $j->vars());
		}
		$vars = array_merge($vars, $this->parts->where->vars());
		return $vars;
	}

	protected function parse_mysql(){
		$lines = [];
		$this->merge_lines($lines, $this->select_parser());
		$this->merge_lines($lines, $this->fields_parser());
		$this->merge_lines($lines, $this->from_parser());
		$this->merge_lines($lines, $this->join_parser());
		$this->merge_lines($lines, $this->where_parser());
		$this->merge_lines($lines, $this->groupby_parser());
		$this->merge_lines($lines, $this->orderby_parser());

		if(isset($this->rows) && isset($this->offset)){
			$lines[] = "LIMIT $this->offset,$this->rows";
		} elseif(isset($this->rows)){
			$lines[] = "LIMIT $this->rows";
		} elseif(isset($this->offset)){
			throw new InvalidArgumentException("offset without rows");
		}

		return $lines;
	}

	protected function parse_fbird(){
		$lines = [];
		$this->merge_lines($lines, $this->select_parser());

		if(!isset($this->rows) && isset($this->offset)){
			$lines[] = "SKIP $this->offset";
		}

		$this->merge_lines($lines, $this->fields_parser());
		$this->merge_lines($lines, $this->from_parser());
		$this->merge_lines($lines, $this->join_parser());
		$this->merge_lines($lines, $this->where_parser());
		$this->merge_lines($lines, $this->groupby_parser());
		$this->merge_lines($lines, $this->orderby_parser());

		if(isset($this->rows) && isset($this->offset)){
			$lines[] = sprintf("ROWS %d TO %d", $this->offset + 1, $this->rows + $this->offset);
		} elseif(isset($this->rows)){
			$lines[] = "ROWS $this->rows";
		}

		return $lines;
	}

	protected function _select(){
		$lines = ['SELECT'];
		if($this->distinct){
			$lines[] = 'DISTINCT';
		}
		return $lines;
	}

	protected function _fields(){
		return [$this->parts->fields ? join(",\n", $this->parts->fields) : '*'];
	}

	# TODO: absrahēt ar parametriem. Varbūt kādā citā klasē pat
	protected function _from(){
		if($this->parts->from){
			$lines[] = 'FROM';
			$lines[] = join(', ', $this->parts->from);
		}
		return $lines??[];
	}

	protected function _join(){
		if($this->parts->join){
			$lines[] = join("\n", $this->parts->join);
		}
		if($this->parts->joinlast){
			$lines[] = join("\n", $this->parts->joinlast);
		}
		return $lines??[];
	}

	protected function _where(){
		if($where = (string)$this->parts->where){
			$lines[] = 'WHERE';
			$lines[] = $where;
		}
		return $lines??[];
	}

	protected function _groupby(){
		if($this->parts->groupby){
			$lines[] = 'GROUP BY';
			$lines[] = join(', ', $this->parts->groupby);
		}
		return $lines??[];
	}

	protected function _orderby(){
		if($this->parts->orderby){
			$lines[] = 'ORDER BY';
			$lines[] = join(', ', $this->parts->orderby);
		}
		return $lines??[];
	}
}
