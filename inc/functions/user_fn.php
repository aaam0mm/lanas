<?php

/**
 * Che
 */

if (!function_exists("dd")) {
	/**
	 * dd()
	 */
	function dd($datas, $exit_stat = true)
	{
?>
		<div style="overflow: auto;display: flex;justify-content: flex-start; align-items:left; width: 100vw; height: 100vh; position:fixed; z-index: 5555; background-color:#000; color:#FFF; padding: 1rem; left: 0; top: 0;">
			<pre style="width: 100%; height: 100%; color:#FFF !important">
						<?= var_dump($datas); ?>
				</pre>
		</div>
		<?php
		if ($exit_stat) {
			exit;
		}
	}
}

function is_user_id($input)
{
	global $dsql;

	return $dsql->dsql()->table('users')->where('id', $input)->getRow();
}

function is_author_id($input)
{
	global $dsql;

	return $dsql->dsql()->table('authors')->where('id', $input)->getRow();
}

/**
 * Save/update user
 * 
 * @param	array	$fields
 * @return array on success otherwise false
 */
function save_user($fields)
{

	$user_role = $fields["user_role"] ?? get_option("new_members_role");

	if (empty($user_role)) {
		return false;
	}

	$user_email = $fields["user_email"] ?? "";
	if (is_user_email_exist($user_email)) {
		return false;
	}

	$username = $fields["username"] ?? "";
	if (is_username_exist($username)) {
		return false;
	}


	$user_gender = $fields["user_gender"] ?? "";
	if (!in_array($user_gender, ["male", "female"])) {
		return false;
	}

	$user_picture = $fields["user_picture"] ?? "";
	if (!absint($user_picture)) {
		$user_picture = $user_gender == 'male' ? get_option('default_male_picture') : get_option('default_female_picture');
	}

	$user_id = (int) ($fields["user_id"] ?? null);
	$user_name = $fields["user_name"] ?? "";
	$user_pwd = $fields["user_pwd"] ?? "";
	$user_status = $fields["user_status"] ?? "active";
	$user_country = $fields["user_country"] ?? "";
	$user_lang = $fields["user_lang"] ?? current_content_lang();
	$birth_date = $fields["birth_date"] ?? "0000-00-00";
	$username = preg_replace('/\s+/', '', $username);
	$user_meta = [
		"points_remaining" => points_manage("openaccount", "add"),
		"points_consumed" => 0,
		"user_current_lang" => current_lang(),
		"notifs_settings" => json_encode([
			'comment' => 'on',
			'reply' => 'on',
			'subscribe' => 'on',
			'unsubscribe' => 'on',
			'reaction' => 'on',
			'email' => 'on',
			'rate' => 'on'
		])
	];

	$user_meta = @array_merge($user_meta, @$fields["user_meta"]);

	$data = [
		"username" => $username,
		"user_name" => $user_name,
		"user_email" => $user_email,
		"user_role"  => $user_role,
		"user_gender" => $user_gender,
		"user_picture" => $user_picture,
		"user_pwd" => $user_pwd,
		"user_status" => $user_status,
		"user_verified" => "no",
		"user_country" => $user_country,
		"user_lang" => $user_lang,
		"user_lastseen" => "1970-01-01 00:00:00",
		"birth_date" => $birth_date
	];

	if (empty($user_id)) {
		$data["user_joindate"] = gmdate("Y-m-d H:i");
	}

	if (empty($user_id)) {
		$data["user_login_identify"] = generateRandomString(64);
	}

	global $dsql;

	$query = $dsql->dsql()->table('users')->set($data);
	if (absint($user_id)) {
		$query->where('id', $user_id)->update();
	} else {
		$query->insert();
		$data["user_id"] = get_last_inserted_id();
	}

	foreach ($user_meta as $meta_key => $meta_value) {
		update_user_meta($data["user_id"], $meta_key, $meta_value);
	}
	return $data;
}

/**
 * 
 */
function user_social_media_signin($data)
{

	$platform = $data["platform"] ?? null;
	if (!in_array($platform, SOCIAL_LOGIN)) {
		return false;
	}

	$identifier = $data["identifier"] ?? "";
	$user_name = $data["displayName"] ?? "";
	$user_email = $data["email"] ?? "";
	$user_gender = isset($data["gender"]) && !empty($data["gender"]) ? $data["gender"] : "male";

	if (is_user_email_exist($user_email)) {
		$user_login_identify = get_user_field($user_email, 'user_login_identify', 'user_email');
		if (empty($user_login_identify)) {
			return false;
		}

		$user_id = get_user_field($user_email, 'id', 'user_email');
		$platform = get_user_meta($user_id, "use_" . $platform);

		if (empty($platform)) {
			return false;
		}

		return set_user_cookie($user_login_identify);
	}

	$data = [
		"user_name" => $user_name,
		"username" => time(),
		"user_email" => $user_email,
		"user_gender" => $user_gender,
		"user_meta" => [
			"use_" . $platform => $platform
		]
	];

	return save_user($data);
}

/**
 * 
 * @return true on failure otherwise false
 */
function is_user_email_exist($email, $user_id = null): bool
{
	$email = filter_var($email, FILTER_VALIDATE_EMAIL);
	if (!$email) {
		return true;
	}

	global $dsql;
	$query = $dsql->dsql()->table('users')->where('user_email', $email);
	if ($user_id) {
		$query->where('id', '!=', $user_id);
	}

	return $query->getRow() === false ? false : true;
}

/**
 * 
 */
function is_username_exist($username, $user_id = null)
{

	global $dsql;

	$query = $dsql->dsql()->table('users')->where('username', $username);

	if ($user_id) {
		$query->where('id', '!=', $user_id);
	}

	return $query->getRow() === false ? false : true;
}

/**
 * 
 */
function is_username_valid($username)
{
	return mb_strlen($username) > 4;
}

/**
 * 
 */
function is_user_pwd_valid($pwd)
{
	return mb_strlen($pwd) < 16 && mb_strlen($pwd) > 6;
}

/**
 * 
 */
function is_user_name_valid($user_name)
{
	return mb_strlen($user_name) > 4 &&  mb_strlen($user_name) < 25;
}

/**
 * @param string $cookie_value
 * @param string $expiry
 *
 * @return boolean
 */
function set_user_cookie($cookie_value, $expiry = "+30 days")
{
	return setcookie("user", $cookie_value, strtotime($expiry), "/", "", is_ssl(), true);
}

if (!function_exists('get_posts_comments')) {
	/**
	 *
	 */
	function get_posts_comments($user_posts = false, $user_id = null, $order_by = 'desc')
	{

		global $dsql;

		$get_comments = $dsql->dsql()->table('comments')->join('posts.id', 'post_id');
		if ($user_posts && $user_id) {
			$get_comments->where('posts.post_author', $user_id);
		} elseif ($user_id) {
			$get_comments->where('comments.comment_user', $user_id);
		}

		$get_comments->field($get_comments->expr('SQL_CALC_FOUND_ROWS comments.*'))->limit(paged('end'), paged('start'))->order('id', $order_by);

		$get_comments = $get_comments->get();
		return $get_comments;
	}
}

if (!function_exists("get_user_link")) {
	/**
	 * 
	 */
	function get_user_link($user)
	{

		if (is_object($user)) {
			$user_id = $user->id;
		} else {
			$user_id = $user;
		}

		return siteurl() . '/user/' . $user_id;
	}
}

if (!function_exists('get_user_bookmared_posts_id')) {
	/**
	 * @param	int	$user_id
	 * @return 
	 */
	function get_user_bookmared_posts_id($user_id)
	{

		global $dsql;

		$current_user = get_current_user_info();

		$query = $dsql->dsql()->table('user_meta')->where('meta_key', 'LIKE', 'post_bookmark_%')->where('user_id', $current_user->id)->field('meta_value');

		$results = $query->get();
		if ($results) {
			return array_column($results, 'meta_value');
		}
		return $results;
	}
}

if (!function_exists("logout")) {
	/**
	 * logout()
	 
	 */
	function logout()
	{
		if (!is_login_in()) {
			return false;
		}
		$user_cookie = @filter_input(INPUT_COOKIE, "user", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		if (!$user_cookie or strlen($user_cookie) !== 64) {
			return false;
		}
		if (setcookie('user', null, -1, '/')) {
			return true;
		}
		return false;
	}
}

if (!function_exists("count_user_posts")) {
	/**
	 * count_user_posts()
	 *
	 * @param int $user_id
	 * @return mixed (int on success|boolean on failure)
	 */
	function count_user_posts($user_id, $post_in = false)
	{

		$user_id = absint($user_id);

		if (empty($user_id)) {
			return false;
		}

		global $dsql;

		$count_user_posts = $dsql->dsql()->table('posts')->where('post_author', $user_id)->where('post_status', 'publish');

		if (!is_bool($post_in)) {
			$count_user_posts->where('post_in', $post_in);
		}

		$count_user_posts->field('count(*)');
		$count_user_posts = $count_user_posts->getRow();
		return count_rows($count_user_posts);
	}
}

if (!function_exists("count_author_posts")) {
	/**
	 * count_author_posts()
	 *
	 * @param int $user_id
	 * @return mixed (int on success|boolean on failure)
	 */
	function count_author_posts($author_name)
	{

		global $dsql;

		if(!$author_name) {
			return false;
		}

		$query = $dsql->dsql()->table('posts');

		$query->join('post_meta', $query->expr("post_meta.post_id = posts.id"), 'inner')
			->where('posts.post_type', 'book')
			->where('post_meta.meta_key', 'book_author')
			->where('post_meta.meta_value', $author_name);

		$query->field('count(*)');

		$query = $query->getRow();
		return count_rows($query);
	}
}

if (!function_exists("get_users")) {
	/**
	 * get_users()
	 *
	 * @param string $user_name
	 * @param string $sort_by
	 * @param string $monotony
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_users($sort_by = null, $monotony = "desc", $filter = false)
	{

		$monotony = strtolower($monotony);
		if (!in_array($monotony, ['desc', 'asc'])) {
			return false;
		}

		global $dsql;

		$limit = $filter['limit'] ?? null;
		$q = $filter['q'] ?? null;

		$query = $dsql->dsql()->table('users')->where('users.user_status', 'active');

		if ($q) {
			$query->where('users.user_name', 'LIKE', "%" . $q . "%");
		}

		switch ($sort_by) {
			case "posts":
				$query->join('posts.post_author', 'users.id')->where('posts.post_status', 'publish')->where('posts.post_in', 'trusted')->order('count(posts.id)', $monotony);
				break;
			case "points":
				// $query->join('user_meta.user_id')->where('meta_key', 'points_remaining')->order($query->expr("CAST(user_meta.meta_value AS UNSIGNED) {$monotony}"));
				$query->join('user_meta.user_id')->where('meta_key', 'points_remaining')->order($query->expr("CAST(MAX(user_meta.meta_value) AS UNSIGNED) {$monotony}"));
				break;
			case "post_views":
				$query->join('posts.post_author')->where('posts.post_status', 'publish')->group('posts.post_author')->order($query->expr("SUM(post_views) {$monotony} "));
				break;
			case "post_share":
				$query->join('posts.post_author')->where('posts.post_status', 'publish')->group('posts.post_author')->order($query->expr("SUM(post_share) {$monotony} "));
				break;
			case "most_followed":
				$query->join('subscribe_sys.user_id', 'users.id')->order('subscribe_sys.id')->order('count(subscribe_sys.user_id)', $monotony);
				break;
			case "active":
				$query->order('user_lastseen', 'desc');
				break;
			case "active_today":
				$query->where($query->expr('DATE(users.user_lastseen) = CURDATE()'));
				break;
			case "rand()":
				$query->order($query->expr('rand()'));
				break;
			default:
				$query->order('users.id', $monotony);
		}

		$query->group('users.id');

		$query->field($query->expr('SQL_CALC_FOUND_ROWS users.*'));

		// Return query object if we want to filter more
		if ($filter  === true) {
			return $query;
		}

		// Count all users matched 
		//$query_count = count_rows($query_count->getRow());

		//$query->reset('getRow')->reset('field');
		if($limit == 'all') {
			goto no_limit;
		}
		if ($limit) {
			$query->limit($limit);
		} else {
			$query->limit(paged('end'), paged('start'));
		}
		no_limit:

		$results = $query->get();

		return ["rows" => count_last_query(), "results" => $results];
	}
}

if (!function_exists("un_verify_user")) {
	/**
	 * un_verify_user()
	 *
	 * @param int $user_id
	 * @param boolean $default_status
	 * @return boolean
	 */
	function un_verify_user($user_id, $default_status = false)
	{

		if (admin_authority()->users != "on") {
			return false;
		}

		if (empty($user_id) || $user_id == 1) {
			return false;
		}

		global $dsql;
		$user_verified = get_user_field($user_id, "user_verified");
		$verified = "";
		if ($user_verified == "yes") {
			$verified = "no";
		} else {
			$verified = "yes";
		}

		/** Specific case this case is reserved for multi_action() function */
		if ($default_status) {
			if ($default_status == "verify") {
				$default_status = 'yes';
			} else {
				$default_status = 'no';
			}
			$verified = $default_status;
		}

		$update = $dsql->dsql()->table('users')->set(["user_verified" => $verified])->update();
		if ($update) {
			return true;
		}
		return false;
	}
}

if (!function_exists("un_lock_user")) {
	/**
	 * un_lock_user()
	 *
	 * @param int $user_id
	 * @param boolean $default_status
	 * @return boolean
	 */
	function un_lock_user($user_id, $default_status = false)
	{

		if (admin_authority()->users != "on") {
			return false;
		}

		if (empty($user_id) || $user_id == 1) {
			return false;
		}

		global $dsql;

		$user_status = get_user_field($user_id, "user_status");
		$status = "";
		if ($user_status == "active") {
			$status = "blocked";
		} else {
			$status = "active";
		}

		/** Specific case this case is reserved for multi_action() function */
		if ($default_status) {
			if ($default_status == "publish") {
				$default_status = 'active';
			} else {
				$default_status = 'blocked';
			}
			$status = $default_status;
		}

		$update = $dsql->dsql()->table('users')->set(["user_status" => $status])->where('id', $user_id)->update();

		if ($update) {
			return true;
		}
		return false;
	}
}

if (!function_exists("is_login_in")) {
	/**
	 * is_login_in()
	 * Check whether user is login or not & if account is active
	 *
	 * @return boolean
	 */
	function is_login_in()
	{
		$user_cookie = @filter_input(INPUT_COOKIE, "user", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		if (!$user_cookie) {
			return false;
		}
		if (strlen($user_cookie) !== 64) {
			return false;
		}
		global $dsql;
		$check_user = $dsql->dsql()->table('users')->where('user_login_identify', $user_cookie)->where('user_status', ['active', 'email_verify'])->field('count(*)', 'records')->limit(1)->getRow();
		if ($check_user['records'] == 1) {
			return true;
		}
		return false;
	}
}

if (!function_exists("is_admin")) {
	/**
	 * is_admin()
	 * Check if user has permission to access admin panel
	 *
	 * @return boolean
	 */
	function is_admin()
	{
		if (@user_authority()->access_adminpanel == true || is_super_admin()) {
			return true;
		}
		return false;
	}
}

function get_user_id_from_username($username)
{

	global $dsql;

	$query = $dsql->dsql()->table('users')->where('username', $username)->field('id');

	$result = $query->getRow();

	return $result['id'];
}

function get_author_id_from_name($name)
{

	global $dsql;

	$query = $dsql->dsql()->table('authors')->where('name', $name)->field('id');

	$result = $query->getRow();

	return $result['id'];
}

function get_user_id_from_user_name($username)
{

	global $dsql;

	$query = $dsql->dsql()->table('users')->where('user_name', $username)->field('id');

	$result = $query->getRow();

	return $result['id'];
}

if (!function_exists("get_user_info")) {
	/**
	 * get_user_info()
	 *
	 * @param int|string $user_id
	 * @param string $info
	 * @return object on success|boolean on failure
	 */
	function get_user_info($user_id, $info = null)
	{

		global $dsql;

		$user_cache = get_cache($user_id, 'users_info');
		if ($user_cache) {
			return (object) $user_cache;
		}

		$user_info = $dsql->dsql()->table('users')->where('id', $user_id)->limit(1)->getRow();

		/**
		 * @$user_cache return 'users' table info but we don't know if it will return some fields that needed when call get_user_info()
		 * So we check if @$user_cache contain this fields. if not we request it from database and store in cache
		 */

		if (!isset($user_info['post_of_today'])) {
			$post_of_today = $dsql->dsql()->table('posts')->where('post_author', $user_id)->where('post_status', 'not in', ['auto-draft']);
			$post_of_today = $post_of_today->where('post_date_gmt', '<', $post_of_today->expr('DATE_SUB(CURRENT_DATE(), INTERVAL -1 DAY)'))->where('post_date_gmt', '>', $post_of_today->expr('DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)'))->field('count(*)', 'records')->getRow();
			$user_info['post_of_today'] = $post_of_today['records'];
		}

		if (!isset($user_info['points_remaining'])) {
			$user_info['points_remaining'] = (float) get_user_meta($user_id, "points_remaining");
		}

		if (!isset($user_info['points_consumed'])) {
			$user_info['points_consumed'] = (float) get_user_meta($user_id, "points_consumed");
		}

		add_cache($user_id, 'users_info', $user_info);

		if (!is_null($info) && isset($user_info[$info])) {
			return $user_info[$info];
		}

		return (object) $user_info;
	}
}


if (!function_exists("get_author_info")) {
	/**
	 * get_author_info()
	 *
	 * @param int|string $author_id
	 * @param string $info
	 * @return object on success|boolean on failure
	 */
	function get_author_info($author_id, $info = null)
	{

		global $dsql;


		$author_info = $dsql->dsql()->table('authors')->where('id', $author_id)->limit(1)->getRow();

		// if (!isset($author_info['post_of_today'])) {
		// 	$post_of_today = $dsql->dsql()->table('posts')->where('post_author', $author_id)->where('post_status', 'not in', ['auto-draft']);
		// 	$post_of_today = $post_of_today->where('post_date_gmt', '<', $post_of_today->expr('DATE_SUB(CURRENT_DATE(), INTERVAL -1 DAY)'))->where('post_date_gmt', '>', $post_of_today->expr('DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)'))->field('count(*)', 'records')->getRow();
		// 	$author_info['post_of_today'] = $post_of_today['records'];
		// }

		if (!is_null($info) && isset($author_info[$info])) {
			return $author_info[$info];
		}

		return (object) $author_info;
	}
}


if (!function_exists("user_authority")) {
	/**
	 * user_authority()
	 * get user authories by user role
	 *
	 * @return mixed(object on success|boolean on failure)
	 */
	function user_authority($user_id = null)
	{

		if (empty($user_id)) {

			$get_current_user = get_current_user_info();
			if (!$get_current_user) {
				return false;
			}
			$user_id = $get_current_user->id;
		} else {
			$get_current_user = get_user_info($user_id);
		}
		$user_role = $get_current_user->user_role;
		$get_role = get_roles($user_role);
		if ($get_role === false) {
			return false;
		}
		
		$role_perm = json_decode($get_role["role_permissions"]);
		$authorities = [
			"upload" => $role_perm->upload ?? false,
			"move_multi_posts" => $role_perm->move_multi_posts ?? false,
			"post_per_day" => $role_perm->post_per_day ?? 0,
			"auto_approve" => $role_perm->auto_approve ?? false,
			"publish_in" => $role_perm->publish_in ?? "",
			"access_adminpanel" => $role_perm->access_adminpanel ?? false,
			"read_sources" => $role_perm->read_sources ?? false,
			"upload_links" => $role_perm->upload_links ?? false,
			"cv_badge" => $role_perm->cv_badge ?? false
		];

		return (object) $authorities;
	}
}

if (!function_exists("is_super_admin")) {
	/**
	 * is_super_admin()
	 * only user with ID = 1 in database (first user registered in website setup)
	 * 
	 * @param int $user_id
	 */
	function is_super_admin($user_id = null)
	{
		if (is_null($user_id)) {
			$current_user = get_current_user_info();
			if (!$current_user) {
				return false;
			}
			$user_ID = $current_user->id;
		} else {
			$user_ID = (int) $user_id;
		}

		if ($user_ID == 1) {
			return true;
		}
		return false;
	}
}

if (!function_exists("admin_authority")) {
	/**
	 * admin_authority()
	 * get admin authority
	 *
	 * @return mixed(object on success|boolean on failure)
	 */
	function admin_authority()
	{

		$current_user = get_current_user_info();
		if ($current_user === false) {
			return false;
		}
		$df = "off";
		if (is_super_admin()) {
			$df = "on";
		} else {
			$admin_perms = json_decode(get_user_meta($current_user->id, "user_permissions"));
		}

		$user_role = $current_user->user_role;
		$get_role = get_roles($user_role);

		if ($get_role === false) {
			return false;
		}

		$role_perm = json_decode($get_role["role_permissions"]);
		$admin_authorities = [
			"delete" => $role_perm->delete ?? $df,
			"statistics" => $admin_perms->statistics ?? $df,
			"users" => $admin_perms->users ?? $df,
			"external_links" => $admin_perms->external_links ?? $df,
			"taxonomies" => $admin_perms->taxonomies ?? $df,
			"points" => $admin_perms->points ?? $df,
			"files" => $admin_perms->files ?? $df,
			"posts" => $admin_perms->posts ?? $df,
			"authors" => $admin_perms->authors ?? $df,
			"books" => $admin_perms->books ?? $df,
			"contents" => $admin_perms->contents ?? $df,
			"ads" => $admin_perms->ads ?? $df,
			"categories" => $admin_perms->categories ?? $df,
			"pages" => $admin_perms->pages ?? $df,
			"social_accounts" => $admin_perms->social_accounts ?? $df,
			"badges" => $admin_perms->badges ?? $df,
			"comments" => $admin_perms->comments ?? $df,
			"general_settings" => $admin_perms->general_settings ?? $df,
			"advanced_settings" => $admin_perms->advanced_settings ?? $df,
			"contact" => $admin_perms->contact ?? $df,
			"complains" => $admin_perms->complains ?? $df,
			"countries" =>  $admin_perms->countries ?? $df,
			"languages_control" => $admin_perms->languages_control ?? "all",
			"boots" => $admin_perms->boots ?? $df
		];

		return (object) $admin_authorities;
	}
}
if (!function_exists("get_user_notifs")) {
	/**
	 * get_user_notifs()
	 *
	 * @param int $case 
	 * whether notification is has been seen or not
	 * $case == 1 : notification has been inserted/not delivered/not seen
	 * $case == 2 : notification has been inserted /delivered/not seen
	 * $case == 3 : notification has been inserted/delivered/seen

	 */
	function get_user_notifs($case = null)
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;

		$get_notifs = $dsql->dsql()->table('notifications_sys')->where('notif_to', $current_user->id);

		if (!is_null($case)) {
			$get_notifs->where('notif_case', $case);
		}

		$get_notifs->order('notif_date', 'desc')->limit(paged('end'), paged('start'));

		$get_notifs =  $get_notifs->get();

		return $get_notifs;
	}
}

if (!function_exists("count_user_notifs")) {
	/**
	 * count_user_notifs()
	 *
	 * @param int $case
	 * @return int
	 */
	function count_user_notifs($case = null)
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;

		$query = $dsql->dsql()->table('notifications_sys')->where('notif_to', $current_user->id);

		if (!is_null($case)) {
			$query->where('notif_case', $case);
		}

		$query->field('count(*)', 'records');

		return $query->getRow()['records'];
	}
}

if (!function_exists("count_user_msgs")) {
	/**
	 * count_user_msgs()
	 *
	 * @param string $read_case
	 * @return int
	 */
	function count_user_msgs($read_case = null)
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;

		$query = $dsql->dsql()->table('conversations_sys')->where('msg_to', $current_user->id);

		if (!is_null($read_case)) {
			$query->where('read_case', $read_case);
		}

		$query->field('count(*)', 'records');

		return $query->getRow()['records'];
	}
}

if (!function_exists("get_current_user_info")) {
	/**
	 * get_current_user_info()
	 * Get logged user info
	 *
	 * @return mixed(boolean on failure || object on success)
	 */
	function get_current_user_info()
	{
		if (!is_login_in()) {
			return false;
		}
		global $dsql;

		$user_cookie = filter_input(INPUT_COOKIE, "user", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		
		$get_user = $dsql->dsql()->table('users')->where('user_login_identify', $user_cookie)->limit(1)->getRow();
		
		if ($get_user) {
			$user_id = $get_user["id"];
			return get_user_info($user_id);
		}
		return false;
	}
}

if (!function_exists("user_cv_info_form")) {
	/**
	 * user_cv_info_form()
	 * set user cv form inputs 
	 * @see parts/dashboard/profile.php
	 *
	 * @param array $infos
	 * @return HTML markup
	 */
	function user_cv_info_form($infos)
	{
		$html = "";
		foreach ($infos as $prefix_name => $info) {
			$html .= '<div class="' . $prefix_name . ' profile-infos">';
			$html .= '<div class="d-flex flex-column">';
			$html .= '<form method="post" class="cv_form">';
			foreach ($info["inputs"] as $inp_name => $inp_value) {
				$html .= '<div class="form-group mb-5">';
				$html .= '<div class="d-flex">';
				$label = $inp_value["label"] ?? "";
				$length = $inp_value["length"] ?? "";
				$type = $inp_value["type"] ?? "text";
				$default_value = $inp_value["default_value"] ?? "";
				$value = $inp_value["value"] ?? "";
				$privacy = $inp_value["privacy"] ?? false;
				if (!empty($label)) {
					$html .= '<label for="" class="font-weight-bold">' . $label . '</label>';
				}
				$html .= '<div class="ml-auto mb-2">';
				$max_length_attr = '';
				if (!empty($length)) {
					$html .= '<span>' . $length . '</span>';
					$max_length_attr = 'maxlength="' . $length . '"';
				}
				$html .= '</div>';
				$html .= '</div>';
				if (!empty($value) && !is_array($default_value)) {
					$default_value = $value;
				}

				if ($type == "textarea") {
					$html .= '<textarea class="form-control ' . $prefix_name . '_' . $inp_name . '" name="cv[' . $prefix_name . '][' . $inp_name . ']" id="' . $inp_name . '" ' . $max_length_attr . '>' . $default_value . '</textarea/>';
				} elseif ($type == "select") {
					$html .= '<select name="cv[' . $prefix_name . '][' . $inp_name . ']" id="' . $inp_name . '" class="form-control custom-select">';
					foreach ($default_value as $opt_value => $opt_text) {
						$html .= '<option value="' . $opt_value . '">' . $opt_text . '</option>';
					}
					$html .= '</select>';
				} else {
					$html .= '<input type="' . $type . '" class="form-control ' . $prefix_name . '_' . $inp_name . '" name="cv[' . $prefix_name . '][' . $inp_name . ']" id="' . $inp_name . '" value="' . $default_value . '" ' . $max_length_attr . '/>';
				}
				$html .= '</div>';
			}

			if ($info["privacy"]) {
				$html .= '<div class="privacy-dropdown dropdown d-inline-block ml-3">';
				$html .= '<button class="btn btn-secondary dropdown-toggle rounded-0 ' . $prefix_name . '_dropdown-menu-privacy-selected" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
				$html .= '<i class="fas fa-globe"></i>&nbsp;<span>' . _t("الجميع") . '</span>';
				$html .= '</button>';
				$html .= '<div class="dropdown-menu rounded-0 dropdown-menu-privacy" data-select=".' . $prefix_name . '_dropdown-menu-privacy-selected">';
				$html .= '<span class="dropdown-item" data-value="public" data-input="#privacy-t-' . $prefix_name . '"><i class="fas fa-globe"></i>&nbsp;' . _t("الجميع") . '</span>';
				$html .= '<span class="dropdown-item" data-value="followers" data-input="#privacy-t-' . $prefix_name . '"><i class="fas fa-users"></i>&nbsp;' . _t("متابعون") . '</span>';
				$html .= '<span class="dropdown-item" data-value="only_me" data-input="#privacy-t-' . $prefix_name . '"><i class="fas fa-user-secret"></i>&nbsp;' . _t("أنا فقط") . '</span>';
				$html .= '</div>';
				$html .= '<input type="hidden" id="privacy-t-' . $prefix_name . '" class="' . $prefix_name . '_privacy" name="cv[' . $prefix_name . '][privacy]" value=""/>';
				$html .= '</div>';
			}
			$html .= '<input type="hidden" name="method" value="user_ajax"/>';
			$html .= '<input type="hidden" name="request" value="update_cv_info"/>';
			$html .= '</form>';
			$html .= '</div>';
			$html .= '</div>';
		}
		return $html . "\n";
	}
}

if (!function_exists("user_personal_info_form")) {
	/**
	 * user_personal_info_form()
	 * Build user personal info form
	 * @see parts/dashboard/profile.php
	 *
	 * @return string HTML markup
	 */
	function user_personal_info_form()
	{
		$get_current_user_info = get_current_user_info();
		$user_personal_info = get_user_meta($get_current_user_info->id, "user_personal_info");
		$uinfo = $user_personal_info ? json_decode($user_personal_info, false) : [];

		/** 
		 * Use this  array to create user personal info fields 
		 *
		 * @key array input name and ID
		 * @index string label (Recommended to use _() translate function) or put name directly
		 * @index int length input maxlength (if you change length value you need to change it also in User->update_personal_info() )
		 * @ see inc/classes/User.class.php ( update_personal_info() )
		 *
		 * @index string type input type			
		 * @index array default_value input type
		 * @index mixed value
		 * @index boolean privacy
		 */

		/** @var string $_t_gender (set value to male or female in arabic to translate string)  */
		$_t_gender = "ذكر";
		if ($get_current_user_info->user_gender == "female") {
			$_t_gender = "أنثى";
		}

		$personal_infos = [
			"full_name" => [
				"label" => _t("إسم الثنائي أو الثلاثي"),
				"length" => 40,
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->full_name->value ?? "",
				"privacy" => $uinfo->full_name->privacy ?? "public",
			],
			"gender" => [
				"label" => _t("الجنس"),
				"length" => 40,
				"type" => "select",
				"default_value" => [$get_current_user_info->user_gender => $_t_gender],
				"value" => "",
				"privacy" => $uinfo->gender->privacy ?? "public",
			],
			"current_job" => [
				"label" => _t("الوظيفة الحالية"),
				"length" => 40,
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->current_job->value ?? "",
				"privacy" => $uinfo->current_job->privacy ?? "public",
			],
			"social_status" => [
				"label" => _t("الحالة الإجتماعية"),
				"length" => 40,
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->social_status->value ?? "",
				"privacy" => $uinfo->social_status->privacy ?? "public",
			],
			"current_address_residence" => [
				"label" => _t("محل الإقامة و العنوان الحالي"),
				"length" => 40,
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->current_address_residence->value ?? "",
				"privacy" => $uinfo->current_address_residence->privacy ?? "public",
			],
			"email" => [
				"label" => _t("البريد الإلكتروني"),
				"length" => 40,
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->email->value ?? "",
				"privacy" => $uinfo->email->privacy ?? "public",
			],
			"site_web" => [
				"label" => _t("الموقع"),
				"length" => 40,
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->site_web->value ?? "",
				"privacy" => $uinfo->site_web->privacy ?? "public",
			],
			"phone" => [
				"label" => _t("الهاتف"),
				"length" => 40,
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->phone->value ?? "",
				"privacy" => $uinfo->phone->privacy ?? "public",
			],
			"bio" => [
				"label" => _t("نبذة مختصرة عنك"),
				"length" => 350,
				"type" => "textarea",
				"default_value" => "",
				"value" => $uinfo->bio->value ?? "",
				"privacy" => $uinfo->bio->privacy ?? "public",
			],
			"youtube_url" => [
				"label" => _t("رابط يوتيوب لفيديو تعريفي"),
				"type" => "text",
				"default_value" => "",
				"value" => $uinfo->youtube_url->value ?? "",
				"privacy" => $uinfo->youtube_url->privacy ?? "public",
			],
			"download_cv" => [
				"label" => _t("تحميل السيرة الذاتية"),
				"type" => null,
				"default_value" => "",
				"value" => $uinfo->download_cv->value ?? "",
				"privacy" => $uinfo->download_cv->privacy ?? "public",
			],

		];
		$html = "";
		foreach ($personal_infos as $inp_name => $info) {

			$html .= '<div class="form-group">';
			$html .= '<div class="d-flex">';
			$label = $info["label"] ?? "";
			$length = $info["length"] ?? "";
			$type = $info["type"] ?? false;
			$default_value = $info["default_value"] ?? "";
			$value = $info["value"] ?? "";
			$privacy = $info["privacy"] ?? false;
			if (!empty($label)) {
				$html .= '<label for="" class="font-weight-bold">' . $label . '</label>';
			}
			$html .= '<div class="ml-auto mb-2">';
			$max_length_attr = '';
			if (!empty($length)) {
				$html .= '<span>' . $length . '</span>';
				$max_length_attr = 'maxlength="' . $length . '"';
			}
			if ($privacy !== false) {
				$public_privacy_attr = $followers_privacy_attr = $only_me_privacy_attr = "";
				switch ($privacy) {
					case "public":
						$public_privacy_attr = "selected-opt";
						break;
					case "followers":
						$followers_privacy_attr = "selected-opt";
						break;
					case "only_me":
						$only_me_privacy_attr = "selected-opt";
						break;
					default:
						$public_privacy_attr = "selected-opt";
				}
				if (empty($privacy)) {
					$privacy = "public";
				}
				$html .= '<div class="privacy-dropdown dropdown d-inline-block ml-3">';
				$html .= '<button class="btn btn-secondary dropdown-toggle rounded-0 dropdown-menu-privacy-selected-' . $inp_name . '" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
				$html .= '<i class="fas fa-globe"></i>&nbsp;<span>' . _t("الجميع") . '</span>';
				$html .= '</button>';
				$html .= '<div class="dropdown-menu rounded-0 dropdown-menu-privacy" data-select=".dropdown-menu-privacy-selected-' . $inp_name . '">';
				$html .= '<span class="dropdown-item ' . $public_privacy_attr . '" data-value="public" data-input="#privacy-t-' . $inp_name . '"><i class="fas fa-globe"></i>&nbsp;' . _t("الجميع") . '</span>';
				$html .= '<span class="dropdown-item ' . $followers_privacy_attr . '" data-value="followers" data-input="#privacy-t-' . $inp_name . '"><i class="fas fa-users"></i>&nbsp;' . _t("متابعون") . '</span>';
				$html .= '<span class="dropdown-item ' . $only_me_privacy_attr . '" data-value="only_me" data-input="#privacy-t-' . $inp_name . '"><i class="fas fa-user-secret"></i>&nbsp;' . _t("أنا فقط") . '</span>';
				$html .= '</div>';
				$html .= '<input type="hidden" id="privacy-t-' . $inp_name . '" name="personal_info[' . $inp_name . '][privacy]" value="' . $privacy . '"/>';
				$html .= '</div>';
			}
			$html .= '</div>';
			if (!empty($value) && !is_array($default_value)) {
				$default_value = $value;
			}
			$html .= "</div>";
			if ($type == "text" || $type == "email" || $type == "password") {
				$html .= '<input type="' . $type . '" class="form-control" name="personal_info[' . $inp_name . '][value]" id="' . $inp_name . '" value="' . $default_value . '" ' . $max_length_attr . '/>';
			} elseif ($type == "textarea") {
				$html .= '<textarea class="form-control" name="personal_info[' . $inp_name . '][value]" id="' . $inp_name . '" ' . $max_length_attr . '>' . $default_value . '</textarea/>';
			} elseif ($type == "select") {
				$html .= '<select name="personal_info[' . $inp_name . '][value]" id="' . $inp_name . '" class="form-control custom-select">';
				foreach ($default_value as $opt_value => $opt_text) {
					$html .= '<option value="' . $opt_value . '">' . $opt_text . '</option>';
				}
				$html .= '</select>';
			}
			$html .= '</div>';
		}
		return $html;
	}
}


if (!function_exists("user_info_privacy")) {
	/**
	 * user_info_()
	 * Manage privacies if is can be viewd by current logged user
	 *
	 * @param int $user_id
	 * @param string $privacy
	 * @return boolean
	 */
	function user_info_privacy($user_id, $privacy)
	{

		$current_user = get_current_user_info();
		if ($current_user->id == $user_id || admin_authority()->users == "on") {
			return true;
		}
		if ($privacy == "only_me" && $user_id != @$current_user->id) {
			return _t("لا يراه احد");
		}

		if ($privacy == "followers" && is_follower($user_id) === false) {
			return _t("لا يراه احد الا متابعه. تابعه");
		}
		return true;
	}
}

if (!function_exists("get_user_subscribed_taxonomies")) {
	/**
	 * get_user_subscribed_taxonomies()
	 * 
	 * 
	 */
	function get_user_subscribed_taxonomies($user_id = null)
	{

		if (is_null($user_id)) {
			$get_current_user_info = get_current_user_info();
			if (!$get_current_user_info) {
				return false;
			}
			$user_id = $get_current_user_info->id;
		}

		global $dsql;
		$query  = $dsql->table('user_meta')->where('user_id', $user_id)->where('meta_key', 'LIKE', 'taxonomy_subscribe__%')->get();
		$returns = [];
		foreach ($query as $meta_value) {
			$returns[] = $meta_value["meta_value"];
		}
		if (!empty($returns)) {
			return $returns;
		}
		return false;
	}
}
if (!function_exists("get_user_cv")) {
	/**
	 * get_user_cv()
	 * 
	 * @param int $user_id
	 * @param bool $ready_print
	 * @return HTML Markup
	 */
	function get_user_cv($user_id, $ready_print = false)
	{
		$user_personal_info = get_user_meta($user_id, "user_personal_info");
		$uinfo = @json_decode($user_personal_info, false);
		$get_skills_experiences = get_user_meta($user_id, 'skills_experiences', false);
		$get_achievements_publications = get_user_meta($user_id, 'achievements_publications', false);
		$get_activities_courses = get_user_meta($user_id, 'activities_courses', false);
		$get_skills_degrees = get_user_meta($user_id, 'skills_degrees', false);

		ob_start();
		?>
		<!-- personal info -->
		<div class="cv-info personal-info mb-3">
			<div class="border-bottom">
				<h4><i class="fas fa-user mr-2"></i>
					<?php echo _t("المعلومات الشخصية"); ?>
				</h4>
			</div>
			<div class="row py-3">
				<div class="col-12 col-sm-6 order-2 order-sm-1 mb-3">
					<ul class="list-unstyled d-flex flex-column">

						<li>
							<i class="fas fa-user mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->full_name->privacy))):
								@esc_html($uinfo->full_name->value);
							else:
								echo user_info_privacy($user_id, $uinfo->full_name->privacy);
							endif;
							?>
						</li>

						<li>
							<i class="fas fa-venus-mars mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->gender->privacy))):
								@esc_html(translate_const(get_user_field($user_id, "user_gender")));
							else:
								echo user_info_privacy($user_id, $uinfo->gender->privacy);
							endif;
							?>
						</li>

						<li>
							<i class="fas fa-briefcase mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->current_job->privacy))):
								@esc_html($uinfo->current_job->value);
							else:
								echo user_info_privacy($user_id, $uinfo->current_job->privacy);
							endif;
							?>
						</li>

						<li><i class="fas fa-heart mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->social_status->privacy))):
								@esc_html($uinfo->social_status->value);
							else:
								echo user_info_privacy($user_id, $uinfo->social_status->privacy);
							endif;
							?>
						</li>

						<li><i class="fas fa-map mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->current_address_residence->privacy))):
								@esc_html($uinfo->current_address_residence->value);
							else:
								echo user_info_privacy($user_id, $uinfo->current_address_residence->privacy);
							endif;
							?>
						</li>

						<li><i class="fas fa-envelope mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->email->privacy))):
								@esc_html($uinfo->email->value);
							else:
								echo user_info_privacy($user_id, $uinfo->email->privacy);
							endif;
							?>
						</li>

						<li><i class="fas fa-globe mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->site_web->privacy))):
								@esc_html($uinfo->site_web->value);
							else:
								echo user_info_privacy($user_id, $uinfo->site_web->privacy);
							endif;
							?>
						</li>

						<li><i class="fas fa-phone mr-2"></i>
							<?php
							if (is_bool(@user_info_privacy($user_id, $uinfo->phone->privacy))):
								@esc_html($uinfo->phone->value);
							else:
								echo user_info_privacy($user_id, $uinfo->phone->privacy);
							endif;
							?>
						</li>

					</ul>
				</div>
				<div class="col-12 col-sm-6 text-sm-right profile-cv-picture order-1 order-sm-2">
					<img src="<?php echo get_thumb(get_user_field($user_id, "user_picture"), "md");  ?>" class="img-fluid" />
				</div>
			</div>

			<!-- bio -->
			<div class="personal-info-bio">
				<div class="border-bottom mb-2">
					<h6 class="font-weight-bold">
						<?php echo _t("نبذة مختصرة"); ?>
					</h6>
				</div>
				<p>
					<?php
					if (is_bool(@user_info_privacy($user_id, $uinfo->bio->privacy))):
						@esc_html($uinfo->bio->value);
					else:
						echo user_info_privacy($user_id, $uinfo->bio->privacy);
					endif;
					?>
				</p>
			</div>
			<!-- / bio -->

			<?php if ($ready_print === false): ?>
				<!-- video -->
				<div class="personal-info-desc-video">
					<div class="border-bottom mb-2">
						<h6 class="font-weight-bold">
							<?php echo _t("فيديو تعريفي"); ?>
						</h6>
					</div>
					<?php
					if (is_bool(@user_info_privacy($user_id, $uinfo->youtube_url->privacy))):
						if (!empty($uinfo->youtube_url->value)):
							$embera = new \Embera\Embera(array('allow' => array('Youtube')));
					?>
							<div class="embed-responsive embed-responsive-16by9">
								<?php echo $embera->autoEmbed($uinfo->youtube_url->value); ?>
							</div>
					<?php
						endif;
					else:
						echo user_info_privacy($user_id, $uinfo->youtube_url->privacy);
					endif;
					?>
				</div>
				<!-- / video -->
			<?php endif; ?>
			<!-- / personal info -->

			<!-- skills & degrees -->
			<div class="cv-info skills-degrees mb-3">
				<div class="border-bottom">
					<h4><i class="fas fa-graduation-cap mr-2"></i>
						<?php echo _t("المؤهلات العلمية و الشهادات"); ?>
					</h4>
				</div>
				<div class="py-3">
					<?php if ($get_skills_degrees): ?>
						<?php
						foreach ($get_skills_degrees as $skill_degree):
							$skill_degree_meta_value = json_decode($skill_degree);
						?>
							<div class="d-sm-flex">
								<div class="mr-auto">
									<i class="fas fa-check-square mr-2"></i>
									<?php
									if (is_bool(user_info_privacy($user_id, $skill_degree_meta_value->privacy))):
										esc_html($skill_degree_meta_value->name);
									else:
										echo user_info_privacy($user_id, $skill_degree_meta_value->privacy);
									endif;
									?>
								</div>
								<div class="ml-auto">
									<ul class="list-unstyled d-flex flex-row">
										<li class="mr-2"><i class="fas fa-calendar"></i></li>
										<li class="mr-2">
											<?php esc_html($skill_degree_meta_value->graduation_date); ?>
										</li>
									</ul>
								</div>
							</div>
						<?php endforeach; ?>
					<?php
					else:
						no_content();
					endif;
					?>
				</div>
			</div>
			<!-- / skills & degrees -->

			<!-- activities & courses -->
			<div class="cv-info activities-courses mb-3">
				<div class="border-bottom">
					<h4><i class="fas fa-briefcase mr-2"></i>
						<?php echo _t("النشاطات و الدورات"); ?>
					</h4>
				</div>
				<div class="py-3">
					<?php if ($get_activities_courses): ?>
						<?php
						foreach ($get_activities_courses as $activite_course):
							$activite_course_meta_value = json_decode($activite_course);
						?>
							<div class="d-sm-flex">
								<div class="mr-auto">
									<i class="fas fa-check-square mr-2"></i>
									<?php
									if (is_bool(user_info_privacy($user_id, $activite_course_meta_value->privacy))):
										esc_html($activite_course_meta_value->name);
									else:
										echo user_info_privacy($user_id, $activite_course_meta_value->privacy);
									endif;
									?>
								</div>
								<div class="ml-auto">
									<ul class="list-unstyled d-flex flex-row">
										<li class="mr-2"><i class="fas fa-calendar"></i></li>
										<li class="mr-2">
											<?php esc_html($activite_course_meta_value->joining_date); ?>
										</li>
										<li class="mr-2"><i class="fas fa-long-arrow-alt-left"></i></li>
										<li>
											<?php esc_html($activite_course_meta_value->leaving_date); ?>
										</li>
									</ul>
								</div>
							</div>
					<?php
						endforeach;
					else:
						no_content();
					endif;
					?>
				</div>
			</div>
			<!-- / activities & courses -->

			<!-- skills & experiences -->
			<div class="cv-info skills-experiences mb-3">
				<div class="border-bottom">
					<h4><i class="fas fa-list-ol mr-2"></i>
						<?php echo _t("المهارات و الخبرات"); ?>
					</h4>
				</div>
				<div class="py-3">
					<?php if ($get_skills_experiences): ?>
						<?php
						foreach ($get_skills_experiences as $skill_exper):
							$skill_meta_value = json_decode($skill_exper);
							if ($ready_print === false):
						?>

								<div class="mb-3">
									<h6 class="font-weight-bold">
										<?php
										if (is_bool(user_info_privacy($user_id, $skill_meta_value->privacy))):
											esc_html($skill_meta_value->name);
										else:
											echo user_info_privacy($user_id, $skill_meta_value->privacy);
										endif;
										?>
									</h6>
									<div class="progress progress-user-profile rounded-0">
										<div class="progress-bar progress-bar-striped progress-bar-animated text-right" role="progressbar" aria-valuenow="<?php esc_html($skill_meta_value->level); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php esc_html($skill_meta_value->level); ?>%">
											<?php esc_html($skill_meta_value->level); ?>%
										</div>
									</div>
								</div>
							<?php else: ?>
								<div class="d-sm-flex">
									<div class="mr-auto">
										<i class="fas fa-check-square mr-2"></i>
										<?php
										if (is_bool(user_info_privacy($user_id, $skill_meta_value->privacy))):
											esc_html($skill_meta_value->name);
										else:
											echo user_info_privacy($user_id, $skill_meta_value->privacy);
										endif;
										?>
									</div>
									<div class="ml-auto">
										<ul class="list-unstyled d-flex flex-row">
											<li class="mr-2">
												<?php esc_html($skill_meta_value->level . "%"); ?>
											</li>
										</ul>
									</div>
								</div>

							<?php endif; ?>
					<?php
						endforeach;
					else:
						no_content();
					endif;
					?>
				</div>
			</div>
			<!-- / skills & experiences -->

			<!-- achievements & publications -->
			<div class="cv-info achievements-publications mb-3">
				<div class="border-bottom">
					<h4><i class="fas fa-ribbon mr-2"></i>
						<?php echo _t("الإنجازات و الإصدارات"); ?>
					</h4>
				</div>
				<div class="py-3">
					<?php if ($get_achievements_publications): ?>
						<?php
						foreach ($get_achievements_publications as $achiev_pub):
							$achiev_pub_meta_value = json_decode($achiev_pub);
						?>
							<div class="d-sm-flex">
								<div class="mr-auto">
									<i class="fas fa-check-square mr-2"></i>
									<?php
									if (is_bool(user_info_privacy($user_id, $achiev_pub_meta_value->privacy))):
										echo '<a href="' . $achiev_pub_meta_value->url . '" target="_blank">';
										esc_html($achiev_pub_meta_value->name);
										echo '</a>';
									else:
										echo user_info_privacy($user_id, $achiev_pub_meta_value->privacy);
									endif;
									?>
								</div>
								<div class="ml-auto">
									<ul class="list-unstyled d-flex flex-row">
										<li class="mr-2"><i class="fas fa-calendar"></i></li>
										<li class="mr-2">
											<?php esc_html($achiev_pub_meta_value->date); ?>
										</li>
									</ul>
								</div>
							</div>
					<?php
						endforeach;
					else:
						no_content();
					endif;
					?>
				</div>
			</div>
			<!-- / achievements & publications -->
			<?php if ($ready_print === false && is_bool(@user_info_privacy($user_id, $uinfo->download_cv->privacy))): ?>
				<!-- Download cv -->
				<div class="cv-info download-cv mb-3">
					<a href="user/<?php esc_html($user_id); ?>?printcv=true" class="btn btn-primary print-cv"><i class="fas fa-download mr-2"></i><?php echo _t("تحميل السيرة الذاتية"); ?></a>
				</div>
				<!-- / Download cv -->
			<?php endif; ?>
		</div>
		<!-- / cv tab panel -->
<?php
		return ob_get_clean();
	}
}




if (!function_exists("count_user_followers")) {
	/**
	 * count_user_followers()
	 *
	 * @param int $user_id
	 * @return mixed (int on success|boolean on failure)
	 */
	function count_user_followers($user_id)
	{

		global $dsql;
		$count = count_rows($dsql->dsql()->table('subscribe_sys')->join('users.id', 'subscribe_sys.subscriber', 'right')->where('subscribe_sys.user_id', $user_id)->where('users.user_status', 'active')->field('count(*)')->getRow());
		return $count;
	}
}
