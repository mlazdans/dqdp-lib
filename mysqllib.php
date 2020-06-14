<?php

use dqdp\DBA\DBA;

function mysql_last_id(DBA $dba){
	return get_prop($dba->execute_single("SELECT LAST_INSERT_ID() AS last_id"), 'last_id');
}
