<?php

namespace dqdp\DB;

interface Entity {
	function fetch();
	function fetch_all();
	function get($ID);
	function get_all();
	function search();
	function save();
	function delete($IDS);

	function set_trans($tr);
	function get_trans();
	function new_trans();
	function commit();
	function rollback();
}
