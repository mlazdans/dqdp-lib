<?php

declare(strict_types = 1);

namespace dqdp\FireBird\Field;

class Type
{
	const SHORT                          = 7;
	const LONG                           = 8;
	const QUAD                           = 9;
	const FLOAT                          = 10;
	const DATE                           = 12;
	const TIME                           = 13;
	const TEXT                           = 14;
	const CHAR                           = 14; // Alias TYPE_TEXT
	const INT64                          = 16;
	const DOUBLE                         = 27;
	const TIMESTAMP                      = 35;
	const VARYING                        = 37;
	const CSTRING                        = 40;
	const BLOB_ID                        = 45;
	const BLOB                           = 261;

	const SUBTYPE_INT_NUMERIC                 = 1;
	const SUBTYPE_INT_DECIMAL                 = 2;

	const SUBTYPE_BINARY                      = 0;
	const SUBTYPE_TEXT                        = 1;
	const SUBTYPE_BLR                         = 2;
	const SUBTYPE_ACL                         = 3;
	const SUBTYPE_RANGES                      = 4;
	const SUBTYPE_SUMMARY                     = 5;
	const SUBTYPE_FORMAT                      = 6;
	const SUBTYPE_TRANSACTION_DESCRIPTION     = 7;
	const SUBTYPE_EXTERNAL_FILE_DESCRIPTION   = 8;
	const SUBTYPE_DEBUG_INFORMATION           = 9;

	// static $QuotableTypes = [
	// 	Type::TEXT,
	// 	Type::VARYING,
	// 	Type::CSTRING,
	// 	Type::BLOB,
	// 	Type::DATE,
	// 	Type::TIME,
	// 	Type::TIMESTAMP, # NOTE: test database dialects
	// ];

	static $TypeNames = [
		Type::SHORT            => 'SMALLINT',
		Type::LONG             => 'INTEGER',
		Type::QUAD             => 'QUAD',
		Type::FLOAT            => 'FLOAT',
		Type::DATE             => 'DATE',
		Type::TIME             => 'TIME',
		Type::TEXT             => 'CHAR',
		Type::INT64            => 'BIGINT',
		Type::DOUBLE           => 'DOUBLE PRECISION',
		Type::TIMESTAMP        => 'TIMESTAMP',
		Type::VARYING          => 'VARCHAR',
		Type::CSTRING          => 'CSTRING',
		Type::BLOB_ID          => 'BLOB_ID',
		Type::BLOB             => 'BLOB',
	];

	static $SubtypeNames = [
		Type::SUBTYPE_BINARY                     => 'BINARY',
		Type::SUBTYPE_TEXT                       => 'TEXT',
		Type::SUBTYPE_BLR                        => 'BLR',
		Type::SUBTYPE_ACL                        => 'ACL',
		Type::SUBTYPE_RANGES                     => 'RANGES',
		Type::SUBTYPE_SUMMARY                    => 'SUMMARY',
		Type::SUBTYPE_FORMAT                     => 'FORMAT',
		Type::SUBTYPE_TRANSACTION_DESCRIPTION    => 'TRANSACTION_DESCRIPTION',
		Type::SUBTYPE_EXTERNAL_FILE_DESCRIPTION  => 'EXTERNAL_FILE_DESCRIPTION',
		Type::SUBTYPE_DEBUG_INFORMATION          => 'DEBUG_INFORMATION',
	];

	static $IntSubtypeNames = [
		Type::SUBTYPE_INT_NUMERIC   => 'NUMERIC',
		Type::SUBTYPE_INT_DECIMAL   => 'DECIMAL',
	];

	static function name(int $type): string {
		return Type::$TypeNames[$type];
	}

	// static function nameByIntSubtype($type){
	// 	return (isset(Field::$IntSubtypeNames[$type]) ? Field::$IntSubtypeNames[$type] : false);
	// }

	# Numeric types when scale know but precision not
	# TODO: might change in FB 4.0
	static function precision($type){
		$FIELD_PRECISION = false;

		if($type == Type::SHORT) {
			$FIELD_PRECISION = 4;
		}

		if($type == Type::LONG) {
			$FIELD_PRECISION = 9;
		}

		if($type == Type::DOUBLE) {
			$FIELD_PRECISION = 15;
		}

		return $FIELD_PRECISION;
	}

	// <scalar_datatype> ::=
	//     SMALLINT | INT[EGER] | BIGINT
	//   | FLOAT | DOUBLE PRECISION
	//   | BOOLEAN
	//   | DATE | TIME | TIMESTAMP
	//   | {DECIMAL | NUMERIC} [(precision [, scale])]
	//   | {VARCHAR | {CHAR | CHARACTER} VARYING} (length)
	//     [CHARACTER SET charset]
	//   | {CHAR | CHARACTER} [(length)] [CHARACTER SET charset]
	//   | {NCHAR | NATIONAL {CHARACTER | CHAR}} VARYING (length)
	//   | {NCHAR | NATIONAL {CHARACTER | CHAR}} [(length)]
	static function isScalarType(int $type): bool {
		return ($type >= 7) && ($type <= 40);
	}

	// <blob_datatype> ::=
	//     BLOB [SUB_TYPE {subtype_num | subtype_name}]
	//     [SEGMENT SIZE seglen] [CHARACTER SET charset]
	//   | BLOB [(seglen [, subtype_num])]
	static function isBlobType($type): bool {
		return $type == Type::BLOB;
	}

	// $MD - metadata object
	// FIELD_TYPE
	// CHARACTER_LENGTH
	// FIELD_LENGTH
	// BYTES_PER_CHARACTER
	// FIELD_SUB_TYPE
	// FIELD_PRECISION
	// FIELD_SCALE
	// COLLATION_NAME
	// CHARACTER_SET_NAME
	// FIELD_SOURCE
	static function datatype($MD){
		$FT = $MD->FIELD_TYPE;

		# Domain
		if(!empty($MD->FIELD_SOURCE) && (substr($MD->FIELD_SOURCE, 0, 4) != 'RDB$')){
			return $MD->FIELD_SOURCE;
		}

		$datatype = Type::name($FT);

		if(in_array($FT, [Type::TEXT, Type::VARYING, Type::CSTRING])){
			if($MD->CHARACTER_LENGTH){
				$datatype = sprintf("%s(%d)", Type::name($FT), $MD->CHARACTER_LENGTH);
			} elseif($MD->FIELD_LENGTH && $MD->BYTES_PER_CHARACTER){
				$datatype = sprintf("%s(%d)", Type::name($FT), $MD->FIELD_LENGTH / $MD->BYTES_PER_CHARACTER);
			} else {
				trigger_error("FIELD_LENGTH");
				$datatype = sprintf("%s(%d)", Type::name($FT), $MD->FIELD_LENGTH);
			}
		}

		if(in_array($FT, [Type::SHORT, Type::LONG, Type::QUAD, Type::INT64])){
			if($MD->FIELD_SUB_TYPE){
				$datatype = sprintf(
					"%s(%d, %d)",
					Type::$IntSubtypeNames[$MD->FIELD_SUB_TYPE],
					$MD->FIELD_PRECISION,
					-$MD->FIELD_SCALE
				);
			}
		}

		if($FT == Type::BLOB){
			$datatype .= " SUB_TYPE";
			if(in_array($MD->FIELD_SUB_TYPE, [Type::SUBTYPE_BINARY, Type::SUBTYPE_TEXT])){
				$datatype .= " ".Type::$SubtypeNames[$MD->FIELD_SUB_TYPE];
			} else {
				$datatype .= sprintf(" %d", $MD->FIELD_SUB_TYPE);
			}
		}

		# TODO: default db utf8 skipt charset
		if(in_array($FT, [Type::TEXT, Type::VARYING, Type::CSTRING, Type::BLOB])){
			if($MD->CHARACTER_SET_NAME){
				$datatype .= " CHARACTER SET $MD->CHARACTER_SET_NAME";
			}
		}

		return $datatype;
	}

	// static function isArrayType($type): bool {
	// }
}
