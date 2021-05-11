<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class TableField extends FirebirdObject
{
	protected $table;

	function __construct(Table $table, $name){
		$this->type = FirebirdObject::TYPE_FIELD;
		$this->table = $table;
		parent::__construct($table->getDb(), $name);
	}

	function loadMetadata(){
		$sql = (new Select('rf.*, f.*, c.RDB$COLLATION_NAME'))
		->From('RDB$FIELDS f')
		->Join('RDB$RELATION_FIELDS rf', 'rf.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME')
		->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = rf.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
		->Where('f.RDB$SYSTEM_FLAG = 0')
		->Where(['RDB$RELATION_NAME = ?', $this->table])
		->Where(['rf.RDB$FIELD_NAME = ?', $this->name])
		;

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

