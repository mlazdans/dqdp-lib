<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

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

		$sql = (new Select('RDB$FIELD_NAME AS NAME'))
		->From('RDB$INDEX_SEGMENTS')
		->Where(['RDB$INDEX_NAME = ?', $this->index])
		->OrderBy('RDB$FIELD_POSITION')
		;

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new IndexSegment($this->index, $r->NAME);
		}

		return $this->list;
	}

}

