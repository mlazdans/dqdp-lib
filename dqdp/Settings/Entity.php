<?php

namespace dqdp\Settings;

class Entity extends \dqdp\Entity
{
	function __construct(){
		$this->Table = new SettingsTable;
		parent::__construct();
	}
}
