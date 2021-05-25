<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

use dqdp\SQL\Select;

class UDFArgument extends Field
{
	const MECHANISM_VALUE                = 0;
	const MECHANISM_REFERENCE            = 1;
	const MECHANISM_DESCRIPTOR           = 2;
	const MECHANISM_BLOB_DESCRIPTOR      = 3;
	const MECHANISM_ARRAY_DESCRIPTOR     = 4;
	const MECHANISM_NULL                 = 5;

	protected $UDF;

	function __construct(UDF $UDF, $name){
		$this->UDF = $UDF;
		parent::__construct($UDF->getDb(), $name);
	}

	static function getSQL(): Select {
		return (new Select('fa.*, cs.RDB$BYTES_PER_CHARACTER'))
		->From('RDB$FUNCTION_ARGUMENTS fa')
		->LeftJoin('RDB$CHARACTER_SETS cs', 'cs.RDB$CHARACTER_SET_ID = fa.RDB$CHARACTER_SET_ID')
		->OrderBy('fa.RDB$ARGUMENT_POSITION')
		;
	}

	function loadMetadata(){
		$sql = $this->getSQL()
		->Where(['fa.RDB$FUNCTION_NAME = ?', $this->UDF->name])
		->Where(['fa.RDB$ARGUMENT_POSITION = ?', $this->name])
		;

		return parent::loadMetadataBySQL($sql);
	}

	function ddl(): string {
		$ddl = '';

		$MT = $this->getMetadata();
		$FMT = $this->UDF->getMetadata();
		$ddl = parent::ddl();

		$paramType = "";
		if($MT->MECHANISM == UDFArgument::MECHANISM_VALUE){
			$paramType = " BY VALUE";
		} elseif($MT->MECHANISM == UDFArgument::MECHANISM_REFERENCE){
			// $paramType = " BY REFERENCE";
		} elseif($MT->MECHANISM == UDFArgument::MECHANISM_DESCRIPTOR){
			$paramType = " BY DESCRIPTOR";
		} elseif($MT->MECHANISM == UDFArgument::MECHANISM_ARRAY_DESCRIPTOR){
			$paramType = " BY SCALAR_ARRAY";
		} elseif($MT->MECHANISM == UDFArgument::MECHANISM_NULL){
			$paramType = " NULL";
		} else {
			trigger_error("Unknown MECHANISM: $MT->MECHANISM");
			dumpr($MT);
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
