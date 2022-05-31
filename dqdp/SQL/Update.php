<?php declare(strict_types = 1);

namespace dqdp\SQL;

class Update extends Statement
{
	protected $table;
	protected $vars;
	protected Condition $where;

	function __construct($table){
		$this->table = $table;
		$this->where = new Condition;
	}

	function Set(...$args){
		if(count($args) == 1){
			$data = $args[0];
			if(is_object($data)){
				$this->vars = get_object_vars($data);
			} elseif(is_array($data)){
				$this->vars = $data;
			} else {
				trigger_error("Expected array or object", E_USER_ERROR);
			}
		} elseif(count($args) == 2){
			$this->vars[$args[0]] = $args[1];
		} else {
			trigger_error("Expected array, object or [key, value] pair", E_USER_ERROR);
		}

		return $this;
	}

	function Where($condition): Update {
		$this->where->add_condition($condition);
		return $this;
	}

	function parse(){
		$lines = ['UPDATE'];

		if($this->table){
			$lines[] = "$this->table";
		}

		$this->merge_lines($lines, $this->values_parser());
		$this->merge_lines($lines, $this->where_parser());

		return join("\n", $lines);
	}

	# TODO: cache build_sql_raw() output
	function vars(){
		$build = build_sql(array_keys($this->vars), eo($this->vars), true);
		$vars = $build[2];
		$vars = array_merge($vars, $this->where->vars());

		return $vars;
	}

	protected function _where(){
		if($where = (string)$this->where){
			$lines[] = 'WHERE';
			$lines[] = $where;
		}
		return $lines??[];
	}

	protected function _values(){
		list($fields, $holders) = build_sql(array_keys($this->vars), eo($this->vars), true);

		$set_line = [];
		foreach($fields as $i=>$f){
			$set_line[] = "$f = ".$holders[$i];
		}

		if($set_line){
			$lines[] = "SET ".join(', ', $set_line);
		}

		return $lines;
	}
}
