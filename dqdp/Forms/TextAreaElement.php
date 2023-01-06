<?php declare(strict_types = 1);

namespace dqdp\Forms;

class TextAreaElement extends AbstractElement {
	function __construct(
		protected readonly string $name,
		protected readonly ?int $cols = null,
		protected readonly ?int $rows = null,
		protected readonly mixed $value = null
	) {
	}

	function parse(): ?string {
		$props = [];
		$this->addPropsIfSet($props, ["name", "cols", "rows"]);

		foreach($props as $k=>$v){
			$p[] = $k.'="'.specialchars($v).'"';
		}

		return isset($p) ? '<textarea '.(join(" ", $p)).'>'.specialchars($this->value).'</textarea>' : null;
	}
}
