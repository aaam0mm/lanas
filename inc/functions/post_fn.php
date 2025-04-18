<?php

use OpenCafe\Datium;

if (!function_exists("post_random_indexed_column")) {
	function post_random_indexed_column()
	{

		$columns = [
			"posts.id",
			"posts.post_date_gmt",
			"posts.post_title"
		];

		$min = 0;
		$max = count($columns) - 1;

		$key = rand($min, $max);

		return $columns[$key];
	}
}

if (!function_exists("get_post_title")) {
	/**
	 * 
	 * @param mixed (int|array) $post
	 * @return mixed (string on success|null on failure) 
	 */
	function get_post_title($post = null)
	{
		if (absint($post) > 0) {
			return get_post_field($post, 'post_title');
		}

		if (is_array($post)) {
			return $post['post_title'] ?? null;
		}
	}
}

if (!function_exists("get_post_content")) {
	/**
	 * 
	 * @param mixed (int|array) $post
	 * @return mixed (string on success|null on failure) 
	 */
	function get_post_content($post = null)
	{
		if (absint($post) > 0) {
			return get_post_field($post, 'post_content');
		}

		if (is_array($post)) {
			return $post['post_content'] ?? null;
		}
	}
}

if (!function_exists("get_post_link")) {
	/**
	 * 
	 * @param mixed (int|array) $post
	 * @return mixed (string on success|null on failure) 
	 */
	function get_post_link($post)
	{

		if (empty($post)) {
			return false;
		}

		$link_args = [];
		if (!is_array($post)) {
			$link_args = get_post_field($post, ['post_type', 'post_url_title']);
			if (!$link_args) {
				return false;
			}
			$link_args['post_id'] = $post;
		} else {
			$link_args = [
				'post_url_title' => $post['post_url_title'],
				'post_type' => $post['post_type'],
				'post_id' => $post['id']
			];
		}
		$post_id = $link_args['post_id'];
		$post_url_title = $link_args['post_url_title'];
		$post_type = $link_args['post_type'];
		return siteurl() . '/post/' . $post_type . '/' . $post_id . '/' . $post_url_title;
	}
}
if (!function_exists('can_edit_post')) {
	/**
	 * 
	 */
	function can_edit_post($post)
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		if (!is_array($post)) {
			$post = get_post_field($post, ['post_status', 'post_author', 'post_in']);
		}

		$post_in = $post["post_in"];
		$post_status = $post["post_status"];
		$post_author = $post["post_author"];

		if ((admin_authority()->posts == "on" || ($post_author == $current_user->id)) === false) {
			return false;
		}

		if ($post_status == "blocked" && admin_authority()->posts != "on") {
			//$this->errors[] = ["error" => _t("تم إيقاف هذا المحتوى من طرف الإدارة")];
			return false;
		}
		return true;
	}
}

if (!function_exists('can_edit_post_info')) {
	/**
	 * 
	 */
	function can_edit_post_info($post)
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		if (!is_array($post)) {
			$post = get_post_info_field($post, ['post_status', 'post_author', 'post_in']);
		}

		$post_in = $post["post_in"];
		$post_status = $post["post_status"];
		$post_author = $post["post_author"];
		if ((admin_authority()->posts == "on" || ($post_author == $current_user->id)) === false) {
			return false;
		}

		if ($post_status == "blocked" && admin_authority()->posts != "on") {
			//$this->errors[] = ["error" => _t("تم إيقاف هذا المحتوى من طرف الإدارة")];
			return false;
		}
		return true;
	}
}

if (!function_exists('get_post_slices')) {
	/**
	 * @param int $post_id
	 * @param string $order_by
	 */
	function get_post_slices($post_id, $order_by = "DESC")
	{

		global $dsql;

		$query = $dsql->dsql()->table('post_slices')->where('post_id', $post_id)->order('id', $order_by);
		$get_slices = $query->get();

		return $get_slices;
	}
}

if (!function_exists('get_post_actions')) {
	/**
	 * 
	 */
	function get_post_actions($post)
	{

		$current_user = get_current_user_info();

		if (!$current_user) {
			return '';
		}

		if (!is_array($post)) {
			$post = get_post_field($post, ["post_type", "post_in"]);
			$post["id"] = $post;
		}

		$post_type = $post["post_type"];
		$post_in = $post["post_in"];
		$post_id = $post["id"];

		$post_author = get_post_field($post_id, "post_author");
		$post_status = get_post_field($post_id, "post_status");
		$post_in = get_post_field($post_id, "post_in");

?>
		<!-- Actions buttons -->
		<div class="btn-actions d-inline-block">
			<div class="d-flex">
				<?php if (($current_user->id == $post_author && $post_status != "blocked") || admin_authority()->posts == "on"): ?>
					<div class="px-1">
						<a href="<?php echo siteurl(); ?>/post.php?post_type=<?php esc_html($post_type); ?>&post_in=<?php esc_html($post_in); ?>&action=edit&post_id=<?php esc_html($post_id); ?>" class="btn btn-transparent border rounded-circle" data-toggle="tooltip" title="<?php echo _t('تعديل'); ?>"><i class="fas fa-pen"></i></a>
					</div>
				<?php endif; ?>

				<?php
				if (admin_authority()->posts == "on"):
					$class_attr = esc_html__("post-" . $post_in);
					$tooltip = _t("إلغاء التوثيق");
					if ($post_in == "trusted") {
						$class_attr .= ' btn-primary';
					} else {
						$class_attr .= ' btn-secondary';
						$tooltip = _t("توثيق");
					}
				?>
					<div class="px-1">
						<button class="btn rounded-circle un_trusted_post <?php echo $class_attr; ?>" data-id="<?php esc_html($post_id); ?>" data-toggle="tooltip" title="<?php echo $tooltip; ?>"></button>
					</div>
				<?php endif; ?>

				<?php if (can_edit_post($post)): ?>
					<div class="px-1">
						<button class="btn rounded-circle un_lock un_lock_post <?php is_post_publish_h_c($post_id); ?>" data-id="<?php esc_html($post_id); ?>" data-toggle="tooltip" title="<?php echo _t('قفل'); ?>"></button>
					</div>
					<div class="px-1">
						<button class="btn btn-danger rounded-circle delete-post-btn" data-id="<?php esc_html($post_id); ?>" data-toggle="tooltip" title="<?php echo _t('حدف'); ?>"><i class="fas fa-trash"></i></button>
					</div>
				<?php endif; ?>
			</div>
		</div><!-- Actions buttons -->
		<?php
	}
}


if (!function_exists('get_post_actions_new')) {
	/**
	 * 
	 */
	function get_post_actions_new($post)
	{

		$current_user = get_current_user_info();

		if (!$current_user) {
			return '';
		}

		if (!is_array($post)) {
			$post = get_post_field($post, ["post_type", "post_in"]);
			$post["id"] = $post;
		}

		$post_type = $post["post_type"];
		$post_in = $post["post_in"];
		$post_id = $post["id"];

		$post_author = get_post_field($post_id, "post_author");
		$post_status = get_post_field($post_id, "post_status");
		$post_in = get_post_field($post_id, "post_in");

?>
		<!-- Actions buttons -->
		<div class="mr-1">
			<button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-wrench"></i></button>
			<div class="dropdown-menu">
				<?php if (($current_user->id == $post_author && $post_status != "blocked") || admin_authority()->posts == "on"): ?>
					<span class="dropdown-item">
						<a href="<?php echo siteurl(); ?>/post.php?post_type=<?php esc_html($post_type); ?>&post_in=<?php esc_html($post_in); ?>&action=edit&post_id=<?php esc_html($post_id); ?>" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=facebook" class="text-dark" title="<?php echo _t('تعديل'); ?>">
							<i class="fas fas fa-pen"></i>
							<span class="ml-2"><?php echo _t('تعديل'); ?></span>
						</a>
					</span>
				<?php endif; ?>
				<?php
				if (admin_authority()->posts == "on"):
					$class_attr = esc_html__("post-" . $post_in);
					$tooltip = _t("إلغاء التوثيق");
					if ($post_in == "trusted") {
						$tooltip = _t("إلغاء التوثيق");
					} else {
						$tooltip = _t("توثيق");
					}
				?>
					<span class="dropdown-item">
						<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=twitter" data-untrusted="<?php echo _t('توثيق'); ?>" data-trusted="<?php echo _t('إلغاء التوثيق'); ?>" class="un_trusted_post text-dark <?php echo $class_attr; ?>" data-id="<?php esc_html($post_id); ?>" title="<?php echo $tooltip; ?>">
							<span><?php echo $tooltip; ?></span>
						</a>
					</span>
				<?php endif; ?>

				<?php if (can_edit_post($post)): ?>

					<?php
					$post_status = get_post_field($post_id, "post_status");
					if ($post_status != "publish") {
						$cls = "un_lock_post";
						$text = _t('فتح');
						$action_change_text = _t('قفل');
					} else {
						$cls = "post-locked";
						$text = _t('قفل');
						$action_change_text = _t('فتح');
					}
					?>

					<span class="dropdown-item">
						<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=facebook" data-action-change="<?php echo $action_change_text; ?>" class="text-dark un_lock  <?php echo $cls; ?>" data-id="<?php esc_html($post_id); ?>" title="<?php echo $text; ?>">
							<span class="ml-2"><?php echo $text; ?></span>
						</a>
					</span>

					<span class="dropdown-item">
						<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=facebook" class="text-dark delete-post-btn" data-id="<?php esc_html($post_id); ?>" title="<?php echo _t('حدف'); ?>">
							<i class="fas fa-trash"></i>
							<span class="ml-2"><?php echo _t('حدف'); ?></span>
						</a>
					</span>

					<span class="dropdown-item">
						<a href="#" data-toggle="tooltip" data-url="" class="text-dark" data-id="<?php esc_html($post_id); ?>" title="<?php echo _t('تلخيص الكتاب'); ?>">
							<i class="fas fa-file-signature"></i>
							<span class="ml-2"><?php echo _t('تلخيص الكتاب'); ?></span>
						</a>
					</span>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}



function get_history_posts($date_type, $date_month, $date_day, $target_calendar = 'gregorian')
{

	global $dsql;

	if (((int) $date_day) < 10) {
		$date_day = "0" . (int) $date_day;
	}

	if ($date_type == "hijri") {
		$date_gregorian = Datium::create(get_gergorian_year('hijri'), $date_month, $date_day)->from('hijri')->get('d-n');
		$date_kurdish = Datium::create(get_gergorian_year('hijri'), $date_month, $date_day)->from('hijri')->to('kurdish')->get('d-n');
		$date_hijri = $date_day . "-" . $date_month;
	} elseif ($date_type == "gregorian") {
		$date_gregorian = $date_day . "-" . $date_month;
		$date_kurdish = Datium::create(get_gergorian_year(), $date_month, $date_day)->to('kurdish')->get('d-n');
		$date_hijri = Datium::create(get_gergorian_year(), $date_month, $date_day)->to('hijri')->add('1 day')->get('d-n');
	} elseif ($date_type == "kurdish") {
		$date_kurdish = $date_day . "-" . $date_month;
		$date_gregorian = Datium::create(get_gergorian_year('kurdish'), $date_month, $date_day)->from('kurdish')->get('d-n');
		$date_hijri = Datium::create(get_gergorian_year('kurdish'), $date_month, $date_day)->from('kurdish')->add('1 day')->to('hijri')->get('d-n');
	}

	$dates_where = [
		'hijri' => $date_hijri,
		'gregorian' => $date_gregorian,
		'kurdish' => $date_kurdish
	];


	$date = $dates_where[$target_calendar];

	$get_posts = $dsql->dsql()->table('post_meta')->join('posts.id', 'post_meta.post_id')->where('post_meta.meta_key', 'history_calendar')->where('post_meta.meta_value', $target_calendar)->where('posts.post_title', 'like', $date . '-%')->where('posts.post_lang', current_content_lang())->where('posts.post_type', 'history');

	$get_posts = $get_posts->get();

	$history_posts = build_history_post($get_posts);

	return $history_posts;
}

if (!function_exists("merge_to_un_trusted")) {
	/**
	 * merge_to_un_trusted()
	 * 
	 */
	function merge_to_un_trusted()
	{
		$response = ["success" => false];
		$post_id = $_POST["id"] ?? "";
		if (make_post_un_trusted($post_id)) {
			$response["msg"] = _t("تم تحويل المنشور بنجاح");
			$response["success"] = true;
		} else {
			$response["msg"] = _t("المعذرة ! المرجو إعادة المحاولة");
		}
		echo json_encode($response);
	}
}

if (!function_exists("authorize_share")) {
	/**
	 * authorize_share()
	 * 
	 */
	function authorize_share()
	{
		global $dsql;
		$response = ["success" => false];
		$post_id = $_POST["id"] ?? "";
		$stat = $_POST['stat'] ?? "";
		if($post_id > 0 || !empty($stat)) {
			$stat = $stat == 'off' ? 'on' : "off";
			$update = $dsql->dsql()->table('posts')->set(["share_authority" => $stat])->where("id", $post_id)->update();
			if ($update) {
				$response["msg"] = _t("تم تغيير حالة سحب النشر بنجاح");
				$response["success"] = true;
			} else {
				$response["msg"] = _t("المعذرة ! المرجو إعادة المحاولة");
			}
		} else {
			$response["msg"] = _t("المعذرة ! المرجو إعادة المحاولة");
		}
		echo json_encode($response);
	}
}

if (!function_exists("make_post_un_trusted")) {
	/**
	 * make_post_un_trusted()
	 * Merge post_in
	 * 
	 * @param int $post_id
	 * @return boolean
	 */
	function make_post_un_trusted($post_id)
	{

		$post_id = absint($post_id);

		if (empty($post_id) || @admin_authority()->posts != "on") {
			return false;
		}

		$post_in = get_post_field($post_id, "post_in");
		$new_post_in = "";
		if ($post_in == "trusted") {
			$new_post_in = "untrusted";
		} elseif ($post_in == "untrusted") {
			$new_post_in = "trusted";
		} else {
			return false;
		}

		global $dsql;

		$update = $dsql->dsql()->table('posts')->set(["post_in" => $new_post_in])->where('id', $post_id)->update();

		if ($update) {
			return true;
		}
		return false;
	}
}
if (!function_exists("get_post_reaction")) {
	/**
	 * get_post_reaction()
	 *
	 * @param int $post_id
	 * @return array
	 */
	function get_post_reaction($post_id)
	{

		$post_id = absint($post_id);

		if (empty($post_id)) {
			return false;
		}

		global $dsql;
		$get_post_reactions = $dsql->dsql()->table('user_meta')->where('meta_key', 'post_reaction__' . $post_id);
		if ($get_post_reactions) {
			$reactions = [];
			$current_user_reacted = get_user_meta(@get_current_user_info()->id, "post_reaction__" . $post_id);
			foreach ($get_post_reactions as $reaction) {
				$reactions[] = $reaction["meta_value"];
			}
			return ["react" => $current_user_reacted, "reactions" => array_count_values($reactions)];
		}
		return false;
	}
}

function move_posts($posts, $category)
{

	if (!is_array($posts)) {
		$posts = [$posts];
	}

	if (empty($posts) || empty($category)) {
		return false;
	}

	global $dsql;

	$data = [];

	foreach ($posts as $post) {
		$exists = $dsql->dsql()->table('post_category')->where('post_id', $post)->where('post_category', $category)->limit(1)->getRow();
		if ($exists) {
			$delete = $dsql->dsql()->table('post_category')->where("post_id", $post)->where("post_category", $category)->delete();
		} else {
			$insert = $dsql->dsql()->table('post_category')->set(["post_id" => $post, "post_category" => $category])->insert();
		}
	}
}

function get_posts_categories($posts)
{

	global $dsql;

	$query = $dsql->dsql()->table('post_category')->join('categories.id', 'post_category.post_category')->where('post_category.post_id', $posts)->field('categories.*')->field('post_category.post_id', 'post_id')->get();

	if (!$query) {
		return false;
	}

	return array_value_to_key($query, 'post_id');
}

function get_posts_categories_distinct($posts)
{

	global $dsql;

	$query = $dsql->dsql()->expr("select DISTINCT `categories`.*,`post_category`.`post_id` `post_id` from `post_category` left join `categories` on `categories`.`id` = `post_category`.`post_category` where post_category.post_id = $posts;")->get();

	if (!$query) {
		return false;
	}

	return array_value_to_key($query, 'post_id');
}

function get_posts_categories_for_info($infos)
{

	global $dsql;
	$query = $dsql->dsql()->table('post_info')->join('categories.id', 'post_info.post_category')->where('post_info.id', $infos)->field('categories.*')->field('post_info.post_category')->get();

	if (!$query) {
		return false;
	}
	return array_value_to_key($query, 'id');
}

if (!function_exists("get_category_posts")) {
	/**
	 * get_category_posts()
	 * get posts in category page
	 *
	 * @param string $post_type
	 * @return mixed array
	 */
	function get_category_posts($post_type, $load_more = false)
	{

		$category_id = (int) ($_GET["category"] ?? 0);

		$s = $_GET["s"] ?? "";
		$filter = [];
		$order_by = $_GET["order_by"] ?? null;
		$order_by_query = '';
		$cat_st = get_category_settings($category_id);

		$rand_col = isset($_GET["rand_col"]) && !empty($_GET["rand_col"]) ? $_GET["rand_col"] : post_random_indexed_column();

		if (empty($order_by)) {
			$order_by_query = @$cat_st->option["sort"] == 'latest' ? ['posts.id', 'desc'] : [$rand_col, 'desc'];
		} else {
			$order_by_query = ['posts.id', $order_by == 'desc' ? 'desc' : 'asc'];
		}

		switch ($post_type):
			case "name":
			case "dictionary":
				include ROOT . '/parts/posts/name.php';
				break;
			case "history":
				include ROOT . '/parts/posts/history.php';
				break;
			case "quote":
				include ROOT . '/parts/posts/quote.php';
				break;
			default:
				$query = new Query_post(["post_title" => $s, "post_category" => $category_id, "post_type" => $post_type, "order" => $order_by_query]);
				$query->do_query_count = true;
				$get_posts = $query->get_posts();
				if ($get_posts):
		?>
					<div id="posts-feed">
						<div class="row m-0">
							<?php
							foreach ($get_posts as $post):

								$thumb_size = "md";

								if ($post["post_type"] == "book" || $post["post_type"] == "research") {
									$thumb_size = ["w" => 200, "h" => 280];
								}

								if ($post["post_type"] == "author_article") {
									$post["post_thumbnail"] = get_user_field($post["post_author"], 'user_picture');
								}


							?>
								<!--  post -->
								<div class="col-lg-3 col-md-4 <?php echo ($post["post_type"] == "book") ? "col-6" : "col-sm-6"; ?>  px-1 mb-2">
									<div class="posts-post <?php esc_html($post["post_type"]); ?>-posts position-relative overflow-hidden h-100">
										<a href="<?php echo get_post_link($post); ?>"><img src="<?php esc_html(get_thumb($post["post_thumbnail"], $thumb_size, true, $post["id"])); ?>" class="w-100 img-fluid thumb-src" /></a>
										<div class="post-details position-absolute w-100 h-100 top-0 right-0 animated fadeIn">
											<div class="d-flex flex-column h-100">
												<div class="mb-auto w-100">
													<a href="<?php echo get_post_link($post); ?>" class="bg-primary text-white text-center h6 px-2 py-2 d-block" title="<?php esc_html($post["post_title"]); ?>">
														<?php echo substr_str($post["post_title"], 20); ?>
													</a>
													<?php echo bookmark_opt($post["id"]); ?>
												</div>
												<div class="my-auto text-center w-100">
													<i class="fas fa-search fa-lg text-white"></i>
												</div>
												<div class="d-flex p-2">
													<a href="<?php esc_html(get_author_in_post($post["post_author"])->link); ?>" class="text-light float-left author-in-post author-in-post-sm">
														<img src="<?php esc_html(get_author_in_post($post["post_author"])->user_picture); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
														<?php esc_html(get_author_in_post($post["post_author"])->user_name); ?>
													</a>
													<!-- <span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($post["post_views"]); ?></span> -->
												</div>
											</div>
										</div>
									</div>
								</div><!-- / post -->
							<?php endforeach; ?>
						</div>
					</div>
<?php
				else:
					no_content();
				endif;
		endswitch;
		if ($load_more) {
			if (is_object($query)) {
				$count = $query->count_results();
			}
			echo load_more_btn($count, "#posts-feed", array_merge(["request" => "posts-ajax", "taxonomy" => $post_type, "category" => $category_id, "order_by" => $order_by, "rand_col" => $rand_col], $filter));
		}
	}
}


if (!function_exists("load_post_slices")) {
	/**
	 * load_post_slices()
	 *
	 * @param int $post_id
	 * @return mixed (array on success|boolean on failure)
	 */
	function load_post_slices($post_id)
	{
		if (empty($post_id)) {
			return false;
		}
		$query = new Query_post(["post_id" => $post_id]);
		$get_slices = $query->get_post_slices();
		if ($get_slices) {
			$slices = [];
			foreach ($get_slices as $slice_key => $slice) {
				$slices[$slice_key]["slice_id"] = $slice["id"];
				$slices[$slice_key]["type"] = $slice["slice_type"];
				$slices[$slice_key]["content"] = json_decode($slice["slice_content"]);
			}
			return $slices;
		}
		return false;
	}
}

if (!function_exists("un_lock_post")) {
	/**
	 * un_lock_post()
	 *
	 * @param int $post_id
	 * @param boolean $default_status
	 * @return boolean
	 */
	function un_lock_post($post_id, $default_status = false)
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		/**
		 * cases that user can make change lock/unlock posts
		 * had admin authority to access posts
		 * he/she is post author & post not in trusted (this ignored if first condition is true)
		 */
		$post_author = get_post_field($post_id, "post_author");
		$post_in = get_post_field($post_id, "post_in");

		if ((admin_authority()->posts == "on" || ($post_author == $current_user->id && $post_in == "untrusted")) === false) {
			return false;
		}
		global $dsql;

		$post_status = get_post_field($post_id, "post_status");
		// notification type to send to post author
		$notif_type = '';
		if ($post_status == "publish") {
			/**
			 * "blocked" status reserved for admin only if admin made lock request.
			 * "closed" status used to close untrusted posts from own author's
			 */
			$status = "blocked";
			if ($post_author == $current_user->id && $post_in == "untrusted") {
				$status = "closed";
			}
			$notif_type = 'block_post';
		} else {
			if ($post_status == "pending") {
				// add user points when publish post first time
				$post_type = get_post_field($post_id, "post_type");
				$user_points = get_user_info($post_author)->points_remaining;
				$new_points = $user_points + distribute_points($post_type, "add");
				if (!update_user_meta($post_author, "points_remaining", $new_points)) {
					return false;
				}
			}
			// if post status not equal publish we change it to publish
			$status = 'publish';
			$notif_type = 'publish_post';
			$post_content_edit = get_post_meta($post_id, "post_content_edit");
			if ($post_content_edit) {
				if ($dsql->dsql()->table('posts')->set(["post_content" => $post_content_edit])->where('id', $post_id)->update()) {
					remove_post_meta($post_id, "post_content_edit");
				}
			}
		}
		/** Specific case this case is reserved for multi_action() function */
		if ($default_status) {
			if ($default_status != "publish") {
				$default_status = 'blocked';
			}
			$status = $default_status;
		}

		$update = $dsql->dsql()->table('posts')->set(["post_status" => $status])->where('id', $post_id)->update();
		if ($update) {
			insert_notif(0, $post_author, $post_id, $notif_type, 1);
			return true;
		}
		return false;
	}
}

if (!function_exists("get_post_in_html")) {
	/**
	 * get_post_status_html()
	 * echo icon to user show if post is trsuted/untrusted type
	 * @param string $post_in
	 * @param string $custom_class
	 * @echo string HTML Markup
	 */
	function get_post_in_html($post_in, $custom_class = "")
	{

		$post_in_text = "";
		$post_in_attr = "";
		switch ($post_in) {
			case "trusted":
				$post_in_text = _t("موثقة");
				$post_in_attr = "fas fa-check-circle text-success";
				break;
			case "untrusted":
				$post_in_text = _t("حرة");
				$post_in_attr = "fas fa-exclamation-circle text-secondary";
				break;
		}

		echo '<i class="' . $post_in_attr . ' ml-2 ' . $custom_class . '" data-toggle="tooltip" title="' . _t($post_in_text) . '"></i>';
	}
}

if (!function_exists("get_post_comments")) {
	/**
	 * get_post_comments()
	 *
	 * @param int $post_id
	 * @return HTML markup
	 */
	function get_post_comments($post_id)
	{
		if (empty($post_id)) {
			return false;
		}
		global $dsql;
		$comments = $dsql->dsql()->table('comments')->where('post_id', $post_id)->where('comment_status', 'publish')->order('id', 'desc')->limit(paged('end'), paged('start'))->get();

		if (empty($comments)) {
			return;
		}

		$r_c = [];

		foreach ($comments as $comment) {
			$r_c[$comment['id']] = $comment;
		}
		$r_c =  multi_level_childs($r_c, 0, 'comment_parent', 'id', 'replies');
		echo '<div class="get_comments"><div class="form-row"><div class="col-12">';
		if ($r_c):
			echo post_comment_html($r_c);
		endif;
		echo '</div></div></div>';
	}
}

if (!function_exists("build_history_post")) {
	/**
	 * build_history_post()
	 * 
	 * @param array $posts
	 */
	function build_history_post($posts)
	{

		if (!is_array($posts)) {
			return false;
		}

		$new = [];

		foreach ($posts as $post) {
			$history_event = get_post_meta($post["id"], "history_event");
			$history_calendar = get_post_meta($post["id"], "history_calendar");
			if ($history_event) {
				$post["post_title"] = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $post["post_title"]);
				$date = explode('-', $post["post_title"]);
				if ($history_event == 'occasions') {
					$post["post_title"] = $date[0] . ' ' . months_names($date[1], $history_calendar);
				} else {
					$post["post_title"] = $date[0] . ' ' . months_names($date[1], $history_calendar) . ' ' . $date[2];
				}
				$new[$history_event][]  = $post;
			}
		}

		return $new;
	}
}
