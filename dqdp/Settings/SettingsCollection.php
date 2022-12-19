<?php declare(strict_types = 1);

namespace dqdp\Settings;

class SettingsCollection extends \dqdp\DBA\AbstractDataCollection {
	function current(): SettingsType {
		return parent::current();
	}

	function &offsetGet(mixed $k): SettingsType {
		return parent::offsetGet($k);
	}
}
