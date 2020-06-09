<?php

namespace dqdp\DBLayer;

class DBException extends \RuntimeException {
}

abstract class Layer
{
	var $use_exception = false;
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

	function execute_single(...$args){
		$data = $this->Execute(...$args);
		if(is_array($data) && isset($data[0])){
			return $data[0];
		}

		return [];
	}
}
