<?php declare(strict_types = 1);

namespace dqdp\DBA\interfaces;

interface TransactionInterface
{
	function set_trans(DBAInterface $dba);
	function get_trans(): DBAInterface;
}
