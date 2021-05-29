<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Relation extends FirebirdObject implements DDL
{
	// const TYPE_PERSISTENT                  = 0;
	// const TYPE_VIEW                        = 1;
	// const TYPE_EXTERNAL                    = 2;
	// const TYPE_VIRTUAL                     = 3;
	// const TYPE_GLOBAL_TEMPORARY_PRESERVE   = 4;
	// const TYPE_GLOBAL_TEMPORARY_DELETE     = 5;

	static function getSQL(): Select {
		return (new Select())->From('RDB$RELATIONS')->Where('RDB$SYSTEM_FLAG = 0');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['RDB$RELATION_NAME = ?', $this->name]);
	 }

	/**
	 * @return RelationField[]
	 **/
	function getFields(): array {
		$sql = RelationField::getSQL()->Where(['RDB$RELATION_NAME = ?', $this->name]);

		foreach($this->getList($sql) as $r){
			$list[] = (new RelationField($this, $r->FIELD_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return RelationIndex[]
	 **/
	function getIndexes(): array {
		$sql = RelationIndex::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new RelationIndex($this, $r->INDEX_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintFK[]
	 **/
	function getFKs(): array {
		$sql = RelationConstraintFK::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new RelationConstraintFK($this, $r->INDEX_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintPK[]
	 **/
	function getPKs(): array {
		$sql = RelationConstraintPK::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new RelationConstraintPK($this, $r->INDEX_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintUniq[]
	 **/
	function getUniqs(): array {
		$sql = RelationConstraintUniq::getSQL()->Where(['indices.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new RelationConstraintUniq($this, $r->INDEX_NAME))->setMetadata($r);;
		}

		return $list??[];
	}

	/**
	 * @return RelationConstraintCheck[]
	 **/
	function getChecks(): array {
		$sql = RelationConstraintCheck::getSQL()->Where(['relation_constraints.RDB$RELATION_NAME = ?', $this]);

		foreach($this->getList($sql) as $r){
			$list[] = (new RelationConstraintCheck($this, $r->CONSTRAINT_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	function ddlParts(): array {
		trigger_error("Not implemented yet");
		return [];
	}

	# TODO: view
	function ddl($PARTS = null): string {
		// if(is_null($PARTS)){
		// 	$PARTS = $this->ddlParts();
		// }

		$MD = $this->getMetadata();

		$ddl = [];
		$fields = $this->getFields();
		if($MD->RELATION_TYPE == Relation\Type::PERSISTENT){
			foreach($fields as $o){
				$fddl[] = $o->ddl();
			}
			$ddl[] = "CREATE TABLE $this (".join(",\n\t", $fddl).")";
		} elseif($MD->RELATION_TYPE == Relation\Type::VIEW){
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
