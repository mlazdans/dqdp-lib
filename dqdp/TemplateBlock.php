<?php declare(strict_types = 1);

namespace dqdp;

define('TMPL_APPEND', true);

class TemplateBlock
{
	var $ID;
	var $vars = [];
	var $blocks = [];

	var $parent = null;
	var $block_vars = null;

	var $content;
	var $parsed_content;
	var $parsed_count = 0;

	var $attributes = [
		'disabled'=>false
	];

	function __construct(TemplateBlock $parent = NULL, $ID, $content){
		if($this->block_exists($ID)){
			$this->error("block already exists ($ID)", E_USER_ERROR);
			return;
		}

		$this->ID = $ID;
		$this->parent = $parent;
		$this->content = $content;
		$this->__find_blocks();
	}

	function __get($ID){
		return $this->get_block($ID);
	}

	private function __find_blocks(){
		$patt = '/<!--\s+BEGIN\s+(.*)\s+(.*)-->(.*)<!--\s+END\s+\1\s+-->/smU';
		preg_match_all($patt, $this->content, $m);

		if(!isset($m[1])){
			return false;
		}

		for($c = 0; $c < count($m[1]); $c++){
			$id = $m[1][$c];
			$this->blocks[$id] = new TemplateBlock($this, $id, $m[3][$c]);
			//$this->blocks[$id] = new TemplateBlock($id, $m[3][$c]);
			//$this->blocks[$id]->vars = $this->vars;

			$arr_attributes = explode(' ', strtolower($m[2][$c]));
			$this->blocks[$id]->attributes['disabled'] = in_array('disabled', $arr_attributes);
		}
	}

	private function __parse_vars(){
		$patt = $repl = $vars_cache = [];

		if($this->block_vars === null){
			preg_match_all("/{([a-zA-Z0-9_]+)}/U", $this->content, $m);
			$this->block_vars = $m[1];
		}

		foreach($this->block_vars as $k){
			$patt[] = '/{'.$k.'}/';
			//chr(92).chr(92)
			$p = array("/([\\\])+/", "/([\$])+/");
			$r = array("\\\\$1", "\\\\$1");
			$vars_cache[$k] = $vars_cache[$k]??$this->get_var($k);

			$repl[] = preg_replace($p, $r, $vars_cache[$k]);
		}

		$r = preg_replace($patt, $repl, $this->content);

		if($r === NULL){
			printr($this->ID, $patt, $repl, $this->content);
		}

		return $r;

		/*
		switch ($this->undefined)
		{
			case 'remove':
				$content = preg_replace('/([^\\\])?{'.$variable_pattern.'}/U', '\1', $content);
				$content = preg_replace('/\\\{('.$variable_pattern.')}/', '{\1}', $content);
				return $content;
				//preg_replace('/\\\{'.$variable_pattern.'\}/', 'aaa{'.$variable_pattern.'}', $content);
				//return preg_replace('/(\n+|\r\n+)?{'.$variable_pattern.'}(\n+|\r\n+)?/', '', $content);
				break;
			case 'comment':
				return preg_replace('/{('.$variable_pattern.')}/', '<!-- UNDEFINED \1 -->', $content);
				//return preg_replace('/(\n+|\r\n+)?{('.$variable_pattern.')}(\n+|\r\n+)?/', '<!-- UNDEFINED \1 -->', $content);
				break;
			case 'warn':
				return preg_replace('/{('.$variable_pattern.')}/', 'UNDEFINED \1', $content);
				//return preg_replace('/(\n+|\r\n+)?{('.$variable_pattern.')}(\n+|\r\n+)?/', 'UNDEFINED \1', $content);
				break;
		}
		*/
	}

	protected function error($msg, $e = E_USER_WARNING){
		$tmsg = '';
		$t = debug_backtrace();
		for($i=1;$i<count($t);$i++){
			$bn = basename($t[$i]['file']);
			if($bn == 'TemplateBlock.php' || $bn == 'Template.php'){
				continue;
			}
			$tmsg = sprintf("(called %s line %d)", $t[$i]['file'], $t[$i]['line']);
			break;
		}

		if($tmsg){
			$msg .= " $tmsg";
		}

		trigger_error($msg, $e);
	}

	private function __get_block($ID): ?TemplateBlock {
		if($ID instanceof TemplateBlock){
			return $ID;
		}

		if(!$ID || ($this->ID == $ID)){
			$block = $this;
		} else {
			$block = $this->get_block_under($ID);
		}

		return $block;
	}

	function block_exists($ID): bool {
		return (bool)$this->__get_block($ID);
	}

	function get_block($ID): ?TemplateBlock {
		$block = $this->__get_block($ID);
		if($block === NULL){
			$this->error("block not found ($ID)");
		}

		return $block;
	}

	function get_block_under($ID): ?TemplateBlock {
		if(isset($this->blocks[$ID])){
			return $this->blocks[$ID];
		}

		foreach($this->blocks as $o){
			if($block = $o->get_block_under($ID)){
				return $block;
			}
		}

		return NULL;
	}

	function parse_block($ID = NULL, $append = false){
		if($block = $this->get_block($ID)){
			return $block->parse($append);
		}

		return '';
	}

	function parse($append = false){
		# ja bloks sleegts
		if($this->attributes['disabled']){
			return '';
		}

		# ja jau noparseets
		if($this->parsed_count && !$append) {
			return $this->get_parsed_content();
		}

		$this->parsed_count++;

		$parsed_content = $this->__parse_vars();

		# ja blokaa veel ir bloki
		foreach($this->blocks as $block_id => $object){
			$block_content = $object->parse();
			$patt = '/\s*<!--\s+BEGIN\s+' . $block_id . '\s+[^<]*-->.*<!--\s+END\s+' . $block_id . '\s+-->\s*/smi';
			preg_match_all($patt, $parsed_content, $m);
			//printr($block_id,$m);
			foreach($m[0] as $mm) {
				$parsed_content = str_replace($mm, $block_content, $parsed_content);
			}
		}

		if($append) {
			$this->parsed_content .= $parsed_content;
		} else {
			$this->parsed_content = $parsed_content;
		}

		# reset childs
		//if($append) {
			foreach($this->blocks as $object) {
				$object->reset();
			}
		//}

		return $this->parsed_content;
	}

	function get_parsed_content($ID = NULL){
		return ($block = $this->get_block($ID)) ? $block->parsed_content : NULL;
	}

	function get_var($k, $ID = NULL){
		if($block = $this->get_block($ID)){
			if(isset($block->vars[$k])) {
				return $block->vars[$k];
			} elseif($block->parent) {
				return $block->parent->get_var($k);
			}
		}

		return NULL;
	}

	function set_var($var_id, $value, $ID = NULL){
		if($block = $this->get_block($ID)){
			$block->vars[$var_id] = $value;
		}

		return $this;
	}

	function set_array(Array $array, $ID = NULL){
		if($block = $this->get_block($ID)){
			foreach($array as $k=>$v){
				$block->vars[$k] = $v;
			}
		}

		return $this;
	}

	function set_except(Array $exclude, Array $data, $ID = NULL){
		if($block = $this->get_block($ID)){
			$diff = array_diff(array_keys($data), $exclude);
			foreach($diff as $k){
				$block->vars[$k] = $data[$k];
			}
		}

		return $this;
	}

	function reset($ID = NULL){
		if($block = $this->get_block($ID)){
			$block->parsed_content = '';
			$block->parsed_count = 0;
			foreach($block->blocks as $o){
				$o->reset();
			}
		}

		return $this;
	}

	function enable_if($cond, $ID = NULL){
		return $this->set_attribute('disabled', !((bool)$cond), $ID);
	}

	function enable($ID = NULL){
		return $this->set_attribute('disabled', false, $ID);
	}

	function disable($ID = NULL){
		return $this->set_attribute('disabled', true, $ID);
	}

	function set_attribute($attribute, $value, $ID = NULL){
		if(($block = $this->get_block($ID)) && isset($block->attributes[$attribute])){
			$block->attributes[$attribute] = $value;
		}

		return $this;
	}

	# TODO: test vai remove?
	// function copy_block($ID_to, $ID_from){
	// 	if(!($block_to = $this->get_block($ID_to))){
	// 		return false;
	// 	}

	// 	if(!($block_from = $this->get_block($ID_from))){
	// 		return false;
	// 	}

	// 	# tagat noskaidrosim, vai block_to nav zem block_from
	// 	if($block_from->get_block_under($ID_to)){
	// 		$this->error("block is a child of parent ($ID_from:$ID_to)");
	// 		return false;
	// 	}

	// 	# paarkopeejam paareejos parametrus
	// 	$block_to->vars = &$block_from->vars;
	// 	$block_to->blocks = &$block_from->blocks;
	// 	$block_to->block_vars = &$block_from->block_vars;
	// 	$block_to->content = &$block_from->content;
	// 	$block_to->parsed_content = &$block_from->parsed_content;
	// 	$block_to->attributes = &$block_from->attributes;
	// 	$block_from->parent = $block_to;

	// 	//unset($block_from->parent->blocks[$ID_from]);

	// 	return true;
	// }

	function set_block_string($ID, $content){
		if($block = $this->get_block($ID)){
			$block->content = $content;
		}

		return $this;
	}

	function dump_blocks($pre = ''){
		foreach($this->blocks as $block_id=>$object){
			$a = ($object->blocks ? '+' : '-');
			print "$pre$a$block_id($object->parsed_count)<br>\n";
			$object->dump_blocks("| $pre");
		}
	}
}
