<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class TriggerList extends ObjectList
{
	function __construct(Database $db){
		parent::__construct($db);
	}

	function get($params = array()){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql_add = array();
		$sql_add[] = 'RDB$SYSTEM_FLAG = 0';

		if(!empty($params['active'])){
			$sql_add[] = 'RDB$TRIGGER_INACTIVE = 0';
		}

		$sql = '
		SELECT
			RDB$TRIGGER_NAME AS NAME
		FROM
			RDB$TRIGGERS
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "").'
		ORDER BY
			RDB$TRIGGER_NAME
		';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->query($sql);
		while($r = $conn->fetch($q)){
			$this->list[] = new Trigger($this->getDb(), $r->NAME);
		}

		return $this->list;
	}
}
