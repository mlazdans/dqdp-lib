<?php declare(strict_types = 1);

namespace dqdp\Settings;

# TODO: rename
enum SetType: string {
	case int = "SetInt";
	case bool = "SetBool";
	case string = "SetString";
	case date = "SetDate";
	case binary = "SetBinary";
	case serialize = "SetSerialize";
	case text = "SetText";
}
