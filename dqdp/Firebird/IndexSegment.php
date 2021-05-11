<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class IndexSegment extends FirebirdObject
{
	protected $index;

	# $name is FIELD_NAME
	function __construct(Index $index, $name){
		$this->type = FirebirdObject::TYPE_INDEX_SEGMENT;
		$this->index = $index;
		parent::__construct($index->getDb(), $name);
	}

	function loadMetadata(){
		$sql = (new Select())
		->From('RDB$INDEX_SEGMENTS')
		->Where(['RDB$INDEX_NAME = ?', $this->index->name])
		->Where(['RDB$FIELD_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

}
