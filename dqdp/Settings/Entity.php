<?php

namespace dqdp\Settings;

use dqdp\DBA\Entity as DBAEntity;

class Entity extends DBAEntity
{
	function __construct(){
		$this->Table = new SettingsTable;
		parent::__construct();
	}
}
