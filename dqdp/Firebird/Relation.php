<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\FireBird\Relation\Field;
use dqdp\SQL\Select;

class Relation extends FirebirdObject
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

	function ddlParts(): array {
		$MD = $this->getMetadata();

		$parts['relation_name'] = "$this";
		$parts['relation_type'] = $MD->RELATION_TYPE;
		$parts['col_def'] = $this->getFields();

		return $parts;
	}
}
