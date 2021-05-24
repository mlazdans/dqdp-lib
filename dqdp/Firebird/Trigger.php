<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Trigger extends FirebirdType
{
	const TYPE_PRE_STORE            = 1;
	const TYPE_POST_STORE           = 2;
	const TYPE_PRE_MODIFY           = 3;
	const TYPE_POST_MODIFY          = 4;
	const TYPE_PRE_ERASE            = 5;
	const TYPE_POST_ERASE           = 6;
	const TYPE_CONNECT              = 8192;
	const TYPE_DISCONNECT           = 8193;
	const TYPE_TRANSACTION_START    = 8194;
	const TYPE_TRANSACTION_COMMIT   = 8195;
	const TYPE_TRANSACTION_ROLLBACK = 8196;

	const TRIGGER_TYPE_SHIFT = 13;

	const TRIGGER_TYPE_MASK  = 3 << Trigger::TRIGGER_TYPE_SHIFT;
	const TRIGGER_TYPE_DML   = 0 << Trigger::TRIGGER_TYPE_SHIFT;
	const TRIGGER_TYPE_DB    = 1 << Trigger::TRIGGER_TYPE_SHIFT;
	const TRIGGER_TYPE_DDL   = 2 << Trigger::TRIGGER_TYPE_SHIFT;
	// that's how database trigger action types are encoded
	//    (TRIGGER_TYPE_DB | type)

	// that's how DDL trigger action types are encoded
	//    (TRIGGER_TYPE_DDL | DDL_TRIGGER_{AFTER | BEFORE} [ | DDL_TRIGGER_??? ...])

	// const DB_TRIGGER_CONNECT        = 0;
	// const DB_TRIGGER_DISCONNECT     = 1;
	// const DB_TRIGGER_TRANS_START    = 2;
	// const DB_TRIGGER_TRANS_COMMIT   = 3;
	// const DB_TRIGGER_TRANS_ROLLBACK = 4;
	// const DB_TRIGGER_MAX            = 5;

	// function __construct(Database $db, $name){
	// 	$this->type = FirebirdObject::TYPE_TRIGGER;
	// 	parent::__construct($db, $name);
	// }

	// function activate(){
	// 	return $this->getDb()->getConnection()->Query("ALTER TRIGGER $this ACTIVE");
	// }

	// function deactivate(){
	// 	return $this->getDb()->getConnection()->Query("ALTER TRIGGER $this INACTIVE");
	// }

	// function enable(){
	// 	return $this->activate();
	// }

	// function disable(){
	// 	return $this->deactivate();
	// }

	static function getSQL(): Select {
		return (new Select())
		->From('RDB$TRIGGERS')
		->Where('RDB$SYSTEM_FLAG = 0');
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['RDB$TRIGGER_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	// Copied from FB source
	function TRIGGER_ACTION_PREFIX($val){
		return ($val + 1) & 1;
	}

	function TRIGGER_ACTION_SUFFIX($val, $slot){
		return (($val + 1) >> ($slot * 2 - 1)) & 3;
	}

	function ddl(): string {
		$MD = $this->getMetadata();
		$ddl = ["CREATE OR ALTER TRIGGER $this FOR $MD->RELATION_NAME ".($MD->TRIGGER_INACTIVE ? "INACTIVE" : "ACTIVE")];

		if(($MD->TRIGGER_TYPE & Trigger::TRIGGER_TYPE_MASK) == Trigger::TRIGGER_TYPE_DML){
			$tddl = [];
			for($slot = 1; $slot <= 3; $slot++){
				$suff = $this->TRIGGER_ACTION_SUFFIX($MD->TRIGGER_TYPE, $slot);
				if($suff == 1)
					$tddl[] = "INSERT";
				elseif($suff == 2)
					$tddl[] = "UPDATE";
				elseif($suff == 3)
					$tddl[] = "DELETE";
			}

			$ddl[] = ($MD->TRIGGER_TYPE & 1 ? "BEFORE" : "AFTER")." ".join(" OR ", $tddl)." POSITION $MD->TRIGGER_SEQUENCE";
		} else {
			trigger_error("TRIGGER_TYPE = $MD->TRIGGER_TYPE not implemented");
		}

		$ddl[] = $MD->TRIGGER_SOURCE;

		return join("\n", $ddl);
	}

	// function isInActive(){
	// 	$this->loadMetadata();
	// 	if($metadata = $this->getMetadata()){
	// 		return (bool)$metadata->TRIGGER_INACTIVE;
	// 	}
	// }

	// function isActive(){
	// 	return !$this->isInActive();
	// }
}
