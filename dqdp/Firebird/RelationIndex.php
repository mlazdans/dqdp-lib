<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

// CREATE [UNIQUE] [ASC[ENDING] | DESC[ENDING]]
//   INDEX indexname ON tablename
//   {(col [, col …]) | COMPUTED BY (<expression>)}

class RelationIndex extends FirebirdType
{
	const TYPE_INDEX    = 0;
	const TYPE_FK       = 1;
	const TYPE_PK       = 2;
	const TYPE_UNIQUE   = 3;

	const INDEX_TYPE_ASC  = 0;
	const INDEX_TYPE_DESC = 1;

	protected $relation;

	function __construct(Relation $relation, $name){
		$this->relation = $relation;
		parent::__construct($relation->getDb(), $name);
	}

	static function getSQL(): Select {
		// return (new Select('i.*, rc.*, refc.*'))
		return (new Select('i.*'))
		->From('RDB$INDICES i')
		->LeftJoin('RDB$RELATION_CONSTRAINTS rc', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		->Where('rc.RDB$CONSTRAINT_TYPE IS NULL')
		// ->LeftJoin('RDB$REF_CONSTRAINTS refc', 'refc.RDB$CONSTRAINT_NAME = rc.RDB$CONSTRAINT_NAME')
		->Where('i.RDB$SYSTEM_FLAG = 0');
	}

	function loadMetadata(){
		$sql = $this->getSQL()->Where(['i.RDB$INDEX_NAME = ?', $this->name]);
		// $sql = (new Select('i.*, rc.*'))
		// ->From('RDB$INDICES i')
		// ->LeftJoin('RDB$RELATION_CONSTRAINTS rc', 'rc.RDB$INDEX_NAME = i.RDB$INDEX_NAME')
		// ->Where('RDB$SYSTEM_FLAG = 0')
		// ->Where(['i.RDB$INDEX_NAME = ?', $this->name])
		// ;

		// if(isset($params['CONSTRAINT_TYPE'])){
		// 	if($params['CONSTRAINT_TYPE'] == Index::TYPE_FK){
		// 		$sql->Where('rc.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\'');
		// 	} elseif($params['CONSTRAINT_TYPE'] == Index::TYPE_PK){
		// 		$sql->Where('rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'');
		// 	} elseif($params['CONSTRAINT_TYPE'] == Index::TYPE_UNIQUE){
		// 		$sql->Where('rc.RDB$CONSTRAINT_TYPE = \'UNIQUE\'');
		// 	} else {
		// 		$sql->Where('rc.RDB$CONSTRAINT_TYPE IS NULL');
		// 	}
		// }

		// if(isset($params['RELATION_NAME'])){
		// 	$sql->Where(['i.RDB$RELATION_NAME = ?', $params['RELATION_NAME']]);
		// }

		// if(!empty($params['active'])){
		// 	$sql->Where('((i.RDB$INDEX_INACTIVE = 0) OR (i.RDB$INDEX_INACTIVE IS NULL))');
		// }

		return parent::loadMetadataBySQL($sql);
	}

	function getSegments(): array {
		$sql = (new Select())->From('RDB$INDEX_SEGMENTS')
		->Where(['RDB$INDEX_NAME = ?', $this->name])
		->OrderBy('RDB$FIELD_POSITION')
		;

		foreach($this->getList($sql) as $r){
			$list[] = $r->FIELD_NAME;
		}

		return $list??[];
	}

	// ALTER TABLE tablename ADD [CONSTRAINT constraint] {PRIMARY KEY | UNIQUE} ( col [, col …])
	// ALTER TABLE tablename ADD [CONSTRAINT constraint] FOREIGN KEY ( col [, col …]) REFERENCES other_table [( other_col [, other_col …])] [ON DELETE {NO ACTION|CASCADE|SET DEFAULT|SET NULL}] [ON UPDATE {NO ACTION|CASCADE|SET DEFAULT|SET NULL}]

	function getRelation(){
		return $this->relation;
	}

	function ddl(): string {
		$MD = $this->getMetadata();

		$ddl = [];
		# TODO: move to contraints class
		# TODO: INACTIVE
		// if($MT->CONSTRAINT_TYPE){
		// 	//$ddl = "ALTER TABLE $MT->RELATION_NAME ADD ";
		// 	if($MT->CONSTRAINT_NAME){
		// 		$ddl[] = "CONSTRAINT $MT->CONSTRAINT_NAME";
		// 	}
		// 	$ddl[] = "$MT->CONSTRAINT_TYPE (".join(",", $segments).")";

		// 	if($MT->CONSTRAINT_TYPE === "FOREIGN KEY"){
		// 		$fk = new Index($this->getDb(), $MT->FOREIGN_KEY);
		// 		$fkMT = $fk->getMetadata();
		// 		$ddl[] = "REFERENCES $fkMT->RELATION_NAME (".join(",", $fk->getSegments()).")";

		// 		if($MT->UPDATE_RULE !== 'RESTRICT'){
		// 			$ddl[] = "ON UPDATE $MT->UPDATE_RULE";
		// 		}
		// 		if($MT->DELETE_RULE !== 'RESTRICT'){
		// 			$ddl[] = "ON DELETE $MT->DELETE_RULE";
		// 		}
		// 	}
		// } else {

		$ddl[] = "CREATE";
		if($MD->UNIQUE_FLAG){
			$ddl[] = "UNIQUE";
		}

		if($MD->INDEX_TYPE == RelationIndex::INDEX_TYPE_DESC){
			$ddl[] = "DESCENDING";
		}

		$ddl[] = "INDEX $MD->INDEX_NAME ON $MD->RELATION_NAME";

		if($MD->SEGMENT_COUNT){
			$segments = $this->getSegments();
			$ddl[] = "(".join(",", $segments).")";
		} elseif($MD->EXPRESSION_SOURCE){
			$ddl[] = $MD->EXPRESSION_SOURCE;
		}

		return join(" ", $ddl);
	}
}
