<?php

namespace dqdp;

use dqdp\DB\IbaseEntity;

class Settings extends IbaseEntity
{
	var $CLASS;
	var $DB_STRUCT;
	protected $SDATA = [];

	function __construct($class){
		$this->Table = 'SETTINGS';
		$this->PK = ['SET_CLASS','SET_KEY'];
		$this->CLASS = $class;
	}

	function get($k){
		if(isset($this->SDATA[$k])){
			return $this->SDATA[$k];
		} else {
			return $this->fetch($this->search(['SET_KEY'=>$k]));
		}
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
		$this->SDATA = [];
		return $this;
	}

	# $k = key | arr | obj
	function set($k, $v = null){
		if(is_array($k)){
			$this->set_array($k);
		} elseif(is_object($k)){
			$this->set_array(get_object_vars($k));
		} else {
			$this->SDATA[$k] = $v;
		}
		return $this;
	}

	function set_array($new_data){
		$this->SDATA = array_merge($this->SDATA, $new_data);
		return $this;
	}

	function save(){
		$SDATA = eoe($this->SDATA);

		$fields = [
			'SET_CLASS', 'SET_KEY', 'SET_INT', 'SET_BOOLEAN', 'SET_FLOAT', 'SET_STRING', 'SET_DATE', 'SET_BINARY', 'SET_SERIALIZE'
		];

		$ret = true;
		foreach($this->DB_STRUCT as $k=>$v){
			if($SDATA->isset($k)){
				$v = strtoupper($v);
				$DB_DATA = eo([
					'SET_CLASS'=>$this->CLASS,
					'SET_KEY'=>strtoupper($k),
				]);

				if($v == 'SERIALIZE'){
					$DB_DATA->{"SET_$v"} = serialize($SDATA->{$k});
				} else {
					$DB_DATA->{"SET_$v"} = $SDATA->{$k};
				}

				$ret = $ret && parent::save($fields, $DB_DATA);
			}
		}

		return $ret;
	}

	function fetch_all(){
		$ret = [];
		foreach($this->DB_STRUCT as $k=>$v){
			$ret[$k] = null;
		}

		while($r = call_user_func_array([$this, 'fetch'], func_get_args())){
			$ret = merge($ret, $r);
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

		return $ret??(object)[];
	}

	function search($PARAMS = null){
		$PARAMS = eoe($PARAMS);
		$PARAMS->SET_CLASS = $this->CLASS; // part of PK, parent will take care

		return parent::search($PARAMS);
	}
}
