<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class ProcedureList extends ObjectList
{
	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = 'SELECT RDB$PROCEDURE_NAME AS NAME FROM RDB$PROCEDURES WHERE RDB$SYSTEM_FLAG = 0 ORDER BY RDB$PROCEDURE_NAME';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new Procedure($this->getDb(), $r->NAME);
		}

		return $this->list;
	}

}
