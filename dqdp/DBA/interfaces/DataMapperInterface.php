<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

use dqdp\DBA\DataObject;
use stdClass;

interface DataMapperInterface {
	function __construct(?iterable $data, ?iterable $defaults = null);
	function initPoperty($v, $k);
	function toDBObject(): stdClass;
	static function withDefaults(): DataObject;
	static function fromDBObject(stdClass $o): DataObject;
}
