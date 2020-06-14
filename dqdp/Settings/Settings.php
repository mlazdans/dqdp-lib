<?php

namespace dqdp\Settings;

use dqdp\DBLayer\DBLayer;
use dqdp\Entity\EntityInterface;

/* Ibase
CREATE TABLE SETTINGS
(
  SET_CLASS VARCHAR(64) NOT NULL,
  SET_KEY VARCHAR(64) NOT NULL,
  SET_INT INTEGER,
  SET_BOOLEAN SMALLINT,
  SET_FLOAT DOUBLE PRECISION,
  SET_STRING TEXT,
  SET_DATE TIMESTAMP,
  SET_BINARY BLOB SUB_TYPE 0,
  SET_SERIALIZE TEXT,
  ENTERED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UPDATED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT PK_SETTINGS PRIMARY KEY (SET_CLASS,SET_KEY)
);
*/

/* MySQL
CREATE TABLE settings
(
  SET_CLASS VARCHAR(64) NOT NULL,
  SET_KEY VARCHAR(64) NOT NULL,
  SET_INT INTEGER,
  SET_BOOLEAN SMALLINT,
  SET_FLOAT DOUBLE,
  SET_STRING TEXT,
  SET_DATE TIMESTAMP,
  SET_BINARY BLOB,
  SET_SERIALIZE TEXT,
  ENTERED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UPDATED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT PK_SETTINGS PRIMARY KEY (SET_CLASS,SET_KEY)
);
*/


class Settings implements EntityInterface
{
	var $CLASS;
	protected $DB_STRUCT;
	protected $DATA = [];
	protected $Ent;

	function __construct($class){
		$this->CLASS = $class;
		$this->Ent = new Entity;
	}

	# Interface f-ns
	function get($ID, $params = null){
		$params = eoe($params);
		$params->SET_KEY = $ID;
		return $this->fetch($this->search($params));
	}

	function get_all($params = null){
		$ret = array_fill_keys(array_keys($this->DB_STRUCT), null);
		$q = $this->search($params);
		while($r = $this->fetch($q)){
			$ret = merge($ret, $r);
		}
		return $ret;
	}

	function get_single($params = null){
		if($q = $this->search($params)){
			return $this->fetch($q);
		}
	}

	function search($PARAMS = null){
		$PARAMS = eoe($PARAMS);
		$PARAMS->SET_CLASS = $this->CLASS; // part of PK, parent will take care

		return $this->Ent->search($PARAMS);
	}

	function save(){
		$DATA = eoe($this->DATA);

		$ret = true;
		foreach($this->DB_STRUCT as $k=>$v){
			if($DATA->exists($k)){
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

				$ret = $ret && $this->Ent->save($DB_DATA);
			}
		}

		return $ret;
	}

	# TODO: entity multi key delete
	function delete(){
		trigger_error("Not implemented", E_USER_ERROR);
		// list($k) = func_get_args();
		// $params->SET_KEY = $ID;
	}

	function set_trans(DBLayer $dba) {
		$this->Ent->set_trans($dba);
		return $this;
	}

	function get_trans(): DBLayer {
		return $this->Ent->get_trans();
	}

	# Settings f-ns
	function set_struct(array $struct){
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

	# $k = key | arr | obj
	function set($k, $v = null){
		if(is_array($k)){
			$this->set_array($k);
		} elseif(is_object($k)){
			$this->set_array(get_object_vars($k));
		} else {
			$this->DATA[$k] = $v;
		}
		return $this;
	}

	function set_array($new_data){
		$this->DATA = array_merge($this->DATA, $new_data);
		return $this;
	}

	protected function fetch(...$args){
		list($q) = $args;
		if(!($r = $this->get_trans()->fetch($q))){
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

		# TODO: respektēt default f
		return $ret??(object)[];
	}

}
