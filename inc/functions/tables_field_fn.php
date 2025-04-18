<?php

/** 
 * All function that get column from some table
 * get_{table_name}_field()
 */

if (!function_exists('get_table_field')) {
	/**
	 *
	 * @param	string	$table
	 * @param	string	$field
	 * @param	mixed	$where_value
	 * @param	string	$where_field
	 */
	function get_table_field($table, $field, $where_value, $where_field = 'id')
	{

		global $dsql;

		$query = $dsql->dsql()->table($table)->where($where_field, $where_value)->field($field);
		$result = $query->getRow();
		if (!is_array($field)) {
			$result = $result[$field];
		}
		return $result;
	}
}

if (!function_exists("get_post_field")) {
	/**
	 * get_post_field()
	 * select field from posts table
	 *
	 * @param int $post_id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_post_field($post_id, $field)
	{

		return get_table_field('posts', $field, $post_id);
	}
}

if (!function_exists("get_post_info_field")) {
	/**
	 * get_post_info_field()
	 * select field from posts table
	 *
	 * @param int $id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_post_info_field($id, $field)
	{

		return get_table_field('post_info', $field, $id);
	}
}

if (!function_exists("get_badge_field")) {
	/**
	 * get_badge_field()
	 * select field from badges table
	 *
	 * @param int $post_id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_badge_field($badge_id, $field)
	{

		return get_table_field('badges', $field, $badge_id);
	}
}

if (!function_exists("get_language_field")) {
	/**
	 * get_language_field()
	 * select field from languages table
	 *
	 * @param int $post_id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_language_field($lang_code, $field)
	{

		return get_table_field('languages', $field, $lang_code, 'lang_code');
	}
}

if (!function_exists("get_message_field")) {
	/**
	 * get_message_field()
	 * select field from messages_sys table
	 *
	 * @param int $msg_id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_message_field($msg_id, $field)
	{
		return get_table_field('messages_sys', $field, $msg_id);
	}
}

if (!function_exists("get_file_field")) {
	/**
	 * get_file_field()
	 * select field from posts table
	 *
	 * @param int $file_id
	 * @param string $field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_file_field($file_id, $field)
	{

		return get_table_field('files', $field, $file_id);
	}
}

if (!function_exists("get_ad_field")) {
	/**
	 * get_ad_field()
	 * select field from posts table
	 *
	 * @param int $ad_id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_ad_field($ad_id, $field)
	{

		return get_table_field('ads', $field, $ad_id);
	}
}

if (!function_exists("get_page_field")) {
	/**
	 * get_page_field()
	 * select field from pages table
	 *
	 * @param int $page_id
	 * @param string $field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_page_field($page_id, $field)
	{

		return get_table_field('pages', $field, $page_id);
	}
}

if (!function_exists("get_category_field")) {
	/**
	 * get_category_field()
	 * select field from categories table
	 *
	 * @param int $cat_id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_category_field($cat_id, $field)
	{

		return get_table_field('categories', $field, $cat_id);
	}
}

if (!function_exists("get_comment_field")) {
	/**
	 * get_comment_field()
	 * select field from comments table
	 *
	 * @param int $comment_id
	 * @param string field
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_comment_field($comment_id, $field)
	{

		return get_table_field('comments', $field, $comment_id);
	}
}

if (!function_exists("get_user_field")) {
	/**
	 * get_user_field()
	 * select field from users table
	 * 
	 * @param int $object_value
	 * @param string $field
	 * @return mixed (string on success|boolean on failure)
	 */
	function get_user_field($object_value, $field, $where_field = 'id')
	{

		return get_table_field('users', $field, $object_value, $where_field);
	}
}

if (!function_exists("get_author_field")) {
	/**
	 * get_author_field()
	 * select field from users table
	 * 
	 * @param int $object_value
	 * @param string $field
	 * @return mixed (string on success|boolean on failure)
	 */
	function get_author_field($object_value, $field, $where_field = 'id')
	{

		return get_table_field('authors', $field, $object_value, $where_field);
	}
}

if (!function_exists("get_author_field")) {
	/**
	 * get_author_field()
	 * select field from users table
	 * 
	 * @param int $object_value
	 * @param string $field
	 * @return mixed (string on success|boolean on failure)
	 */
	function get_author_field($field, $where_value, $where_field = 'id')
	{
		global $dsql;

		$query = $dsql->dsql()->table('authors')->where($where_field, $where_value)->field($field);
		$result = $query->getRow();
		if (!is_array($field)) {
			$result = $result[$field];
		}
		return $result;
	}
}

if (!function_exists("get_translator_field")) {
	/**
	 * get_translator_field()
	 * select field from users table
	 * 
	 * @param int $object_value
	 * @param string $field
	 * @return mixed (string on success|boolean on failure)
	 */
	function get_translator_field($field, $where_value, $where_field = 'id')
	{
		global $dsql;

		$query = $dsql->dsql()->table('translators')->where($where_field, $where_value)->field($field);
		$result = $query->getRow();
		if (!is_array($field)) {
			$result = $result[$field] ?? false;
		}
		return $result;
	}
}
