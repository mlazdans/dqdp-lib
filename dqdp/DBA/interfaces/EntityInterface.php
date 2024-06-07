<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\DBA\AbstractFilter;

interface EntityInterface
{
	function query(?AbstractFilter $filters = null): mixed;
	function fetch(mixed $q): mixed;

	function insert(array|object $DATA): mixed;
	function update(int|string|array $ID, array|object $DATA): bool;
	function save(array|object $DATA): mixed;

	function delete(int|string|array $ID): bool;
	function delete_multiple(array $IDS): bool;
}
