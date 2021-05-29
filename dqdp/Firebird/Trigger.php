<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// CREATE TRIGGER trigname
//   { <relation_trigger_legacy>
//   | <relation_trigger_sql2003>
//   | <database_trigger>
//   | <ddl_trigger> }
//   <module-body>

// <module-body> ::=
//   !! See Syntax of Module Body !!

// <relation_trigger_legacy> ::=
//   FOR {tablename | viewname}
//   [ACTIVE | INACTIVE]
//   {BEFORE | AFTER} <mutation_list>
//   [POSITION number]

// <relation_trigger_sql2003> ::=
//   [ACTIVE | INACTIVE]
//   {BEFORE | AFTER} <mutation_list>
//   [POSITION number]
//   ON {tablename | viewname}

// <database_trigger> ::=
//   [ACTIVE | INACTIVE] ON <db_event>
//   [POSITION number]

// <ddl_trigger> ::=
//   [ACTIVE | INACTIVE]
//   {BEFORE | AFTER} <ddl_event>
//   [POSITION number]

// <mutation_list> ::=
//   <mutation> [OR <mutation> [OR <mutation>]]

// <mutation> ::= INSERT | UPDATE | DELETE

// <db_event> ::=
//     CONNECT | DISCONNECT
//   | TRANSACTION {START | COMMIT | ROLLBACK}

// <ddl_event> ::=
//     ANY DDL STATEMENT
//   | <ddl_event_item> [{OR <ddl_event_item>} ...]

// <ddl_event_item> ::=
//     {CREATE | ALTER | DROP} TABLE
//   | {CREATE | ALTER | DROP} PROCEDURE
//   | {CREATE | ALTER | DROP} FUNCTION
//   | {CREATE | ALTER | DROP} TRIGGER
//   | {CREATE | ALTER | DROP} EXCEPTION
//   | {CREATE | ALTER | DROP} VIEW
//   | {CREATE | ALTER | DROP} DOMAIN
//   | {CREATE | ALTER | DROP} ROLE
//   | {CREATE | ALTER | DROP} SEQUENCE
//   | {CREATE | ALTER | DROP} USER
//   | {CREATE | ALTER | DROP} INDEX
//   | {CREATE | DROP} COLLATION
//   | ALTER CHARACTER SET
//   | {CREATE | ALTER | DROP} PACKAGE
//   | {CREATE | DROP} PACKAGE BODY
//   | {CREATE | ALTER | DROP} MAPPING

class Trigger extends FirebirdObject
{
	const TYPE_PRE_STORE            = 1;
	const TYPE_POST_STORE           = 2;
	const TYPE_PRE_MODIFY           = 3;
	const TYPE_POST_MODIFY          = 4;
	const TYPE_PRE_ERASE            = 5;
	const TYPE_POST_ERASE           = 6;
	const TYPE_CONNECT              = 8192;
	const TYPE_DISCONNECT           = 8193;
	const TYPE_TRANSACTION_START    = 8194;
	const TYPE_TRANSACTION_COMMIT   = 8195;
	const TYPE_TRANSACTION_ROLLBACK = 8196;

	const TRIGGER_TYPE_SHIFT = 13;

	const TRIGGER_TYPE_DML   = 0 << Trigger::TRIGGER_TYPE_SHIFT;
	const TRIGGER_TYPE_DB    = 1 << Trigger::TRIGGER_TYPE_SHIFT;
	const TRIGGER_TYPE_DDL   = 2 << Trigger::TRIGGER_TYPE_SHIFT;
	const TRIGGER_TYPE_MASK  = 3 << Trigger::TRIGGER_TYPE_SHIFT;
	// that's how database trigger action types are encoded
	//    (TRIGGER_TYPE_DB | type)

	// that's how DDL trigger action types are encoded
	//    (TRIGGER_TYPE_DDL | DDL_TRIGGER_{AFTER | BEFORE} [ | DDL_TRIGGER_??? ...])

	// const DB_TRIGGER_CONNECT        = 0;
	// const DB_TRIGGER_DISCONNECT     = 1;
	// const DB_TRIGGER_TRANS_START    = 2;
	// const DB_TRIGGER_TRANS_COMMIT   = 3;
	// const DB_TRIGGER_TRANS_ROLLBACK = 4;
	// const DB_TRIGGER_MAX            = 5;

	static function getSQL(): Select {
		return (new Select())->From('RDB$TRIGGERS AS triggers');
		// ->Where('triggers.RDB$SYSTEM_FLAG = 0')
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['triggers.RDB$TRIGGER_NAME = ?', $this->name]);
	}

	# Copied from FB source
	protected function TRIGGER_ACTION_SUFFIX($val, $slot){
		return (($val + 1) >> ($slot * 2 - 1)) & 3;
	}

	# database_trigger | relation_trigger | ddl_trigger
	static function getType($TRIGGER_TYPE){
		if(($TRIGGER_TYPE & Trigger::TRIGGER_TYPE_MASK) == Trigger::TRIGGER_TYPE_DML){
			return 'relation_trigger';
		} elseif(($TRIGGER_TYPE & Trigger::TRIGGER_TYPE_MASK) == Trigger::TRIGGER_TYPE_DB){
			return 'database_trigger';
		}
	}

	function ddlParts(): array {
		$MD = $this->getMetadata();

		$parts['trigname'] = "$this";
		$parts['module_body'] = $MD->TRIGGER_SOURCE;
		$parts['active'] = $MD->TRIGGER_INACTIVE ? "INACTIVE" : "ACTIVE";
		$parts['position'] = $MD->TRIGGER_SEQUENCE;
		$parts['system_flag'] = $MD->SYSTEM_FLAG;
		$parts['type'] = Trigger::getType($MD->TRIGGER_TYPE);

		return $parts;
	}
}
