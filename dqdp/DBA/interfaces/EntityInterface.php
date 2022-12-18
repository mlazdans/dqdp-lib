<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

interface EntityInterface extends TransactionInterface {
	function get($ID, ?iterable $filters = null): ?iterable;
	function getAll(?iterable $filters = null): ?iterable;
	function getSingle(?iterable $filters = null): ?iterable;

	function query(?iterable $filters = null);
	function fetch(): ?iterable;

	function insert(iterable $DATA);
	function update($ID, iterable $DATA);
	function save(iterable $DATA);
	function delete($ID);

	// function insert(iterable $DATA, TableInterface $Table);
	// function update($ID, iterable $DATA, TableInterface $Table);
	// function save(iterable $DATA, TableInterface $Table);

}
