<?php declare(strict_types = 1);

namespace dqdp\Settings;

use dqdp\DBA\AbstractDataObject;
use dqdp\DBA\DataObjectInitTrait;
use dqdp\DBA\Types\Varchar;
use InvalidArgumentException;

class SettingsType extends AbstractDataObject {
	use DataObjectInitTrait;

	readonly ?string $domain;
	readonly ?string $key;

	readonly ?int $int;
	readonly ?int $bool;
	readonly ?string $string;
	readonly ?int $date;
	readonly ?string $binary;
	readonly ?string $serialize;
	readonly ?string $text;

	function __construct(?iterable $data = null, ?iterable $defaults = null) {
		parent::__construct($data, $defaults);
		if(isset($this->domain))new Varchar($this->domain, 64);
		if(isset($this->key))new Varchar($this->key, 64);
		if(isset($this->string))new Varchar($this->string, 128);
		# TODO: new DbBool type
		if(isset($this->bool) && ($this->bool > 1 || $this->bool < 0)){
			throw new InvalidArgumentException("Expected 0 or 1, found: $this->bool");
		}
	}

}
