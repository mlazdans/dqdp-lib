<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class TriggerList extends ObjectList
{
	function get(Array $params = []){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = (new Select('RDB$TRIGGER_NAME AS NAME'))
		->From('RDB$TRIGGERS')
		->Where('RDB$SYSTEM_FLAG = 0');

		if(!empty($params['active'])){
			$sql->Where('RDB$TRIGGER_INACTIVE = 0');
		}

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->query($sql);
		while($r = $conn->fetch($q)){
			$this->list[] = new Trigger($this->getDb(), $r->NAME);
		}

		return $this->list;
	}
}
