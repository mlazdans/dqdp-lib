<?php

declare(strict_types = 1);

namespace dqdp\DBA;

abstract class AbstractDBA
{
	var $use_exceptions = true;

	protected $execute_fetch_function = 'fetch_assoc';

	abstract function connect();
	abstract function connect_params(iterable $params);
	abstract function query();
	abstract function prepare();
	abstract function fetch_assoc();
	abstract function fetch_object();
	abstract function execute();
	abstract function trans();
	abstract function commit(): bool;
	abstract function rollback(): bool;
	abstract function affected_rows(): int;
	abstract function close(): bool;
	abstract function escape($v): string;

	function set_default_fetch_function($func): AbstractDBA {
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

	protected function is_dqdp_statement($args): bool {
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Statement;
	}

}
