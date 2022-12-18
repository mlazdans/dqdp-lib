<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\DBA\AbstractDataObject;
use stdClass;

interface ORMInterface {
	static function fromDBObject(array|object $o): AbstractDataObject;
	static function toDBObject(AbstractDataObject $o): stdClass;
	static function getDataType(): string;
	static function getCollectionType(): string;
}
