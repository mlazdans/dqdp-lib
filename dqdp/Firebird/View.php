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
		trigger_error("Not implemented yet");
		return [];
	}

	function ddl($PARTS = null): string {
		// if(is_null($PARTS)){
		// 	$PARTS = $this->ddlParts();
		// }

		$MD = $this->getMetadata();

		$ddl = [];
		$fields = $this->getFields();
		foreach($fields as $o){
			$fddl[] = "$o";
		}
		$ddl[] = "CREATE VIEW $this (".join(", ", $fddl).") AS";
		$ddl[] = $MD->VIEW_SOURCE;

		return join("\n", $ddl);
	}
}
