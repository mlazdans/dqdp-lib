<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class ProcedureParameter extends FirebirdObject
{
	const TYPE_INPUT                = 0;
	const TYPE_RETURN               = 1;

	protected $proc;

	function __construct(Procedure $proc, $name){
		$this->type = FirebirdObject::TYPE_PROCEDURE_PARAMETER;
		$this->proc = $proc;
		parent::__construct($proc->getDb(), $name);
	}

	function loadMetadata(){
		$sql_add = array();
		$sql_add[] = 'pp.RDB$SYSTEM_FLAG = 0';
		$sql_add[] = sprintf('pp.RDB$PROCEDURE_NAME = \'%s\'', $this->proc->name);
		$sql_add[] = sprintf('pp.RDB$PARAMETER_NAME = \'%s\'', $this->name);

		$sql = '
		SELECT
			f.*,
			pp.*
		FROM
			RDB$PROCEDURE_PARAMETERS pp
		LEFT JOIN RDB$FIELDS f ON f.RDB$FIELD_NAME = pp.RDB$FIELD_SOURCE
		'.($sql_add ? " WHERE ".join(" AND ", $sql_add) : "");

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(){
		$ddl = '';

		/*
		$MT = $this->getMetadata();
		$FT = $MT->FIELD_TYPE;
		if(in_array($FT, array(IbaseField::TYPE_TEXT, IbaseField::TYPE_VARYING, IbaseField::TYPE_CSTRING))){
			# TODO: CHARACTER SET
			$ddl = sprintf("%s(%d)", IbaseField::nameByType($FT), $MT->FIELD_LENGTH);
		} elseif(in_array($FT, array(IbaseField::TYPE_SHORT, IbaseField::TYPE_LONG, IbaseField::TYPE_QUAD))){
			if($MT->FIELD_PRECISION){
				if($MT->FIELD_SUB_TYPE){
					$ddl = sprintf(
						"%s(%d, %d)",
						IbaseField::nameByIntSubtype($MT->FIELD_SUB_TYPE),
						$MT->FIELD_PRECISION,
						-$MT->FIELD_SCALE
						);
				} else {
				}
			} else {
				$ddl = sprintf("%s", IbaseField::nameByType($FT));
			}
		} else {
			$ddl = sprintf("%s", IbaseField::nameByType($FT));
		}
		*/

		$ddl = "$this ".Field::ddl($this->getMetadata());

		return $ddl;
	}
}

