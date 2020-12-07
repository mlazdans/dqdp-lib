<?php

declare(strict_types = 1);

namespace dqdp\Settings;

class Entity extends \dqdp\DBA\Entity
{
	function __construct(){
		$this->Table = new SettingsTable;
		parent::__construct();
	}
}
