<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

class ObjectList
{
	protected $db;
	protected $list;

	function __construct(Database $db){
		$this->db = $db;
	}

	function getDb(){
		return $this->db;
	}
}
