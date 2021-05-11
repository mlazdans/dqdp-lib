<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class Exception extends FirebirdObject
{
	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_EXCEPTION;
		parent::__construct($db, $name);
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('RDB$EXCEPTION_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			*
		FROM
			RDB$EXCEPTIONS
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}
}

