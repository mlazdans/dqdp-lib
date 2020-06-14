<?php

namespace dqdp;

interface EntityInterface extends DBA\TransInterface {
	function get($ID, $params = null);
	function get_all($params = null);
	function get_single($params = null);
	function search($params = null);
	function save($DATA);
	function delete();
}
