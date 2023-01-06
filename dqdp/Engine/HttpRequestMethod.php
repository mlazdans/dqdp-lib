<?php declare(strict_types = 1);

namespace dqdp\Engine;

interface HttpRequestMethod {
	function url(): string;
}
