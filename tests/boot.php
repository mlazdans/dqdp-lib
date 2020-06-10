<?php

spl_autoload_register();

$root = realpath(__DIR__);

# Include paths
$LIBS = [
	$root.DIRECTORY_SEPARATOR.'..',
];
$include_path = array_unique(array_merge($LIBS, explode(PATH_SEPARATOR, ini_get('include_path'))));
ini_set('include_path', join(PATH_SEPARATOR, $include_path));

require_once("stdlib.php");
