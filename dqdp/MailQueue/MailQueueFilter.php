<?php declare(strict_types = 1);

namespace dqdp\MailQueue;

use dqdp\DBA\AbstractFilter;
use dqdp\SQL\Condition;
use dqdp\SQL\Select;

class MailQueueFilter extends AbstractFilter {
	function __construct(
		public ?int $ID = null,
		public ?int $SENT = null,
		public ?int $UNSENT = null,
		public ?int $ERRORED = null,
	) {}

	function apply_filter(Select $sql): Select {
		$this->apply_fields_with_values($sql, ['ID']);

		$Cond = new Condition();
		if($this->SENT)$Cond->add_condition('SENT_TIME IS NOT NULL', Condition::OR);
		if($this->UNSENT)$Cond->add_condition('SENT_TIME IS NULL AND TRY_SENT = 0', Condition::OR);
		if($this->ERRORED)$Cond->add_condition('SENT_TIME IS NULL AND TRY_SENT >= 1', Condition::OR);

		if($Cond->non_empty()){
			$sql->Where($Cond);
		}

		return $sql;
	}
}
