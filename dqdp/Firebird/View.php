<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class View extends Table
{
	function __construct(Database $db, $name){
		parent::__construct($db, $name);
		$this->type = FirebirdObject::TYPE_VIEW;
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'RDB$SYSTEM_FLAG = 0';
		$sql_add[] = 'RDB$RELATION_TYPE = '.Table::TYPE_VIEW;
		$sql_add[] = sprintf('RDB$RELATION_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			*
		FROM
			RDB$RELATIONS
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

}
