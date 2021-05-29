<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// CREATE DOMAIN name [AS] <datatype>
//   [DEFAULT {<literal> | NULL | <context_var>}]
//   [NOT NULL] [CHECK (<dom_condition>)]
//   [COLLATE collation_name]

// <datatype> ::=
//   <scalar_datatype> | <blob_datatype> | <array_datatype>

class Domain extends Field implements DDL
{
	// static function getSQL(): Select {
	// 	return (new Select('f.*, cs.*, c.*'))
	// 	->From('RDB$FIELDS f')
	// 	->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID')
	// 	->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = f.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
	// 	->Where('f.RDB$SYSTEM_FLAG = 0')
	// 	->Where('f.RDB$FIELD_NAME NOT LIKE \'RDB$%\'')
	// 	// ->OrderBy('f.RDB$FIELD_NAME')
	// 	;
	// }

	static function getSQL(): Select {
		return parent::getSQL()->Select('fields.*, collations.*, character_sets.*')->Where('fields.RDB$FIELD_NAME NOT LIKE \'RDB$%\'');
	}

	function getMetadataSQL(): Select {
		return $this->getSQL()->Where(['fields.RDB$FIELD_NAME = ?', $this->name]);
	}

	function ddlParts(): array {
		$PARTS = parent::ddlParts();
		$MD = $this->getMetadata();

		if($MD->VALIDATION_SOURCE){
			$PARTS['dom_condition'] = $MD->VALIDATION_SOURCE;
		}

		return $PARTS;
	}

	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		// $ddl = ["CREATE DOMAIN $this AS"];
		$ddl = ["$parts[colname] AS"];

		$ddl[] = $parts['datatype'];
		// if(isset($parts['domainname'])){
		// 	$ddl[] = $parts['domainname'];
		// } else {
		// 	$ddl[] = $parts['datatype'];
		// }

		if(isset($parts['charset'])){
			$ddl[] = "CHARACTER SET $parts[charset]";
		}

		if(isset($parts['default'])){
			$ddl[] = $parts['default'];
		}

		if(!empty($parts['null_flag'])){
			$ddl[] = "NOT NULL";
		}

		if(isset($parts['dom_condition'])){
			$ddl[] = $parts['dom_condition'];
		}

		if(isset($parts['collation_name'])){
			$ddl[] = "COLLATE $parts[collation_name]";
		}

		return join(" ", $ddl);
	}
}
