<?php

namespace dqdp\QueueMailer;

use dqdp\SQL\Condition;
use dqdp\SQL\Select;

class Entity extends \dqdp\Entity
{
	function __construct(){
		$this->Table = 'MAIL_QUEUE';
		$this->PK = 'ID';
		$this->Gen = 'GEN_MAIL_QUEUE_ID';
	}

	function select(){
		return (new Select)
		->From($this->Table)
		->OrderBy('CREATE_TIME DESC');
	}

	function set_filters($sql, $DATA = null){
		$DATA = eoe($DATA);
		$Cond = new Condition;
		if($DATA->SENT)$Cond->add_condition('SENT_TIME IS NOT NULL', Condition::OR);
		if($DATA->UNSENT)$Cond->add_condition('SENT_TIME IS NULL AND TRY_SENT = 0', Condition::OR);
		if($DATA->ERRORED)$Cond->add_condition('SENT_TIME IS NULL AND TRY_SENT >= 1', Condition::OR);
		if($Cond->non_empty()){
			$sql->Where($Cond);
		}

		return parent::set_filters($sql, $DATA);
	}

	function fields(): array {
		return [
			'CREATE_TIME', 'TIME_TO_SEND', 'SENT_TIME', 'ID_USER', 'IP', 'SENDER', 'RECIPIENT', 'BODY',
			'ALT_BODY', 'MIME_HEADERS', 'MIME_BODY', 'MAILER_OBJ', 'ERROR_MSG', 'TRY_SENT', 'DELETE_AFTER_SEND'
		];
	}

}
