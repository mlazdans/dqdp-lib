<?php declare(strict_types = 1);

namespace dqdp\Settings;

use TypeError;
use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\interfaces\TransactionInterface;

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

class Settings implements TransactionInterface
{
	protected array $DB_STRUCT = [];
	protected Entity $Ent;

	function __construct(protected string $Domain){
		$this->Ent = new Entity;
	}

	function load() {
		$ret = array_fill_keys(array_keys($this->DB_STRUCT), null);
		$data = $this->Ent->getAll(new SettingsFilter(SET_DOMAIN: $this->Domain));
		foreach($data as $r){
			if(!isset($this->DB_STRUCT[$r->SetKey])){
				throw new TypeError("Undefined struct entry: $r->SetKey");
			}
			$type = $this->DB_STRUCT[$r->SetKey];
			$ret[$r->SetKey] = match($type) {
				SetType::serialize => unserialize($r->{$type->value}),
				default => $r->{$type->value}
			};
		}

		return (object)$ret;
	}

	function save(array|object $DATA){
		$ret = true;
		foreach($this->DB_STRUCT as $k=>$v){
			if(!prop_exists($DATA, $k) || !prop_initialized($DATA, $k)){
				continue;
			}

			$params = new SettingsDummy(SetDomain: $this->Domain, SetKey: $k);

			switch($v) {
				case SetType::serialize:
					$params->{$v->value} = serialize(get_prop($DATA, $k));
					break;
				default:
					$params->{$v->value} = get_prop($DATA, $k);
			}

			$ret = $ret && $this->Ent->save(new SettingsType($params));
		}

		return $ret;
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
}
