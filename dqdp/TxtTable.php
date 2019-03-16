<?php

namespace dqdp;

class TxtTable {

	// lauku (kolonnu) definiicija (tabulas headeris)
	var $fields = array();

	// dati (tabulas rindas)
	var $rows = array();

	// lauku platumi
	var $field_widths = array();

	// rindu atdaliitaajs
	var $seperator;

	var $cellAlign = 'left';

	// constructor
	function __construct() {

		$this->ResetFields();
		$this->ResetRows();
	}

	// pievienojam tabulai lauku
	// $name - nosaukums (string)
	// $field_wrap - minimaalais platums (int)
	function AddField($name, $field_wrap = 0) {

		$this->fields[] = $name;
		$this->field_widths[] = $field_wrap;
	}

	// izdzeest lauku definiiciju
	// reizee ar to tiek izdzeestas arii dati,
	// kaada jeega no datiem, ja nav lauki :)
	function ResetFields() {

		$this->fields = array();
		$this->field_widths = array();
		$this->ResetRows();
	}

	// izdzeest visus datus
	function ResetRows() {

		$this->rows = array();
	}

	// izdzeest konkreetu rindu
	function DeleteRow($row_num) {
		if(isset($this->rows[$row_num])) {
			array_splice($this->rows, $row_num, 1);
		} else {
			trigger_error("Row $row_num not found", E_USER_WARNING);

			return false;
		}
	}

	// izdzeest konkreetu lauku
	// $field_num - lauks peec kaartas (int)
	// tiek izdzeesti attieciigie dati kolonnaa
	function DeleteField($field_num) {

		if(isset($this->fields[$field_num])) {

			array_splice($this->fields, $field_num, 1);

			reset($this->rows);
			foreach($this->rows as $k=>$v)
				if(is_array($this->rows[$k]))
					array_splice($this->rows[$k], $field_num, 1);

			return true;
		} else {
			trigger_error("Field $field_num not found", E_USER_WARNING);

			return false;
		}
	}

	// cik pavisam lauku?
	function GetFieldCount() {

		return isset($this->fields) ? count($this->fields) : 0;
	}

	// pievienot jaunu rindu (datus)
	// AddRow([$field1[, $field2[,...]]])
	// ja noraadiits mazaak datu kaa lauku skaits, paareejie ir tuksji
	// ja vairaak - tiek ignoreeti
	function AddRow() {

		$num_args = func_num_args();
		$num_fields = $this->GetFieldCount();
		//if($num_args != $num_fields)
			//trigger_error("Wrong parameter count for AddRow()", E_USER_WARNING);

		$row = func_get_args();

		if($num_args > $num_fields)
			array_splice($row, 0, $num_fields);

		if($num_args < $num_fields) {
			$add = array_fill($num_args, $num_fields - $num_args, '');
			$row = array_merge($row, $add);
		}

		$this->rows[] = $row;
	}

	// izkalkuleejam rindu atdaliitaaju
	// +------+-------------+ formaa
	function _calcseperator() {

		reset($this->fields);
		$seperator = "+";
		foreach($this->fields as $k=>$v)
			$seperator .= str_repeat('-', $this->field_widths[$k]).'+';
		$seperator .= "\n";
		$this->seperator = $seperator;
	}

	// apreekinam, max cik katra kolonna aiznjem vietu
	function _calcfieldlengths($row) {

		if(!is_array($row))
			return;

		foreach($row as $k=>$v) {
			$field_len = mb_strlen($v);
			if($field_len > $this->field_widths[$k])
				$this->field_widths[$k] = $field_len;
		}
	}

	function CalcFieldLengths() {

		reset($this->fields);
		$this->_calcfieldlengths($this->fields);

		reset($this->rows);
		foreach($this->rows as $row)
			$this->_calcfieldlengths($row);
	}

	// uzziimeejam laukus
	function DrawFields() {

		print $this->seperator;
		print "|";
		foreach($this->fields as $k=>$v) {
			$field_len = mb_strlen($v);
			print $v.str_repeat(' ', $this->field_widths[$k] - $field_len).'|';
		}
		print "\n$this->seperator";
	}

	// uzziimeejam vienu rindu
	// $row - massiivs ar rindas datiem (array)
	function DrawRow($row) {

		//print_r($row);
		if(!is_array($row)) {
			print $this->seperator;
			return;
		}

		print "|";
		foreach($row as $k=>$v) {
			$field_len = mb_strlen($v);
			if($this->cellAlign == 'left')
				print $v.str_repeat(' ', $this->field_widths[$k] - $field_len).'|';
			else
				print str_repeat(' ', $this->field_widths[$k] - $field_len).$v.'|';
		}
		print "\n";
	}

	// uzziimeejam visas rindas
	function DrawRows() {
		reset($this->rows);
		foreach($this->rows as $row)
			$this->DrawRow($row);

		if(count($this->rows))
			print $this->seperator;
	}

	// ielikam atdaliitaaju
	function DrawSeperator() {
		$this->rows[] = 'seperator';
	}

	// uzziimeejam tabulu
	function DrawTable() {

		$this->CalcFieldLengths();
		$this->_calcseperator();
		$this->DrawFields();
		$this->DrawRows();
	}
/*
	function SetCellAlign($align){
		$this->cellAlign = $align;
	}

	// masīvs, katrai cellei
	function SetRowAlign($cellAlign){
		$this->rowAlign = $cellAlign;
	}
	*/
}
