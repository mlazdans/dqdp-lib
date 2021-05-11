<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Trigger extends FirebirdObject
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

	function __construct(Database $db, $name){
		$this->type = FirebirdObject::TYPE_TRIGGER;
		parent::__construct($db, $name);
	}

	function activate(){
		return $this->getDb()->getConnection()->Query("ALTER TRIGGER $this ACTIVE");
	}

	function deactivate(){
		return $this->getDb()->getConnection()->Query("ALTER TRIGGER $this INACTIVE");
	}

	function enable(){
		return $this->activate();
	}

	function disable(){
		return $this->deactivate();
	}

	function loadMetadata(){
		if($metadata = $this->getMetadata()){
			return $metadata;
		}

		$sql = (new Select())
		->From('RDB$TRIGGERS')
		->Where('RDB$SYSTEM_FLAG = 0')
		->Where(['RDB$TRIGGER_NAME = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function isInActive(){
		$this->loadMetadata();
		if($metadata = $this->getMetadata()){
			return (bool)$metadata->TRIGGER_INACTIVE;
		}
	}

	function isActive(){
		return !$this->isInActive();
	}
}
