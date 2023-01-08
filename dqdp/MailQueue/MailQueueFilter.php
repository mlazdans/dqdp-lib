<?php declare(strict_types = 1);

namespace dqdp\MailQueue;

use dqdp\DBA\AbstractFilter;
use dqdp\SQL\Condition;
use dqdp\SQL\Select;
use InvalidArgumentException;

class MailQueueFilter extends AbstractFilter {
	function __construct(
		public ?int $ID = null,
		public ?bool $IS_SENT = null,
		public ?bool $IS_ERRORED = null,
		public ?array $MQ_IDS = null,
	) {}

	function apply_filter(Select $sql): Select {
		$this->apply_fields_with_values($sql, ['ID']);

		$Cond = new Condition();
		if(isset($this->IS_SENT)){
			$Cond->add_condition($this->IS_SENT ? 'SENT_TIME IS NOT NULL' : 'SENT_TIME IS NULL');
		}

		if(isset($this->IS_ERRORED)){
			$Cond->add_condition($this->IS_ERRORED ? 'TRY_SENT > 0' : 'TRY_SENT = 0');
		}

		if($this->MQ_IDS !== null){
			if(empty($this->MQ_IDS)){
				throw new InvalidArgumentException('Norādīts tukšs $MQ_IDS[]');
			}
			$sql->WhereIn("ID", $this->MQ_IDS);
		}

		if($Cond->non_empty()){
			$sql->Where($Cond);
		}

		return $sql;
	}
}
