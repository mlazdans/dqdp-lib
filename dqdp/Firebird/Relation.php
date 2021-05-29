<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\FireBird\Relation\Field;
use dqdp\SQL\Select;

abstract class Relation extends FirebirdObject implements DDL
{
	static function getSQL(): Select {
		return (new Select())->From('RDB$RELATIONS AS relations')->Where('relations.RDB$SYSTEM_FLAG = 0');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['relations.RDB$RELATION_NAME = ?', $this->name]);
	}

	/**
	 * @return Field[]
	 **/
	function getFields(): array {
		$sql = Field::getSQL()->Where(['relation_fields.RDB$RELATION_NAME = ?', $this->name]);

		foreach($this->getList($sql) as $r){
			$list[] = (new Field($this, $r->FIELD_NAME))->setMetadata($r);
		}

		return $list??[];
	}
}
