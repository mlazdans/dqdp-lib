<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

interface EntityInterface extends TransactionInterface {
	function get($ID, ?iterable $filters = null): mixed;
	function getAll(?iterable $filters = null): mixed;
	function getSingle(?iterable $filters = null): mixed;

	function query(?iterable $filters = null);
	function fetch(): mixed;

	function insert(array|object $DATA);
	function update($ID, array|object $DATA);
	function save(array|object $DATA);
	function delete($ID);

	// function insert(iterable $DATA, TableInterface $Table);
	// function update($ID, iterable $DATA, TableInterface $Table);
	// function save(iterable $DATA, TableInterface $Table);

}
