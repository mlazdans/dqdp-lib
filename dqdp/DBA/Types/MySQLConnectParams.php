<?php declare(strict_types = 1);

namespace dqdp\DBA\Types;

class MySQLConnectParams extends DBAConnectParams {
	function __construct(
		readonly ?string $host = 'localhost',
		readonly ?string $username = '',
		readonly ?string $password = '',
		readonly ?string $database = '',
		readonly ?string $charset = '',
		readonly ?int $port = 3306,
	) {
	}
}
