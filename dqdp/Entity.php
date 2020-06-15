<?php

namespace dqdp;

use dqdp\DBA;
use dqdp\SQL\Insert;
use dqdp\SQL\Select;

require_once('mysqllib.php');

abstract class Entity implements EntityInterface {
	var $Table;
	var $PK;

	protected $lex;
	protected $dba;

	protected function select(){
		return (new Select("*"))->From($this->Table);
	}

	abstract protected function fields(): Array;

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
		return $this->get_trans()->Query($this->set_filters($this->select(), eoe($params)));
	}

	function save($DATA){
		//list($fields, $DATA) = func_get_args();
		$fields = $this->fields();
		//list($DATA) = func_get_args();

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
					return get_prop($retPK, $this->PK);
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
						return mysql_last_id($this->get_trans());
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
		# TODO: dqdp\SQL\Statement
		return $this->ids_process("DELETE FROM $this->Table WHERE $this->PK = ?", ...func_get_args());
	}

	function set_trans(DBA $dba){
		$this->dba = $dba;

		if($dba instanceof \dqdp\DBA\IBase){
			$this->lex = 'fbird';
			if(!is_array($this->PK)){
				$this->PK = strtoupper($this->PK);
			}
		}

		if($dba instanceof \dqdp\DBA\MySQL_PDO){
			$this->lex = 'mysql';
		}

		return $this;
	}

	function get_trans() : DBA {
		return $this->dba;
	}

	protected function set_default_filters(Select $sql, $DATA, array $defaults, $prefix = null){
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($defaults as $field=>$value){
			if($DATA->exists($field)){
				if(!is_null($DATA->{$field})){
					# TODO: f-ija, kā build_sql
					$sql->Where(["$prefix$field = ?", $DATA->{$field}]);
				}
			} else {
				$sql->Where(["$prefix$field = ?", $value]);
			}
		}

		return $sql;
	}

	# TODO: abstract out funkcionālo daļu
	# TODO: uz Select???
	protected function set_null_filters(Select $sql, $DATA, array $fields, $prefix = null){
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($fields as $k){
			if($DATA->exists($k)){
				if(is_null($DATA->{$k})){
					$sql->Where(["$prefix$k IS NULL"]);
				} else {
					# TODO: f-ija, kā build_sql
					$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
				}
			}
		}

		return $sql;
	}

	protected function set_non_null_filters(Select $sql, $DATA, array $fields, $prefix = null){
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($fields as $k){
			if($DATA->exists($k)){
				# TODO: f-ija, kā build_sql
				$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
			}
		}

		return $sql;
	}

	protected function set_field_filters(Select $sql, $DATA, array $fields, $prefix = null){
		$DATA = eoe($DATA);

		if(is_null($prefix)){
			$prefix = "$this->Table.";
		}

		foreach($fields as $k){
			if($DATA->$k){
				# TODO: f-ija, kā build_sql
				$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
			}
		}

		return $sql;
	}

	protected function set_filters(Select $sql, $filters = null){
		$filters = eoe($filters);
		if(is_array($this->PK)){
		} else {
			//if($filters->exists($this->PK) && is_empty($filters->{$this->PK})){
			if($filters->exists($this->PK) && is_null($filters->{$this->PK})){
				trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
				return $sql;
			}
		}

		foreach(array_enfold($this->PK) as $k){
			if($filters->exists($k) && !is_null($filters->{$k})){
				$sql->Where(["$this->Table.$k = ?", $filters->{$k}]);
			}
		}

		# TODO: multi field PK
		if(!is_array($this->PK)){
			$k = $this->PK."S";
			if($filters->exists($k)){
				if(is_array($filters->{$k})){
					$IDS = $filters->{$k};
				} elseif(is_string($filters->{$k})){
					$IDS = explode(',',$filters->{$k});
				} else {
					trigger_error("Illegal multiple PRIMARY KEY value for $this->PKS", E_USER_ERROR);
				}
				# TODO: keys var būt arī ne-int!!!!
				$sql->Where(sql_create_int_filter("$this->Table.{$this->PK}", $IDS));
			}
		}

		# TODO: unify
		$Order = $filters->order_by??($filters->ORDER_BY??'');
		if($Order){
			$sql->ResetOrderBy()->OrderBy($Order);
		}

		if($filters->limit){
			$sql->Rows($filters->limit);
		}

		if($filters->rows){
			$sql->Rows($filters->rows);
		}

		if($filters->offset){
			$sql->Offset($filters->offset);
		}

		if($filters->fields){
			if(is_array($filters->fields)){
				$sql->ResetFields()->Select(join(", ", $filters->fields));
			} else {
				$sql->ResetFields()->Select($filters->fields);
			}
		}

		return $sql;
	}

	# TODO: savest kārtībā
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
			$ret = $ret && $this->get_trans()->execute($smt, [$ID]);
			// $params = array_merge([$smt], $args, [$ID]);
			// $ret = $ret && call_user_func_array('ibase_execute', $params);
		}
		return $ret;
	}

	# TODO: šiem trans() te nevajadzētu būt?
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
	###
}
