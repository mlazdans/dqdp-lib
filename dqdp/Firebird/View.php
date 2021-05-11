<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class View extends Table
{
	function __construct(Database $db, $name){
		parent::__construct($db, $name);
		$this->type = FirebirdObject::TYPE_VIEW;
	}

	function loadMetadata(){
		$sql = (new Select())
		->From('RDB$RELATIONS')
		->Where('RDB$SYSTEM_FLAG = 0')
		->Where(['RDB$RELATION_TYPE = ?', Table::TYPE_VIEW])
		->Where(['RDB$RELATION_NAME = ?', $this->name])
		;
		return parent::loadMetadataBySQL($sql);
	}

}
