<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

abstract class RelationConstraint extends RelationIndex
{
	static function getSQL(): Select {
		// return (new Select('i.*, rc.*, refc.*'))
		return (new Select('rc.*'))
		->From('RDB$RELATION_CONSTRAINTS rc')
		// ->Join('RDB$INDICES i', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		// ->Where('i.RDB$SYSTEM_FLAG = 0')
		;
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		// ->Where(['rf.RDB$RELATION_NAME = ?', $this->relation])
		->Where(['rc.RDB$INDEX_NAME = ?', $this->name]);
		;

		return parent::loadMetadataBySQL($sql);
	}
}
