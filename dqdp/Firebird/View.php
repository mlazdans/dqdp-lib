<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\FireBird\Relation;

// CREATE VIEW viewname [<full_column_list>]
//   AS <select_statement>
//   [WITH CHECK OPTION]

// <full_column_list> ::= (colname [, colname ...])

class View extends Relation implements DDL
{
	function ddlParts(): array {
		$MD = $this->getMetadata();

		$parts = parent::ddlParts();
		$parts['select_statement'] = $MD->VIEW_SOURCE;

		return $parts;
	}

	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		$ddl = [$parts['relation_name']];
		$ddl[] = "(";
		$ddl[] = "\t".join(",\n\t", $parts['col_def']);
		$ddl[] = ") AS";
		$ddl[] = $parts['select_statement'];

		return join("\n", $ddl);
	}
}
