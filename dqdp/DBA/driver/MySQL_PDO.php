<?php declare(strict_types = 1);

namespace dqdp\DBA\driver;

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\Types\MySQLConnectParams;
use Error;
use Exception;
use PDO;
use PDOStatement;

class MySQL_PDO implements DBAInterface
{
	private ?PDO $conn;
	protected $transactionCounter = 0;
	protected $row_count;

	function __construct(private MySQLConnectParams $params)
	{
	}

	// function connect_params($params){
	// 	$host = $params['host'] ?? 'localhost';
	// 	$username = $params['username'] ?? '';
	// 	$password = $params['password'] ?? '';
	// 	$database = $params['database'] ?? '';
	// 	$charset = $params['charset'] ?? 'utf8';
	// 	$port = $params['port'] ?? 3306;

	// 	return $this->connect($host, $username, $password, $database, $charset, $port);
	// }

	function get_conn(): mixed
	{
		return $this->conn;
	}

	// function connect($host = null, $username = null, $password = null, $database = null, $charset = null, $port = null){
	function connect()
	{
		$dsn = [];
		if($this->params->host)$dsn[]= "host=".$this->params->host;
		if($this->params->database)$dsn[]= "dbname=".$this->params->database;
		if($this->params->charset)$dsn[]= "charset=".$this->params->charset;
		if($this->params->port)$dsn[]= "port=".$this->params->port;

		$this->conn = new PDO("mysql:".join(";", $dsn), $this->params->username, $this->params->password);
		//$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		//$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

		return $this;
	}

	// private function __execute($f, ...$args){
	// 	$q = $this->query(...$args);
	// 	if($q && $q->columnCount()){
	// 		$data = $this->$f($q);
	// 	}

	// 	# ja selekteejam datus, tad atgriezam tos, savaadaak querija rezultaatu
	// 	return isset($data) ? $data : $q;
	// }

	// function execute(){
	// 	return $this->__execute("fetch_all", ...func_get_args());
	// }

	// function execute_single(){
	// 	return $this->__execute("fetch", ...func_get_args());
	// }

	function execute()
	{
		$args = func_get_args();
		$q = array_shift($args);
		return $q->execute($args);
	}

	function query(){
		$args = func_get_args();

		try {
			if(is_dqdp_statement($args)){
				/** @var \dqdp\SQL\Statement */
				$s = $args[0];
				if($q = $this->prepare($s)){
					$q->execute($s->getVars());
				}
			} elseif(count($args) == 1) {
				$q = $this->conn->query(...$args);
			} else {
				if($q = $this->conn->prepare(array_shift($args))){
					$q->execute($args);
				}
				// $q = $this->conn->query(...$args);
			}

			if($q){
				return $q;
			}

			throw new Error("Invalid query");
		} finally {
			if(!empty($q)){
				$this->row_count = $q->rowCount();
			}
		}
	}

	function fetch_array(): array|null {
		/** @var PDOStatement */ list($q) = func_get_args();

		return ($o = $q->Fetch(PDO::FETCH_NUM)) ? $o : null;
	}

	function fetch_assoc(): array|null {
		/** @var PDOStatement */ list($q) = func_get_args();

		return ($o = $q->Fetch(PDO::FETCH_ASSOC)) ? $o : null;
	}

	function fetch_object(): object|null {
		/** @var PDOStatement */ list($q) = func_get_args();

		return ($o = $q->Fetch(PDO::FETCH_OBJ)) ? $o : null;
	}

	// function trans(){
	// 	if (!$this->transactionCounter++) {
	// 		return $this->conn->beginTransaction();
	// 	}
	// 	$this->execute('SAVEPOINT trans'.$this->transactionCounter);
	// 	return $this->transactionCounter >= 0;
	// }

	// function commit(){
	// 	if (!--$this->transactionCounter) {
	// 		return $this->conn->commit();
	// 	}
	// 	return $this->transactionCounter >= 0;
	// }

	// function rollback(){
	// 	if (--$this->transactionCounter) {
	// 		$this->execute('ROLLBACK TO trans'.($this->transactionCounter + 1));
	// 		return true;
	// 	}
	// 	return $this->conn->rollBack();
	// }

	function new_trans(){
		$this->conn->beginTransaction();
		$this->transactionCounter++;

		# TODO: nested transactions
		// $this->conn->exec("SAVEPOINT trans $this->transactionCounter");

		return $this;
	}

	function commit(): bool {
		return $this->conn->commit();
	}

	function commit_ret(): bool {
		throw new Exception("Not implemented");
	}

	function rollback(): bool {
		return $this->conn->rollBack();
	}

	function auto_commit(...$args){
		list($b) = $args;

		return $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $b);
	}

	function affected_rows(): int {
		return $this->row_count;
	}

	function close(): bool {
		$this->conn = null;

		return true;
	}

	function prepare(){
		$args = func_get_args();

		if(is_dqdp_statement($args)){
			return $this->conn->prepare((string)$args[0]);
		} else {
			return $this->conn->prepare(...$args);
		}
	}

	function escape($v): string {
		return trim($this->conn->quote($v), "'");
	}

	// function save(iterable $DATA, Table $Table){
	// 	$sql_fields = (array)merge_only($Table->getFields(), $DATA);

	// 	$PK = $Table->getPK();
	// 	if(!is_array($PK)){
	// 		$PK_val = get_prop($DATA, $PK);
	// 		if(is_null($PK_val)){
	// 		} else {
	// 			$sql_fields[$PK] = $PK_val;
	// 		}
	// 	}

	// 	$sql = (new Insert)
	// 	->Into($Table->getName())
	// 	->Values($sql_fields)
	// 	->Update();

	// 	if($this->query($sql)){
	// 		if(is_array($PK)){
	// 			foreach($PK as $k){
	// 				$ret[] = get_prop($DATA, $k);
	// 			}
	// 			return $ret??[];
	// 		} else {
	// 			if(empty($sql_fields[$PK])){
	// 				return $this->mysql_last_id();
	// 			} else {
	// 				return $sql_fields[$PK];
	// 			}
	// 		}
	// 	} else {
	// 		return false;
	// 	}
	// }

	// private function mysql_last_id(){
	// 	return get_prop($this->execute_single("SELECT LAST_INSERT_ID() AS last_id"), 'last_id');
	// }

	// function with_new_trans(callable $func, ...$args){
	// 	$this->new_trans();
	// 	if($result = $func($this, ...$args)){
	// 		$this->commit();
	// 	} else {
	// 		$this->rollback();
	// 	}

	// 	return $result;
	// }

	function with_new_trans(callable $func, ...$args): mixed {
		// $old_tr = $this->tr;
		$this->new_trans(...$args);
		try {
			if($result = $func($this)){
				$this->commit();
			}
		} finally {
			if(empty($result)){
				$this->rollback();
			}
		}

		return $result;
	}

	function last_insert_id()
	{
		return $this->conn->lastInsertId();
	}
}
