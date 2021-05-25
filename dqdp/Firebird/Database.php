<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\DBA\driver\IBase;
use dqdp\SQL\Select;

class Database extends FirebirdObject
{
	private $conn;

	function __construct(IBase $conn){
		$this->connect($conn);
		parent::__construct($this, '');
	}

	function connect(IBase $conn){
		$this->conn = $conn;
	}

	function getTables(): array {
		$sql = Relation::getSQL()->Where(['RDB$RELATION_TYPE = ?', Relation::TYPE_PERSISTENT]);

		foreach($this->getList($sql) as $r){
			$list[] = new Relation($this->getDb(), $r->RELATION_NAME);
		}

		return $list??[];
	}

	function getViews(): array {
		$sql = Relation::getSQL()->Where(['RDB$RELATION_TYPE = ?', Relation::TYPE_VIEW]);

		foreach($this->getList($sql) as $r){
			$list[] = new Relation($this->getDb(), $r->RELATION_NAME);
		}

		return $list??[];
	}

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

	function getProcedures(){
		$sql = Procedure::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new Procedure($this->getDb(), $r->PROCEDURE_NAME);
		}

		return $list??[];
	}

	function getGenerators(): array {
		$sql = Generator::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new Generator($this->getDb(), $r->GENERATOR_NAME);
		}

		return $list??[];
	}

	function getExceptions(){
		$sql = FireBirdException::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new FireBirdException($this->getDb(), $r->EXCEPTION_NAME);
		}

		return $list??[];
	}

	function getIndexes(){
		$sql = Index::getSQL()->Where('rc.RDB$CONSTRAINT_TYPE IS NULL');
		foreach($this->getList($sql) as $r){
			$list[] = new Index($this->getDb(), $r->INDEX_NAME);
		}

		return $list??[];
	}

	function getFKs(){
		$sql = Index::getSQL()->Where('rc.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\'');
		foreach($this->getList($sql) as $r){
			$list[] = new Index($this->getDb(), $r->INDEX_NAME);
		}

		return $list??[];
	}

	function getUDFs(){
		$sql = UDF::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new UDF($this->getDb(), $r->FUNCTION_NAME);
		}

		return $list??[];
	}

	function getDomains(){
		$sql = Domain::getSQL();
		foreach($this->getList($sql) as $r){
			$list[] = new Domain($this->getDb(), $r->FIELD_NAME);
		}

		return $list??[];
	}

	function getConnection(){
		return $this->conn;
	}

	static function getSQL(): Select {
		return (new Select())->From('RDB$DATABASE');
	}

	function ddl(): string {
		trigger_error("Not implemented yet");
		return "";
	}
}
