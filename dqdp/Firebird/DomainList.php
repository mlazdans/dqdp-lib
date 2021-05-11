<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class DomainList extends ObjectList
{
	function __construct(Database $db){
		parent::__construct($db);
	}

	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = '
		SELECT
			RDB$FIELD_NAME AS NAME
		FROM
			RDB$FIELDS
		WHERE
			RDB$SYSTEM_FLAG = 0 AND
			RDB$FIELD_NAME NOT LIKE \'RDB$%\'
		ORDER BY
			RDB$FIELD_NAME
		';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new Domain($this->getDb(), $r->NAME);
		}

		return $this->list;
	}
}
