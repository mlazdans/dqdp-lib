<?php declare(strict_types = 1);

namespace dqdp;

interface PropertyInitInterface {
	function initPoperty(string|int $k, mixed $v, bool $is_dirty): void;
	static function initFromDefaults(array|object|null $defaults = null): static;
	static function initValue(string|int $k, mixed $v): mixed;
	static function initFrom(array|object|null $data = null, array|object|null $defaults = null): static;
}
