<?php declare(strict_types = 1);

namespace dqdp\Settings;

use dqdp\DBA\AbstractFilter;
use dqdp\SQL\Select;

class SettingsFilter extends AbstractFilter {
	function __construct(
		public ?string $SET_DOMAIN = null,
		public ?string $SET_KEY = null,
	) { }

	protected function apply_filter(Select $sql): Select {
		$this->apply_fields_with_values($sql, ['SET_KEY', 'SET_DOMAIN']);

		return $sql;
	}
}
