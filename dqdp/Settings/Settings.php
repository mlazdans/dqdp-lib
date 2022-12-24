<?php declare(strict_types = 1);

namespace dqdp\Settings;

use dqdp\DBA\AbstractFilter;
use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\interfaces\EntityInterface;
use dqdp\Settings\SetType;
use dqdp\Settings\Types\SettingsFilter;
use Exception;
use TypeError;

/* Ibase
CREATE TABLE SETTINGS
(
  SET_DOMAIN VARCHAR(64) NOT NULL,
  SET_KEY VARCHAR(64) NOT NULL,
  SET_INT INTEGER,
  SET_BOOLEAN SMALLINT,
  SET_FLOAT DOUBLE PRECISION,
  SET_STRING VARCHAR(128),
  SET_DATE TIMESTAMP,
  SET_BINARY BLOB SUB_TYPE 0,
  SET_SERIALIZE TEXT,
  SET_TEXT TEXT,
  ENTERED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UPDATED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT PK_SETTINGS PRIMARY KEY (SET_DOMAIN,SET_KEY)
);
*/

/* MySQL
CREATE TABLE settings
(
  SET_DOMAIN VARCHAR(64) NOT NULL,
  SET_KEY VARCHAR(64) NOT NULL,
  SET_INT INTEGER,
  SET_BOOLEAN SMALLINT,
  SET_FLOAT DOUBLE,
  SET_STRING VARCHAR(128),
  SET_DATE TIMESTAMP,
  SET_BINARY BLOB,
  SET_SERIALIZE TEXT,
  SET_TEXT TEXT,
  ENTERED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UPDATED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT PK_SETTINGS PRIMARY KEY (SET_DOMAIN,SET_KEY)
);
*/

class Settings implements EntityInterface
{
	protected array $DB_STRUCT = [];
	// protected array $DATA = [];
	protected Entity $Ent;

	function __construct(public string $domain){
		$this->Ent = new Entity;
	}

	function get($ID, ?AbstractFilter $filters = null): mixed {
		return $this->getSingle(SettingsFilter::initFrom(["SET_KEY" => $ID], $filters));
	}

	function getAll(?AbstractFilter $filters = null): mixed {
		$ret = array_fill_keys(array_keys($this->DB_STRUCT), null);
		$q = $this->query($filters);
		while($r = $this->fetch($q)){
			$ret = merge($ret, $r);
		}

		return $ret;
	}

	function getSingle(?AbstractFilter $filters = null): mixed {
		if($q = $this->query($filters)){
			return $this->fetch($q);
		}
	}

	function query(?AbstractFilter $filters = null){
		return $this->Ent->query(SettingsFilter::initFrom(["SET_DOMAIN" => $this->domain], $filters));
	}

	function insert(array|object $DATA){
		throw new Exception("Not implemented");
	}

	function update($ID, array|object $DATA){
		throw new Exception("Not implemented");
	}

	function save(array|object $DATA){
		// $ST = new SettingsType();
		// $ST->class = $this->CLASS;
		// dumpr($ST, $this->DATA, $this->DB_STRUCT);
		// die;

		$ret = true;
		foreach($this->DB_STRUCT as $k=>$v){
			if(!prop_exists($DATA, $k) || !prop_initialized($DATA, $k)){
				continue;
			}

			// $v = strtoupper($v);
			$params = [
				'SetDomain'=>$this->domain,
				'SetKey'=>$k,
			];

			switch($v) {
				case SetType::serialize:
					$params[$v->value] = serialize(get_prop($DATA, $k));
					break;
				default:
					$params[$v->value] = get_prop($DATA, $k);
			}

			$ret = $ret && $this->Ent->save(new Types\Settings($params));
		}

		return $ret;
	}

	# TODO: entity multi key delete
	function delete($ID){
		trigger_error("Not implemented", E_USER_ERROR);
		// list($k) = func_get_args();
		// $params->SET_KEY = $ID;
	}

	function set_trans(DBAInterface $dba): static {
		$this->Ent->set_trans($dba);

		return $this;
	}

	function get_trans(): DBAInterface {
		return $this->Ent->get_trans();
	}

	# TODO: separate class for struct
	function set_struct(array $struct): static {
		$this->DB_STRUCT = $struct;

		return $this;
	}

	function unset(string|int $k): static {
		unset($this->DATA[$k]);

		return $this;
	}

	// function reset(){
	// 	$this->DATA = [];

	// 	return $this;
	// }

	# $k = key | arr | obj
	// function set(string|int $k, mixed $v = null): static {
	// 	if(is_array($k)){
	// 		$this->set_array($k);
	// 	} elseif(is_object($k)){
	// 		$this->set_array(get_object_vars($k));
	// 	} elseif(isset($this->DB_STRUCT[$k])) {
	// 		$this->DATA[$k] = $v;
	// 	} else {
	// 		throw new TypeError("Undefined struct entry: $k");
	// 	}

	// 	return $this;
	// }

	// function set_array(array $new_data): static {
	// 	$this->DATA = array_merge($this->DATA, $new_data);

	// 	return $this;
	// }

	function fetch(...$args): mixed {
		list($q) = $args;

		if(!($r = $this->Ent->fetch($q))){
			return null;
		}

		if(!isset($this->DB_STRUCT[$r->SetKey])){
			throw new TypeError("Undefined struct entry: $r->SetKey");
		}

		switch($v = $this->DB_STRUCT[$r->SetKey]){
			case SetType::serialize:
				return [$r->SetKey => unserialize($r->{$v->value})];
			default:
				return [$r->SetKey => $r->{$v->value}];
		}
	}

}
