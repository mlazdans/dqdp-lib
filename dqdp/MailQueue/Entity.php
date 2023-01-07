<?php declare(strict_types = 1);

namespace dqdp\MailQueue;

use dqdp\SQL\Select;

class Entity extends \dqdp\DBA\AbstractEntity
{
	use MailQueueEntityTrait;

	function select(): Select {
		return (new Select)
		->From($this->getTableName())
		->OrderBy('CREATE_TIME DESC');
	}

	function save(array|object $DATA): mixed {
		$DB_DATA = MailQueueType::toDBObject($DATA);

		$DB_DATA->CREATE_TIME = static function(){
			return 'CURRENT_TIMESTAMP';
		};

		$DB_DATA->TIME_TO_SEND = static function(){
			return 'CURRENT_TIMESTAMP';
		};

		return parent::save($DB_DATA);
	}

}
