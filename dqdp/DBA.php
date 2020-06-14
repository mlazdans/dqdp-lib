<?php

namespace dqdp;

abstract class DBA
{
	var $use_exceptions = true;
	var $dev = true;

	protected $execute_fetch_function = 'fetch_assoc';

	abstract function connect();
	abstract function connect_params($params);
	abstract function query();
	abstract function prepare();
	abstract function fetch_assoc();
	abstract function fetch_object();
	abstract function execute();
	abstract function trans();
	abstract function commit();
	abstract function rollback();
	abstract function affected_rows();
	abstract function close();
	abstract function escape($v);

	function set_dev(bool $dev){
		$this->dev = $dev;
		return $this;
	}

	function set_default_fetch_function($func){
		$this->execute_fetch_function = $func;
		return $this;
	}

	function fetch(){
		return $this->{$this->execute_fetch_function}(...func_get_args());
	}

	function fetch_all(){
		while($r = $this->fetch(...func_get_args())){
			$ret[] = $r;
		}
		return $ret??[];
	}

	function execute_single(){
		$data = $this->execute(...func_get_args());
		if(is_array($data) && isset($data[0])){
			return $data[0];
		}

		return [];
	}

	protected function is_dqdp_statement($args){
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Statement;
	}

}
