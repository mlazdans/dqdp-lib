<?php

namespace dqdp\Entity;

use dqdp\DBA\TransInterface;

interface EntityInterface extends TransInterface {
	function get($ID, $params = null);
	function get_all($params = null);
	function get_single($params = null);
	function search($params = null);
	function save($DATA);
	function delete();
}
