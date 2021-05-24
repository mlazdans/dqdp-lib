<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class Field extends FirebirdType
{
	static function getSQL(): Select {
		return (new Select())
		->From('RDB$FIELDS')
		->Where('RDB$SYSTEM_FLAG = 0');
		;
		// return (new Select('rf.*, f.*, c.RDB$COLLATION_NAME, cs.RDB$BYTES_PER_CHARACTER'))
		// ->From('RDB$FIELDS f')
		// ->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID')
		// ->LeftJoin('RDB$RELATION_FIELDS rf', 'rf.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME')
		// ->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = rf.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
		// ->Where('f.RDB$SYSTEM_FLAG = 0');
	}

	// function __construct(Database $db, $name){
	// 	$this->type = FirebirdObject::TYPE_FIELD;
	// 	parent::__construct($db, $name);
	// }

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

	// static $QuotableTypes = [
	// 	Field::TYPE_TEXT,
	// 	Field::TYPE_VARYING,
	// 	Field::TYPE_CSTRING,
	// 	Field::TYPE_BLOB,
	// 	Field::TYPE_DATE,
	// 	Field::TYPE_TIME,
	// 	Field::TYPE_TIMESTAMP, # NOTE: test database dialects
	// ];

	static $TypeNames = [
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
	];

	static $SubtypeNames = [
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
	];

	static $IntSubtypeNames = [
		Field::SUBTYPE_INT_NUMERIC   => 'NUMERIC',
		Field::SUBTYPE_INT_DECIMAL   => 'DECIMAL',
	];

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
	# TODO: might change in FB 4.0
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

	// static function isQuotable($type) {
	// 	return in_array($type, Field::$QuotableTypes);
	// }

	function ddl(): string {
		$MT = $this->metadata;
		$FT = $MT->FIELD_TYPE;

		# Default
		$ddl = sprintf("%s", Field::nameByType($FT));

		//[COMPUTED_SOURCE] => (CAST(CURRENCY_SELL_PRICE_PVN / (1 + PVN_RATE) AS MONEY4))

		# Computed
		if(!empty($MT->COMPUTED_SOURCE)){
			$ddl = "COMPUTED BY $MT->COMPUTED_SOURCE";
		# Domain
		} elseif(!empty($MT->FIELD_SOURCE) && substr($MT->FIELD_SOURCE, 0, 4) != 'RDB$'){
			$ddl = $MT->FIELD_SOURCE;
		# VARCHAR/TEXT
		} elseif(in_array($FT, [Field::TYPE_TEXT, Field::TYPE_VARYING, Field::TYPE_CSTRING])){
			if($MT->CHARACTER_LENGTH){
				$ddl = sprintf("%s(%d)", Field::nameByType($FT), $MT->CHARACTER_LENGTH);
			} elseif($MT->FIELD_LENGTH && $MT->BYTES_PER_CHARACTER){
				$ddl = sprintf("%s(%d)", Field::nameByType($FT), $MT->FIELD_LENGTH / $MT->BYTES_PER_CHARACTER);
			} else {
				trigger_error("FIELD_LENGTH");
				$ddl = sprintf("%s(%d)", Field::nameByType($FT), $MT->FIELD_LENGTH);
			}
			//
		} elseif(in_array($FT, [Field::TYPE_SHORT, Field::TYPE_LONG, Field::TYPE_QUAD])){
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
			$ddl .= " SUB_TYPE";
			if(in_array($MT->FIELD_SUB_TYPE, [Field::SUBTYPE_BINARY, Field::SUBTYPE_TEXT])){
				$ddl .= " ".Field::$SubtypeNames[$MT->FIELD_SUB_TYPE];
			} else {
				$ddl .= sprintf(" %d", $MT->FIELD_SUB_TYPE);
			}
			// $ddl .= sprintf(" SUB_TYPE %d", $MT->FIELD_SUB_TYPE);
			// $ddl .= Field::nameBySubtype($MT->FIELD_SUB_TYPE);
		}

		if($MT->DEFAULT_SOURCE){
			$ddl .= " $MT->DEFAULT_SOURCE";
		}

		if($MT->NULL_FLAG){
			$ddl .= " NOT NULL";
		}

		if(!empty($MT->COLLATION_NAME)){
			# TODO: test CHARACTER_SET_NAME | COLLATION_NAME
			if($this->getDb()->metadata->CHARACTER_SET_NAME != $MT->COLLATION_NAME){
				$ddl .= " COLLATE ".trim($MT->COLLATION_NAME);
			}
		}

		return $ddl;
	}

	// static function getSQL(){
	// 	return (new Select('rf.*, f.*, c.RDB$COLLATION_NAME, cs.RDB$BYTES_PER_CHARACTER'))
	// 	->From('RDB$FIELDS f')
	// 	->Join('RDB$RELATION_FIELDS rf', 'rf.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME')
	// 	->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = rf.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
	// 	->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID')
	// 	->Where('f.RDB$SYSTEM_FLAG = 0');
	// }

	// function loadMetadata(){
	// 	$sql = $this->getSQL()
	// 	->Where(['RDB$RELATION_NAME = ?', $this->table])
	// 	->Where(['rf.RDB$FIELD_NAME = ?', $this->name])
	// 	;

	// 	return parent::loadMetadataBySQL($sql);
	// }
}
