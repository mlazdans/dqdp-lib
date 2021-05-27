<?php

# TODO: RDB$VIEW_RELATIONS
# TODO: ddl() ALTER, CREATE, etc

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\DBA\driver\IBase;
use dqdp\SQL\Select;

class Database extends FirebirdObject
{
	private $conn;

	function __construct(IBase $conn, string $name = "firebird_db"){
		$this->connect($conn);
		parent::__construct($this, $name);
	}

	function __toString(){
		return $this->name;
	}

	function connect(IBase $conn){
		$this->conn = $conn;
	}

	/**
	 * @return Relation[]
	 **/
	function getTables(): array {
		$sql = Relation::getSQL()->Where(['RDB$RELATION_TYPE = ?', Relation::TYPE_PERSISTENT]);

		foreach($this->getList($sql) as $r){
			$list[] = new Relation($this->getDb(), $r->RELATION_NAME);
		}

		return $list??[];
	}

	/**
	 * @return Relation[]
	 **/
	function getViews(): array {
		$sql = Relation::getSQL()->Where(['RDB$RELATION_TYPE = ?', Relation::TYPE_VIEW]);

		foreach($this->getList($sql) as $r){
			$list[] = new Relation($this->getDb(), $r->RELATION_NAME);
		}

		return $list??[];
	}

	/**
	 * @return Trigger[]
	 **/
	function getTriggers(): array {
		$sql = Trigger::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new Trigger($this->getDb(), $r->TRIGGER_NAME);
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
			$list[] = new Procedure($this->getDb(), $r->PROCEDURE_NAME);
		}

		return $list??[];
	}

	/**
	 * @return Generator[]
	 **/
	function getGenerators(): array {
		$sql = Generator::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new Generator($this->getDb(), $r->GENERATOR_NAME);
		}

		return $list??[];
	}

	/**
	 * @return FireBirdException[]
	 **/
	function getExceptions(){
		$sql = FireBirdException::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new FireBirdException($this->getDb(), $r->EXCEPTION_NAME);
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
			$list[] = new UDF($this->getDb(), $r->FUNCTION_NAME);
		}

		return $list??[];
	}

	/**
	 * @return Domain[]
	 **/
	function getDomains(){
		$sql = Domain::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new Domain($this->getDb(), $r->FIELD_NAME);
		}

		return $list??[];
	}

	function getConnection(): IBase {
		return $this->conn;
	}

	static function getSQL(): Select {
		return (new Select())->From('RDB$DATABASE');
	}

	function ddlParts(): array {
		trigger_error("Not implemented yet");
		return [];
	}

	function ddl($PARTS = null): string {
		if(is_null($PARTS)){
			$PARTS = $this->ddlParts();
		}

		trigger_error("Not implemented yet");
		return "";
	}
}
