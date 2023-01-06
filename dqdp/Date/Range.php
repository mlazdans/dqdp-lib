<?php declare(strict_types = 1);

namespace dqdp\Date;

enum Range: string {
	case Q1 = 'Q1';
	case Q2 = 'Q2';
	case Q3 = 'Q3';
	case Q4 = 'Q4';
	case PREV_YEAR = 'PREV_YEAR';
	case THIS_YEAR = 'THIS_YEAR';
	case TODAY = 'TODAY';
	case YESTERDAY = 'YESTERDAY';
	case THIS_WEEK = 'THIS_WEEK';
	case THIS_MONTH = 'THIS_MONTH';
	case PREV_MONTH = 'PREV_MONTH';
	case PREV_30DAYS = 'PREV_30DAYS';

	function to_range(?int $Year = null): array {
		return match($this){
			Range::Q1 => QRange::Q1->to_range($Year),
			Range::Q2 => QRange::Q2->to_range($Year),
			Range::Q3 => QRange::Q3->to_range($Year),
			Range::Q4 => QRange::Q4->to_range($Year),
			Range::PREV_YEAR => [strtotime('first day of January last year'), strtotime('last day of December last year')],
			Range::THIS_YEAR => [strtotime('first day of January'), time()],
			Range::TODAY => [strtotime('today'), strtotime('today')],
			Range::YESTERDAY => [strtotime('yesterday'), strtotime('yesterday')],
			Range::THIS_WEEK => [strtotime("last Monday"), time()],
			Range::THIS_MONTH => [strtotime("first day of"), time()],
			Range::PREV_MONTH => [strtotime("first day of previous month"), strtotime("last day of previous month")],
			Range::PREV_30DAYS => [strtotime("-30 days"), time()]
		};
	}
}
