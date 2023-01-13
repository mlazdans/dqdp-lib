<?php declare(strict_types = 1);

namespace dqdp\DBA;

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\interfaces\EntityInterface;
use dqdp\DBA\interfaces\TransactionInterface;
use dqdp\SQL\Condition;
use dqdp\SQL\Insert;
use dqdp\SQL\Select;
use dqdp\SQL\Update;
use InvalidArgumentException;

// TODO: maybe do separate classes? ProcEntity, ReadOnlyEntity, etc
abstract class AbstractEntity implements EntityInterface, TransactionInterface {
	protected DBAInterface $dba;

	function __construct(){
	}

	// Select can be made from tabe,view or procedure
	// Update,insert can be made to table or view
	protected abstract function getTableName(): ?string;
	protected abstract function getProcName(): ?string;
	protected abstract function getPK(): array|string|null;
	protected abstract function getGen(): ?string;
	protected abstract function getProcArgs(): ?array;

	# TODO: SelectFields() or SelectOnly() or similar
	protected function select(): Select {
		if($TableName = $this->getTableName()){
			return (new Select("*"))->From($TableName);
		} elseif($TableName = $this->getProcName()){
			return (new Select("*"))->From($TableName)->withArgs($this->getProcArgs());
		} else {
			throw new InvalidArgumentException("Table not found");
		}
	}

	function fetch(mixed $q): mixed {
		if($data = $this->get_trans()->fetch_object($q)){
			return $data;
		} else {
			return null;
		}
	}

	function getSingle(?AbstractFilter $filters = null): mixed {
		if($q = $this->query($filters)){
			return $this->fetch($q);
		}

		return null;
	}

	function query(?AbstractFilter $filter = null): mixed {
		return $this->get_trans()->query($filter ? $filter->apply($this->select()) : $this->select());
	}

	// function count(?iterable $filters = null): int {
	// 	$sql = $this->set_filters($this->select(), $filters)
	// 	->ResetFields()
	// 	->ResetOrderBy()
	// 	->ResetJoinLast() // Reset LEFT JOINS
	// 	->Select("COUNT(*) sk")
	// 	->Rows(1);

	// 	return (int)($this->get_trans()->execute_single($sql)['sk']??0);
	// }

	function save(array|object $DATA): mixed {
		return $this->_insert_query($DATA, true);
	}

	function insert(array|object $DATA): mixed {
		return $this->_insert_query($DATA, false);
	}

	function update(int|string|array $ID, array|object $DATA): bool {
		if(is_null($TableName = $this->getTableName())){
			throw new InvalidArgumentException("Table not found");
		}

		if(is_null($PK = $this->getPK())){
			throw new InvalidArgumentException("Primary key not set");
		}

		$Where = new Condition();
		if(is_array($PK)){
			foreach($PK as $i=>$k){
				$Where->add_condition(["$k = ?", $ID[$i]]);
			}
		} else {
			$Where->add_condition(["$PK = ?", $ID]);
		}

		$sql = (new Update($TableName))->Set($DATA)->Where($Where);

		if($this->get_trans()->query($sql)){
			return true;
		}

		return false;
	}

	private function _pk_in_data(array|object $DATA): bool {
		$PK = $this->getPK();
		if(is_null($PK)){
			return false;
		} elseif(is_array($PK)){
			foreach($PK as $k){
				if(!prop_exists($DATA, $k) || !prop_initialized($DATA, $k)){
					return false;
				}
			}
		} elseif(!prop_exists($DATA, $PK) || !prop_initialized($DATA, $PK)){
			return false;
		}

		return true;
	}

	private function _insert_query(array|object $DATA, $update = false): mixed {
		if(is_null($TableName = $this->getTableName())){
			throw new InvalidArgumentException("Table not found");
		}

		$PKSetInData = $this->_pk_in_data($DATA);

		$PK = $this->getPK();
		if(is_array($PK)){
		} else {
			$needSetGen = !$PKSetInData || !$update;
			if($needSetGen && $Gen = $this->getGen()){
				set_prop($DATA, $PK, function() use ($Gen) {
					return "NEXT VALUE FOR $Gen";
				});
			}
		}

		$sql = (new Insert)->Into($TableName)->Values($DATA);

		if($update){
			$sql->Update()->Matching($PK);
		}

		$sql->Returning($PK);

		if($q = $this->get_trans()->query($sql)){
			$retPK = $this->get_trans()->fetch_object($q);
			if(is_array($PK)){
				return $retPK;
			} else {
				return get_prop($retPK, $PK);
			}
		}

		return null;
	}

	function delete(int|string|array $ID): bool {
		if(is_null($TableName = $this->getTableName())){
			throw new InvalidArgumentException("Table not found");
		}

		if(is_null($PK = $this->getPK())){
			throw new InvalidArgumentException("Primary key not set");
		}

		# TODO: code duplication from update
		$Where = new Condition();
		if(is_array($PK)){
			foreach($PK as $i=>$k){
				$Where->add_condition(["$k = ?", $ID[$i]]);
			}
		} else {
			if(is_array($ID)){
				throw new InvalidArgumentException("Invalid type for primary key: ".get_multitype($ID));
			}
			$Where->add_condition(["$PK = ?", $ID]);
		}

		$sql = "DELETE FROM $TableName WHERE $Where";
		# TODO: dqdp\SQL\Statement
		// $sql = (new Delete)->From($TableName)->Where($Where);

		return $this->get_trans()->query($sql, ...$Where->getVars()) ? true : false;
	}

	function delete_multiple(array $IDS): bool {
		foreach($IDS as $ID){
			if(!$this->delete($ID)){
				return false;
			}
		}

		return true;
		// if(is_null($TableName = $this->getTableName())){
		// 	throw new InvalidArgumentException("Table not found");
		// }

		// if(is_null($PK = $this->getPK())){
		// 	throw new InvalidArgumentException("Primary key not set");
		// }

		// if(is_array($PK)){
		// 	new TODO("delete_multiple not implemented for array PK");
		// } else {
		// 	$sql = sprintf(
		// 		"DELETE FROM $TableName WHERE $PK IN (%s)",
		// 		qb_create_placeholders(count($IDS))
		// 	);

		// 	if($this->get_trans()->query($sql, ...$IDS)){
		// 		return true;
		// 	}
		// }

		// return false;
	}

	function set_trans(DBAInterface $dba): static {
		$this->dba = $dba;

		return $this;
	}

	function get_trans(): DBAInterface {
		return $this->dba;
	}

}
