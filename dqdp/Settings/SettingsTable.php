<?php

declare(strict_types = 1);

namespace dqdp\Settings;

use dqdp\Entity\Table;

class SettingsTable extends Table {
	function __construct(){
		$this->Name = 'settings';
		$this->PK = ['SET_CLASS','SET_KEY'];
	}

	function getFields(): array {
		return ['SET_CLASS', 'SET_KEY', 'SET_INT', 'SET_BOOLEAN', 'SET_FLOAT', 'SET_STRING', 'SET_DATE', 'SET_BINARY', 'SET_SERIALIZE'];
	}
}
