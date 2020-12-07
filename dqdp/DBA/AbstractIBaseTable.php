<?php

declare(strict_types = 1);

namespace dqdp\DBA;

abstract class AbstractIBaseTable extends AbstractTable {
	protected ?string $Gen;

	function getGen(){
		return $this->Gen;
	}
}
