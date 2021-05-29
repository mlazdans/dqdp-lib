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

class FireBirdException extends FirebirdObject implements DDL
{
	static function getSQL(): Select {
		return (new Select())->From('RDB$EXCEPTIONS AS exceptions')->Where('exceptions.RDB$SYSTEM_FLAG = 0');
		// ->OrderBy('RDB$EXCEPTION_NAME')
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['exceptions.RDB$EXCEPTION_NAME = ?', $this->name]);
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
