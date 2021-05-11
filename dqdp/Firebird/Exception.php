<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Exception extends FirebirdObject
{
	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_EXCEPTION;
		parent::__construct($db, $name);
	}

	function loadMetadata(){
		$sql = (new Select())
		->From('RDB$EXCEPTIONS')
		->Where('RDB$SYSTEM_FLAG = 0')
		->Where(['RDB$EXCEPTION_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}
}

