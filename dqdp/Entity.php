<?php

namespace dqdp;

use dqdp\SQL\Insert;
use dqdp\SQL\Select;
use TypeError;

class Entity {
	var $Table;
	var $PK;

	protected $lex;
	protected $dba;

	protected function select(){
		return (new Select("*"))->From($this->Table);
	}

	function get($ID, $params = null){
		$params = eoe($params);
		$params->{$this->PK} = $ID;
		return $this->get_single($params);
	}

	function get_all($params = null){
		if($q = $this->search($params)){
			return $this->get_trans()->fetch_all($q);
		}
	}

	function get_single($params = null){
		if($q = $this->search($params)){
			return $this->get_trans()->fetch($q);
		}
	}

	function search($params = null){
		$params = eoe($params);
		return $this->get_trans()->Query($this->set_filters($this->select(), $params));
	}

	function set_default_filters(Select $sql, $params, $fields, $prefix = ''){
		$params = eoe($params);
		foreach($fields as $field=>$default){
			if($params->isset($field)){
				if(!is_null($params->{$field})){
					$sql->Where(["$field = ?", $params->{$field}]);
				}
			} else {
				$sql->Where(["$prefix$field = ?", $default]);
			}
		}

		return $sql;
	}

	function set_null_filters(Select $sql, $params, $fields, $prefix = ''){
		$params = eoe($params);
		$fields = array_wrap($fields);
		foreach($fields as $k){
			if($params->isset($k)){
				if(is_null($params->{$k})){
					$sql->Where(["$prefix$k IS NULL"]);
				} else {
					$sql->Where(["$prefix$k = ?", $params->{$k}]);
				}
			}
		}

		return $sql;
	}

	function set_filters(Select $sql, $params = null){
		$params = eoe($params);
		if(is_array($this->PK)){
		} else {
			if($params->isset($this->PK) && is_empty($params->{$this->PK})){
				trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
				return $sql;
			}
		}

		$pks = array_wrap($this->PK);
		foreach($pks as $k){
			if($params->{$k}){
				$sql->Where(["$this->Table.$k = ?", $params->{$k}]);
			}
		}

		# TODO: multi field PK
		if(!is_array($this->PK)){
			$k = $this->PK."S";
			if($params->isset($k)){
				if(is_array($params->{$k})){
					$IDS = $params->{$k};
				} elseif(is_string($params->{$k})){
					$IDS = explode(',',$params->{$k});
				} else {
					trigger_error("Illegal multiple PRIMARY KEY value for $this->PKS", E_USER_ERROR);
				}
				$sql->Where(sql_create_int_filter("$this->Table.{$this->PK}", $IDS));
			}
		}

		$Order = $params->order_by??($params->ORDER_BY??'');
		if($Order){
			$sql->ResetOrderBy()->OrderBy($Order);
		}

		if($params->limit){
			$sql->Rows($params->limit);
		}

		if($params->rows){
			$sql->Rows($params->rows);
		}

		if($params->offset){
			$sql->Offset($params->offset);
		}

		if($params->fields){
			if(is_array($params->fields)){
				$sql->ResetFields()->Select(join(", ", $params->fields));
			} else {
				$sql->ResetFields()->Select($params->fields);
			}
		}

		return $sql;
	}

	function save(){
		list($fields, $DATA) = func_get_args();

		$sql_fields = (array)merge_only($fields, $DATA);

		if(!is_array($this->PK)){
			$PK_val = get_prop($DATA, $this->PK);
			if(is_null($PK_val)){
				if($this->lex == 'fbird'){
					if(!empty($this->Gen)){
						$sql_fields[$this->PK] = function(){
							return ["NEXT VALUE FOR $this->Gen"];
						};
					}
				}
				if($this->lex == 'mysql'){
				}
			} else {
				$sql_fields[$this->PK] = $PK_val;
			}
		}

		$sql = (new Insert)
		->Into($this->Table)
		->Values($sql_fields)
		->Update()
		;

		if($this->lex == 'fbird'){
			$PK_fields_str = is_array($this->PK) ? join(",", $this->PK) : $this->PK;
			$sql
			->after("values", "matching", "MATCHING ($PK_fields_str)")
			->after("values", "returning", "RETURNING $PK_fields_str")
			;
		}

		if($q = $this->get_trans()->query($sql)){
			if($this->lex == 'fbird'){
				$retPK = $this->get_trans()->fetch($q);
				if(is_array($this->PK)){
					return $retPK;
				} else {
					return $retPK->{$this->PK};
				}
			}
			if($this->lex == 'mysql'){
				if(is_array($this->PK)){
					foreach($this->PK as $k){
						$ret[] = get_prop($DATA, $k);
					}
					return $ret??[];
				} else {
					if(empty($sql_fields[$this->PK])){
						return $this->get_trans()->last_id();
					} else {
						return $sql_fields[$this->PK];
					}
				}
			}
		} else {
			return false;
		}
	}

	function delete(){
		# TODO: multi field PK
		return $this->ids_process("DELETE FROM $this->Table WHERE $this->PK = ?", ...func_get_args());
	}

	// Uncaught Exception(TypeError): Argument 1 passed to dqdp\Entity::set_trans() must be an instance of dqdp\DBLayer\DBLayer, resource given, called in D:\vienpatis\vienpatis\modules\journal\accounting.php on line 19 in dqdp\Entity.php on line 188
	// set_trans(...) in modules\journal\accounting.php on line 19
	// include(modules\journal\accounting.php) in dqdp\PHPTemplate.php on line 39
	// include(...) in modules\journal.php on line 34
	// include(modules\journal.php) in dqdp\PHPTemplate.php on line 39
	// include(...) in public\main.php on line 10

	function set_trans(...$args){
		list($dba) = $args;
		if(!($dba instanceof \dqdp\DBLayer\DBLayer)){
			//$msg = sprintf("Argument 1 passed to set_trans() must be an instance of dqdp\DBLayer\DBLayer, %s given, called in %s on line %d");
			$msg = sprintf("Argument 1 passed to set_trans() must be an instance of dqdp\DBLayer\DBLayer, %s given", gettype($dba));
			throw new TypeError($msg);
			//trigger_error("asdad", E_USER_ERROR);
		}

		$this->dba = $dba;

		if($dba instanceof \dqdp\DBLayer\Ibase_Layer){
			$this->lex = 'fbird';
		}

		if($dba instanceof \dqdp\DBLayer\MySQL_PDO_Layer){
			$this->lex = 'mysql';
		}

		return $this;
	}

	function get_trans(){
		return $this->dba;
	}

	# TODO: šiem trans() te nevajadzētu būt
	function new_trans(){
		$this->dba = $this->dba->trans();
		return $this;
	}

	function commit(...$args){
		return $this->dba->commit(...$args);
	}

	function rollback(...$args){
		return $this->dba->rollback(...$args);
	}

	protected function ids_process(...$args){
		$sql = array_shift($args);
		$IDS = array_shift($args);
		if(!is_array($IDS)){
			$IDS = [$IDS];
		}

		if(!($smt = $this->get_trans()->prepare($sql))){
			return false;
		}

		$ret = true;
		foreach($IDS as $ID){
			$ret = $ret && $this->get_trans()->execute($smt, $ID);
			// $params = array_merge([$smt], $args, [$ID]);
			// $ret = $ret && call_user_func_array('ibase_execute', $params);
		}
		return $ret;
	}
}
