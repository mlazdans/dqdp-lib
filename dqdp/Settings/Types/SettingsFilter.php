<?php declare(strict_types = 1);

namespace dqdp\Settings\Types;

use dqdp\DBA\AbstractFilter;
use dqdp\SQL\Select;

class SettingsFilter extends AbstractFilter {
	function __construct(
		readonly string $SET_KEY = "",
		readonly string $SET_DOMAIN = "",
	) {
	}

	function apply(Select $sql): Select {
		$this->apply_fields_with_values($sql, ['SET_KEY', 'SET_DOMAIN']);

		return $sql;
	}

}
