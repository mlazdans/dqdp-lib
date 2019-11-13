<?php

namespace dqdp;

# TODO: implementēt Entity
class Settings
{
	var $classId = null;
	var $trans = null;
	var $dbStruct = null;

	function __construct($classId){
		$this->classId = $classId;
	}

	function setDbStruct($struct){
		$this->dbStruct = $struct;
	}

	function setTrans($tr){
		$this->trans = $tr;
	}

	function getTrans(){
		return $this->trans;
	}

	function load(){
		$sql = "SELECT * FROM SETTINGS WHERE SET_CLASS = ?";

		if(!($prep = ibase_prepare($this->getTrans(), $sql))){
			return false;
		}

		if(!($q = ibase_execute($prep, $this->classId))){
			return false;
		}

		$data = [];
		while($r = ibase_fetch_object($q, IBASE_TEXT))
		{
			$type = strtoupper($this->dbStruct[$r->SET_KEY]);
			if($type == 'SERIALIZE'){
				$data[$r->SET_KEY] = unserialize($r->{'SET_'.$type});
			} else {
				$data[$r->SET_KEY] = $r->{'SET_'.$type};
			}
		}

		return $data;
	}

	function save($data)
	{
		$ret = true;
		$sql = "
		UPDATE OR INSERT INTO SETTINGS (
			SET_CLASS, SET_KEY, SET_INT, SET_BOOLEAN, SET_FLOAT, SET_STRING, SET_DATE, SET_BINARY, SET_SERIALIZE
		) VALUES (
			?,?,?,?,?,?,?,?,?
		) MATCHING (SET_CLASS, SET_KEY)
		";

		if(!($q = ibase_prepare($this->getTrans(), $sql))){
			return false;
		}

		foreach($data as $k=>$v){
			$int = null;
			$boolean = null;
			$float = null;
			$string = null;
			$date = null;
			$binary = null;
			$serialize = null;

			$type = strtolower($this->dbStruct[$k]);
			if($type == 'serialize'){
				${$type} = serialize($v);
			} else {
				${$type} = $v;
			}
			$ret = ibase_execute($q, $this->classId, $k, $int, $boolean, $float, $string, $date, $binary, $serialize) ? $ret : false;
		}

		return $ret && ibase_commit_ret($this->getTrans());
	}
}
