<?php declare(strict_types = 1);

namespace dqdp\Forms;

class Labels {
	static function getLabels(): array {
		return get_class_public_vars(static::class);
	}
}
