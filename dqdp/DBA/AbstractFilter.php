<?php declare(strict_types = 1);

/**
 *
 * Child konstruktorā padot visus filtrus kā named parametrus ar default vērtībām.
 * Tas nozīmē, ka NULL filtri ir jāhandlo speciāli
 *
 * */

namespace dqdp\DBA;

use dqdp\DBA\interfaces\EntityFilterInterface;
use dqdp\DBA\Types\None;
use dqdp\InvalidTypeException;
use dqdp\SQL\Select;
use dqdp\StricStdObject;

abstract class AbstractFilter extends StricStdObject implements EntityFilterInterface {
	protected ?string $ORDER_BY    = null;
	protected ?int $ROWS           = null;
	protected ?int $OFFSET         = null;
	protected ?int $PAGE           = null;
	protected ?int $ITEMS_PER_PAGE = null;
	protected ?array $FIELDS       = null;

	abstract protected function apply_filter(Select $sql): Select;

	final function apply(Select $sql): Select {
		$this->apply_filter($sql);
		$this->apply_base_filters($sql);
		$this->apply_fields($sql);

		return $sql;
	}

	function merge(?AbstractFilter $F): static {
		return static::initFrom($this, $F);
	}

	function orderBy(string $order): static
	{
		$this->ORDER_BY = $order;
		return $this;
	}

	function getOrderBy(): ?string
	{
		return $this->ORDER_BY;
	}

	function rows(int $rows): static
	{
		$this->ROWS = $rows;
		return $this;
	}

	function getRows(): ?int
	{
		return $this->ROWS;
	}

	function offset(int $offset): static
	{
		$this->OFFSET = $offset;
		return $this;
	}

	function getOffset(): ?int
	{
		return $this->OFFSET;
	}

	function page(int $page, int $items_per_page): static
	{
		$this->PAGE = $page;
		$this->ITEMS_PER_PAGE = $items_per_page;
		return $this;
	}

	function getPage(): array
	{
		return [$this->PAGE, $this->ITEMS_PER_PAGE];
	}

	function fields(...$args): static {
		$this->FIELDS = [];
		foreach($args as $arg){
			if(is_string($arg)){
				$this->FIELDS[] = $arg;
			} elseif(is_array($arg)){
				$this->FIELDS = array_merge($this->FIELDS, $arg);
			} else {
				throw new InvalidTypeException($arg);
			}
		}
		return $this;
	}

	function get_fields(): array {
		return $this->FIELDS;
	}

	protected function apply_fields_with_values(Select $sql, array $fields, string $prefix = null): Select {
		foreach($fields as $k){
			if(empty($this->$k)){
				continue;
			}

			if(is_scalar($this->$k)) {
				$sql->Where(["$prefix$k = ?", $this->$k]);
			} elseif(is_array($this->$k) || is_iterable($this->$k)){
				$sql->WhereIn("$prefix$k", $this->$k);
			} else {
				throw new InvalidTypeException($this->$k);
			}
		}

		return $sql;
	}

	protected function apply_set_fields(Select $sql, array $fields, string $prefix = null): Select {
		foreach($fields as $k){
			if(isset($this->$k)){
				$sql->Where(["$prefix$k = ?", $this->$k]);
			}
		}

		return $sql;
	}

	protected function apply_null_fields(Select $sql, array $fields, string $prefix = null): Select {
		foreach($fields as $k){
			if($this->$k instanceof None)
			{
				$sql->Where("$prefix$k IS NULL");
			} elseif(isset($this->$k)){
				$sql->Where(["$prefix$k = ?", $this->$k]);
			}
		}

		return $sql;
	}

	# var <type 1>|...|<type n>|bool $this->$k
	# false indicates ignore field
	protected function apply_falsed_fields(Select $sql, array $fields, string $prefix = null): Select {
		foreach($fields as $k){
			if($this->$k !== false) {
				$sql->Where(["$prefix$k = ?", $this->$k]);
			}
		}

		return $sql;
	}

	protected function apply_base_filters(Select $sql): Select {
		if(isset($this->ORDER_BY)){
			$sql->ResetOrderBy()->OrderBy($this->ORDER_BY);
		}

		if(isset($this->ROWS)){
			$sql->Rows($this->ROWS);
		}

		if(isset($this->OFFSET)){
			$sql->Offset($this->OFFSET);
		}

		if(isset($this->PAGE) && isset($this->ITEMS_PER_PAGE)){
			$sql->Page($this->PAGE, $this->ITEMS_PER_PAGE);
		}

		return $sql;
	}

	protected function apply_fields(Select $sql): Select {
		if($this->FIELDS){
			$sql->ResetFields()->Select(join(",", $this->FIELDS));
		}

		return $sql;
	}
}
