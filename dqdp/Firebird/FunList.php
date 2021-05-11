<?php

namespace dqdp\FireBird;

class FunList extends ObjectList
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
			RDB$FUNCTION_NAME AS NAME
		FROM
			RDB$FUNCTIONS
		WHERE
			RDB$SYSTEM_FLAG = 0
		ORDER BY
			RDB$FUNCTION_NAME
		';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new Fun($this->getDb(), $r->NAME);
		}

		return $this->list;
	}
}
