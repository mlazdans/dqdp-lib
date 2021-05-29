<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\FireBird\DDL;
use dqdp\SQL\Select;

class DBTrigger extends Trigger implements DDL
{
	static function getSQL(): Select {
		return parent::getSQL()
		->Where('triggers.RDB$RELATION_NAME IS NULL')
		->Where(sprintf('triggers.RDB$TRIGGER_TYPE IN (%s)', join(",", [
			Trigger::TYPE_CONNECT, Trigger::TYPE_DISCONNECT, Trigger::TYPE_TRANSACTION_START,
			Trigger::TYPE_TRANSACTION_COMMIT, Trigger::TYPE_TRANSACTION_ROLLBACK
			])))
		->Where('triggers.RDB$SYSTEM_FLAG = 0')
		;
	}

	function ddlParts(): array{
		$parts = parent::ddlParts();
		$MD = $this->getMetadata();

		$event_id = ($MD->TRIGGER_TYPE & ~Trigger::TRIGGER_TYPE_DB);

		$events = [
			"CONNECT", "DISCONNECT", "TRANSACTION START",
			"TRANSACTION COMMIT", "TRANSACTION ROLLBACK"
		];

		$parts['db_event'] = $events[$event_id];

		return $parts;
	}

	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		$ddl = ["$this $parts[active] ON $parts[db_event] POSITION $parts[position]"];
		$ddl[] = $parts['module_body'];

		return join(" ", $ddl);
	}
}
