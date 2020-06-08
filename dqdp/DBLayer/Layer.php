<?php

namespace dqdp\DBLayer;

abstract class Layer
{
	const MYSQL = 1;
	const PGSQL = 2;
	const MYSQLI = 3;
	const PDO_MYSQL = 4;
	const IBASE = 5;

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

	function is_dqdp_select($args){
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Select;
	}

	function execute_single(...$args){
		$data = $this->Execute(...$args);
		if(is_array($data) && isset($data[0])){
			return $data[0];
		}

		return [];
	}
}
