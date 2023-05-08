<?php declare(strict_types = 1);

namespace dqdp;

class Template extends TemplateBlock
{
	function __construct(string $file_name) {
		if(!file_exists($file_name)){
			throw new \Error("file not found: $file_name");
		}

		parent::__construct(null, $file_name, file_get_contents($file_name));
	}
}
