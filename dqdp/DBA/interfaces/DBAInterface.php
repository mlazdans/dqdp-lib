<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

interface DBAInterface
{
	// # TODO: static?
	// protected $fetch_case = null;
	function connect();
	// function connect_params(iterable $params);
	function query();
	function fetch_array(): array|null;
	function fetch_assoc(): array|null;
	function fetch_object(): object|null;
	function execute();
	// function execute_single();
	function prepare();
	// function execute();
	function new_trans();
	function commit(): bool;
	function commit_ret(): bool;
	function rollback(): bool;
	function affected_rows(): int;
	function close(): bool;
	function escape(string $v): string;
	function with_new_trans(callable $func, ...$args);

	// function set_fetch_case(string $case): void {
	// 	$this->fetch_case = $case;
	// }

	# TODO: remove!!
	// function fetch_all(): DataCollection {
	// 	while($r = $this->fetch_object(...func_get_args())){
	// 		$ret[] = $r;
	// 	}

	// 	return $ret??[];
	// }

	// protected function is_dqdp_statement($args): bool {
	// 	return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Statement;
	// }

}
