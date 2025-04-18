<?php

/**
 * dashboard-ajax.php
 * Handle all html ajax request coming from dashboard
 */

if ($data == "load-posts") {
	$current_user = get_current_user_info();
	$user_id = $current_user->id;
	$q = $_GET["q"] ?? "";
	$post_type = $_GET["post_type"] ?? "";
	$per_page = (int) ($_GET["per_page"] ?? RESULT_PER_PAGE);
	if ($per_page > 250 || $per_page < 50) {
		$per_page = RESULT_PER_PAGE;
	}
	$order_by = $_GET["order_by"] ?? "desc";

	$query = new Query_post(
		[
			"post_author" => $user_id,
			"post_type" => $post_type,
			"post_title" => $q,
			"post_lang" => false,
			"post_status" => false,
			"post_status__not" => 'auto-draft',
			'limit' => $per_page,
			'order' => [
				'id',
				$order_by
			],

		]
	);

	$get_posts = $query->get_posts();
	$posts_categories = get_posts_categories(array_column($get_posts, 'id'));
	$posts_categories_title = get_posts_categories_name($posts_categories);

	if ($get_posts):
		foreach ($get_posts as $post):
			$post_cats = '';
			if (isset($posts_categories_title[$post["id"]]) && is_array($posts_categories_title[$post["id"]])) {
				$post_cats = '(' . implode(',', $posts_categories_title[$post["id"]]) . ')';
			}

?>
			<tr class="animated fadeIn">
				<td>
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input check-box-action" data-id="<?php esc_html($post["id"]); ?>" id="select-all-checkbox-<?php esc_html($post["id"]); ?>">
						<label class="custom-control-label" for="select-all-checkbox-<?php esc_html($post["id"]); ?>"></label>
					</div>
				</td>
				<td>
					<a href="<?php echo get_post_link($post); ?>" class="color-link" title="<?php esc_html($post["post_title"]); ?>">
						<?php
						esc_html(substr_str($post["post_title"], 40));
						echo get_post_in_html($post["post_in"]);
						?>
					</a>&nbsp;<?php echo $post_cats; ?>
				</td>
				<td>
					<?php esc_html(get_taxonomy_title($post["post_type"])); ?>
				</td>
				<td><span class="badge badge-primary"><?php esc_html(formatWithSuffix($post["post_views"])); ?></span></td>
				<td><?php get_post_actions($post); ?></td>
			</tr>
		<?php
		endforeach;
	endif;
} elseif ($data == "load-comments") {
	$btn_primary = "btn-primary";
	$btn_secondary = "btn-secondary";
	$current_user = get_current_user_info();
	$comment_type = $_GET["comment_type"] ?? "posts_comments";
	$order_by = $_GET["order_by"] ?? "desc";
	if ($comment_type == "posts_comments") {
		/**
		 * Get user posts then get posts comments
		 */

		$get_comments = get_posts_comments(true, null, $order_by);
	} elseif ($comment_type == "my_comments") {
		$get_comments = get_posts_comments(null, $current_user->id);
		$btn_primary = "btn-secondary";
		$btn_secondary = "btn-primary";
	}

	if ($get_comments) {
		foreach ($get_comments as $comment):
			$un_lock_btn_class = "btn-success";
			if ($comment["comment_status"] != "publish") {
				$un_lock_btn_class = "btn-warning post-locked";
			}
		?>
			<tr class="tr-comment-<?php esc_html($comment["id"]); ?>">
				<td>
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input check-box-action" data-id="<?php esc_html($comment["id"]); ?>" id="select-all-checkbox-<?php esc_html($comment["id"]); ?>">
						<label class="custom-control-label" for="select-all-checkbox-<?php esc_html($comment["id"]); ?>"></label>
					</div>
				</td>
				<td>
					<a href="<?php echo get_post_link($comment["post_id"]); ?>?comment_id=<?php esc_html($comment["id"]); ?>" class="color-link">
						<?php esc_html(substr_str($comment["comment"], 20)); ?>
					</a>
				</td>
				<td><a href="<?php echo get_post_link($comment["post_id"]); ?>" class="color-link"><?php esc_html($comment["post_id"]); ?></a></td>
				<td><a href="user/<?php esc_html($comment["comment_user"]); ?>" class="color-link"><?php esc_html(get_user_field($comment["comment_user"], "user_name")); ?></a></td>
				<td><?php esc_html(get_taxonomy_title(get_post_field($comment["post_id"], "post_type"))); ?></td>
				<td>
					<!-- Actions buttons -->
					<div class="btn-actions row">
						<div class="col-md-12 col-lg-4 mb-2 mb-lg-0 px-2">
							<button class="btn btn-danger rounded-circle delete-comment-btn" data-id="<?php esc_html($comment["id"]); ?>" data-remove=".tr-comment-<?php esc_html($comment["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('حدف'); ?>"><i class="fas fa-trash"></i></button>
						</div>
						<?php if ($comment_type == "posts_comments" && $current_user->id != $comment["comment_user"]): ?>
							<div class="col-md-12 col-lg-4 mb-2 mb-lg-0 px-2">
								<button class="btn <?php echo $un_lock_btn_class; ?> rounded-circle un_lock un_lock_comment" data-id="<?php esc_html($comment["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('قفل'); ?>"></button>
							</div>
						<?php endif; ?>
					</div><!-- Actions buttons -->
				</td>
			</tr>
			<?php endforeach;
	}
} elseif ($data == "statistics") {
	$current_user = get_current_user_info();
	$section = $_GET["section"] ?? "";

	if ($section == "posts") {
		$args = [
			"post_author" => $current_user->id,
			"post_lang" => false
		];
		$sort_by = $_GET["sort_by"] ?? "";
		switch ($sort_by) {
			case "most_viewed":
				$sort_text = _t("مشاهدة");
				$args["order"] = 'posts.post_views desc';
				$col = 'post_views';

				break;
			case "most_rated":
				$sort_text = _t("تقييم");
				$get_posts = $dsql->dsql()->table('posts');
				$get_posts->join('rating_sys.post_id', 'posts.id')->where('posts.post_author', $current_user->id)->where('posts.post_status', 'not in', ['auto-draft', 'draft'])->where('rating_sys.post_id', '!=', null)->field('count(*)', 'ratings')->field('posts.*')->order('ratings', 'desc')->group('rating_sys.post_id')->limit(12);
				$get_posts = $get_posts->get();
				$col = 'ratings';
				break;
			case "most_reacted":
				$get_posts = $dsql->dsql()->table('posts');
				$get_posts->join('user_meta.meta_key', $get_posts->expr('user_meta.meta_key = CONCAT("post_reaction__",posts.id)'))->where('posts.post_author', $current_user->id)->where('posts.post_status', 'not in', ['auto-draft', 'draft'])->where('user_meta.meta_key', '!=', null)->field('count(*)', 'reactions')->field('posts.*')->order('reactions', 'desc')->group('user_meta.meta_key')->limit(12);
				$get_posts = $get_posts->get();
				$sort_text = _t("رد فعل");
				$col = 'reactions';
				break;
			case "most_shared":
				$sort_text = _t("مشاركة");
				$args["order"] = 'posts.post_share desc';
				$col = 'post_share';
				break;
		}
		if (!isset($get_posts)) {
			$query_posts = new Query_post($args);
			$get_posts = $query_posts->get_posts();
		}
		if ($get_posts) {
			foreach ($get_posts as $post) {
			?>
				<div class="col-md-6 col-sm-12 mb-3">
					<div class="row">
						<?php if (!empty($post["post_thumbnail"])): ?>
							<div class="col-md-3 col-sm-12 mb-3 mb-md-0">
								<a hreff="<?php echo get_post_link($post); ?>"><img src="<?php echo get_thumb($post["post_thumbnail"]); ?>" class="img-fluid" alt="" /></a>
							</div>
						<?php endif; ?>
						<div class="col-md-9 col-sm-12">
							<a href="<?php echo get_post_link($post); ?>" class="h6 color-link"><?php esc_html($post["post_title"]); ?>&nbsp;<?php echo get_post_in_html($post["post_in"]); ?></a> <br />
							<span class="text-danger mt-3 d-block"><?php esc_html($post[$col]);
																											echo ' ' . $sort_text; ?></span>
						</div>
					</div>
				</div>
			<?php
			}
		}
	} elseif ($section == "countries") {
		$duration = $_GET["duration"] ?? "today";
		$countries_analytics = get_analytics("post_views", "views_by_country", $current_user->id, $duration);
		if (is_array($countries_analytics)) {
			foreach ($countries_analytics as $country_code => $visits_country):
				$country_flag = false;
				$country_info = get_countries($country_code);
				if ($country_info) {
					$country_flag = get_thumb($country_info["country_flag"]);
				}
				$country_name = $country_info["country_name"] ?? "n/a";
			?>
				<tr>
					<td>
						<?php
						if ($country_flag) {
							echo '<img src="' . $country_flag . '" class="img-fluid mr-2" width="18" height="18"/>';
						}
						esc_html(json_decode($country_name)->{current_lang()} ?? "n/a");
						?>
					</td>
					<td><?php esc_html($visits_country["all_views"] ?? 0); ?></td>
					<td><?php esc_html($visits_country["trusted_views"] ?? 0); ?></td>
					<td><?php esc_html($visits_country["untrusted_views"] ?? 0); ?></td>
				</tr>
<?php
			endforeach;
		}
	}
}
?>