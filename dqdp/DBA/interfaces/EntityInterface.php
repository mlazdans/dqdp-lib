<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\DBA\DataCollection;
use dqdp\DBA\DataObject;

interface EntityInterface extends TransactionInterface {
	function get($ID, ?iterable $filters = null): ?DataObject;
	function getAll(?iterable $filters = null): ?DataCollection;
	function getSingle(?iterable $filters = null): ?DataObject;

	function query(?iterable $filters = null);
	function fetch(): ?DataObject;

	function insert(DataObject $DATA);
	function update($ID, DataObject $DATA);
	function save(DataObject $DATA);
	function delete($ID);

	// function insert(iterable $DATA, TableInterface $Table);
	// function update($ID, iterable $DATA, TableInterface $Table);
	// function save(iterable $DATA, TableInterface $Table);

}
