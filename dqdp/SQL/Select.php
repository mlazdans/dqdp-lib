<?php declare(strict_types = 1);

namespace dqdp\SQL;

use InvalidArgumentException;

class Select extends Statement
{
	protected bool $isDistinct = false;
	protected ?int $rowOffset = null;
	protected ?int $rowRows = null;

	protected array         $fromArgCounts   = [];
	protected array         $fromParts       = [];
	protected array         $fieldsParts     = [];
	protected array         $joinsParts      = [];
	protected array         $lastJoinsParts  = [];
	protected array         $orderByParts    = [];
	protected array         $groupByParts    = [];
	protected Condition     $whereParts;

	function __construct(string|Select $fromFields = null){
		$this->ResetWhere();
		if($fromFields){
			$this->Select($fromFields);
		}
	}

	// function __clone(){
	// 	$pp = [];
	// 	foreach((array)$this->parts as $k=>$v){
	// 		$pp[$k] = is_object($v) ? clone $v : $v;
	// 	}
	// 	$this->parts = (object)$pp;
	// }

	# TODO: make native methods
	// function __call(string $name, array $arguments){
	// 	if(strpos($name, 'Reset') === 0){
	// 		$part = strtolower(substr($name, 5));
	// 		if(isset($this->parts->{$part})){
	// 			$this->parts->{$part} = [];
	// 			return $this;
	// 		}
	// 		throw new BadMethodCallException($name);
	// 	}

	// 	return parent::__call($name, $arguments);
	// }

	function ResetDistinct(): static {
		$this->isDistinct = false;
		return $this;
	}

	function ResetWhere(): static {
		$this->whereParts = new Condition;
		return $this;
	}

	function ResetOrderBy(): static {
		$this->orderByParts = [];
		return $this;
	}

	function ResetFields(): static {
		$this->fieldsParts = [];
		return $this;
	}

	function Distinct(): static {
		$this->isDistinct = true;
		return $this;
	}

	# TODO: sameklēt kodā, kur tiek lietots Select([]) vai otrs arguments kā wrapper
	function Select(string|Select $Field, ?string $alias = null): static {
		if($Field instanceof \dqdp\SQL\Select){
			// if($wrapper){
			// 	$this->parts->fields[] = "$wrapper(($sql)) $alias";
			// }
			$fieldStr = "($Field)";
			$this->whereParts->addVar($Field->getVars());
		} else {
			$fieldStr = $Field;
		}

		$this->fieldsParts[] = $fieldStr.($alias ? " $alias" : "");

		return $this;
	}
	// function Select($arg, $wrapper = ''): static {
	// 	if(is_array($arg) && $arg[0] instanceof \dqdp\SQL\Select){
	// 		list($sql, $alias) = $arg;
	// 		if($wrapper){
	// 			$this->parts->fields[] = "$wrapper(($sql)) $alias";
	// 		} else {
	// 			$this->parts->fields[] = "($sql) $alias";
	// 		}
	// 		$this->parts->where->add_vars($sql->vars());
	// 	} else {
	// 		$this->parts->fields[] = $arg;
	// 	}

	// 	return $this;
	// }

	# Add () and optional arguments to *last added* ->From()
	# Useful for selectable procedures
	function withArgs(...$args): static {
		$i = count($this->fromParts) - 1;
		$this->fromArgCounts[$i] = $this->fromArgCounts[$i]??0;
		$this->fromArgCounts[$i] += $this->addVar($args);

		return $this;
	}

	function Offset(?int $offset): static {
		$this->rowOffset = $offset;
		return $this;
	}

	function Rows(?int $rows): static {
		$this->rowRows = $rows;
		return $this;
	}

	# TODO: sameklēt kodā, kur tiek lietots From([])
	function From(string|Select $From, ?string $alias = null): static {
		if($From instanceof \dqdp\SQL\Select){
			$fromStr = "($From)";
			$this->whereParts->addVar($From->getVars());
		} else {
			$fromStr = "$From";
		}

		$this->fromParts[] = $fromStr.($alias ? " $alias" : "");

		return $this;
	}

	function Join(string $table, $condition): static {
		$this->joinsParts[] = new Join($table, $condition, Join::INNER_JOIN);
		return $this;
	}

	function LeftJoin(string $table, $condition): static {
		$this->joinsParts[] = new Join($table, $condition, Join::LEFT_OUTER_JOIN);
		return $this;
	}

	function LeftJoinLast(string $table, $condition): static {
		$this->lastJoinsParts[] = new Join($table, $condition, Join::LEFT_OUTER_JOIN);
		return $this;
	}

	function Where(mixed $condition): static {
		$this->whereParts->add_condition($condition);
		return $this;
	}

	function WhereIn(mixed $field, mixed $v): static {
		$this->whereParts->add_condition(qb_filter_in($field, $v));
		return $this;
	}

	function Between(mixed $Col, mixed $v1 = null, mixed $v2 = null): static {
		if($v1 && $v2){
			$this->Where(["$Col BETWEEN ? AND ?", $v1, $v2]);
		} elseif($v1){
			$this->Where(["$Col >= ?", $v1]);
		} elseif($v2){
			$this->Where(["$Col <= ?", $v2]);
		}
		return $this;
	}

	function OrderBy(...$args): static {
		$this->orderByParts[] = Order::factory(...$args);
		return $this;
	}

	function GroupBy($group): static {
		$this->groupByParts[] = $group;
		return $this;
	}

	function Page(int $page, int $items_per_page): static {
		return $this->Offset(($page - 1) * $items_per_page)->Rows($items_per_page);
	}

	# TODO: abstract out
	function parse(): string {
		if(SQL::$lex == 'mysql'){
			$lines = $this->parse_mysql();
		} elseif(SQL::$lex == 'fbird'){
			$lines = $this->parse_fbird();
		} else {
			throw new InvalidArgumentException("Unknown SQL::\$lex: ".SQL::$lex);
		}

		return join("\n", $lines);
	}

	function getVars(): array {
		$vars = parent::getVars();

		foreach($this->joinsParts as $j){
			$vars = array_merge($vars, $j->getVars());
		}

		foreach($this->lastJoinsParts as $j){
			$vars = array_merge($vars, $j->getVars());
		}

		$vars = array_merge($vars, $this->whereParts->getVars());

		return $vars;
	}

	protected function parse_mysql(): array {
		$lines = [];
		$this->merge_lines($lines, $this->select_parser());
		$this->merge_lines($lines, $this->fields_parser());
		$this->merge_lines($lines, $this->from_parser());
		$this->merge_lines($lines, $this->join_parser());
		$this->merge_lines($lines, $this->where_parser());
		$this->merge_lines($lines, $this->groupby_parser());
		$this->merge_lines($lines, $this->orderby_parser());

		if(isset($this->rowRows) && isset($this->rowOffset)){
			$lines[] = "LIMIT $this->rowOffset,$this->rowRows";
		} elseif(isset($this->rowRows)){
			$lines[] = "LIMIT $this->rowRows";
		} elseif(isset($this->rowOffset)){
			throw new InvalidArgumentException("offset without rows");
		}

		return $lines;
	}

	protected function parse_fbird(): array {
		$lines = [];
		$this->merge_lines($lines, $this->select_parser());

		if(!isset($this->rowRows) && isset($this->rowOffset)){
			$lines[] = "SKIP $this->rowOffset";
		}

		$this->merge_lines($lines, $this->fields_parser());
		$this->merge_lines($lines, $this->from_parser());
		$this->merge_lines($lines, $this->join_parser());
		$this->merge_lines($lines, $this->where_parser());
		$this->merge_lines($lines, $this->groupby_parser());
		$this->merge_lines($lines, $this->orderby_parser());

		if(isset($this->rowRows) && isset($this->rowOffset)){
			$lines[] = sprintf("ROWS %d TO %d", $this->rowOffset + 1, $this->rowRows + $this->rowOffset);
		} elseif(isset($this->rowRows)){
			$lines[] = "ROWS $this->rowRows";
		}

		return $lines;
	}

	protected function select_parser(): array {
		$lines = ['SELECT'];
		if($this->isDistinct){
			$lines[] = 'DISTINCT';
		}
		return $lines;
	}

	protected function fields_parser(): array {
		return [$this->fieldsParts ? join(",\n", $this->fieldsParts) : '*'];
	}

	protected function from_parser(): array {
		if(!$this->fromParts){
			return [];
		}

		$lines[] = 'FROM';
		foreach($this->fromParts as $k=>$v){
			if(isset($this->fromArgCounts[$k])){
				$parts[] = "$v(".qb_create_placeholders($this->fromArgCounts[$k]).")";
			} else {
				$parts[] = $v;
			}
		}

		if(isset($parts)){
			$lines[] = join(', ', $parts);
		}

		return $lines;
	}

	protected function join_parser(): array {
		if($this->joinsParts){
			$lines[] = join("\n", $this->joinsParts);
		}
		if($this->lastJoinsParts){
			$lines[] = join("\n", $this->lastJoinsParts);
		}
		return $lines??[];
	}

	protected function where_parser(): array {
		if($where = (string)$this->whereParts){
			$lines[] = 'WHERE';
			$lines[] = $where;
		}
		return $lines??[];
	}

	protected function groupby_parser(): array {
		if($this->groupByParts){
			$lines[] = 'GROUP BY';
			$lines[] = join(', ', $this->groupByParts);
		}
		return $lines??[];
	}

	protected function orderby_parser(): array {
		if($this->orderByParts){
			$lines[] = 'ORDER BY';
			$lines[] = join(', ', $this->orderByParts);
		}
		return $lines??[];
	}
}
