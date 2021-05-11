<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

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
		$sql = (new Select())
		->From('RDB$RELATIONS r')
		->Where('r.RDB$SYSTEM_FLAG = 0')
		->Where(['r.RDB$RELATION_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	function getFields(){
		return (new TableFieldList($this))->get();
	}

	function getIndexes(){
		# TODO: params Database
		return (new IndexList($this->getDb()))->get([
			'RELATION_NAME'=>$this->name,
		]);
	}

	function getPK(){
		return (new IndexList($this->getDb()))->get([
			'RELATION_NAME'=>$this->name,
			'CONSTRAINT_TYPE'=>Index::TYPE_PK,
		]);
	}

	function getFK(){
		return (new IndexList($this->getDb()))->get([
			'RELATION_NAME'=>$this->name,
			'CONSTRAINT_TYPE'=>Index::TYPE_FK,
		]);
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

