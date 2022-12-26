<?php declare(strict_types = 1);

/**
 *
 * Child konstruktorā padot visus filtrus kā named parametrus ar default vērtībām.
 * Tas nozīmē, ka NULL filtri ir jāhandlo speciāli
 *
 * */

namespace dqdp\DBA;

use dqdp\DBA\interfaces\EntityFilterInterface;
use dqdp\ParametersConstructor;
use dqdp\SQL\Select;
use dqdp\StricStdObject;

abstract class AbstractFilter extends StricStdObject implements EntityFilterInterface, ParametersConstructor {
	// protected function applay_default_filters(Select $sql, $DATA, array $defaults, $prefix = null): Select {
	// 	if(is_null($prefix)){
	// 		$prefix = "$this->Table.";
	// 	}

	// 	foreach($defaults as $field=>$value){
	// 		if(is_int($field)){
	// 			$sql->Where($value);
	// 		} elseif($DATA->exists($field)){
	// 			if(!is_null($DATA->{$field})){
	// 				# TODO: f-ija, kā build_sql
	// 				$sql->Where(["$prefix$field = ?", $DATA->{$field}]);
	// 			}
	// 		} else {
	// 			$sql->Where(["$prefix$field = ?", $value]);
	// 		}
	// 	}

	// 	return $sql;
	// }

	# TODO: abstract out filters funkcionālo daļu
	# TODO: uz Select???
	# TODO: vai vispār vajag atdalīt NULL filters? Varbūt visiem vajag NULL check?
	// protected function applay_nullable_filters(Select $sql, $DATA, array $fields, string $prefix = null): Select {
	// 	// if(is_null($prefix)){
	// 	// 	$prefix = "$this->Table.";
	// 	// }

	// 	foreach($fields as $k){
	// 		if($DATA->exists($k)){
	// 			if(is_null($DATA->{$k})){
	// 				$sql->Where(["$prefix$k IS NULL"]);
	// 			} else {
	// 				# TODO: f-ija, kā build_sql
	// 				$sql->Where(["$prefix$k = ?", $DATA->{$k}]);
	// 			}
	// 		}
	// 	}

	// 	return $sql;
	// }

	protected function apply_fields_with_values(Select $sql, array $fields, string $prefix = null): Select {
		foreach($fields as $k){
			if(!empty($this->$k)){
				$sql->Where(["$prefix$k = ?", $this->{$k}]);
			}
		}

		return $sql;
	}

	protected function apply_base_filters(Select $sql): Select {
		// $filters = eoe($filters);
		// if($this->PK){
		// 	if(is_array($this->PK)){
		// 	} else {
		// 		//if($filters->exists($this->PK) && is_empty($filters->{$this->PK})){
		// 		if($filters->exists($this->PK) && is_null($filters->{$this->PK})){
		// 			trigger_error("Illegal PRIMARY KEY value for $this->PK", E_USER_ERROR);
		// 			return $sql;
		// 		}
		// 	}
		// }

		// if($this->PK){
		// 	foreach(array_enfold($this->PK) as $k){
		// 		if($filters->exists($k) && !is_null($filters->{$k})){
		// 			$sql->Where(["$this->Table.$k = ?", $filters->{$k}]);
		// 		}
		// 	}
		// }

		// # TODO: multi field PK
		// if($this->PK && !is_array($this->PK)){
		// 	$k = $this->PK."S";
		// 	if($filters->exists($k)){
		// 		if(is_array($filters->{$k})){
		// 			$IDS = $filters->{$k};
		// 		} elseif(is_string($filters->{$k})){
		// 			$IDS = explode(',',$filters->{$k});
		// 		} else {
		// 			trigger_error("Illegal multiple PRIMARY KEY value for $this->PKS", E_USER_ERROR);
		// 		}
		// 		$sql->Where(qb_filter_in("$this->Table.{$this->PK}", $IDS));
		// 	}
		// }

		# TODO: unify
		// $Order = $filters->order_by??($filters->ORDER_BY??'');
		if(isset($this->ORDER_BY)){
			$sql->ResetOrderBy()->OrderBy($this->ORDER_BY);
		}

		// if($filters->limit){
		// 	$sql->Rows($filters->limit);
		// }

		if(isset($this->ROWS)){
			$sql->Rows($this->ROWS);
		}

		if(isset($this->OFFSET)){
			$sql->Offset($this->OFFSET);
		}

		// if($filters->fields){
		// 	if(is_array($filters->fields)){
		// 		$sql->ResetFields()->Select(join(", ", $filters->fields));
		// 	} else {
		// 		$sql->ResetFields()->Select($filters->fields);
		// 	}
		// }

		return $sql;
	}

}
