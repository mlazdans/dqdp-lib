<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

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
		$sql_add = array();
		$sql_add[] = sprintf('RDB$INDEX_NAME = \'%s\'', $this->index->name);
		$sql_add[] = sprintf('RDB$FIELD_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			*
		FROM
			RDB$INDEX_SEGMENTS
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

}
