<?php declare(strict_types = 1);

/**
 *
 * Child konstruktorā padot visus filtrus kā named parametrus ar default vērtībām.
 * Tas nozīmē, ka NULL filtri ir jāhandlo speciāli
 *
 * */

namespace dqdp\DBA;

use dqdp\DBA\interfaces\EntityFilterInterface;
use dqdp\SQL\Select;
use dqdp\StricStdObject;

abstract class AbstractFilter extends StricStdObject implements EntityFilterInterface {
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

}
