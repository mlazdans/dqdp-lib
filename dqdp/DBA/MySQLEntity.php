<?php

declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\DBA\driver\MySQL_PDO;
use dqdp\SQL\Insert;

class MySQLEntity extends Entity {
	protected AbstractMySQLTable $Table;
	protected MySQL_PDO $dba;

	function getTable() {
		return $this->Table;
	}

	function save(iterable $DATA){
		$sql_fields = (array)merge_only($this->Table->getFields(), $DATA);

		if(!is_array($this->PK)){
			$PK_val = get_prop($DATA, $this->PK);
			if(is_null($PK_val)){
			} else {
				$sql_fields[$this->PK] = $PK_val;
			}
		}

		$sql = (new Insert)->Into($this->tableName)
			->Values($sql_fields)
			->Update();

		if($this->get_trans()->query($sql)){
			if(is_array($this->PK)){
				foreach($this->PK as $k){
					$ret[] = get_prop($DATA, $k);
				}
				return $ret??[];
			} else {
				if(empty($sql_fields[$this->PK])){
					return $this->mysql_last_id();
				} else {
					return $sql_fields[$this->PK];
				}
			}
		} else {
			return false;
		}
	}

	private function mysql_last_id(){
		return get_prop($this->get_trans()->execute_single("SELECT LAST_INSERT_ID() AS last_id"), 'last_id');
	}
}
