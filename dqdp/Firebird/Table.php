<?php

namespace dqdp\FireBird;

class Table extends FirebirdObject
{
	const TYPE_TABLE                       = 0;
	const TYPE_VIEW                        = 1;
	const TYPE_EXTERNAL                    = 2;
	const TYPE_VIRTUAL                     = 3;
	const TYPE_GLOBAL_TEMPORARY_PRESERVE   = 4;
	const TYPE_GLOBAL_TEMPORARY_DELETE     = 5;

	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_TABLE;
		parent::__construct($db, $name);
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'r.RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('r.RDB$RELATION_NAME = \'%s\'', $this->name);

		/*LEFT JOIN RDB$VIEW_RELATIONS v ON v.RDB$VIEW_NAME = r.RDB$RELATION_NAME*/
		$sql = 'SELECT r.* FROM RDB$RELATIONS AS r'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

	function getFields(){
		$L = new TableFieldList($this);
		return $L->get();
	}

	function getIndexes(){
		$L = new IndexList($this->getDb());
		return $L->get(array(
			'RELATION_NAME'=>$this->name,
			));
	}

	function getPK(){
		$L = new IndexList($this->getDb());
		return $L->get(array(
			'RELATION_NAME'=>$this->name,
			'CONSTRAINT_TYPE'=>Index::TYPE_PK,
			));
	}

	function getFK(){
		$L = new IndexList($this->getDb());
		return $L->get(array(
			'RELATION_NAME'=>$this->name,
			'CONSTRAINT_TYPE'=>Index::TYPE_FK,
			));
	}

	function ddl(){
		$ddl = array();
		$ddl[] = "CREATE TABLE $this";
		$ddl[] = "(";

		$fields = $this->getFields();
		$fddl = array();
		foreach($fields as $item){
			$fddl[] = $item->ddl();
		}

		$prim_keys = $this->getPK();
		foreach($prim_keys as $pk){
			if($pddl = $pk->ddl()){
				$fddl[] = $pddl;
			}
		}

		# Should always enter
		if($fddl){
			$ddl[] = "\t".join(",\n\t", $fddl);
		}

		$ddl[] = ")";

		return join("\n", $ddl);
	}
}

