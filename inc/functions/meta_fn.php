<?php

/**
 * meta_fn.php
 * All function used to control meta's for website
 *
 */

if (!function_exists("update_metadata")) {
	/**
	 * @param string $table
	 * @param string $meta_special
	 * @param int $meta_special_val
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param int $meta_id
	 * @param bool $update (true => if meta_key aleardy exist we update value , false => insert new record with same meta_key)
	 * @return bool
	 */
	function update_metadata($meta_type, $object_name, $object_value, $meta_key, $meta_value = "", $update = true, $meta_id = null)
	{
		global $dsql;

		$data = ["meta_key" => $meta_key, "meta_value" => $meta_value];

		if (!empty($object_name)) {
			$data[$object_name] = $object_value;
		}

		$query = $dsql->dsql()->table($meta_type . '_meta')->set($data);

		$count = $dsql->dsql()->table($meta_type . '_meta')->where("meta_key", $meta_key);

		if (!empty($object_name)) {
			$count->where($object_name, $object_value);
		}

		$count->field("count(*)")->limit(1);
		
		if ($update === false || count_rows($count->getRow()) == 0) {
			$query->insert();
		} else {
			if (!empty($meta_id)) {
				$query->where("id", $meta_id);
			}
			$query->where("meta_key", $meta_key);
			if (!empty($object_name)) {
				$query->where($object_name, $object_value);
			}
			$query->update();
		}
		if ($query) {
			return true;
		}
		return false;
	}
}

if (!function_exists("update_post_meta")) {
	/**
	 *
	 */
	function update_post_meta($post_id, $meta_key, $meta_value = '', $update = true, $meta_id = null)
	{
		return update_metadata('post', 'post_id', $post_id, $meta_key, $meta_value, $update, $meta_id);
	}
}

if (!function_exists("update_user_meta")) {
	/**
	 *
	 */
	function update_user_meta($user_id, $meta_key, $meta_value = '', $update = true, $meta_id = null)
	{
		return update_metadata('user', 'user_id', $user_id, $meta_key, $meta_value, $update, $meta_id);
	}
}

if (!function_exists("update_meta_settings")) {
	/**
	 * update_meta_settings()
	 * update values from meta_settings table
	 *
	 * @param string $meta_key
	 * @param mixed $meta_value
	 * @param boolean $update
	 * @param int $meta_id
	 */
	function update_meta_settings($meta_key, $meta_value = "", $update = true, $meta_id = null)
	{

		if (empty($meta_key)) {
			return false;
		}

		global $dsql;
		$data = ["meta_key" => $meta_key, "meta_value" => $meta_value];
		$count = $dsql->dsql()->table('meta_settings')->where('meta_key', $meta_key)->field('count(*)', 'records')->limit(1)->getRow()['records'];
		if ($update === false || $count == 0) {
			$insert_meta = $dsql->dsql()->table('meta_settings')->set($data)->insert();
			if ($insert_meta === false) return false;
		} else {
			$update_meta = $dsql->dsql()->table('meta_settings')->set(["meta_value" => $meta_value])->where('meta_key', $meta_key);
			if (!empty($meta_id)) {
				$update_meta->where('id', $meta_id);
			}
			$update_meta = $update_meta->update();
			if ($update_meta === false) return false;
		}

		return true;
	}
}

if (!function_exists("update_meta_options")) {
	/**
	 * update_meta_options()
	 * update values from meta_options table
	 *
	 * @param string $option_name
	 * @param mixed $meta_value
	 * @param boolean $update
	 * @param int $meta_id
	 */
	function update_meta_options($option_name, $option_value = "", $update = true, $meta_id = null)
	{

		if (empty($option_name)) {
			return false;
		}

		global $dsql;

		$data = ["option_name" => $option_name, "option_value" => $option_value];
		$count = $dsql->dsql()->table('meta_options')->where('option_name', $option_name)->field('count(*)', 'records')->limit(1)->getRow()['records'];

		if ($update === false || $count == 0) {

			$insert_meta = $dsql->dsql()->table('meta_options')->set($data)->insert();
			if (!$insert_meta) return false;
		} else {
			$update_meta = $dsql->dsql()->table('meta_options')->set(["option_value" => $option_value])->where('option_name', $option_name);
			if (!empty($meta_id)) {
				$update_meta->where('id', $meta_id);
			}
			$update_meta = $update_meta->update();
			if (!$update_meta) return false;
		}
		return true;
	}
}

if (!function_exists("get_user_meta")) {
	/**
	 * @param string $meta_key
	 * @return mixed
	 */
	function get_user_meta($user_id, $meta_key, $single = true, $return_meta_id = false)
	{
		return get_metadata('user', $user_id, $meta_key, $single, $return_meta_id);
	}
}

if (!function_exists("get_post_meta")) {
	/**
	 * @param string $meta_key
	 * @return mixed
	 */
	function get_post_meta($post_id, $meta_key, $single = true, $return_meta_id = false)
	{
		return get_metadata('post', $post_id, $meta_key, $single, $return_meta_id);
	}
}

if (!function_exists("get_boot_meta")) {
	/**
	 * @param string $meta_key
	 * @return mixed
	 */
	function get_boot_meta($boot_id, $meta_key, $single = true, $return_meta_id = false)
	{
		return get_metadata('boot', $boot_id, $meta_key, $single, $return_meta_id);
	}
}

if (!function_exists("get_settings")) {
	/**
	 * @param string $meta_key
	 * @return mixed
	 */
	function get_settings($meta_key, $single = true)
	{

		$get_cache = get_cache($meta_key, 'settings');
		if ($get_cache && isset($get_cache[$meta_key])) {
			return $get_cache[$meta_key];
		}

		global $dsql;

		$query = $dsql->dsql()->table('meta_settings')->where("meta_key", $meta_key);

		$query->field("meta_value");

		if (!$single) {
			$query->field('meta_key');
		}

		$results = $query->get();

		if ($results) {
			add_cache($meta_key, 'settings', $results[0]["meta_value"]);
			if ($single) {
				$results = $results[0]['meta_value'];
			} else {
				$results = array_column($results, 'meta_value');
			}
			add_cache($meta_key, 'settings', $results);
		}

		return $results;
	}
}

if (!function_exists("get_option")) {
	/**
	 * return meta_options option_value value
	 *
	 * @param string $option_name
	 * @param string $option_value
	 * @return mixed (string on success|boolean on failure)
	 */
	function get_option($option_name, $option_value = "")
	{

		if (empty($option_name)) {
			return false;
		}

		$get_cache = get_cache($option_name, 'options');

		if ($get_cache && isset($get_cache[$option_name])) {
			return $get_cache[$option_name];
		}

		global $dsql;
		$get_meta = $dsql->dsql()->table('meta_options')->where('option_name', $option_name)->field('option_value')->getRow();
		if ($get_meta) {
			add_cache($option_name, 'options', $get_meta["option_value"]);
			return $get_meta["option_value"];
		}
		return false;
	}
}

if (!function_exists('remove_metadata')) {
	/**
	 *
	 * @param	string 	$meta_type
	 * @param	int		$object_id
	 * @param	mixed	$meta_key
	 * @param	mixed	$meta_value
	 */
	function remove_metadata($meta_type, $object_id, $meta_key = '', $meta_value = '', $meta_id = null)
	{

		global $dsql;

		$query = $dsql->dsql()->table($meta_type . '_meta')->where($meta_type . '_id', $object_id);

		if (!empty($meta_key)) {
			$query->where('meta_key', $meta_key);
		}

		if (!empty($meta_value)) {
			$query->where('meta_value', $meta_value);
		}

		if (!empty($meta_id)) {
			$query->where('id', $meta_id);
		}

		return $query->delete();
	}
}

if (!function_exists("remove_post_meta")) {
	/**
	 *
	 */
	function remove_post_meta($post_id, $meta_key, $meta_value = "")
	{
		return remove_metadata('post', $post_id, $meta_key, $meta_value);
	}
}

if (!function_exists("remove_user_meta")) {
	/**
	 *
	 */
	function remove_user_meta($user_id, $meta_key = '', $meta_value = '', $meta_id = null)
	{
		return remove_metadata('user', $user_id, $meta_key, $meta_value, $meta_id);
	}
}
