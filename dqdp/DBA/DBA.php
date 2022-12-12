<?php

declare(strict_types = 1);

namespace dqdp\DBA;

abstract class DBA
{
	# TODO: static?
	protected $fetch_function = 'fetch_assoc';
	protected $fetch_case = null;

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
	abstract function save(iterable $DATA, Table $Table);
	abstract function insert(iterable $DATA, Table $Table);
	abstract function update($ID, iterable $DATA, Table $Table);
	abstract function with_new_trans(callable $func, ...$args);

	function set_default_fetch_function($func): static {
		$this->fetch_function = $func;

		return $this;
	}

	function set_fetch_case(string $case): void {
		$this->fetch_case = $case;
	}

	function fetch(): mixed {
		return $this->{$this->fetch_function}(...func_get_args());
	}

	function fetch_all(): array {
		while($r = $this->fetch(...func_get_args())){
			$ret[] = $r;
		}

		return $ret??[];
	}

	protected function is_dqdp_statement($args): bool {
		return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Statement;
	}

}
