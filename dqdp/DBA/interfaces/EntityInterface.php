<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\DBA\AbstractFilter;

interface EntityInterface {
	// function get($ID, ?AbstractFilter $filters = null): mixed;
	function getAll(?AbstractFilter $filters = null): mixed;
	function getSingle(?AbstractFilter $filters = null): mixed;

	// function query(?AbstractFilter $filters = null): mixed;
	// function fetch(): mixed;

	function insert(array|object $DATA);
	function update($ID, array|object $DATA);
	function save(array|object $DATA);

	# TODO: nepieņemt array
	function delete($ID);
}
