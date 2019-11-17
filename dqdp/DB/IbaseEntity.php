<?php

namespace dqdp\DB;

use dqdp\SQL\Select;

class IbaseEntity implements Entity {
	var $Table;
	var $PK;
	var $Gen;
	var $SearchSQL;

	protected $TR;

	function __construct(){
		$this->init();
		return $this;
	}

	function fetch(){
		return call_user_func_array('ibase_fetch', func_get_args());
	}

	function fetch_all(){
		while($r = call_user_func_array([$this, 'fetch'], func_get_args())){
			$ret[] = $r;
		}
		return $ret??[];
	}

	function get($ID){
		if($q = $this->search([$this->PK=>$ID])){
			return $this->fetch($q);
		}

		return false;
	}

	function get_all($params = null){
		if($q = $this->search($params)){
			return $this->fetch_all($q);
		}

		return false;
	}

	protected function before_search(&$DATA){
		$DATA = eoe($DATA);

		if(is_array($this->PK)){
		} else {
			if($DATA->isset($this->PK) && is_empty($DATA->{$this->PK})){
				trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
				return false;
			}
		}

		$pks = array_wrap($this->PK);
		foreach($pks as $k){
			if($DATA->{$k}){
				$this->SearchSQL->Where(["$this->Table.$k = ?", $DATA->{$k}]);
			}
		}

		if(!is_array($this->PK)){
			$k = $this->PK."S";
			if($DATA->isset($k)){
				if(is_array($DATA->{$k})){
					$IDS = $DATA->{$k};
				} elseif(is_string($DATA->{$k})){
					$IDS = explode(',',$DATA->{$k});
				} else {
					$IDS = [$IDS];
				}
				call_user_func([$this->SearchSQL, 'Where'], sql_create_int_filter("$this->Table.{$this->PK}", $IDS));
			}
		}

		if($DATA->ORDER_BY){
			$this->ResetOrderBy()->OrderBy($DATA->ORDER_BY);
		}

		return $this;
	}

	function search($DATA = null){
		$this->before_search($DATA);
		return $this->do_search();
	}

	protected function do_search(){
		if($this->get_trans()){
			return ibase_query_array($this->get_trans(), $this->SearchSQL, $this->SearchSQL->vars());
		} else {
			return false;
		}
	}

	function save(){
		list($fields, $DATA) = func_get_args();

		$PK_fields_str = $this->PK;
		$Gen_value_str = $Gen_field_str = '';

		if(is_array($this->PK)){
			$PK_fields_str = join(",", $this->PK);
		} else {
			if(empty($DATA->{$this->PK})){
				if(isset($this->Gen)){
					$Gen_field_str = "$this->PK,";
					$Gen_value_str = "NEXT VALUE FOR $this->Gen,";
				}
			} else {
				if(!in_array($this->PK, $fields)){
					$fields[] = $this->PK;
				}
			}
		}

		list($fieldSQL, $valuesSQL, $values) = build_sql($fields, $DATA, true);

		# TODO: ja vajadzēs nodalīt GRANT tiesības pa INSERT/UPDATE, tad jāatdala UPDATE OR INSERT atsevišķos pieprasījumos
		$sql = "UPDATE OR INSERT INTO $this->Table ($Gen_field_str$fieldSQL) VALUES ($Gen_value_str$valuesSQL) MATCHING ($PK_fields_str) RETURNING $PK_fields_str";

		if($q = ibase_query_array($this->get_trans(), $sql, $values)){
			$retPK = ibase_fetch($q);
			if(is_array($this->PK)){
				return $retPK;
			} else {
				return $retPK->{$this->PK};
			}
		} else {
			return false;
		}
	}

	function delete($IDS){
		return $this->ids_process("DELETE FROM $this->Table WHERE $this->PK = ?", $IDS);
	}

	function set_trans($tr){
		if($tr instanceof \dqdp\DB\Entity){
			$this->TR = $tr->get_trans();
		} elseif($tr) {
			$this->TR = $tr;
		}
		return $this;
	}

	function get_trans(){
		return $this->TR;
	}

	function commit(){
		return ibase_commit($this->get_trans());
	}

	function commit_ret(){
		return ibase_commit_ret($this->get_trans());
	}

	function rollback(){
		return ibase_rollback($this->get_trans());
	}

	function rollback_ret(){
		return ibase_rollback_ret($this->get_trans());
	}

	function new_trans(){
		return $this->set_trans(ibase_trans());
	}


	function __call($name, $arguments){
		call_user_func_array([$this->SearchSQL, $name], $arguments);
		return $this;
	}

	protected function reset_searchSQL(){
		$this->SearchSQL = (new Select())->From($this->Table);
		return $this;
	}

	protected function init(){
		// $parts = explode('\\', get_class($this));
		// $this->ModID = strtolower($parts[count($parts) - 1]);
		// $this->Table = strtoupper($this->ModID);
		// $this->PK = $this->Table."_ID";
		$this->set_trans(\App::$DB);
		$this->reset_searchSQL();
		//$json = file_get_contents(\App::$root."/public/schemas/".strtolower($serviceName).".json");
		//$this->Schema = json_decode($json);
		//$this->Table = strtoupper($this->Schema->id);
		//$this->Struct = &$this->Schema->properties;
		return $this;
	}

	protected function ids_process(...$args){
		$sql = array_shift($args);
		$IDS = array_shift($args);
		if(!is_array($IDS)){
			$IDS = [$IDS];
		}

		$ret = true;
		$smt = ibase_prepare($this->get_trans(), $sql);
		foreach($IDS as $ID){
			$params = array_merge([$smt], $args, [$ID]);
			$ret = $ret && call_user_func_array('ibase_execute', $params);
		}
		return $ret;
	}
}
