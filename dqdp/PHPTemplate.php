<?php

namespace dqdp;

class PHPTemplate
{
	var $Vars;

	function __construct(){
		return $this;
	}

	function set($k, $v){
		$this->Vars[$k] = $v;
		return $this;
	}

	function get($k){
		return $this->Vars[$k];
	}

	function getVars(){
		return $this->Vars;
	}

	function setVars($vars){
		$this->Vars = $vars;
		return $this;
	}

	function appendVars($vars){
		$this->Vars = array_merge($this->Vars, $vars);
		return $this;
	}

	function include($template){
		$this->set('TPL', $this);
		extract($this->getVars());
		//ob_start();
		include($template);
		//return ob_get_clean();
	}
}