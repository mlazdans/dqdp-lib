<?php

namespace dqdp;

class Template extends TemplateBlock
{
	var $mod_time;

	function __construct($file_name){
		if(!file_exists($file_name)){
			$this->error("file not found ($file_name)", E_USER_ERROR);
		}

		$this->mod_time = filemtime($file_name);

		parent::__construct(NULL, $file_name, file_get_contents($file_name));
	}
}
