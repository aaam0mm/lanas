<?php

/**
 * profile-ajax.php
 * Handle all html ajax request coming from profile(user.php)
 */

if ($data == "posts-users-tab") {

	$tab = $_GET["tab"] ?? "";
	$user_id = $_GET["user_id"] ?? "";
	if ($tab == "news-feed") {
	    $current_user = get_current_user_info();
		if(is_login_in() && $current_user->id == $user_id) {
        	$get_news_feed = $dsql->dsql()->table('subscribe_sys','ss')->join('posts.post_author','user_id')->where('ss.subscriber', $current_user->id)->where('posts.post_status', 'publish')->where('posts.post_in','trusted')->order('posts.id','desc');
        	$get_news_feed_rows = count_rows($get_news_feed->field('count(*)')->getRow());
        	$get_news_feed->reset('getRow')->reset('field');
        	$results = $get_news_feed->limit(paged('end'),paged('start'))->get();
		}else{
			$query_news_feed = new Query_post([
				"post_author" => $user_id,
				"post_lang" => false,
				"order" => ['posts.id','desc'],
				"post_in" => false
			]);
			$results = $query_news_feed->get_posts();
		}
	} elseif ($tab == "my-posts") {
		$query_my_posts = new Query_post(["post_author" => $user_id, "post_status" => "publish", "post_lang" => false, "post_in" => false, 'order' => ['posts.id', 'desc']]);
		$results = $query_my_posts->get_posts();
	} elseif ($tab == "fav-posts") {
		$get_user_bookmared_posts = get_user_bookmared_posts_id($user_id);

		$query_fav_posts = new Query_post([
			"post_id" => $get_user_bookmared_posts,
			"post_lang" => false,
			"post_in" => false,
			'order' => ['posts.id', 'desc']
		]);
		$results = $query_fav_posts->get_posts();
	} elseif ($tab == "featured-posts") {
		$query_featured_posts = new Query_post(["post_status" => "publish", "in_special" => "on", "post_lang" => false, 'order' => ['posts.id', 'desc']]);
		$results = $query_featured_posts->get_posts();
	} elseif ($tab == "taxo-posts") {
		$get_taxo_feed = $dsql->dsql()->table('user_meta')->join('posts.post_type', 'user_meta.meta_value')->where('user_meta.meta_key', 'like', 'taxonomy_subscribe__%')->where('user_meta.meta_value', '!=', 'no')->where('user_meta.user_id', $user_id)->where('posts.post_status','publish')->where('posts.post_in','trusted')->order('posts.id', 'desc');
		$get_taxo_feed_rows = count_rows($get_taxo_feed->field('count(*)')->getRow());
		$get_taxo_feed->reset('getRow')->reset('field');
		$results = $get_taxo_feed->limit(paged('end'), paged('start'))->get();
	}

	if ($results) {
		echo profile_page_post_lyt($results);
	}
} elseif ($data == "my-posts-tab") {
	$user_id = $_GET["user_id"] ?? "";
	$post_type = $_GET["post_type"] ?? false;
	if ($post_type === "false" || empty($pos_type)) {
		$post_type = false;
	}
	$query_my_posts = new Query_post([
		"post_author" => $user_id,
		"post_type__not" => ['name', 'wisdom', 'dictionary', 'quote', 'history'],
		"post_type" => $post_type,
		"post_lang" => false,
		"post_in" => false,
		"order" => ['posts.id', 'desc']
	]);
	$my_posts = $query_my_posts->get_posts();
	if ($my_posts) :
		foreach ($my_posts as $my_post) : ?>
			<!--  post -->
			<div class="col-lg-3 col-md-4 col-sm-12 px-1 mb-2">
				<div class="posts-post h-100 position-relative overflow-hidden">
					<a href="<?php echo get_post_link($my_post); ?>" class="modal-link-post" data-id="<?php esc_html($my_post["id"]); ?>"><img src="<?php esc_html(get_thumb($my_post["post_thumbnail"], "md", true, $my_post["id"])); ?>" class="img-fluid object-fit w-100 h-100" /></a>
					<div class="post-details position-absolute w-100 h-100 top-0 right-0 animated fadeIn">
						<div class="d-flex flex-column h-100">
							<div class="mb-auto w-100">
								<a href="<?php echo get_post_link($my_post); ?>" data-id="<?php esc_html($my_post["id"]); ?>" class="bg-primary modal-link-post text-white text-center h6 px-2 py-2 d-block"><?php echo substr_str($my_post["post_title"], 16); ?></a>
								<?php echo bookmark_opt($my_post["id"]); ?>
							</div>
							<div class="my-auto text-center w-100">
								<i class="fas fa-search fa-lg text-white"></i>
							</div>
							<div class="d-flex p-2">
								<a href="<?php esc_html(get_author_in_post($my_post["post_author"])->link); ?>" class="text-light float-left author-in-post author-in-post-sm">
									<img src="<?php esc_html(get_author_in_post($my_post["post_author"])->user_picture); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
									<?php esc_html(get_author_in_post($my_post["post_author"])->user_name); ?>
								</a>
								<span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($my_post["post_views"]); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div><!-- / post -->
<?php
		endforeach;
	endif;
}elseif($data == "followers") {
    $user_id = $_GET["user_id"] ?? 0;
    $get_followers = get_followers($user_id);
    if($get_followers) :
	foreach($get_followers["results"] as $follow): ?>
		<div class="col-lg-6 col-sm-12 text-center user-profile px-2 mb-3">
			<?php echo user_box_module($follow); ?>
		</div>
	<?php endforeach; endif;
}
