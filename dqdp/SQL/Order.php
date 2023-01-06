<?php declare(strict_types = 1);

namespace dqdp\SQL;

class Order extends Statement
{
	const ASC = 1;
	const DESC = 2;

	private $order;
	private $dir;
	private $collate;

	function __construct(string $order, int $dir = null){
		$this->order = $order;
		$this->dir = $dir;
	}

	function Collate(string $str): static {
		$this->collate = $str;
		return $this;
	}

	function parse(): string {
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

	static function factory(string|Order $o, int $dir = null){
		if($o instanceof Order){
			return $o;
		} else {
			return new Order($o, $dir);
		}
	}
}
