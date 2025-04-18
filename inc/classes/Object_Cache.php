<?php

/**
 * 
 */
class Object_Cache {
	
	private $cache = [];
	
	public function add( $key = '123', $group = 'default', $data = null ) {
		
		if(empty($group)) {
			$group = 'default';
		}
		
		return $this->set( $key, $group, $data );
		
	}
	
	public function set( $key, $group, $data ) {
		$this->cache[$group][$key] = $data;
		return true;
	}
	
	public function get( $key, $group = 'default' ) {
		if(isset($this->cache[$group][$key])) {
			return $this->cache[$group][$key];
		}
		
		return false;
	}

	public function get_all() {
		return $this->cache;
	}
	
	public function replace() {
		
	}
	
}