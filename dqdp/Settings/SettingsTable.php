<?php

declare(strict_types = 1);

namespace dqdp\Settings;

use dqdp\DBA\AbstractTable;

class SettingsTable extends AbstractTable {
	function getName(): string {
		return 'settings';
	}

	function getPK(){
		return ['SET_DOMAIN','SET_KEY'];
	}

	function getGen(): ?string {
		return null;
	}

	function getFields(): array {
		return ['SET_DOMAIN', 'SET_KEY', 'SET_INT', 'SET_BOOLEAN', 'SET_FLOAT', 'SET_STRING', 'SET_DATE', 'SET_BINARY', 'SET_SERIALIZE'];
	}
}
