<?php

namespace dqdp\DBLayer;

interface Layer
{
	const MYSQL = 1;
	const PGSQL = 2;
	const MYSQLI = 3;
	const PDO_MYSQL = 4;

	function Connect();
	function Query();
	function Prepare();
	function FetchAssoc();
	function FetchObject();
	function Execute();
	//function ExecuteSingle();
	function BeginTransaction();
	function Commit();
	function Rollback();
	//function Quote();
	//function Now();
	function LastID();
	//function AutoCommit();
	function AffectedRows();
	function Close();
}
