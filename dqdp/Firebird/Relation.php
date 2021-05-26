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
		->Where('RDB$SYSTEM_FLAG = 0')
		->OrderBy('RDB$RELATION_NAME');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['RDB$RELATION_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	 }

	/**
	 * @return RelationField[]
	 **/
	function getFields(): array {
		$sql = RelationField::getSQL()->Where(['RDB$RELATION_NAME = ?', $this->name]);

		foreach($this->getList($sql) as $r){
			$list[] = new RelationField($this, $r->FIELD_NAME);
		}

		return $list??[];
	}

	/**
	 * @return RelationIndex[]
	 **/
	function getIndexes(): array {
		$sql = RelationIndex::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new RelationIndex($this, $r->INDEX_NAME);
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintFK[]
	 **/
	function getFKs(): array {
		$sql = RelationConstraintFK::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new RelationConstraintFK($this, $r->INDEX_NAME);
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintPK[]
	 **/
	function getPKs(): array {
		$sql = RelationConstraintPK::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new RelationConstraintPK($this, $r->INDEX_NAME);
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintUniq[]
	 **/
	function getUniqs(): array {
		$sql = RelationConstraintUniq::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new RelationConstraintUniq($this, $r->INDEX_NAME);
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintCheck[]
	 **/
	function getChecks(): array {
		$sql = RelationConstraintCheck::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new RelationConstraintCheck($this, $r->CONSTRAINT_NAME);
		}

		return $list??[];
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
