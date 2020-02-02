<?php

namespace dqdp;

class Template extends TemplateBlock
{
	var $modtime;
	var $root_dir = '.';

	function __construct($root_dir = '.'){
		//parent::__construct(NULL, )
		$this->set_root($root_dir);
	}

	function set_root($root_dir){
		$this->root_dir = $root_dir;
		return;
	}

	private function __filename($file_name){
		$file_name = "$this->root_dir/$file_name";
		if(!file_exists($file_name)){
			$this->error('filename: file ['.$file_name.'] does not exists', E_USER_ERROR);
		}

		return $file_name;
	}

	function set_file($ID, $file_name){
		$modtime = 0;
		$content = '';

		$file_path = $this->__filename($file_name);
		if(file_exists($file_path)){
			$modtime = filemtime($file_path);
			$content = file_get_contents($file_path);
		}

		$this->blocks[$ID] = new TemplateBlock($this, $ID, $content);
		$this->blocks[$ID]->modtime = $modtime;

		return true;
	}
}
