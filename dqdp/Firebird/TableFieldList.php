<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class TableFieldList extends ObjectList
{
	protected $table;

	function __construct(Table $table){
		parent::__construct($table->getDb());
		$this->table = $table;
	}

	function get(){
		# TODO: need caching?
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = (new Select('RDB$FIELD_NAME AS NAME'))
		->From('RDB$RELATION_FIELDS')
		->Where('RDB$SYSTEM_FLAG = 0')
		->Where(['RDB$RELATION_NAME = ?', $this->table])
		->OrderBy('RDB$FIELD_POSITION')
		;

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new TableField($this->table, $r->NAME);
		}

		return $this->list;
	}

}
