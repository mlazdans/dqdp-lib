<?php

namespace dqdp\Entity;

use dqdp\DBLayer\DBLayer;

interface EntityInterface {
	function get($ID, $params = null);
	function get_all($params = null);
	function get_single($params = null);
	function search($params = null);
	function save($DATA);
	function delete();
	function set_trans(DBLayer $dba);
	function get_trans() : DBLayer;
}
