<?php

namespace dqdp\Settings;

class Entity extends \dqdp\Entity
{
	function __construct(){
		$this->Table = 'settings';
		$this->PK = ['SET_CLASS','SET_KEY'];
	}

	function fields(): array {
		return ['SET_CLASS', 'SET_KEY', 'SET_INT', 'SET_BOOLEAN', 'SET_FLOAT', 'SET_STRING', 'SET_DATE', 'SET_BINARY', 'SET_SERIALIZE'];
	}
}
