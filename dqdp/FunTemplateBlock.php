<?php declare(strict_types = 1);

namespace dqdp;

use InvalidArgumentException;
use ParseError;

class FunTemplateBlock
{
	private string $ID = '';
	private ?int $offset_start = null;    // where block starts
	private ?int $offset_end = null;      // where block ends
	private ?int $len = null;             // block length
	private bool $disabled = false;
	/** @var FunTemplateBlock[] */
	private array $blocks = [];
	private array $vars = [];
	private array $block_vars = [];
	private string $content = '';
	private ?FunTemplateBlock $parent = null;
	private $parser;

	function __construct(FunTemplateBlock $parent = NULL, string $ID, string $content){
		$this->ID = $ID;
		$this->parent = $parent;
		$this->content = $content;
		$this->__find_blocks();
	}

	function get_id(){
		return $this->ID;
	}

	function block_exists(string $ID): bool {
		return (bool)$this->_get_block($ID);
	}

	function get_block(string $ID): FunTemplateBlock {
		if($block = $this->_get_block($ID)){
			return $block;
		}

		throw new InvalidArgumentException("block not found ($ID)");
	}

	function set_parser(callable $func = null) {
		$this->parser = $func;
	}

	function out(){
		if($this->parser){
			$this->parser->__invoke($this);
		}

		if($this->disabled){
			return;
		}

		$offset = 0;
		foreach($this->blocks as $block){
			// print "[aaa:$offset:$block->offset_start:";
			$this->_apply_vars(substr($this->content, $offset, $block->offset_start - $offset));
			// print ":aaa]\n";
			$block->out();
			$offset = $block->offset_end;
		}
		// print "[bbb:";
		print $this->_apply_vars(substr($this->content, $offset));
		// print ":bbb]\n";
	}

	function parse(): string {
		if($this->disabled){
			return '';
		}

		$parsed_content = $this->content;
		foreach($this->blocks as $id=>$block){
			// No white-space check
			// $patt = "/<!-- BEGIN $id .*-->.*<!-- END $id -->/smU";
			$patt = "/\R?<!-- BEGIN $id .*-->.*<!-- END $id -->\R?/s";
			if(preg_match($patt, $parsed_content, $m, PREG_OFFSET_CAPTURE)){
				$offset = (int)$m[0][1];
				$len = strlen($m[0][0]);
				$parsed_content = substr_replace($parsed_content, $block->parse(), $offset, $len);
			}
		}

		return $this->_apply_vars($parsed_content);
	}

	function get_vars(): array {
		return $this->vars;
	}

	function get_var(string $var_id){
		if(isset($this->vars[$var_id])) {
			return $this->vars[$var_id];
		} elseif($this->parent) {
			return $this->parent->get_var($var_id);
		}

		return NULL;
	}

	function set_var(string $var_id, $value): FunTemplateBlock {
		$this->vars[$var_id] = $value;

		return $this;
	}

	function set_array(iterable $array): FunTemplateBlock {
		foreach($array as $k=>$v){
			$this->vars[$k] = $v;
		}

		return $this;
	}

	function set_except(array $exclude, array $data): FunTemplateBlock {
		$diff = array_diff(array_keys($data), $exclude);
		foreach($diff as $k){
			$this->vars[$k] = $data[$k];
		}

		return $this;
	}

	function enable_if(bool $cond, string $ID = NULL): FunTemplateBlock {
		return $this->set_attribute('disabled', !$cond, $ID);
	}

	function enable(string $ID = NULL): FunTemplateBlock {
		return $this->set_attribute('disabled', false, $ID);
	}

	function disable(string $ID = NULL): FunTemplateBlock {
		return $this->set_attribute('disabled', true, $ID);
	}

	function set_attribute(string $attribute, $value, string $ID = NULL): FunTemplateBlock {
		if($block = $this->_get_block_or_self($ID)){
			if($attribute == 'disabled'){
				$block->disabled = $value;
			}
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

	function dump(){
		$vars = [
			'ID', 'disabled', 'offset_start', 'offset_end', 'len',
			'vars', 'block_vars', 'blocks', 'content'
		];

		foreach($vars as $k){
			if($k == 'blocks'){
				// $ret[$k] = '['.join(', ', array_keys($this->blocks)).']';
				$ret[$k] = array_keys($this->blocks);
				foreach($this->blocks as $id=>$block){
					$ret["block:$id"] = $block->dump();
				}
			} else {
				$ret[$k] = $this->{$k};
			}
		}

		return $ret;
	}

	private function _apply_vars($data): string {
		if(empty($this->block_vars)){
			return $data;
		}

		foreach($this->block_vars as $k){
			$patt[] = '{'.$k.'}';
			$repl[] = $this->get_var($k);
		}

		return str_replace($patt, $repl, $data);
	}

	private function __find_blocks(){
		$m_WHOLE = 0;
		$m_ID = 1;
		$m_ATTRS = 2;
		$m_CONTENTS = 3;

		// $patt = '/(<!-- BEGIN ([\S]+) (.*)-->)(.*)(<!-- END \2 -->)/smUS';
		$patt = '/<!-- BEGIN ([\S]+) (.*)-->(.*)<!-- END \1 -->/smU';

		if(preg_match_all($patt, $this->content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) === false){
			$err = array_flip(array_filter(get_defined_constants(true)['pcre'], function ($value) {
				return substr($value, -6) === '_ERROR';
			  }, ARRAY_FILTER_USE_KEY))[preg_last_error()];

			throw new ParseError(sprintf("template compilation failure $this->ID (%s)", $err));
		}

		$striped_offset = 0;
		$striped_content = '';
		foreach($matches as $item){
			$id = $item[$m_ID][0];

			if(isset($this->blocks[$id])){
				$content_offset = (int)$item[$m_CONTENTS][1];
				throw new InvalidArgumentException(
					sprintf("block already exists ($id), at %d near: '%s'",
					$item[$m_WHOLE][1],
					substr($this->content, $content_offset - 20, 40))
				);
			}

			$Block = new FunTemplateBlock($this, $id, $item[$m_CONTENTS][0]);
			$Block->len = strlen($item[$m_WHOLE][0]);
			$Block->offset_start = (int)$item[$m_WHOLE][1];
			$Block->offset_end = $Block->offset_start + $Block->len;

			$Block->disabled = (strpos($item[$m_ATTRS][0], 'disabled') !== false);

			$this->blocks[$id] = $Block;

			$part = substr($this->content, $striped_offset, $Block->offset_start - $striped_offset);
			$striped_content .= $part;
			$striped_offset = $Block->offset_end;
		}
		$part = substr($this->content, $striped_offset);
		$striped_content .= $part;

		# Vars
		if(preg_match_all("/{([^\s}]+)}/", $striped_content, $m)){
			$this->block_vars = array_unique($m[1]);
		}
	}

	protected function error($msg, $e = E_USER_WARNING){
		$tmsg = '';
		$t = debug_backtrace();
		for($i=1;$i<count($t);$i++){
			$bn = basename($t[$i]['file']);
			if($bn == 'FunTemplateBlock.php' || $bn == 'Template.php'){
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

	private function _get_block_or_self(string $ID = null): ?FunTemplateBlock {
		if($ID){
			return $this->_get_block($ID);
		} else {
			return $this;
		}
	}

	private function _get_block(string $ID): ?FunTemplateBlock {
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
