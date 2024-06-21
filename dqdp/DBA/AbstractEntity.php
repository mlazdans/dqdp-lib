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
abstract class AbstractEntity implements EntityInterface, TransactionInterface
{
	protected DBAInterface $dba;
	protected $Q = null;

	function __construct() {
	}

	// Select can be made from tabe, view or procedure
	// Update, insert, delete can be made to table or view or procedure
	protected abstract function get_pk(): array|string|null;
	protected abstract function get_gen(): ?string;

	protected function get_table_name(): ?string {
		return null;
	}

	protected function get_proc_name(): ?string {
		return null;
	}

	protected function get_proc_args(): ?array {
		return null;
	}

	# TODO: SelectFields() or SelectOnly() or similar
	protected function select(): Select {
		if($TableName = $this->get_table_name()){
			return (new Select("*"))->from($TableName);
		} elseif($TableName = $this->get_proc_name()){
			return (new Select("*"))->from($TableName)->withArgs($this->get_proc_args());
		} else {
			throw new InvalidArgumentException("Table not found");
		}
	}

	function fetch(): ?object {
		return $this->fetch_object();
	}

	function fetch_object(): ?object {
		return ($data = $this->get_trans()->fetch_object($this->Q)) ? $data : null;
	}

	function fetch_array(): ?array {
		return ($data = $this->get_trans()->fetch_array($this->Q)) ? $data : null;
	}

	function get_single(?AbstractFilter $filters = null): mixed
	{
		if($this->query($filters)) {
			$data = $this->fetch();
			$this->close_query();
			return $data;
		} else {
			return null;
		}
	}

	function preview_sql(?AbstractFilter $filters = null)
	{
		return $filters ? $filters->apply($this->select()) : $this->select();
	}

	function query(?AbstractFilter $filters = null): bool
	{
		if($this->Q = $this->get_trans()->query($filters ? $filters->apply($this->select()) : $this->select())) {
			return true;
		} else {
			return false;
		}
	}

	function close_query(): bool
	{
		return $this->get_trans()->close_query($this->Q);
	}

	function count(?AbstractFilter $filter = null): ?int {
		$sql = ($filter ? $filter->apply($this->select()) : $this->select())
		->ResetFields()
		->ResetOrderBy()
		->Select("COUNT(*) sk")
		->Rows(1);

		if($q = $this->get_trans()->query($sql)){
			return ($this->get_trans()->fetch_object($q))->sk ?? null;
		}

		return null;
	}

	function save(array|object $DATA): mixed {
		return $this->_insert_query($DATA, true);
	}

	function insert(array|object $DATA): mixed {
		return $this->_insert_query($DATA, false);
	}

	function update(int|string|array $ID, array|object $DATA): bool {
		if(is_null($TableName = $this->get_table_name())){
			throw new InvalidArgumentException("Table not found");
		}

		if(is_null($PK = $this->get_pk())){
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
		$PK = $this->get_pk();
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
		if(is_null($TableName = $this->get_table_name())){
			throw new InvalidArgumentException("Table not found");
		}

		$PKSetInData = $this->_pk_in_data($DATA);

		$PK = $this->get_pk();
		if(is_array($PK)){
		} else {
			$needSetGen = !$PKSetInData || !$update;
			if($needSetGen && $Gen = $this->get_gen()){
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
		if(is_null($TableName = $this->get_table_name())){
			throw new InvalidArgumentException("Table not found");
		}

		if(is_null($PK = $this->get_pk())){
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
