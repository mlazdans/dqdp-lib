<?php declare(strict_types = 1);

namespace dqdp\Date;

use dqdp\StricStdObject;

class DateFilter extends StricStdObject {
	readonly ?string $START;
	readonly ?string $END;
	readonly ?int $YEAR;
	readonly ?int $MONTH;
	readonly ?Range $RANGE;

	function __construct(
		?string $START = null,
		?string $END = null,
		?int $YEAR = null,
		?int $MONTH = null,
		?Range $RANGE = null,
	) {
		if(isset($RANGE)){
			$this->START = null;
			$this->END = null;
			if(in_array($RANGE, [Range::Q1, Range::Q2, Range::Q3, Range::Q4])){
				$this->YEAR = $YEAR;
			} else {
				$this->YEAR = null;
			}
			$this->MONTH = null;
			$this->RANGE = $RANGE;
		} elseif(isset($MONTH)){
			$this->START = null;
			$this->END = null;
			$this->YEAR = $YEAR;
			$this->MONTH = $MONTH;
			$this->RANGE = null;
		} elseif(isset($YEAR)){
			$this->START = null;
			$this->END = null;
			$this->YEAR = $YEAR;
			$this->MONTH = null;
			$this->RANGE = null;
		} else {
			$this->START = $START;
			$this->END = $END;
			$this->YEAR = null;
			$this->MONTH = null;
			$this->RANGE = null;
		}
	}

	function to_range(): array {
		if($this->RANGE){
			return $this->RANGE->to_range($this->YEAR);
		} elseif($this->MONTH){
			$year = $this->YEAR??(int)date('Y');
			$dc = date_daycount($this->MONTH, $year);
			return [
				strtotime("$year-$this->MONTH-01"),
				strtotime("$year-$this->MONTH-$dc")
			];
		} elseif($this->YEAR){
			return [
				strtotime("first day of January $this->YEAR"),
				strtotime("last day of December $this->YEAR")
			];
		}

		$start_date = $end_date = null;

		if($this->START)$start_date = strtotime($this->START);
		if($this->END)$end_date = strtotime($this->END);

		return [$start_date, $end_date];
	}

	function to_range_formatted(string $format): array {
		list($DATE_FROM, $DATE_TO) = $this->to_range();

		return [date($format, $DATE_FROM), date($format, $DATE_TO)];
	}
}
