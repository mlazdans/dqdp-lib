<?php

namespace dqdp;

class Template extends TemplateBlock
{
	var $mod_time;

	function __construct($file_name){
		if(!file_exists($file_name)){
			$this->error('file ['.$file_name.'] does not exists', E_USER_ERROR);
		}

		$this->mod_time = filemtime($file_name);

		parent::__construct(NULL, $file_name, file_get_contents($file_name));

		// $this->blocks = new TemplateBlock($this, $ID, $content);
		// $this->blocks[$ID]->modtime = $modtime;

		// return true;
	}

	// var $modtime;
	// var $root_dir = '.';

	// function __construct($root_dir = '.'){
	// 	//parent::__construct(NULL, )
	// 	$this->set_root($root_dir);
	// }

	// function set_root($root_dir){
	// 	$this->root_dir = $root_dir;
	// 	return;
	// }

	// private function __filename($file_name){
	// 	$path = $this->root_dir.DIRECTORY_SEPARATOR.$file_name;
	// 	if(!file_exists($path)){
	// 		$this->error('filename: file ['.$file_name.'] does not exists', E_USER_ERROR);
	// 	}

	// 	return $path;
	// }

	// function set_file($ID, $file_name){
	// 	$modtime = 0;
	// 	$content = '';

	// 	$file_path = $this->__filename($file_name);
	// 	if(file_exists($file_path)){
	// 		$modtime = filemtime($file_path);
	// 		$content = file_get_contents($file_path);
	// 	}

	// 	$this->blocks[$ID] = new TemplateBlock($this, $ID, $content);
	// 	$this->blocks[$ID]->modtime = $modtime;

	// 	return true;
	// }
}
