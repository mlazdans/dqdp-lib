<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Relation extends FirebirdType
{
	const TYPE_PERSISTENT                  = 0;
	const TYPE_VIEW                        = 1;
	const TYPE_EXTERNAL                    = 2;
	const TYPE_VIRTUAL                     = 3;
	const TYPE_GLOBAL_TEMPORARY_PRESERVE   = 4;
	const TYPE_GLOBAL_TEMPORARY_DELETE     = 5;

	static function getSQL(): Select {
		return (new Select())
		->From('RDB$RELATIONS')
		->Where('RDB$SYSTEM_FLAG = 0');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['RDB$RELATION_NAME = ?', $this->name]);
		return parent::loadMetadataBySQL($sql);
	 }

	function getFields(): array {
		$sql = RelationField::getSQL()
		->Where(['RDB$RELATION_NAME = ?', $this->name])
		->OrderBy('RDB$FIELD_POSITION');

		foreach($this->getList($sql) as $r){
			$list[] = new RelationField($this, $r->FIELD_NAME);
		}

		return $list??[];
	}

	function getIndexes($params = []): array {
		$sql = Index::getSQL()->Where(['i.RDB$RELATION_NAME = ?', $this->name]);

		if(isset($params['CONSTRAINT_TYPE'])){
			if($params['CONSTRAINT_TYPE'] == Index::TYPE_FK){
				$sql->Where('rc.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\'');
			} elseif($params['CONSTRAINT_TYPE'] == Index::TYPE_PK){
				$sql->Where('rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'');
			} elseif($params['CONSTRAINT_TYPE'] == Index::TYPE_UNIQUE){
				$sql->Where('rc.RDB$CONSTRAINT_TYPE = \'UNIQUE\'');
			} else {
				$sql->Where('rc.RDB$CONSTRAINT_TYPE IS NULL');
			}
		}

		foreach($this->getList($sql) as $r){
			$list[] = new Index($this->getDb(), $r->INDEX_NAME);
		}

		return $list??[];
	}

	function getPK(): array {
		return $this->getIndexes(['CONSTRAINT_TYPE'=>Index::TYPE_PK]);
	}

	function getFK(): array {
		return $this->getIndexes(['CONSTRAINT_TYPE'=>Index::TYPE_FK]);
	}

	function getUnique(): array {
		return $this->getIndexes(['CONSTRAINT_TYPE'=>Index::TYPE_UNIQUE]);
	}

	# TODO: view
	function ddl(): string {
		$MD = $this->getMetadata();

		$ddl = [];
		$fields = $this->getFields();
		if($MD->RELATION_TYPE == Relation::TYPE_PERSISTENT){
			$ddl[]= "CREATE TABLE $this (";
			foreach($fields as $o){
				$fddl[] = $o->ddl();
			}
			$ddl[] = "\t".join(",\n\t", $fddl);
			$ddl[] = ")";
		} elseif($MD->RELATION_TYPE == Relation::TYPE_VIEW){
			foreach($fields as $o){
				$fddl[] = "$o";
			}
			$ddl[] = "CREATE VIEW $this (".join(", ", $fddl).") AS";
			$ddl[] = $MD->VIEW_SOURCE;
		} else {
			trigger_error("RELATION_TYPE: $MD->RELATION_TYPE not implemented");
		}

		// $indexes = $this->getIndexes();
		// foreach($indexes as $o){
		// 	$fddl[] = $o->ddl();
		// }

		// foreach($this->getPK() as $o){
		// 	$fddl[] = $o->ddl();
		// }

		// foreach($this->getUnique() as $o){
		// 	$fddl[] = $o->ddl();
		// }

		return join("\n", $ddl);
	}
}
