<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class ViewList extends ObjectList
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
			RDB$RELATION_NAME AS NAME
		FROM
			RDB$RELATIONS
		WHERE
			RDB$SYSTEM_FLAG = 0 AND
			RDB$RELATION_TYPE = '.Table::TYPE_VIEW.'
		ORDER BY
			RDB$RELATION_NAME
		';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new View($this->getDb(), $r->NAME);
		}

		return $this->list;
	}

}

