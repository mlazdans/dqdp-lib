<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\FireBird\Relation;

// CREATE VIEW viewname [<full_column_list>]
//   AS <select_statement>
//   [WITH CHECK OPTION]

// <full_column_list> ::= (colname [, colname ...])

class View extends Relation {
	function ddlParts(): array {
		$MD = $this->getMetadata();

		$parts['viewname'] = "$this";
		$parts['full_column_list'] = join(", ", $this->getFields());
		$parts['select_statement'] = $MD->VIEW_SOURCE;

		return $parts;
	}

	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		$ddl = [$parts['viewname']];
		$ddl[] = "($parts[full_column_list]) AS";
		$ddl[] = $parts['select_statement'];

		return join("\n", $ddl);
	}
}
