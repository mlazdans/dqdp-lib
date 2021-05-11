<?php

namespace dqdp\FireBird;

class Field
{
	const TYPE_SHORT                          = 7;
	const TYPE_LONG                           = 8;
	const TYPE_QUAD                           = 9;
	const TYPE_FLOAT                          = 10;
	const TYPE_DATE                           = 12;
	const TYPE_TIME                           = 13;
	const TYPE_TEXT                           = 14;
	const TYPE_CHAR                           = 14; // Alias TYPE_TEXT
	const TYPE_INT64                          = 16;
	const TYPE_DOUBLE                         = 27;
	const TYPE_TIMESTAMP                      = 35;
	const TYPE_VARYING                        = 37;
	const TYPE_CSTRING                        = 40;
	const TYPE_BLOB_ID                        = 45;
	const TYPE_BLOB                           = 261;

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

	static $QuotableTypes = array(
		Field::TYPE_TEXT,
		Field::TYPE_VARYING,
		Field::TYPE_CSTRING,
		Field::TYPE_BLOB,
		Field::TYPE_DATE,
		Field::TYPE_TIME,
		Field::TYPE_TIMESTAMP, # NOTE: test database dialects
		);

	static $TypeNames = array(
		Field::TYPE_SHORT            => 'SMALLINT',
		Field::TYPE_LONG             => 'INTEGER',
		Field::TYPE_QUAD             => 'QUAD',
		Field::TYPE_FLOAT            => 'FLOAT',
		Field::TYPE_DATE             => 'DATE',
		Field::TYPE_TIME             => 'TIME',
		Field::TYPE_TEXT             => 'CHAR',
		Field::TYPE_INT64            => 'BIGINT',
		Field::TYPE_DOUBLE           => 'DOUBLE PRECISION',
		Field::TYPE_TIMESTAMP        => 'TIMESTAMP',
		Field::TYPE_VARYING          => 'VARCHAR',
		Field::TYPE_CSTRING          => 'CSTRING',
		Field::TYPE_BLOB_ID          => 'BLOB_ID',
		Field::TYPE_BLOB             => 'BLOB',
	);

	static $SubtypeNames = array(
		Field::SUBTYPE_BINARY                     => 'BINARY',
		Field::SUBTYPE_TEXT                       => 'TEXT',
		Field::SUBTYPE_BLR                        => 'BLR',
		Field::SUBTYPE_ACL                        => 'ACL',
		Field::SUBTYPE_RANGES                     => 'RANGES',
		Field::SUBTYPE_SUMMARY                    => 'SUMMARY',
		Field::SUBTYPE_FORMAT                     => 'FORMAT',
		Field::SUBTYPE_TRANSACTION_DESCRIPTION    => 'TRANSACTION_DESCRIPTION',
		Field::SUBTYPE_EXTERNAL_FILE_DESCRIPTION  => 'EXTERNAL_FILE_DESCRIPTION',
		Field::SUBTYPE_DEBUG_INFORMATION          => 'DEBUG_INFORMATION',
		);

	static $IntSubtypeNames = array(
		Field::SUBTYPE_INT_NUMERIC   => 'NUMERIC',
		Field::SUBTYPE_INT_DECIMAL   => 'DECIMAL',
		);

	static function nameByType($type){
		return (isset(Field::$TypeNames[$type]) ? Field::$TypeNames[$type] : false);
	}

	static function nameBySubtype($type){
		return (isset(Field::$SubtypeNames[$type]) ? Field::$SubtypeNames[$type] : false);
	}

	static function nameByIntSubtype($type){
		return (isset(Field::$IntSubtypeNames[$type]) ? Field::$IntSubtypeNames[$type] : false);
	}

	# Numeric types when scale know but precision not
	static function precisionByType($type){
		$FIELD_PRECISION = false;
		if($type == Field::TYPE_SHORT) {
			$FIELD_PRECISION = 4;
		}
		if($type == Field::TYPE_LONG) {
			$FIELD_PRECISION = 9;
		}
		if($type == Field::TYPE_DOUBLE) {
			$FIELD_PRECISION = 15;
		}

		return $FIELD_PRECISION;
	}

	static function isQuotable($type) {
		return in_array($type, Field::$QuotableTypes);
	}

	# $field object should contain all information from RDB$FIELDS
	# TODO: computed fields
	static function ddl($field){
		$ddl = '';
		$MT = $field;
		$FT = $MT->FIELD_TYPE;

		# Default
		$ddl = sprintf("%s", Field::nameByType($FT));

		//[COMPUTED_SOURCE] => (CAST(CURRENCY_SELL_PRICE_PVN / (1 + PVN_RATE) AS MONEY4))

		# Computed
		if(!empty($field->COMPUTED_SOURCE)){
			$ddl = "COMPUTED BY $field->COMPUTED_SOURCE";
		# Domain
		} elseif(!empty($field->FIELD_SOURCE) && substr($field->FIELD_SOURCE, 0, 4) != 'RDB$'){
			$ddl = $field->FIELD_SOURCE;
		# VARCHAR/TEXT
		} elseif(in_array($FT, array(Field::TYPE_TEXT, Field::TYPE_VARYING, Field::TYPE_CSTRING))){
			$ddl = sprintf("%s(%d)", Field::nameByType($FT), $MT->CHARACTER_LENGTH);
		} elseif(in_array($FT, array(Field::TYPE_SHORT, Field::TYPE_LONG, Field::TYPE_QUAD))){
			if($MT->FIELD_PRECISION){
				if($MT->FIELD_SUB_TYPE){
					$ddl = sprintf(
						"%s(%d,%d)",
						Field::nameByIntSubtype($MT->FIELD_SUB_TYPE),
						$MT->FIELD_PRECISION,
						-$MT->FIELD_SCALE
						);
				} else {
				}
			} else {
			}
		} else {
			if(($scale = abs($MT->FIELD_SCALE)) && ($pr = Field::precisionByType($FT))){
				$ddl = sprintf("NUMERIC(%d,%d)", $pr, $scale);
			} else {
			}
		}

		# It seems it's better just leave sub_types as number to avoid
		# changes in (future) Firebird versions
		# (int | subtype_name). Perhaps configurable?
		if($FT == Field::TYPE_BLOB){
			$ddl .= sprintf(" SUB_TYPE %d", $MT->FIELD_SUB_TYPE);
			//$ddl .= Field::nameBySubtype($MT->FIELD_SUB_TYPE);
		}

		if($MT->DEFAULT_SOURCE){
			$ddl .= " ".$MT->DEFAULT_SOURCE;
		}

		if($MT->NULL_FLAG){
			$ddl .= " NOT NULL";
		}

		if(!empty($field->COLLATION_NAME)){
			$ddl .= " COLLATE $field->COLLATION_NAME";
		}

		return $ddl;
	}
}
