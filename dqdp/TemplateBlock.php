<?php declare(strict_types = 1);

namespace dqdp;

use InvalidArgumentException;
use ParseError;

define('TMPL_APPEND', true);

class TemplateBlock
{
	var string $ID = '';
	var array $vars = [];

	/** @var TemplateBlock[] */
	var array $blocks = [];

	var ?TemplateBlock $parent = null;
	var ?array $block_vars = null;

	var string $content = '';
	var string $parsed_content = '';
	var int $parsed_count = 0;

	var array $attributes = [
		'disabled'=>false
	];

	protected int $offset;    // where block starts
	protected int $len;       // where block ends

	function __construct(TemplateBlock $parent = NULL, string $ID, string $content){
		if($this->block_exists($ID)){
			throw new InvalidArgumentException("block already exists ($ID)");
		}

		$this->ID = $ID;
		$this->parent = $parent;
		$this->content = $content;
		$this->__find_blocks();
	}

	function block_exists(string $ID): bool {
		return (bool)$this->_get_block($ID);
	}

	function get_block(string $ID): TemplateBlock {
		if($block = $this->_get_block($ID)){
			return $block;
		}

		throw new InvalidArgumentException("block not found ($ID)");
	}

	function parse_block(string $ID, bool $append = false): string {
		return $this->get_block($ID)->parse($append);
	}

	function parse(bool $append = false): string {
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

	function get_parsed_content(string $ID = NULL){
		return ($block = $this->_get_block_or_self($ID)) ? $block->parsed_content : NULL;
		// return ($block = $this->get_block($ID)) ? $block->parsed_content : NULL;
	}

	function get_var(string $var_id, string $ID = NULL){
		if($block = $this->_get_block_or_self($ID)){
			if(isset($block->vars[$var_id])) {
				return $block->vars[$var_id];
			} elseif($block->parent) {
				return $block->parent->get_var($var_id);
			}
		}

		return NULL;
	}

	function set_var(string $var_id, $value, string $ID = NULL): TemplateBlock {
		if($block = $this->_get_block_or_self($ID)){
			$block->vars[$var_id] = $value;
		}

		return $this;
	}

	function set_array(iterable $array, string $ID = NULL): TemplateBlock {
		if($block = $this->_get_block_or_self($ID)){
			foreach($array as $k=>$v){
				$block->vars[$k] = $v;
			}
		}

		return $this;
	}

	function set_except(array $exclude, array $data, string $ID = NULL): TemplateBlock {
		if($block = $this->_get_block_or_self($ID)){
			$diff = array_diff(array_keys($data), $exclude);
			foreach($diff as $k){
				$block->vars[$k] = $data[$k];
			}
		}

		return $this;
	}

	function reset(string $ID = NULL): TemplateBlock {
		if($block = $this->_get_block_or_self($ID)){
			$block->parsed_content = '';
			$block->parsed_count = 0;
			foreach($block->blocks as $o){
				$o->reset();
			}
		}

		return $this;
	}

	function enable_if(bool $cond, string $ID = NULL): TemplateBlock {
		return $this->set_attribute('disabled', !$cond, $ID);
	}

	function enable(string $ID = NULL): TemplateBlock {
		return $this->set_attribute('disabled', false, $ID);
	}

	function disable(string $ID = NULL): TemplateBlock {
		return $this->set_attribute('disabled', true, $ID);
	}

	function set_attribute(string $attribute, $value, string $ID = NULL): TemplateBlock {
		if($block = $this->_get_block_or_self($ID)){
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

	function set_block_string(string $ID, string $content){
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

	// private function __find_blocks(){
	// 	$patt = '/<!--\s+BEGIN\s+(.*)\s+(.*)-->(.*)<!--\s+END\s+\1\s+-->/smU';
	// 	preg_match_all($patt, $this->content, $m);

	// 	if(!isset($m[1])){
	// 		return false;
	// 	}

	// 	for($c = 0; $c < count($m[1]); $c++){
	// 		$id = $m[1][$c];
	// 		$this->blocks[$id] = new TemplateBlock($this, $id, $m[3][$c]);
	// 		//$this->blocks[$id] = new TemplateBlock($id, $m[3][$c]);
	// 		//$this->blocks[$id]->vars = $this->vars;

	// 		$arr_attributes = explode(' ', strtolower($m[2][$c]));
	// 		$this->blocks[$id]->attributes['disabled'] = in_array('disabled', $arr_attributes);
	// 	}
	// }

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
	}

	private function __find_blocks(){
		$m_WHOLE = 0;
		$m_BEGIN = 1;
		$m_ID = 2;
		$m_ATTRS = 3;
		$m_CONTENTS = 4;
		$m_END = 5;

		ini_set('pcre.backtrack_limit', '-1');
		// $patt = "/(<!--\s*BEGIN\s+([\S]+)\s*(.*)-->)(.*)(<!--\s*END\s+\\$m_ID\s*-->)/smU";
		// $patt = "/(<!--\s+BEGIN\s+([^\s]*)\s+(.*)-->)(.*)(<!--\s+END\s+\\$m_ID\s+-->)/smU";
		$patt = '/(<!-- BEGIN ([\S]+) (.*)-->)(.*)(<!-- END \2 -->)/smUS';

		if(preg_match_all($patt, $this->content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false){
			// debug_print_backtrace();
			$err = array_flip(array_filter(get_defined_constants(true)['pcre'], function ($value) {
				return substr($value, -6) === '_ERROR';
			  }, ARRAY_FILTER_USE_KEY))[preg_last_error()];
			throw new ParseError(sprintf("template compilation failure $this->ID (%s)", $err));
		}

		// $striped_offset = 0;
		// $striped_content = '';
		foreach($matches as $item){
			$id = $item[$m_ID][0];

			$Block = new TemplateBlock($this, $id, $item[$m_CONTENTS][0]);
			$Block->len = strlen($item[$m_WHOLE][0]);
			$Block->offset = (int)$item[$m_WHOLE][1];

			// $attributes = explode(' ', $item[$m_ATTRS][0]);
			// $Block->attributes['disabled'] = in_array('disabled', $attributes);
			$Block->attributes['disabled'] = (strpos($item[$m_ATTRS][0], 'disabled') !== false);

			$this->blocks[$id] = $Block;
			// $striped_content .= substr($this->content, $striped_offset, $Block->offset - $striped_offset)."<!-- removed $striped_offset:$Block->offset $id";
			// $striped_offset = $Block->offset + $Block->len;
			// $striped_content .= " $striped_offset -->";
		}
		// $striped_content .= substr($this->content, $striped_offset);

		// $this->striped_content = $striped_content;

		preg_match_all("/{([a-zA-Z0-9_]+)}/U", $this->content, $m);

		$this->block_vars = $m[1];
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

	private function _get_block_or_self(string $ID = null): ?TemplateBlock {
		if(is_null($ID)){
			return $this;
		} else {
			return $this->_get_block($ID);
		}
	}

	private function _get_block(string $ID): ?TemplateBlock {
		if($this->ID == $ID){
			return $this;
		}

		if(isset($this->blocks[$ID])){
			return $this->blocks[$ID];
		}

		foreach($this->blocks as $o){
			if($block = $o->_get_block($ID)){
				return $block;
			}
		}

		return null;
	}
}
