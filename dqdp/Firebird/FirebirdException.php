<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// CREATE EXCEPTION exception_name '<message>'

// <message> ::= <message-part> [<message-part> ...]

// <message-part> ::=
//     <text>
//   | @<slot>

// <slot> ::= one of 1..9

class FireBirdException extends FirebirdType
{
	static function getSQL(): Select {
		return (new Select())
		->From('RDB$EXCEPTIONS')
		->Where('RDB$SYSTEM_FLAG = 0')
		// ->OrderBy('RDB$EXCEPTION_NAME')
		;
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['RDB$EXCEPTION_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();
		return [
			'exception_name'=>"$this",
			'message'=>$MD->MESSAGE
		];
	}

	function ddl($PARTS = null): string {
		if(is_null($PARTS)){
			$PARTS = $this->ddlParts();
		}

		# TODO: need qouting?
		return "CREATE EXCEPTION $PARTS[exception_name] '$PARTS[message]'";
	}
}
