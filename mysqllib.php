<?php

use dqdp\DBLayer\DBLayer;

function mysql_last_id(DBLayer $dba){
	$data = $dba->execute_single("SELECT LAST_INSERT_ID() AS last_id");
	return get_prop($data, 'last_id');
}
