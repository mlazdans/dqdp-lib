<?php

namespace dqdp\FireBird;

class TableField extends FirebirdObject
{
	protected $table;

	function __construct(Table $table, $name){
		$this->type = FirebirdObject::TYPE_FIELD;
		$this->table = $table;
		parent::__construct($table->getDb(), $name);
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'f.RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('rf.RDB$FIELD_NAME = \'%s\'', $this->name);
		$sql_add[] = sprintf('RDB$RELATION_NAME = \'%s\'', $this->table);

		$sql = 'SELECT rf.*, f.*, c.RDB$COLLATION_NAME
		FROM RDB$FIELDS f
		JOIN RDB$RELATION_FIELDS rf ON rf.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME
		LEFT JOIN RDB$COLLATIONS c ON (c.RDB$COLLATION_ID = rf.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

	function getTable(){
		return $this->table;
	}

	function isQuotable(){
		return Field::isQuotable((int)$this->getMetadata()->FIELD_TYPE);
	}

	function ddl(){
		return "$this ".Field::ddl($this->getMetadata());
	}

}

