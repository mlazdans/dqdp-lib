<?php

# TODO: RDB$VIEW_RELATIONS
# TODO: ddl() ALTER, CREATE, etc

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\DBA\DBA;
use dqdp\SQL\Select;

class Database extends FirebirdObject
{
	private $conn;

	function __construct(DBA $conn, string $name = "firebird_db"){
		$this->connect($conn);

		return parent::__construct($this, $name);
	}

	static function getSQL(): Select {
		return (new Select())->From('MON$DATABASE')->Join('RDB$DATABASE', 'TRUE');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL();
	}

	function __toString(){
		return $this->name;
	}

	function connect(DBA $conn){
		$this->conn = $conn;

		return $this;
	}

	/**
	 * @return Table[]
	 **/
	function getTables(): array {
		$sql = Relation::getSQL()->Where(['RDB$RELATION_TYPE = ?', Relation\Type::PERSISTENT]);

		foreach($this->getList($sql) as $r){
			$list[] = (new Table($this->getDb(), $r->RELATION_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return Relation[]
	 **/
	function getViews(): array {
		$sql = Relation::getSQL()->Where(['RDB$RELATION_TYPE = ?', Relation\Type::VIEW]);

		foreach($this->getList($sql) as $r){
			$list[] = (new View($this->getDb(), $r->RELATION_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return DBTrigger[]
	 **/
	function getDBTriggers(): array {
		$sql = DBTrigger::getSQL();

		foreach($this->getList($sql) as $r){
			$list[] = (new DBTrigger($this->getDb(), $r->TRIGGER_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return Trigger[]
	 **/
	function getTriggers(): array {
		$sql = Trigger::getSQL()->Where('triggers.RDB$SYSTEM_FLAG = 0');

		foreach($this->getList($sql) as $r){
			$type = Trigger::getType($r->TRIGGER_TYPE);
			if($type == 'relation_trigger'){
				$list[] = new RelationTrigger(new Relation($this->getDb(), $r->RELATION_NAME), $r->TRIGGER_NAME);
			} elseif($type == 'database_trigger'){
				$list[] = new DBTrigger($this->getDb(), $r->TRIGGER_NAME);
			} else {
				trigger_error("TRIGGER_TYPE = $r->TRIGGER_TYPE not implemented");
			}
		}

		return $list??[];
	}

	// function getActiveTriggers(){
	// 	return (new TriggerList($this))->get(['active'=>true]);
	// }

	/**
	 * @return Procedure[]
	 **/
	function getProcedures(){
		$sql = Procedure::getSQL();

		foreach($this->getList($sql) as $r){
			$list[] = (new Procedure($this->getDb(), $r->PROCEDURE_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return Generator[]
	 **/
	function getGenerators(): array {
		$sql = Generator::getSQL();

		foreach($this->getList($sql) as $r){
			$list[] = (new Generator($this->getDb(), $r->GENERATOR_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return FireBirdException[]
	 **/
	function getExceptions(){
		$sql = FireBirdException::getSQL();

		foreach($this->getList($sql) as $r){
			$list[] = (new FireBirdException($this->getDb(), $r->EXCEPTION_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	// function getIndexes(){
	// 	// $sql = Index::getSQL()->Where('rc.RDB$CONSTRAINT_TYPE IS NULL');
	// 	$sql = Index::getSQL();
	// 	foreach($this->getList($sql) as $r){
	// 		$list[] = new Index($this->getDb(), $r->INDEX_NAME);
	// 	}

	// 	return $list??[];
	// }

	// function getFKs(){
	// 	$sql = RelationConstraintFK::getSQL();
	// 	foreach($this->getList($sql) as $r){
	// 		$list[] = new RelationConstraintFK($this->getDb(), $r->INDEX_NAME);
	// 	}

	// 	return $list??[];
	// }

	// function getPKs(){
	// 	$sql = RelationConstraintPK::getSQL();
	// 	foreach($this->getList($sql) as $r){
	// 		$list[] = new RelationConstraintPK($this->getDb(), $r->INDEX_NAME);
	// 	}

	// 	return $list??[];
	// }

	// function getUniqs(){
	// 	$sql = RelationConstraintUniq::getSQL();
	// 	foreach($this->getList($sql) as $r){
	// 		$list[] = new RelationConstraintUniq($this->getDb(), $r->INDEX_NAME);
	// 	}

	// 	return $list??[];
	// }

	// function getChecks(){
	// 	$sql = RelationConstraintCheck::getSQL();
	// 	foreach($this->getList($sql) as $r){
	// 		$list[] = new RelationConstraintCheck($this->getDb(), $r->CONSTRAINT_NAME);
	// 	}

	// 	return $list??[];
	// }

	/**
	 * @return UDF[]
	 **/
	function getUDFs(){
		$sql = UDF::getSQL();

		foreach($this->getList($sql) as $r){
			$list[] = (new UDF($this->getDb(), $r->FUNCTION_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	/**
	 * @return Domain[]
	 **/
	function getDomains(){
		$sql = Domain::getSQL();

		foreach($this->getList($sql) as $r){
			$list[] = (new Domain($this->getDb(), $r->FIELD_NAME))->setMetadata($r);
		}

		return $list??[];
	}

	function getConnection(): DBA {
		return $this->conn;
	}

	function ddlParts(): array {
		trigger_error("Not implemented yet");
		return [];
	}
}
