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

class Domain extends Field
{
	static function getSQL(): Select {
		return (new Select('f.*, cs.*, c.*'))
		->From('RDB$FIELDS f')
		->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID')
		->LeftJoin('RDB$COLLATIONS c', '(c.RDB$COLLATION_ID = f.RDB$COLLATION_ID AND c.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID)')
		->Where('f.RDB$SYSTEM_FLAG = 0')
		->Where('f.RDB$FIELD_NAME NOT LIKE \'RDB$%\'')
		->OrderBy('f.RDB$FIELD_NAME');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['RDB$FIELD_NAME = ?', $this->name]);

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(): string {
		$MD = $this->getMetadata();
		$parts = $this->ddlParts();

		$ddl = ["CREATE DOMAIN $this AS"];
		if(isset($parts['domainname'])){
			$ddl[] = $parts['domainname'];
		} else {
			$ddl[] = $parts['datatype'];
		}

		if(isset($parts['charset'])){
			$ddl[] = "CHARACTER SET $parts[charset]";
		}

		if(isset($parts['default'])){
			$ddl[] = $parts['default'];
		}

		if(!empty($parts['null_flag'])){
			$ddl[] = "NOT NULL";
		}

		if($MD->VALIDATION_SOURCE){
			$ddl[] = $MD->VALIDATION_SOURCE;
		}

		if(isset($parts['collation_name'])){
			$ddl[] = "COLLATE $parts[collation_name]";
		}

		return join(" ", $ddl);
	}
}
