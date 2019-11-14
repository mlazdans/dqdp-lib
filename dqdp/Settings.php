<?php

namespace dqdp;

use dqdp\DB\IbaseEntity;

class Settings extends IbaseEntity
{
	var $CLASS;
	var $DB_STRUCT;
	var $DATA = [];

	function __construct($class){
		$this->Table = 'SETTINGS';
		$this->PK = ['SET_CLASS','SET_KEY'];
		$this->CLASS = $class;
		return parent::__construct();
	}

	function set_struct($struct){
		$this->DB_STRUCT = $struct;
		return $this;
	}

	function unset($k){
		unset($this->DATA[$k]);
		return $this;
	}

	function reset(){
		$this->DATA = [];
		return $this;
	}

	function set($k, $v){
		$this->DATA[$k] = $v;
		return $this;
	}

	function set_array($new_data){
		$this->DATA = array_merge($this->DATA, $new_data);
		return $this;
	}

	function save(){
		$DATA = eoe($this->DATA);

		$fields = [
			'SET_CLASS', 'SET_KEY', 'SET_INT', 'SET_BOOLEAN', 'SET_FLOAT', 'SET_STRING', 'SET_DATE', 'SET_BINARY', 'SET_SERIALIZE'
		];

		$ret = true;
		foreach($this->DB_STRUCT as $k=>$v){
			if($DATA->isset($k)){
				$v = strtoupper($v);
				$DB_DATA = eo([
					'SET_CLASS'=>$this->CLASS,
					'SET_KEY'=>strtoupper($k),
				]);

				if($v == 'SERIALIZE'){
					$DB_DATA->{"SET_$v"} = serialize($DATA->{$k});
				} else {
					$DB_DATA->{"SET_$v"} = $DATA->{$k};
				}

				$ret = $ret && parent::save($fields, $DB_DATA);
			}
		}

		return $ret;
	}

	function fetch(){
		list($q) = func_get_args();
		if(!($r = parent::fetch($q))){
			return false;
		}

		$dbs = $this->DB_STRUCT;
		if(!isset($dbs[$r->SET_KEY])){
			trigger_error("Undefined variable: $r->SET_KEY");
			return false;
		}

		$v = strtoupper($dbs[$r->SET_KEY]);
		if($v == 'SERIALIZE'){
			$ret[$r->SET_KEY] = unserialize($r->{"SET_$v"});
		} else {
			$ret[$r->SET_KEY] = $r->{"SET_$v"};
		}


		return (object)($ret??[]);
	}

	function search($DATA = null){
		$DATA = eoe($DATA);
		$DATA->SET_CLASS = $this->CLASS; // part of PK, parent will take care

		parent::before_search($DATA);
		return parent::do_search();
	}
}
