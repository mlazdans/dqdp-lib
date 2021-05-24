<?php

declare(strict_types = 1);

namespace dqdp\FireBird;

abstract class FirebirdType extends FirebirdObject
{
	const RELATION               = 0;
	const VIEW                   = 1;
	const TRIGGER                = 2;
	const COMPUTED_FIELD         = 3;
	const VALIDATION             = 4;
	const PROCEDURE              = 5;
	const EXPRESSION_INDEX       = 6;
	const EXCEPTION              = 7;
	const USER                   = 8;
	const FIELD                  = 9;
	const INDEX                  = 10;
	const CHARACTER_SET          = 11;
	const USER_GROUP             = 12;
	const ROLE                   = 13;
	const GENERATOR              = 14;
	const UDF                    = 15;
	const BLOB_FILTER            = 16;
	const COLLATION              = 17;
	const PACKAGE                = 18;
	const PACKAGE_BODY           = 19;
}
