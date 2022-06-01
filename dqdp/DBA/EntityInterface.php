<?php

declare(strict_types = 1);

namespace dqdp\DBA;

interface EntityInterface extends TransactionInterface {
	function get($ID, ?iterable $filters = null);
	function get_all(?iterable $filters = null);
	function get_single(?iterable $filters = null);
	function search(?iterable $filters = null);
	function save(iterable $DATA);
	function update($ID, iterable $DATA);
	function insert(iterable $DATA);
	function delete();
}
