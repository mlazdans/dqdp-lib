<?php declare(strict_types = 1);

namespace dqdp\Forms;

abstract class AbstractElement {
	protected function addPropsIfSet(array &$fields, array $keys){
		foreach($keys as $k){
			if(isset($this->$k)){
				$fields[$k] = $this->$k;
			}
		}
	}
}
