<?php

declare(strict_types = 1);

namespace dqdp\FireBird\Relation;

class Type {
	const PERSISTENT                  = 0;
	const VIEW                        = 1;
	const EXTERNAL                    = 2;
	const VIRTUAL                     = 3;
	const GLOBAL_TEMPORARY_PRESERVE   = 4;
	const GLOBAL_TEMPORARY_DELETE     = 5;
}
