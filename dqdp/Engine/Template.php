<?php declare(strict_types = 1);

namespace dqdp;

abstract class EngineTemplate extends PHPTemplate
{
	abstract static function out(string $MODULE_DATA): void;
	// var array $Vars = [];

	// function __construct(protected string $File){
	// 	return $this;
	// }

	// function set($k, $v): static {
	// 	$this->Vars[$k] = $v;
	// 	return $this;
	// }

	// function get($k){
	// 	return $this->Vars[$k];
	// }

	// function get_vars(){
	// 	return $this->Vars;
	// }

	// function set_vars($vars): static {
	// 	$this->Vars = $vars;
	// 	return $this;
	// }

	// function append_vars($vars): static {
	// 	$this->Vars = array_merge($this->Vars, $vars);
	// 	return $this;
	// }

	// function include(PHPTemplate $template): static {
	// 	extract($this->get_vars());
	// 	$TPL = $this;
	// 	include($template);
	// 	return $this;
	// }

	// function get_include($template){
	// 	ob_start();
	// 	$this->include($template);
	// 	return ob_get_clean();
	// }
}
