<?php

namespace dqdp\QueueMailer;

use dqdp\SQL\Condition;
use dqdp\SQL\Select;
use dqdp\SQL\Statement;

class Entity extends \dqdp\DBA\Entity
{
	function __construct(){
		$this->Table = new QueueMailerTable;
		parent::__construct();
	}

	function select(): Select {
		return (new Select)
		->From($this->tableName)
		->OrderBy('CREATE_TIME DESC');
	}

	function set_filters(Statement $sql, ?iterable $F = null): Statement {
		$F = eoe($F);

		$Cond = new Condition;
		if($F->SENT)$Cond->add_condition('SENT_TIME IS NOT NULL', Condition::OR);
		if($F->UNSENT)$Cond->add_condition('SENT_TIME IS NULL AND TRY_SENT = 0', Condition::OR);
		if($F->ERRORED)$Cond->add_condition('SENT_TIME IS NULL AND TRY_SENT >= 1', Condition::OR);
		if($Cond->non_empty()){
			$sql->Where($Cond);
		}

		return parent::set_filters($sql, $F);
	}

}
