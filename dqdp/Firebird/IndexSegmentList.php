<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class IndexSegmentList extends ObjectList
{
	protected $index;

	function __construct(Index $index){
		$this->index = $index;
		parent::__construct($index->getDb());
	}

	function get(){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql = '
		SELECT
			RDB$FIELD_NAME AS NAME
		FROM
			RDB$INDEX_SEGMENTS
		WHERE
			RDB$INDEX_NAME = \''.$this->index.'\'
		ORDER BY
			RDB$FIELD_POSITION
		';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new IndexSegment($this->index, $r->NAME);
		}

		return $this->list;
	}

}

