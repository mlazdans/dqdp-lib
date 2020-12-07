<?php

use dqdp\DBA\AbstractDBA;

function mysql_last_id(AbstractDBA $dba){
	return get_prop($dba->execute_single("SELECT LAST_INSERT_ID() AS last_id"), 'last_id');
}
