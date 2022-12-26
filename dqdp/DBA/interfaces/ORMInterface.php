<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\DataObject;
use stdClass;

interface ORMInterface {
	static function fromDBObject(array|object|null $o): ?DataObject;
	static function toDBObject(DataObject $o): stdClass;
	// static function getDataType(): string;
	// static function getCollectionType(): string;
}
