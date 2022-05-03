<?php

declare(strict_types = 1);

namespace dqdp\DBA;

abstract class AbstractDBA
{
	protected $fetch_function = 'fetch_assoc';

	abstract function connect();
	abstract function connect_params(iterable $params);
	abstract function query();
	abstract function prepare();
	abstract function fetch_assoc();
	abstract function fetch_object();
	abstract function execute();
	abstract function execute_single();
	abstract function execute_prepared();
	abstract function new_trans();
	abstract function commit(): bool;
	abstract function commit_ret(): bool;
	abstract function rollback(): bool;
	abstract function affected_rows(): int;
	abstract function close(): bool;
	abstract function escape($v): string;
	abstract function save(iterable $DATA, AbstractTable $Table);

	function set_default_fetch_function($func): AbstractDBA {
		$this->fetch_function = $func;

		return $this;
	}

	function fetch(){
		return $this->{$this->fetch_function}(...func_get_args());
	}

	function fetch_all(){
		while($r = $this->fetch(...func_get_args())){
			$ret[] = $r;
		}

		return $ret??[];
	}

	protected function is_dqdp_statement($args): bool {
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Statement;
	}

}
