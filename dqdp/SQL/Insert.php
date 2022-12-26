<?php declare(strict_types = 1);

namespace dqdp\SQL;

use InvalidArgumentException;

class Insert extends Statement
{
	protected $on_duplicate_update = false;
	protected string $Table;
	protected array $Values = [];
	protected array $matchingParts = [];
	protected array $returningParts = [];

	function Into(string $table): static {
		$this->Table = $table;
		return $this;
	}

	function Values(array|object $data): static {
		if(is_object($data)){
			$this->Values = get_object_vars($data);
		} elseif(is_array($data)){
			$this->Values = $data;
		}

		return $this;
	}

	function Update(): static {
		$this->on_duplicate_update = true;

		return $this;
	}

	function Matching(string|array $cols): static {
		if(is_string($cols)){
			$this->matchingParts = [$cols];
		} else {
			$this->matchingParts = $cols;
		}

		return $this;
	}

	# TODO: INTO <variables>
	function Returning(string|array $cols): static {
		if(is_string($cols)){
			$this->returningParts = [$cols];
		} else {
			$this->returningParts = $cols;
		}

		return $this;
	}

	function ResetUpdate(): static {
		$this->on_duplicate_update = false;

		return $this;
	}

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
		# Need array_values() otherwise unpacking to functions these will be treated as named parameters
		return array_values($this->Values);
	}

	function getValues(): array {
		return $this->Values;
	}

	protected function values_parser(){
		list($fields, $holders) = build_sql(array_keys($this->Values), eo($this->Values), true);
		$lines[] = "(".join(',', $fields).")";
		$lines[] = "VALUES";
		$lines[] = "(".join(',', $holders).")";

		return $lines;
	}

	protected function parse_mysql(){
		$lines = ['INSERT'];

		if($this->Table){
			$lines[] = "INTO $this->Table";
		}

		$this->merge_lines($lines, $this->values_parser());

		if($this->on_duplicate_update){
			$v_fields = array_map(function($v){
				return "$v=VALUES($v)";
			}, array_keys($this->Values));

			$lines[] = "ON DUPLICATE KEY UPDATE";
			$lines[] = join(",", $v_fields);
		}

		return $lines;
	}

	protected function parse_fbird(){
		if($this->on_duplicate_update){
			$lines = ['UPDATE OR INSERT'];
		} else {
			$lines = ['INSERT'];
		}

		if($this->Table){
			$lines[] = "INTO $this->Table";
		}

		$this->merge_lines($lines, $this->values_parser());

		if($this->matchingParts){
			$lines[] = "MATCHING (".join(",", $this->matchingParts).")";
		}

		if($this->returningParts){
			$lines[] = "RETURNING ".join(",", $this->returningParts);
		}

		return $lines;
	}
}
