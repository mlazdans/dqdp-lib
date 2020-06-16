<?php

namespace dqdp\SQL;

class Insert extends Statement
{
	protected $on_duplicate_update = false;
	protected $parts = null;
	protected $table;
	protected $vars;

	function Into($table){
		$this->table = $table;
		return $this;
	}

	function Values($data){
		if(is_object($data)){
			$this->vars = get_object_vars($data);
		} elseif(is_array($data)){
			$this->vars = $data;
		} else {
			trigger_error("Expected array or object", E_USER_ERROR);
		}
		return $this;
	}

	function Update(){
		$this->on_duplicate_update = true;
		return $this;
	}

	function ResetUpdate(){
		$this->on_duplicate_update = false;
		return $this;
	}

	function parse(){
		if($this->lex() == 'mysql'){
			$lines = $this->parse_mysql();
		} elseif($this->lex() == 'fbird'){
			$lines = $this->parse_fbird();
		} else {
			trigger_error("Unknown SQL::\$lex: ".$this->lex(), E_USER_ERROR);
		}
		return join("\n", $lines);
	}

	# TODO: cache build_sql_raw() output
	function vars(){
		$build = build_sql(array_keys($this->vars), eo($this->vars), true);
		return $build[2];
	}

	protected function _values(){
		list($fields, $holders) = build_sql(array_keys($this->vars), eo($this->vars), true);
		$lines[] = "(".join(',', $fields).")";
		$lines[] = "VALUES";
		$lines[] = "(".join(',', $holders).")";

		return $lines;
	}

	protected function parse_mysql(){
		$lines = ['INSERT'];

		if($this->table){
			$lines[] = "INTO $this->table";
		}

		$this->merge_lines($lines, $this->values_parser());

		if($this->on_duplicate_update){
			$v_fields = array_map(function($v){
				return "$v=VALUES($v)";
			}, array_keys($this->vars));

			$lines[] = "ON DUPLICATE KEY UPDATE";
			$lines[] = join(",", $v_fields);
		}

		return $lines;
	}

	protected function parse_fbird(){
		if($this->on_duplicate_update){
			$lines = ['UPDATE OR INSERT'];
		} else {
			$lines = ['INSERT'];
		}

		if($this->table){
			$lines[] = "INTO $this->table";
		}

		$this->merge_lines($lines, $this->values_parser());

		return $lines;
	}
}
