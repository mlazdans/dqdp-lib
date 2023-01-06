<?php declare(strict_types = 1);

namespace dqdp\Date;

enum QRange: int {
	case Q1 = 1;
	case Q2 = 2;
	case Q3 = 3;
	case Q4 = 4;

	function to_range(?int $year = null): array {
		if(is_null($year)){
			$year = (int)date('Y');
		}

		if($year < 0){
			$year = (int)date('Y') + $year;
		}

		$firstMonth = date_qt_month($this->value, 1);
		$lastMonth = date_qt_month($this->value, 3);

		$start_date = mktime(0,
			month: $firstMonth,
			day: 1,
			year: $year
		);

		$end_date = mktime(0,
			month: $lastMonth,
			day: date_daycount($lastMonth, $year),
			year: $year
		);

		return $start_date && $end_date ? [$start_date, $end_date] : [];
	}
}
