<?php declare(strict_types = 1);

namespace dqdp\Settings;

class Entity extends \dqdp\DBA\AbstractEntity
{
	// function getDataType(): string {
	// 	return SettingsType::class;
	// }

	// function getCollectionType(): string {
	// 	return SettingsCollection::class;
	// }

	function getTableName(): string {
		return 'SETTINGS';
	}

	function getPK(): array {
		return ['SET_CLASS','SET_KEY'];
	}

	function getGen(): ?string {
		return null;
	}

}
