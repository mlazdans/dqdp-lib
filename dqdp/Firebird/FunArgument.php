<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class FunArgument extends FirebirdObject
{
	const MECHANISM_VALUE                = 0;
	const MECHANISM_REFERENCE            = 1;
	const MECHANISM_DESCRIPTOR           = 2;
	const MECHANISM_BLOB_DESCRIPTOR      = 3;
	const MECHANISM_ARRAY_DESCRIPTOR     = 4;
	const MECHANISM_NULL                 = 5;

	protected $func;

	# Argument $name is realy an integer - ARGUMENT_POSITION
	function __construct(Fun $func, $name){
		$this->type = FirebirdObject::TYPE_FUNCTION_ARGUMENT;
		$this->func = $func;
		parent::__construct($func->getDb(), $name);
	}

	function loadMetadata(){
		$sql = (new Select())
		->From('RDB$FUNCTION_ARGUMENTS')
		->Where(['RDB$FUNCTION_NAME = ?', $this->func->name])
		->Where(['RDB$ARGUMENT_POSITION = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(){
		$ddl = '';

		$MT = $this->getMetadata();
		$FMT = $this->func->getMetadata();
		/*
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
		$ddl = Field::ddl($MT);

		$paramType = "";
		if($MT->MECHANISM == FunArgument::MECHANISM_VALUE){
			$paramType = " BY VALUE";
		}
		if($MT->MECHANISM == FunArgument::MECHANISM_DESCRIPTOR){
			$paramType = " BY DESCRIPTOR";
		}
		if($MT->MECHANISM == FunArgument::MECHANISM_ARRAY_DESCRIPTOR){
			$paramType = " BY SCALAR_ARRAY";
		}
		if($MT->MECHANISM == FunArgument::MECHANISM_NULL){
			$paramType = " NULL";
		}

		# Returning argument
		if($MT->ARGUMENT_POSITION == $FMT->RETURN_ARGUMENT){
			if($FMT->RETURN_ARGUMENT){
				$ddl = "RETURNS PARAMETER {$FMT->RETURN_ARGUMENT}";
			} else {
				$ddl = "RETURNS {$ddl}{$paramType}";

				if($MT->MECHANISM < 0){
					$ddl .= " FREE_IT";
				}
			}
		} else {
			$ddl = "{$ddl}{$paramType}";
		}

		return $ddl;
	}
}
