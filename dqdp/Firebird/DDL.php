<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

interface DDL {
	function ddl($PARTS = null): string;
}
