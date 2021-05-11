<?php

namespace dqdp\FireBird;

class TableList extends ObjectList
{
	function __construct(Database $db){
		parent::__construct($db);
	}

	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = 'SELECT r.RDB$RELATION_NAME AS NAME FROM RDB$RELATIONS AS r
		LEFT JOIN RDB$VIEW_RELATIONS v ON v.RDB$VIEW_NAME = r.RDB$RELATION_NAME
		WHERE v.RDB$VIEW_NAME IS NULL AND r.RDB$SYSTEM_FLAG = 0
		ORDER BY r.RDB$RELATION_NAME';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new Table($this->getDb(), $r->NAME);
		}

		return $this->list;
	}

}
