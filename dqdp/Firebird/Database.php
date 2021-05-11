<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\DBA\driver\IBase;

class Database
{
	private $conn;

	function connect(IBase $conn){
		$this->conn = $conn;
	}

	function drop(){
	}

	function backup(){
	}

	function restore(){
	}

	function getTables() {
		return (new TableList($this))->get();
	}

	function getViews(){
		return (new ViewList($this))->get();
	}

	function getTriggers(){
		return (new TriggerList($this))->get();
	}

	function getActiveTriggers(){
		return (new TriggerList($this))->get(['active'=>true]);
	}

	function getProcedures(){
		return (new ProcedureList($this))->get();
	}

	function getGenerators(){
		$List = new GeneratorList($this);
		return $List->get();
	}

	function getExceptions(){
		$List = new ExceptionList($this);
		return $List->get();
	}

	function getIndexes(){
		$List = new IndexList($this);
		return $List->get();
	}

	function getActiveIndexes(){
		$List = new IndexList($this);
		return $List->get(['active'=>true]);
	}

	function getFunctions(){
		$List = new FunList($this);
		return $List->get();
	}

	function getDomains(){
		$List = new DomainList($this);
		return $List->get();
	}

	function getConnection(){
		return $this->conn;
	}

	function getCon(){
		return $this->getConnection();
	}

}

