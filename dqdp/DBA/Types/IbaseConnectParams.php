<?php declare(strict_types = 1);

namespace dqdp\DBA\Types;

class IbaseConnectParams extends DBAConnectParams {
	function __construct(
		readonly ?string $database = '',
		readonly ?string $username = '',
		readonly ?string $password = '',
		readonly ?string $charset = '',
		readonly ?int $buffers = 0,
		readonly ?int $dialect = 0,
		readonly ?string $role = '',
		readonly ?int $sync = 0
	) {
	}
}
