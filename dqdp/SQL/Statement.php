<?php

namespace dqdp\SQL;

abstract class Statement
{
	abstract function parse();

	function __toString(){
		return $this->parse();
	}
}
