<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\FireBird\DDL;
use dqdp\SQL\Select;

class RelationTrigger extends Trigger implements DDL
{
	protected Relation $relation;

	# TODO: abstract out __constructor and getRelation()
	function __construct(Relation $relation, $name){
		$this->relation = $relation;
		parent::__construct($relation->getDb(), $name);
	}

	function getRelation(){
		return $this->relation;
	}

	static function getSQL(): Select {
		return parent::getSQL()
		->Join('RDB$RELATIONS AS relations', 'relations.RDB$RELATION_NAME = triggers.RDB$RELATION_NAME')
		->Where('triggers.RDB$SYSTEM_FLAG = 0')
		;
	}

	function getMetadataSQL(): Select {
		return parent::getMetadataSQL()
		->Where(['triggers.RDB$RELATION_NAME = ?', $this->getRelation()->name])
		;
	}

	function ddlParts(): array{
		$parts = parent::ddlParts();
		$MD = $this->getMetadata();

		$parts['relationname'] = sprintf("%s", $this->getRelation());
		$parts['before_or_after'] = $MD->TRIGGER_TYPE & 1 ? "BEFORE" : "AFTER";

		$mutation_list = [];
		for($slot = 1; $slot <= 3; $slot++){
			$suff = $this->TRIGGER_ACTION_SUFFIX($MD->TRIGGER_TYPE, $slot);
			if($suff == 1)
				$mutation_list[] = "INSERT";
			elseif($suff == 2)
				$mutation_list[] = "UPDATE";
			elseif($suff == 3)
				$mutation_list[] = "DELETE";
		}

		$parts['mutation_list'] = $mutation_list;

		return $parts;
	}

	function ddl($parts = null): string {
		if(is_null($parts)){
			$parts = $this->ddlParts();
		}

		$ddl = ["$this FOR $parts[relationname] $parts[active]"];
		$ddl[] = "$parts[before_or_after] ".join(" OR ", $parts['mutation_list'])." POSITION $parts[position]";
		$ddl[] = $parts['module_body'];

		return join(" ", $ddl);
	}
}
