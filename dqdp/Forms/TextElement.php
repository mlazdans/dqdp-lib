<?php declare(strict_types = 1);

namespace dqdp\Forms;

class TextElement extends AbstractElement {
	function __construct(
		protected readonly string $name,
		protected readonly ?int $size = null,
		protected readonly mixed $value = null,
		protected readonly ?string $class = null,
	) {
	}

	function parse(): ?string {
		$props = ["type"=>"text"];
		$this->addPropsIfSet($props, ["name", "size", "value", "class"]);

		foreach($props as $k=>$v){
			$p[] = $k.'="'.specialchars($v).'"';
		}

		return isset($p) ? '<input '.(join(" ", $p)).'>' : null;
	}
}
