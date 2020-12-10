<?php

namespace dqdp;

use Closure;

class Paginator
{
	var $max_pages;
	var $curr_page;
	var $q_params;
	var $url_builder;

	function __construct(Closure $url_builder){
		$this->url_builder = $url_builder;
	}

	private function params($page){
		return array_merge($this->q_params??[], ['page'=>$page]);
	}

	private function url($page, $name = null){
		return ($this->url_builder)($this, $this->params($page), $name??$page, $page);
	}

	function prev_page_url($name = '[Prev]'){
		if($this->curr_page > 1){
			return $this->url($this->curr_page - 1, $name);
		} else {
			return $name;
		}
	}

	function next_page_url($name = '[Next]'){
		if($this->curr_page < $this->max_pages){
			return $this->url($this->curr_page + 1, $name);
		} else {
			return $name;
		}
	}

	function first_page_url($name = '[First]'){
		if($this->curr_page != 1){
			return $this->url(1, $name);
		} else {
			return $name;
		}
	}

	function last_page_url($name = '[Last]'){
		if($this->curr_page != $this->max_pages){
			return $this->url($this->max_pages, $name);
		} else {
			return $name;
		}
	}

	function page_urls() {
		for($pi = 1; $pi<=$this->max_pages; $pi++){
			$ret[] = $this->url($pi);
		}
		return join(' ', ($ret??[]));
	}

	function paginator(){
		return join(" ", [
			$this->first_page_url(),
			$this->prev_page_url(),
			$this->page_urls(),
			$this->next_page_url(),
			$this->last_page_url(),
			]);
	}
}
