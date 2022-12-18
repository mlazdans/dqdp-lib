<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;
use stdClass;

interface DataMapperInterface {
	function __construct(?iterable $data, ?iterable $defaults = null);
	// function initPoperty($v, $k);
	function toDBObject(): stdClass;
	// static function withDefaults(): iterable;
	static function fromDBObject(array|object $o): iterable;
}
