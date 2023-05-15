<?php declare(strict_types = 1);

namespace dqdp\TypeGenerator;

enum FieldType {
	case int;
	case varchar;
	case char;
	case float;
	case decimal;
	case timestamp;
	case time;
	case date;
	case blob;
	case text;
};
