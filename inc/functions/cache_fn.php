<?php
/**
 * All file with relations to @object Object_Cache 
 * @see /inc/classes/Object_Cache.php
 */
 
 /**
  * 
  */
function cache_init() {
	$GLOBALS['cache_obj'] = new Object_Cache();
}

/**

 */

function get_cache( $key, $group ) {
	global $cache_obj;
	
	if(is_null($cache_obj)) {
		return false;
	}
	
	return $cache_obj->get( $key, $group );
}

/**

 */

function add_cache( $key, $group, $data ) {
	global $cache_obj;
	
	if(is_null($cache_obj)) {
		return false;
	}	
	
	return $cache_obj->add($key,$group,$data);
}