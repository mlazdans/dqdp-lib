<?php

declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\DBA\driver\IBase;
use dqdp\SQL\Insert;

class IBaseEntity extends Entity {
	protected AbstractIBaseTable $Table;
	protected IBase $dba;
	protected ?string $Gen;

	function __construct(){
		parent::__construct();
		$this->Gen = $this->Table->getGen();
	}

	function getTable() {
		return $this->Table;
	}

	// function set_trans(IBase $dba){
	// 	$this->dba = $dba;

	// 	return $this;
	// }

	// function get_trans(): IBase {
	// 	return $this->dba;
	// }

	function save(iterable $DATA){
		$sql_fields = (array)merge_only($this->Table->getFields(), $DATA);

		if(!is_array($this->PK)){
			$PK_val = get_prop($DATA, $this->PK);
			if(is_null($PK_val)){
				if($Gen = $this->Table->getGen()){
					$sql_fields[$this->PK] = function() use ($Gen) {
						return "NEXT VALUE FOR $Gen";
					};
				}
			} else {
				$sql_fields[$this->PK] = $PK_val;
			}
		}

		$sql = (new Insert)->Into($this->tableName)
			->Values($sql_fields)
			->Update();

		$PK_fields_str = is_array($this->PK) ? join(",", $this->PK) : $this->PK;
		$sql->after("values", "matching", "MATCHING ($PK_fields_str)")
			->after("values", "returning", "RETURNING $PK_fields_str");

		if($q = $this->get_trans()->query($sql)){
			$retPK = $this->get_trans()->fetch($q);
			if(is_array($this->PK)){
				return $retPK;
			} else {
				return get_prop($retPK, $this->PK);
			}
		} else {
			return false;
		}
	}
}
