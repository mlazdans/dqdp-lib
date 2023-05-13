<?php declare(strict_types = 1);

use dqdp\SQL\Condition;
use dqdp\SQL\ConditionAnd;
use dqdp\SQL\ConditionOr;

/**
 * Query builder lib
 */

function qb_create_placeholders(int $count){
	return join(",", array_fill(0, $count, "?"));
}

function qb_filter_in($field, $v){
	return ["$field IN (".qb_create_placeholders(count($v)).")", $v];
}

function qb_filter_in_ints($field, $v){
	return qb_filter_in($field, to_int($v));
}

// function sql_create_filter($filter, $join = "AND"){
// 	if(!is_array($filter)){
// 		$filter = [$filter];
// 	}
// 	return $filter ? sprintf("(%s)", join(" $join ", $filter)) : '';
// }

function sql_add_filter(&$filter, &$values, $newf){
	if(!isset($newf[0])){
		return;
	}

	$filter[] = $newf[0];

	if(!isset($newf[1])){
		return;
	}

	if(is_array($newf[1])){
		$values = array_merge($values, $newf[1]);
	} else {
		$values[] = $newf[1];
	}
}

function search_to_sql_cond($q, $fields, $minWordLen = 0, $options = []){
	$words = parse_search_q($q, $minWordLen);
	if(!is_array($fields)){
		$fields = array($fields);
	}

	$MainCond = new Condition();
	foreach($words as $word){
		$Cond = new Condition();
		foreach($fields as $field){
			if(empty($options['wordboundary'])){
				$Cond->add_condition(["$field CONTAINING ?", $word], new ConditionOr);
				// $Cond->add_condition(["UPPER($field) LIKE ?", "%".$word."%"], Condition::OR);
			} else {
				# MySQL specific?? Abstract!
				$Cond->add_condition(["UPPER($field) REGEXP ?", '([[:blank:][:punct:]]|^)'.$word.'([[:blank:][:punct:]]|$)'], new ConditionOr);
				// $Cond->add_condition(["UPPER($field) REGEXP ?", "[[:<:]]".$word."[[:>:]]"], Condition::OR);
			}
		}
		$MainCond->add_condition($Cond, new ConditionAnd);
	}

	return $MainCond;
}

function build_sql(iterable $fields, array|object|null $DATA = null, $skip_nulls = false){
	foreach($fields as $k){
		if($skip_nulls && !($exists = (prop_exists($DATA, $k) && prop_initialized($DATA, $k)))){
			continue;
		}

		if($exists){
			$item = get_prop($DATA, $k);
			# Accept *only* Closure, do not accept is_callable()! Try for example $DATA->system :>
			if($item instanceof Closure){
				$fret = $item();
				if(is_array($fret)){
					# Ja nav uzstādīts otrs parametrs, neliekam to pie fields vai holders
					if(array_key_exists(0, $fret) && array_key_exists(1, $fret)){
						list($h, $v) = $fret;
					} elseif(array_key_exists(0, $fret)) {
						$new_fields[] = $k;
						$holders[] = $fret[0];
						continue;
					}
				} else {
					# Ja return string?
					$new_fields[] = $k;
					$holders[] = $fret;
					continue;
				}
			} else {
				$h = "?";
				$v = $item;
			}
		} else {
			$h = "?";
			$v = null;
		}

		$new_fields[] = $k;
		$holders[] = $h;
		$values[] = $v;
	}

	return [$new_fields??[], $holders??[], $values??[]];
}

function search_fn_ci($word, $field, $Cond){
	$Cond->add_condition(["UPPER($field) LIKE ?", "%".mb_strtoupper($word)."%"], new ConditionOr);
}

function search_fn_nci($word, $field, $Cond){
	$Cond->add_condition(["$field LIKE ?", "%".$word."%"], new ConditionOr);
}

function search_sql(string $q, array $fields, callable $fn = null){
	return __search_sql($q, $fields, $fn??'search_fn_ci');
}

function search_fn_nci_binary($word, $field, $Cond){
	$Cond->add_condition(["UPPER(CONVERT($field USING utf8)) LIKE ?", "%".mb_strtoupper($word)."%"], new ConditionOr);
}

function __search_sql(string $q, array $fields, callable $fn){
	$words = array_unique(split_words($q));

	$MainCond = new Condition();
	foreach($words as $word){
		$Cond = new Condition();
		foreach($fields as $field){
			($fn)($word, $field, $Cond);
		}
		$MainCond->add_condition($Cond, new ConditionAnd);
	}

	return $MainCond;
}

function search_to_sql($q, $fields, $minWordLen = 0): array {
	$words = parse_search_q($q, $minWordLen);
	if(!is_array($fields)){
		$fields = array($fields);
	}

	$match = '';
	$values = [];
	foreach($words as $word){
		$tmp = '';
		foreach($fields as $field){
			//$tmp .= "UPPER($field) LIKE ? COLLATE UNICODE_CI_AI ESCAPE '\\' OR ";
			// $tmp .= "UPPER($field) LIKE ? OR ";
			// $values[] = "%".$word."%";
			$tmp .= "$field CONTAINING ? OR ";
			$values[] = $word;

		}
		$tmp = substr($tmp, 0, -4);
		if($tmp)
			$match .= "($tmp) AND ";
	}

	if($match = substr($match, 0, -5)){
		return ["($match)", $values];
	} else {
		return ["", []];
	}
}
