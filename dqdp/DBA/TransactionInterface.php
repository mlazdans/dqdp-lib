<?php

declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\DBA;

interface TransactionInterface {
	function set_trans(DBA $dba);
	function get_trans(): DBA;
}
