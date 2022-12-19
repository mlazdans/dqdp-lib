<?php declare(strict_types = 1);

namespace dqdp;

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\interfaces\EntityInterface;
use dqdp\Settings\SettingsType;
use Exception;
use TypeError;

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
	protected array $DB_STRUCT = [];
	protected array $DATA = [];
	protected $Ent;

	function __construct($class){
		$this->CLASS = $class;
		$this->Ent = new Settings\Entity;
	}

	# Interface f-ns
	function get($ID, ?iterable $filters = null): mixed {
		$params = eoe($filters);
		$params->SET_KEY = $ID;

		return $this->fetch($this->query($params));
	}

	function getAll(?iterable $filters = null): mixed {
		$ret = array_fill_keys(array_keys($this->DB_STRUCT), null);
		$q = $this->query($filters);
		while($r = $this->fetch($q)){
			$ret = merge($ret, $r);
		}

		return $ret;
	}

	function getSingle(?iterable $filters = null): mixed {
		if($q = $this->query($filters)){
			return $this->fetch($q);
		}
	}

	function query(?iterable $filters = null){
		$PARAMS = eoe($filters);
		$PARAMS->SET_CLASS = $this->CLASS; // part of PK, parent will take care

		return $this->Ent->query($PARAMS);
	}

	function insert(array|object $DATA){
		throw new Exception("Not implemented");
	}

	function update($ID, array|object $DATA){
		throw new Exception("Not implemented");
	}

	function save(array|object $_){
		// $ST = new SettingsType();
		// $ST->class = $this->CLASS;
		// dumpr($ST, $this->DATA, $this->DB_STRUCT);
		// die;

		$ret = true;
		foreach($this->DB_STRUCT as $k=>$v){
			if(!isset($this->DATA[$k])){
				continue;
			}

			// $v = strtoupper($v);
			$params = [
				'class'=>$this->CLASS,
				'key'=>strtoupper($k),
			];

			if($v == 'serialize'){
				$params[$v] = serialize($this->DATA[$k]);
			} else {
				$params[$v] = $this->DATA[$k];
			}

			$ST = new SettingsType($params);
			dumpr($ST);

			$ret = $ret && $this->Ent->save($ST);
		}

		return $ret;
	}

	# TODO: entity multi key delete
	function delete($ID){
		trigger_error("Not implemented", E_USER_ERROR);
		// list($k) = func_get_args();
		// $params->SET_KEY = $ID;
	}

	function set_trans(DBAInterface $dba) {
		$this->Ent->set_trans($dba);

		return $this;
	}

	function get_trans(): DBAInterface {
		return $this->Ent->get_trans();
	}

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

	function fetch(...$args): mixed {
		list($q) = $args;
		if(!($r = $this->get_trans()->fetch_object($q))){
			return null;
		}

		if(!isset($this->DB_STRUCT[$r->SET_KEY])){
			throw new TypeError("Undefined struct entry: $r->SET_KEY");
		}

		$v = strtoupper($this->DB_STRUCT[$r->SET_KEY]);

		if($v == 'serialize'){
			$ret[$r->SET_KEY] = unserialize($r->{"SET_$v"});
		} else {
			$ret[$r->SET_KEY] = $r->{"SET_$v"};
		}

		return $ret;
	}

}
