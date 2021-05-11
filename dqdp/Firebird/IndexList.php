<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class IndexList extends ObjectList
{
	function __construct(Database $db){
		parent::__construct($db);
	}

	function get($params = array()){
		if(is_array($this->list)){
			return $this->list;
		}

		$sql_add = array();
		$sql_add[] = 'i.RDB$SYSTEM_FLAG = 0';

		if(isset($params['CONSTRAINT_TYPE'])){
			if($params['CONSTRAINT_TYPE'] == Index::TYPE_FK){
				$sql_add[] = 'rc.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\'';
			} elseif($params['CONSTRAINT_TYPE'] == Index::TYPE_PK){
				$sql_add[] = 'rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'';
			} elseif($params['CONSTRAINT_TYPE'] == Index::TYPE_UNIQUE){
				$sql_add[] = 'rc.RDB$CONSTRAINT_TYPE = \'UNIQUE\'';
			} else {
				$sql_add[] = 'rc.RDB$CONSTRAINT_TYPE IS NULL';
			}
		}

		if(isset($params['RELATION_NAME'])){
			$sql_add[] = sprintf('i.RDB$RELATION_NAME = \'%s\'', $params['RELATION_NAME']);
		}

		if(!empty($params['active'])){
			$sql_add[] = '((i.RDB$INDEX_INACTIVE = 0) OR (i.RDB$INDEX_INACTIVE IS NULL))';
		}

		$sql = '
		SELECT
			i.RDB$INDEX_NAME AS NAME
		FROM
			RDB$INDICES i
		LEFT JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "").'
		ORDER BY
			i.RDB$RELATION_NAME, i.RDB$INDEX_ID
		';

		$this->list = array();
		$conn = $this->getDb()->getConnection();
		$q = $conn->Query($sql);
		while($r = $conn->fetch_object($q)){
			$this->list[] = new Index($this->getDb(), $r->NAME);
		}

		return $this->list;
	}
}

