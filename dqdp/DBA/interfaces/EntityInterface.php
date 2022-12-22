<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

interface EntityInterface {
	function get($ID, ?iterable $filters = null): mixed;
	// function getAll(?iterable $filters = null): mixed;
	// function getSingle(?iterable $filters = null): mixed;

	// function query(?iterable $filters = null): static;
	// function fetch(): mixed;

	function insert(array|object $DATA);
	function update($ID, array|object $DATA);
	function save(array|object $DATA);

	# TODO: nepieņemt array
	function delete($ID);
}
