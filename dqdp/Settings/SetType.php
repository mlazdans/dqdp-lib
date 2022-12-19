<?php declare(strict_types = 1);

namespace dqdp\Settings;

enum SetType: string {
	case int = "int";
	case bool = "bool";
	case string = "string";
	case date = "date";
	case binary = "binary";
	case serialize = "serialize";
	case text = "text";
}
