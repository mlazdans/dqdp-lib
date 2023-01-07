<?php declare(strict_types = 1);

namespace dqdp\Forms;

use dqdp\DataObject;

class Form {
	function __construct(protected string $LabelsClass, protected DataObject $Data){
	}

	function Text(
		string $name,
		?int $size = null,
		?string $class = null,
	): void { ?>
		<tr>
			<th><?=$this->LabelsClass::$$name ?>:</th>
			<td><?=(new TextElement(
				name: $name,
				size: $size,
				value: $this->Data->$name,
				class: $class,
				))->parse() ?></td>
		</tr>
		<?
	}

	function TextArea(
		string $name,
		?int $cols = null,
		?int $rows = null,
		?string $class = null,
	){?>
		<tr>
			<th><?=$this->LabelsClass::$$name ?>:</th>
			<td><?=(new TextAreaElement(
				name: $name,
				cols: $cols,
				rows: $rows,
				value: $this->Data->$name,
				class: $class,
				))->parse() ?></td>
		</tr>
		<?
	}

}
