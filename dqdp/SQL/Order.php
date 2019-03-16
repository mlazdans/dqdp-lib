<?php

namespace dqdp\SQL;

class Order extends Statement
{
	const ASC = 1;
	const DESC = 2;

	private $order;
	private $dir;
	private $collate;

	function __construct(string $order, $dir = null){
		$this->order = $order;
		$this->dir = $dir;
	}

	function Collate($str){
		$this->collate = $str;
		return $this;
	}

	function parse(){
		$line = $this->order;

		if($this->collate)
			$line .= " COLLATE $this->collate";

		switch($this->dir){
			case Order::ASC:
				$line .= ' ASC';
				break;
			case Order::DESC:
				$line .= ' DESC';
				break;
		}

		return $line;
	}

	static function factory($o){
		if(gettype($o) == 'string'){
			return new Order($o);
		} else {
			return $o;
		}
	}
}
