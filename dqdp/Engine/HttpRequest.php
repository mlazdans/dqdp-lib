<?php declare(strict_types = 1);

namespace dqdp\Engine;

class HttpRequest extends Request {
	// var Args $ARGS;
	# TODO: PUT, etc
	var Args $POST;
	var Args $GET;
	var string $IP;
}
