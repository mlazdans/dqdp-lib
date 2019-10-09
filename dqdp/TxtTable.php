<?php

namespace dqdp;

class TxtTable {
	var $fields = [];
	var $rows = [];
	var $field_widths = [];
	var $seperator;
	var $cellAlign = 'left';

	function __construct() {
	}

	// pievienojam tabulai lauku
	// $name - nosaukums (string)
	// $field_wrap - minimaalais platums (int)
	function AddField($name, $field_wrap = 0) {
		$this->fields[] = $name;
		$this->field_widths[] = $field_wrap;
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

	// pievienot jaunu rindu (datus)
	// AddRow([$field1[, $field2[,...]]])
	// ja noraadiits mazaak datu kaa lauku skaits, paareejie ir tuksji
	// ja vairaak - tiek ignoreeti
	function AddRow() {
		$num_fields = count($this->fields);
		$num_args = func_num_args();
		$row = func_get_args();

		if($num_args > $num_fields){
			array_splice($row, 0, $num_fields);
		}

		if($num_args < $num_fields) {
			$row[] = array_fill($num_args, $num_fields - $num_args, '');
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
