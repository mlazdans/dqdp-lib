<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class TableFieldList extends ObjectList
{
	protected $table;

	function __construct(Table $table){
		parent::__construct($table->getDb());
		$this->table = $table;
	}

	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql_add = array();
		$sql_add[] = 'RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('RDB$RELATION_NAME = \'%s\'', $this->table);

		$sql = 'SELECT RDB$FIELD_NAME AS NAME FROM RDB$RELATION_FIELDS'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "").'
		ORDER BY
			RDB$FIELD_POSITION';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new TableField($this->table, $r->NAME);
		}

		return $this->list;
	}

}
