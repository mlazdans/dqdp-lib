<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class ExceptionList extends ObjectList
{
	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = 'SELECT RDB$EXCEPTION_NAME AS NAME FROM RDB$EXCEPTIONS WHERE RDB$SYSTEM_FLAG = 0 ORDER BY RDB$EXCEPTION_NAME';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new Exception($this->getDb(), $r->NAME);
		}

		return $this->list;
	}
}
