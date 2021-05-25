<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class RelationField extends Field
{
	protected $relation;

	static function getSQL(): Select {
		return (new Select('rf.*, f.*, c.RDB$COLLATION_NAME, cs.RDB$BYTES_PER_CHARACTER'))
		->From('RDB$FIELDS f')
		->Join('RDB$RELATION_FIELDS rf', 'rf.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME')
		# TODO: check c join conditions
		->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = rf.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
		->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID')
		->Where('f.RDB$SYSTEM_FLAG = 0')
		->OrderBy('RDB$FIELD_POSITION');
	}

	function __construct(Relation $relation, $name){
		$this->relation = $relation;
		parent::__construct($relation->getDb(), $name);
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['RDB$RELATION_NAME = ?', $this->relation])
		->Where(['rf.RDB$FIELD_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function getRelation(){
		return $this->relation;
	}

	function ddl(): string {
		return "$this ".parent::ddl();
	}
}
