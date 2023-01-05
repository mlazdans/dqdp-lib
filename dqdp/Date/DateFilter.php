<?php declare(strict_types = 1);

namespace dqdp\Date;

use dqdp\StricStdObject;

class DateFilter extends StricStdObject {
	function __construct(
		readonly ?string $START = null,
		readonly ?string $END = null,
		readonly ?int $YEAR = null,
		readonly ?int $MONTH = null,
		readonly ?Range $RANGE = null,
	) {
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
}
