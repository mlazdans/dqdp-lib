<?php

declare(strict_types = 1);

namespace dqdp\Entity;

use dqdp\DBA\TransInterface;

interface EntityInterface extends TransInterface {
	function get($ID, iterable $params = null);
	function get_all(iterable $params = null);
	function get_single(iterable $params = null);
	function search(iterable $params = null);
	function save(iterable $DATA);
	function delete();
}
