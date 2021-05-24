<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class FireBirdException extends FirebirdType
{
	static function getSQL(): Select {
		return (new Select())
		->From('RDB$EXCEPTIONS')
		->Where('RDB$SYSTEM_FLAG = 0')
		->OrderBy('RDB$EXCEPTION_NAME');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['RDB$EXCEPTION_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(): string {
		$MD = $this->getMetadata();
		return sprintf("CREATE EXCEPTION %s '%s'", $this, $MD->MESSAGE);
	}
}
