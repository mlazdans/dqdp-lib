<?php declare(strict_types = 1);

namespace dqdp\SQL;

use ArgumentCountError;
use dqdp\InvalidTypeException;

class Update extends Statement
{
	protected string $Table;
	protected array $Sets = [];
	protected array $Values = [];
	protected array $returningParts = [];
	protected Condition $Where;

	function __construct(string $Table){
		$this->Table = $Table;
		$this->Where = new Condition;
	}

	/**
	 * Usage:
	 *   Set(["field"=>"value"]);
	 *   Set("field", "value");
	 *   Set("field = value");
	 * */
	function Set(...$args): static {
		if(count($args) == 1){
			# TODO: merge object/array
			if(is_string($args[0])){
				$this->Sets[] = $args[0];
			} elseif(is_object($args[0])){
				$this->Values = get_object_vars($args[0]);
			} elseif(is_array($args[0])){
				$this->Values = $args[0];
			} else {
				throw new InvalidTypeException($args[0]);
			}
		} elseif(count($args) == 2){
			$this->Values[$args[0]] = $args[1];
		} else {
			throw new ArgumentCountError("Expected 1 or 2 arguments");
		}

		return $this;
	}

	function Where(...$args): static {
		$this->Where->add_condition(...$args);
		return $this;
	}

	function WhereIn(mixed $field, mixed $v): static {
		$this->Where->add_condition(qb_filter_in($field, $v));
		return $this;
	}

	function parse(): string {
		$lines = ['UPDATE'];

		if($this->Table){
			$lines[] = "$this->Table";
		}

		$this->merge_lines($lines, $this->values_parser());
		$this->merge_lines($lines, $this->where_parser());

		if($this->returningParts){
			$lines[] = "RETURNING ".join(",", $this->returningParts);
		}

		return join("\n", $lines);
	}

	# TODO: cache build_sql_raw() output
	function getVars(): array {
		$build = build_sql(array_keys($this->Values), $this->Values, true);

		return array_merge($build[2], $this->Where->getVars());
	}

	function getValues(): array {
		return $this->Values;
	}

	function Returning(string|array $cols): static {
		if(is_string($cols)){
			$this->returningParts = [$cols];
		} else {
			$this->returningParts = $cols;
		}

		return $this;
	}

	protected function where_parser(): array {
		if($where = (string)$this->Where){
			$lines[] = 'WHERE';
			$lines[] = $where;
		}
		return $lines??[];
	}

	protected function values_parser(): array {
		list($fields, $holders) = build_sql(array_keys($this->Values), $this->Values, true);

		$set_line = $this->Sets;
		foreach($fields as $i=>$f){
			$set_line[] = "$f = ".$holders[$i];
		}

		if($set_line){
			$lines[] = "SET ".join(', ', $set_line);
		}

		return $lines??[];
	}
}
