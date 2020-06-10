<?php

namespace dqdp\DBLayer;

class DBException extends \RuntimeException {
}

abstract class DBLayer
{
	var $use_exceptions = true;

	protected $execute_fetch_function = 'fetch_assoc';

	abstract function connect();
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
	abstract function save($Ent, $fields, $DATA);

	function set_default_fetch_function($func){
		$this->execute_fetch_function = $func;
	}

	function fetch(){
		return $this->{$this->execute_fetch_function}(...func_get_args());
	}

	function execute_single(){
		$data = $this->Execute(...func_get_args());
		if(is_array($data) && isset($data[0])){
			return $data[0];
		}

		return [];
	}

	protected function is_dqdp_select($args){
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Select;
	}
}
