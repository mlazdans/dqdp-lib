<?php declare(strict_types = 1);

namespace dqdp\MailQueue;

use dqdp\DBA\interfaces\ORMInterface;
use dqdp\SQL\Select;

class Entity extends \dqdp\DBA\AbstractEntity implements ORMInterface
{
	use MailQueueEntityTrait;

	function select(): Select {
		return (new Select)
		->From($this->getTableName())
		->OrderBy('CREATE_TIME DESC');
	}
}
