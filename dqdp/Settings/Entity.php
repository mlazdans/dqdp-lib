<?php declare(strict_types = 1);

namespace dqdp\Settings;

use dqdp\DBA\interfaces\ORMInterface;
use dqdp\Settings\Traits\SettingsEntityTrait;

class Entity extends \dqdp\DBA\AbstractEntity implements ORMInterface
{
	use SettingsEntityTrait;
	// static function getDataType(): string {
	// 	return SettingsType::class;
	// }

	// static function getCollectionType(): string {
	// 	return SettingsCollection::class;
	// }

	// function getTableName(): string {
	// 	return 'SETTINGS';
	// }

	// function getPK(): array {
	// 	return ['SET_DOMAIN','SET_KEY'];
	// }

	// function getGen(): ?string {
	// 	return null;
	// }

	// function fetch(): ?SettingsType {
	// 	return parent::fetch(...func_get_args());
	// }

	// static function fromDBObject(array|object $o): SettingsType {
	// 	$map = [
	// 		'SET_DOMAIN'=>'domain',
	// 		'SET_KEY'=>'key',
	// 		'SET_INT'=>'int',
	// 		'SET_BOOLEAN'=>'bool',
	// 		'SET_FLOAT'=>'float',
	// 		'SET_STRING'=>'string',
	// 		'SET_DATE'=>'date',
	// 		'SET_BINARY'=>'binary',
	// 		'SET_SERIALIZE'=>'serialize',
	// 		'SET_TEXT'=>'text',
	// 	];

	// 	return SettingsType::fromDBObjectFactory($map, $o);
	// }

	// static function toDBObject(AbstractDataObject $o): stdClass {
	// 	$map = [
	// 		'domain'=>'SET_DOMAIN',
	// 		'key'=>'SET_KEY',
	// 		'int'=>'SET_INT',
	// 		'bool'=>'SET_BOOLEAN',
	// 		'float'=>'SET_FLOAT',
	// 		'string'=>'SET_STRING',
	// 		'date'=>'SET_DATE',
	// 		'binary'=>'SET_BINARY',
	// 		'serialize'=>'SET_SERIALIZE',
	// 		'text'=>'SET_TEXT',
	// 	];

	// 	return SettingsType::toDBObjectFactory($o, $map);
	// }
}
