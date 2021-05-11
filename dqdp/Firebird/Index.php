<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Index extends FirebirdObject
{
	const TYPE_INDEX    = 0;
	const TYPE_FK       = 1;
	const TYPE_PK       = 2;
	const TYPE_UNIQUE   = 3;

	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_INDEX;
		parent::__construct($db, $name);
	}

	function activate(){
		return $this->getDb()->getConnection()->Query("ALTER INDEX $this ACTIVE");
	}

	function deactivate(){
		return $this->getDb()->getConnection()->Query("ALTER INDEX $this INACTIVE");
	}

	function enable(){
		return $this->activate();
	}

	function disable(){
		return $this->deactivate();
	}

	function loadMetadata(){
		$sql = (new Select('i.*, rc.*'))
		->From('RDB$INDICES i')
		->LeftJoin('RDB$RELATION_CONSTRAINTS rc', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		->Where('RDB$SYSTEM_FLAG = 0')
		->Where(['i.RDB$INDEX_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function getSegments(){
		$L = new IndexSegmentList($this);
		return $L->get();
	}

	function ddl(){
		$ddl = '';
		$MT = $this->getMetadata();
		$segments = $this->getSegments();

		# First, constraints
		if($MT->CONSTRAINT_TYPE == "PRIMARY KEY"){
			if($MT->CONSTRAINT_NAME){
				$ddl = "CONSTRAINT $MT->CONSTRAINT_NAME ";
			}
			$ddl .= "PRIMARY KEY (".join(",", $segments).")";
		}

		return $ddl;
	}
}
