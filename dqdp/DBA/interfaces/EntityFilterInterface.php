<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\SQL\Select;

interface EntityFilterInterface {
	function apply(Select $sql): Select;
}
