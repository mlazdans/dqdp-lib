<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

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
		$sql = "ALTER INDEX $this ACTIVE";
		return $this->getDb()->getConnection()->Query($sql);
	}

	function deactivate(){
		$sql = "ALTER INDEX $this INACTIVE";
		return $this->getDb()->getConnection()->Query($sql);
	}

	function enable(){
		return $this->activate();
	}

	function disable(){
		return $this->deactivate();
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('i.RDB$INDEX_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			i.*,
			rc.*
		FROM
			RDB$INDICES i
		LEFT JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

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
