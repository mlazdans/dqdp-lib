<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\DBA\DataObject;
use stdClass;

interface DataMapperInterface {
	static function withDefaults(): DataObject;
	// static function fromIterable(array|object $O, array|object $DO = null): DataObject;
	static function fromIterable(array|object $O): DataObject;
	static function fromDBObject(stdClass $o): DataObject;
	function init(): DataObject;
	function toDBObject(): stdClass;
}
