<?php declare(strict_types = 1);

namespace dqdp\Engine;

use dqdp\PHPTemplate;

abstract class Template extends PHPTemplate {
	abstract function out(string $MODULE_DATA): void;
}
