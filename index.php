<?php
require_once 'init.php';

$homepage_blocs = extract(switch_blocs("homepage"));

if ($display_homepage_bloc_history == "on") {
	$history_posts = get_history_posts('gregorian', date('n'), date('d'));
}
if ($display_homepage_bloc_slide == "on") {

	$query_slide = new Query_post(array_merge([
		"in_slide" => "on",
		"limit" => $post_show_homepage_bloc_slide,
		"order" => $order_by_homepage_bloc_slide
	], $args_homepage_bloc_slide));

	$slide_posts = $query_slide->get_posts();
}

if ($display_homepage_bloc_feature == "on") {

	$query_special = new Query_post(array_merge([
		"in_special" => "on",
		"limit" => $post_show_homepage_bloc_feature,
		'order' => $order_by_homepage_bloc_feature
	], $args_homepage_bloc_feature));

	$special_posts = $query_special->get_posts();
}

if ($display_homepage_bloc_varied == "on") {
	$query_varied = new Query_post(array_merge([
		"post_type" => ["article", "image", "video", "author_article"],
		"limit" => $post_show_homepage_bloc_varied,
		"order" => $order_by_homepage_bloc_varied
	], $args_homepage_bloc_varied));
	$varied_posts = $query_varied->get_posts();
}

if ($display_homepage_bloc_books == "on") {
	$query_books = new Query_post(array_merge([
		"post_type" => "book",
		"limit" => $post_show_homepage_bloc_books,
		"order" => $order_by_homepage_bloc_books
	], $args_homepage_bloc_books));
	$book_posts = $query_books->get_posts();
}

if ($display_homepage_bloc_quote == "on") {

	$query_quotes = new Query_post(array_merge([
		"post_type" => "quote",
		"limit" => 1,
		"order" => $order_by_homepage_bloc_quote
	], $args_homepage_bloc_quote));

	$query_names = new Query_post(array_merge([
		"post_type" => "name",
		"limit" => 1,
		"order" => $order_by_homepage_bloc_names
	], $args_homepage_bloc_names));

	$query_dictionary = new Query_post(array_merge([
		"post_type" => "dictionary",
		"limit" => 1,
		"order" => $order_by_homepage_bloc_dictionary
	], $args_homepage_bloc_dictionary));

	$quote_post = $query_quotes->get_post();
	$name_post = $query_names->get_post();

	$dictionary_post = $query_dictionary->get_post();
}

if ($display_homepage_bloc_videos == "on") {
	$query_videos = new Query_post(array_merge([
		"post_type" => "video",
		"limit" => $post_show_homepage_bloc_videos,
		"order" => $order_by_homepage_bloc_videos
	], $args_homepage_bloc_videos));
	$video_posts = $query_videos->get_posts();
}

if ($display_homepage_bloc_images == "on") {
	$query_images = new Query_post(array_merge([
		"post_type" => "image",
		"limit" => $post_show_homepage_bloc_images,
		"order" => $order_by_homepage_bloc_images
	], $args_homepage_bloc_images));
	$image_posts = $query_images->get_posts();
}

if ($display_homepage_bloc_researches == "on") {
	$query_researches = new Query_post(array_merge([
		"post_type" => "research",
		"limit" => $post_show_homepage_bloc_researches,
		"order" => $order_by_homepage_bloc_researches
	], $args_homepage_bloc_researches));
	$research_posts = $query_researches->get_posts();
}

if ($display_homepage_bloc_author_articles == "on") {
	$query_author_articles = new Query_post(array_merge([
		"post_type" => "author_article",
		"limit" => $post_show_homepage_bloc_author_articles,
		"order" => $order_by_homepage_bloc_author_articles
	], $args_homepage_bloc_author_articles));
	$author_article_posts = $query_author_articles->get_posts();
}
if ($display_homepage_bloc_special_users == "on") {
	$get_users = get_users($order_by_homepage_bloc_special_users, 'desc', ['limit' => $post_show_homepage_bloc_special_users]);
}

if ($display_homepage_bloc_latest_views == "on") {

	$query_latest_posts = new Query_post([
		"post_type" => ["article", "image", "video", "author_article"],
		"order" => ['post_date_gmt', 'DESC'],
		'limit' =>  $post_show_homepage_bloc_latest_views
	]);

	$get_latest_post = $query_latest_posts->get_posts();

	$query_mostviewd_posts = new Query_post([
		"post_type" => ["article", "image", "video", "author_article", "book"],
		"order" => ['posts.post_views', 'DESC'],
		'limit' =>  $post_show_homepage_bloc_latest_views
	]);

	$get_mostviewd_posts = $query_mostviewd_posts->get_posts();
}

$get_ads = get_ads("ad_unit_index_115_560");
$get_ads_top_big = get_ads("ad_unit_index_top_big");
$get_ads_under_feature = get_ads("ad_unit_index_under_feature");
$get_ads_side = get_ads("ad_unit_index_side");

$current_lang = current_lang();
$site_title = @json_decode(get_settings("site_title"));
$site_keywords = @json_decode(get_settings("site_keywords"));
$site_desc = @json_decode(get_settings("site_desc"));
?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<title><?php @esc_html($site_title->{$current_lang}); ?></title>
	<meta name="description" content="<?php echo @$site_desc->{$current_lang}; ?>">
	<meta name="keywords" content="<?php esc_html(@$site_keywords->{$current_lang}); ?>">
</head>

<body>
	<button class="btn btn-warning rounded-circle to-top"><i class="fas fa-caret-up"></i></button>
	<?php get_header();  ?>
	<?php
	if ($display_homepage_bloc_st == "on"):
	?>
		<!-- Modal home video -->
		<div class="modal fade" id="homeEmbedvideo" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body text-center">
						<i class="fas fa-spinner fa-spin fa-4x"></i>
					</div>
				</div>
			</div>
		</div><!-- Modal home video -->
	<?php endif; ?>

	<div class="container">
		<div class="my-3"></div>

		<?php
		if ($display_homepage_bloc_st == "on"):
			$bloc_st_text = explode(",", $bloc_st_text_homepage_bloc_st[$current_lang]);
			$bloc_st_text_js = json_encode($bloc_st_text);
		?>
			<div class="jumbotron jumbotron-home bg-primary px-2 py-0 rounded-0 position-relative">
				<div class="row">
					<div class="sign-home-btns col-lg-2 col-md-12 py-2">
						<div class="mb-3">
							<button class="remove-jumbotron-home btn btn-danger rounded-circle"><i class="fas fa-times"></i></button>
						</div>
						<?php if (is_login_in() === false): ?>
							<div class="row m-0">
								<div class="col-12 col-md-6 col-lg-12 pl-0 mb-3">
									<a href="<?php echo siteurl(); ?>/signin.php" class="hover-animate signin-home btn btn-lg bg-green text-white" data-animation-in=".signin-home" data-animation-name="shake"><?php echo _t("تسجيل الدخول"); ?></a>
								</div>
								<div class="col-12 col-md-6 col-lg-12 pl-0">
									<button class="login-modal hover-animate signup-home btn btn-lg bg-green text-white mb-0 mb-md-2" data-animation-in=".signup-home" data-animation-name="shake"><?php echo _t("حساب جديد"); ?></button>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<div class="col-lg-5 col-md-12 py-2  d-md-flex align-items-md-center">
						<span class="h2 typed-element-1"><?php esc_html($bloc_st_text[0]); ?></span>
					</div>
					<div class="col-lg-5 col-md-12 position-relative home-page-video-thumb  py-2" data-url="<?php esc_html($bloc_st_vid_homepage_bloc_st[$current_lang]); ?>">
						<div class="position-absolute w-100 h-100 right-0 top-0 d-flex align-items-center justify-content-center hover-animate" data-animation-in=".home-video-thumb-icon" data-animation-name="bounce">
							<i class="fas fa-play fa-2x color-primary rounded-circle home-video-thumb-icon"></i>
						</div>
						<img src="<?php echo get_thumb($bloc_st_vid_image_homepage_bloc_st, ["w" => 440, "h" => 220]); ?>" class="img-fluid" />
					</div>
				</div>
			</div>
			<div class="my-3"></div>
		<?php endif; ?>

		<?php if ($get_ads_top_big): ?>
			<!-- ads area -->
			<div class="row">
				<?php foreach ($get_ads_top_big as $ad_top): ?>
					<div class="col-12">
						<?php
						update_ad_views($ad_top["ad_key"]);
						echo $ad_top["ad_code"];
						?>
					</div>
				<?php endforeach; ?>
			</div>
			<!-- /ads area -->
			<div class="my-3"></div>
		<?php endif; ?>

		<?php if ($get_ads): ?>
			<!-- ads area -->
			<div class="row">
				<?php foreach ($get_ads as $ad): ?>
					<div class="col-12 col-md-6 pb-3">
						<?php
						update_ad_views($ad["ad_key"]);
						echo $ad["ad_code"];
						?>
					</div>
				<?php endforeach; ?>
			</div>
			<!-- /ads area -->
		<?php endif; ?>
		<div class="my-3"></div>
		<div class="row m-0 overflow-hidden">
			<?php if ($display_homepage_bloc_history == "on"): ?>
				<!-- History section -->
				<div class="col-md-4 col-sm-12 border p-0">
					<div class="history-posts">
						<div class="history-posts-head bg-darker py-3">
							<div class="d-flex px-2">
								<span class="mr-auto text-warning navigate-history" data-history="yesterday"><?php echo _t("الأمس"); ?></span>
								<span class="mx-auto text-white navigate-history" data-history="today"><?php echo date("d-m-Y"); ?></span>
								<span class="ml-auto text-warning navigate-history" data-history="tomorrow"><?php echo _t("غدا"); ?></span>
							</div>
						</div>
						<div class="accordion history-posts-load">
							<a href="#" class="btn btn-block btn-warning text-left btn-lg rounded-0 toggle-history-btn" data-tab="#today-history"><?php echo _t('في مثل هذا اليوم'); ?></a>
							<div class="history-tab-shown" id="today-history">
								<?php if ($history_posts): ?>
									<ul class="timeline mb-0">
										<?php foreach ($history_posts["today"] as $history_post_today): ?>
											<li>
												<a href="<?php echo get_post_link($history_post_today); ?>" class="float-left"><?php esc_html($history_post_today["post_title"]); ?></a><br />
												<p><?php echo substr_str(strip_tags($history_post_today["post_content"]), 120); ?></p>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
							<a href="#" class="btn btn-block btn-warning text-left btn-lg rounded-0 toggle-history-btn" data-tab="#deaths-history"><?php echo _t('ولادة و وفيات اليوم'); ?></a>
							<div class="history-tab-hidden" id="deaths-history">
								<ul class="timeline mb-0">
									<?php foreach ($history_posts["deaths"] as $history_post_deaths): ?>
										<li>
											<a href="<?php echo get_post_link($history_post_deaths); ?>" class="float-left"><?php esc_html($history_post_deaths["post_title"]); ?></a><br />
											<p><?php echo substr_str(strip_tags($history_post_deaths["post_content"]), 120); ?></p>
										</li>
									<?php endforeach; ?>
								</ul>

							</div>
							<a href="#" class="btn btn-block btn-warning text-left btn-lg rounded-0 toggle-history-btn" data-tab="#occasion-history"><?php echo _t('مناسبات اليوم'); ?></a>
							<div class="history-tab-hidden" id="occasion-history">
								<ul class="timeline mb-0">
									<?php foreach ($history_posts["occasions"] as $history_post_occasions): ?>
										<li>
											<a href="<?php echo get_post_link($history_post_occasions); ?>" class="float-left"><?php esc_html($history_post_occasions["post_title"]); ?></a><br />
											<p><?php echo substr_str(strip_tags($history_post_occasions["post_content"]), 120); ?></p>
										</li>
									<?php endforeach; ?>
								</ul>


							</div>
						</div>
					</div>
				</div><!-- History section -->
			<?php endif; ?>

			<?php if ($display_homepage_bloc_slide == "on"): ?>
				<!-- slide section -->
				<div class="col-md-8 col-sm-12 px-0 pl-md-3 mt-3 mt-md-0">
					<div class="slide-posts position-relative border">
						<!-- Slick Navigation -->
						<div class="position-absolute w-100 h-100 section-navs-h">
							<div class="d-flex align-items-center h-100">
								<div class="slick-arrow-h slick-prev-h  smooth-transition animated bounceInRight"><i class="fas fa-arrow-right fa-lg"></i></div>
								<div class="slick-arrow-h slick-next-h ml-auto smooth-transition animated bounceInLeft"><i class="fas fa-arrow-left fa-lg"></i></div>
							</div>
						</div><!-- / Slick Navigation -->
						<div class="home-slide-section">
							<?php
							if ($slide_posts):
								foreach ($slide_posts as $slide_post):
							?>
									<!-- slide post -->
									<div class="slide-post position-relative">
										<div class="h-100">
											<img src="<?php esc_html(get_thumb($slide_post["post_thumbnail"], ["w" => 720, "h" => 480], true, $slide_post["id"])); ?>" class="h-100 w-100 object-fit" />
										</div>
										<?php echo bookmark_opt($slide_post["id"]); ?>
										<div class="post-details p-4 position-absolute w-100">
											<a href="<?php echo get_post_link($slide_post); ?>" class="h5 link-light"><?php esc_html($slide_post["post_title"]); ?></a>
										</div>
									</div><!-- slide post -->
							<?php endforeach;
							endif; ?>
						</div>
					</div>
				</div><!-- slide section -->
			<?php endif; ?>
		</div>

		<div class="my-3"></div>
		<div class="row">
			<div class="col-md-8 col-sm-12">
				<div class="posts-section">
					<?php if ($display_homepage_bloc_feature == "on"): ?>
						<!-- featured section -->
						<div class="home-section-title text-center">
							<span class="h5 bg-white px-3"><?php echo _t("مميز"); ?></span>
						</div>
						<div class="my-3"></div>
						<div class="position-relative m-0 bg-darker p-3 featured-posts">
							<!-- Slick Navigation -->
							<div class="position-absolute w-100 h-100 section-navs-h">
								<div class="d-flex align-items-center h-100">
									<div class="slick-arrow-h slick-prev-h  smooth-transition animated bounceInRight"><i class="fas fa-arrow-right fa-lg"></i></div>
									<div class="slick-arrow-h slick-next-h ml-auto smooth-transition animated bounceInLeft"><i class="fas fa-arrow-left fa-lg"></i></div>
								</div>
							</div><!-- / Slick Navigation -->
							<div class="home-featured-section">
								<?php if ($special_posts): foreach ($special_posts as $special_post): ?>
										<!-- post -->
										<div class="p-1">
											<div class="bg-white p-2 position-relative">
												<?php echo bookmark_opt($special_post["id"]); ?>
												<div class="post-thumb post-thumb-scale overflow-hidden position-relative">
													<a href="<?php echo get_post_link($special_post); ?>">
													</a>
													<img src="<?php esc_html(get_thumb($special_post["post_thumbnail"], ["w" => 480, "h" => 280], true, $special_post["id"])); ?>" class="img-fluid  thumb smooth-transition" />
													<div class="post-details p-2 position-absolute w-100 animated fadeIn">
														<div class="d-flex">
															<div>
																<a href="<?php esc_html(get_author_in_post($special_post["post_author"])->link); ?>" class="link-light author-in-post author-in-post-sm">
																	<img src="<?php esc_html(get_author_in_post($special_post["post_author"])->user_picture); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
																	<?php esc_html(get_author_in_post($special_post["post_author"])->user_name); ?>
																</a>
															</div>
															<!-- <span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($special_post["post_views"]); ?></span> -->
														</div>
													</div>
												</div>
												<div class="post-title my-2">
													<a href="<?php echo get_post_link($special_post); ?>" class="color-link font-weight-bold"><?php echo substr_str($special_post["post_title"], 26); ?></a>
												</div>
											</div>
										</div>
										<!-- / post -->
								<?php endforeach;
								endif; ?>
							</div>
						</div><!-- /featured section -->
						<div class="my-3"></div>
						<?php if ($get_ads_under_feature): ?>
							<!-- ads area -->
							<div class="row">
								<?php foreach ($get_ads_under_feature as $ad_2): ?>
									<div class="col-md-12">
										<?php
										update_ad_views($ad_2["ad_key"]);
										echo $ad_2["ad_code"];
										?>
									</div>
								<?php endforeach; ?>
							</div>
							<!-- /ads area -->
							<div class="my-3"></div>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ($display_homepage_bloc_varied == "on"): ?>
						<!-- Random posts -->
						<div class="home-section-title text-center">
							<span class="h5 bg-white px-3"><?php echo _t("مواضيع متنوعة"); ?></span>
						</div>
						<div class="my-3"></div>
						<div class="position-relative m-0 row home-random-section">
							<?php if ($varied_posts): foreach ($varied_posts as $varied_post): ?>
									<!-- post -->
									<div class="post-rand col-md-6 col-sm-12 mb-3 pr-2 pl-0">
										<div class="d-sm-flex h-100">
											<div class="position-relative post-thumb mr-sm-3 mb-2 mb-sm-0">
												<?php echo img_tag(get_thumb($varied_post["post_thumbnail"], ["w" => 480, "h" => 360], true, $varied_post["id"]), 'class="lazy-load w-100 h-100 img-fluid object-fit" alt="image"'); ?>
												<div class="icon-thumb-rand position-absolute animated zoomIn h-100 w-100">
													<div class="d-flex justify-content-center align-items-center h-100">
														<i class="fas fa-file fa-lg text-white"></i>
													</div>
												</div>
											</div>
											<div class="w-75 overflow-hidden">
												<a href="<?php echo get_post_link($varied_post); ?>" class="h6 color-link"><?php echo substr_str($varied_post["post_title"], 35); ?></a><br />
												<div class="post-details d-flex mt-2 align-items-center">
													<a href="<?php esc_html(get_user_link($varied_post["post_author"])); ?>" class="text-secondary author-in-post author-in-post-sm">
														<img src="<?php echo get_thumb(get_user_meta($varied_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
														<?php esc_html(get_author_in_post($varied_post["post_author"])->user_name); ?>
													</a>
													<!-- <span class="small ml-2"><i class="fas fa-eye mr-1"></i><?php esc_html($varied_post["post_views"]); ?></span> -->
													<div class="ml-auto">
														<?php echo bookmark_opt($varied_post["id"]); ?>
													</div>
												</div>
											</div>
										</div>
									</div><!-- / post -->
							<?php endforeach;
							else: no_content();
							endif; ?>
						</div><!-- / Random posts -->
					<?php endif; ?>

					<?php if ($display_homepage_bloc_books == "on"): ?>
						<!-- Book posts -->
						<div class="my-3"></div>
						<div class="home-section-title text-center">
							<span class="h5 bg-white px-3"><?php echo _t('كتب PDF'); ?></span>
						</div>
						<div class="my-3"></div>
						<div class="book-posts position-relative">
							<?php if ($book_posts): ?>

								<!-- Slick Navigation -->
								<div class="position-absolute w-100 h-100 section-navs-h">
									<div class="d-flex align-items-center h-100">
										<div class="slick-arrow-h slick-prev-h  smooth-transition animated bounceInRight"><i class="fas fa-arrow-right fa-lg"></i></div>
										<div class="slick-arrow-h slick-next-h ml-auto smooth-transition animated bounceInLeft"><i class="fas fa-arrow-left fa-lg"></i></div>
									</div>
								</div><!-- / Slick Navigation -->

								<div class="home-books-section">
									<?php
									foreach ($book_posts as $book_post):
									?>
										<!-- Post book -->
										<div class="post-book px-1">
											<div class="position-relative">
												<div class="post-thumb h-100">
													<a href="<?php echo get_post_link($book_post); ?>"><img src="<?php echo get_thumb($book_post["post_thumbnail"], ["h" => 280, "w" => 200]); ?>" class="img-fluid h-100 w-100" alt="" /></a>
												</div>
												<div class="post-details position-absolute w-100 h-100 animated fadeIn">
													<div class="d-flex flex-column h-100">
														<div class="mb-auto w-100 text-ellipsis">
															<a href="<?php echo get_post_link($book_post); ?>" class="bg-primary text-white text-center h6 px-2 py-2 d-block"><?php esc_html($book_post["post_title"]); ?></a>
															<?php echo bookmark_opt($book_post["id"]); ?>
														</div>
														<div class="my-auto py-5 text-center w-100">
															<i class="fas fa-search fa-lg text-white fa-4x"></i>
														</div>
														<div class="d-flex p-2">
															<a href="<?php esc_html(get_author_in_post($book_post["post_author"])->link); ?>" class="text-light author-in-post author-in-post-sm">
																<img src="<?php echo get_thumb(get_user_meta($book_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
																<?php esc_html(get_author_in_post($book_post["post_author"])->user_name); ?>
															</a>
															<!-- <span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($book_post["post_views"]); ?></span> -->
														</div>
													</div>
												</div>
											</div>
										</div><!-- / Post book -->
									<?php
									endforeach;
									?>
								</div>
							<?php else: no_content();
							endif; ?>
						</div><!-- / Book posts -->
						<div class="my-3"></div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Sidebar Home -->
			<div class="col-md-4 col-sm-12">
				<?php if ($display_homepage_bloc_author_articles == "on"): ?>
					<!-- author articles -->
					<div class="sidebar-box sidebar-box-author-articles">
						<div class="position-relative sidebar-box-title bg-darker py-3">
							<span class="text-white"><?php echo _t('مقالات الرأي'); ?></span>
							<!-- Slick Navigation Vertical -->
							<div class="section-navs-v position-absolute">
								<div class="slick-arrow-v slick-small-arrow-v slick-next-v ml-auto smooth-transition animated bounceInLeft"><i class="fas fa-caret-up text-white fa-lg"></i></div>
								<div class="slick-arrow-v slick-small-arrow-v slick-prev-v  smooth-transition animated bounceInRight"><i class="fas fa-caret-down text-white fa-lg"></i></div>
							</div><!-- / Slick Navigation Vertical -->
						</div>
						<div class="sidebar-box-body">
							<?php
							if ($author_article_posts):
								foreach ($author_article_posts as $author_article_post):
							?>
									<!-- nav post -->
									<div class="sidebar-box-nav-post p-2">
										<div class="d-flex align-items-center">
											<div class="nav-post-text mr-3 text-ellipsis">
												<a href="<?php echo get_post_link($author_article_post); ?>" class="color-link h6"><?php esc_html($author_article_post["post_title"]); ?></a><br />
												<a href="<?php esc_html(get_author_in_post($author_article_post["post_author"])->link); ?>" class="text-secondary float-left author-in-post author-in-post-sm">
													<img src="<?php echo get_thumb(get_user_meta($author_article_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
													<?php esc_html(get_author_in_post($author_article_post["post_author"])->user_name); ?>
												</a>
												<!-- <span class="small float-left ml-2"><i class="fas fa-eye"></i>&nbsp;<?php esc_html($author_article_post["post_views"]); ?></span> -->
												<span class="ml-2 small-bookmark float-left">
													<?php echo bookmark_opt($author_article_post["id"]); ?>
												</span>
											</div>
											<div class="post-thumb ml-auto">
												<a href="<?php echo get_post_link($author_article_post); ?>"><img src="<?php esc_html(get_thumb(get_user_meta($author_article_post["post_author"], "user_picture"), "sm")); ?>" class="" alt="" /></a>
											</div>
										</div>
									</div>
							<?php
								endforeach;
							else:
								no_content();
							endif;
							?>
						</div>
					</div><!-- author articles -->

					<div class="my-3"></div>
				<?php endif; ?>
				<?php if ($display_homepage_bloc_latest_views == "on"): ?>
					<!-- Sidebar navs -->
					<div class="sidebar-box sidebar-navs">
						<ul class="nav mb-3 sidebar-box-title bg-darker row mx-0" id="sidebar-box-tab" role="tablist">
							<li class="nav-item col p-0">
								<a class="nav-link py-2 active" id="sidebar-box-mostviewd-posts-tab" data-toggle="tab" href="#tab-mostviewd-posts" role="tab" aria-controls="tab-mostviewd-posts" aria-selected="false"><?php echo _t("اكثر مشاهدة"); ?></a>
							</li>
							<li class="nav-item col p-0">
								<a class="nav-link py-2" id="sidebar-box-new-posts-tab" data-toggle="tab" href="#tab-new-posts" role="tab" aria-controls="tab-new-posts" aria-selected="true"><?php echo _t("جديد"); ?></a>
							</li>
						</ul>
						<div class="tab-content" id="sidebar-box-tabContent sidebar-box-body">
							<div class="tab-pane fade show active" id="tab-mostviewd-posts" role="tabpanel" aria-labelledby="sidebar-box-mostviewd-posts-tab">
								<?php
								if ($get_mostviewd_posts):
									foreach ($get_mostviewd_posts as $mostviewd_post):
								?>
										<!-- nav post -->
										<div class="sidebar-box-nav-post p-2">
											<div class="d-flex align-items-center">
												<div class="nav-post-text mr-3 text-ellipsis">
													<a href="<?php echo get_post_link($mostviewd_post); ?>" class="color-link h6"><?php esc_html($mostviewd_post["post_title"]); ?></a><br />
													<a href="<?php esc_html(get_author_in_post($mostviewd_post["post_author"])->link); ?>" class="text-secondary float-left author-in-post author-in-post-sm">
														<img src="<?php echo get_thumb(get_user_meta($mostviewd_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
														<?php esc_html(get_author_in_post($mostviewd_post["post_author"])->user_name); ?>
													</a>
													<!-- <span class="small float-left ml-2"><i class="fas fa-eye"></i>&nbsp;<?php esc_html($mostviewd_post["post_views"]); ?></span> -->
													<span class="ml-2 small-bookmark float-left">
														<?php echo bookmark_opt($mostviewd_post["id"]); ?>
													</span>
												</div>
												<div class="post-thumb ml-auto">
													<?php if (get_thumb($mostviewd_post["post_thumbnail"], "sm", true, $mostviewd_post["id"])): ?>
														<a href="<?php echo get_post_link($mostviewd_post); ?>"><img src="<?php esc_html(get_thumb($mostviewd_post["post_thumbnail"], "sm", true, $mostviewd_post["id"])); ?>" class="" alt="" /></a>
													<?php endif; ?>
												</div>
											</div>
										</div>
										<!-- / nav post -->
								<?php endforeach;
								endif; ?>
							</div>
							<div class="tab-pane fade" id="tab-new-posts" role="tabpanel" aria-labelledby="sidebar-box-new-posts-tab">
								<?php
								if ($get_latest_post):
									foreach ($get_latest_post as $latest_post):
								?>
										<!-- nav post -->
										<div class="sidebar-box-nav-post p-2">
											<div class="d-flex align-items-center">
												<div class="nav-post-text mr-3 text-ellipsis">
													<a href="<?php echo get_post_link($latest_post); ?>" class="color-link h6"><?php esc_html($latest_post["post_title"]); ?></a><br />
													<a href="<?php esc_html(get_author_in_post($latest_post["post_author"])->link); ?>" class="text-secondary float-left author-in-post author-in-post-sm">
														<img src="<?php echo get_thumb(get_user_meta($latest_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
														<?php esc_html(get_user_meta($latest_post["post_author"], 'user_name')); ?>
													</a>
													<!-- <span class="small float-left ml-2"><i class="fas fa-eye"></i>&nbsp;<?php esc_html($latest_post["post_views"]); ?></span> -->
													<span class="ml-2 small-bookmark float-left">
														<?php echo bookmark_opt($latest_post["id"]); ?>
													</span>
												</div>
												<div class="post-thumb ml-auto">
													<?php if (get_thumb($latest_post["post_thumbnail"], "sm", true, $latest_post["id"])): ?>
														<a href="<?php echo get_post_link($latest_post); ?>"><img src="<?php esc_html(get_thumb($latest_post["post_thumbnail"], "sm", true, $latest_post["id"])); ?>" class="" alt="" /></a>
													<?php endif; ?>
												</div>
											</div>
										</div>
										<!-- / nav post -->
								<?php
									endforeach;
								else: no_content();
								endif;
								?>
							</div>
						</div>
					</div><!-- Sidebar navs -->
				<?php endif; ?>
				<div class="my-3"></div>
				<?php if ($display_homepage_bloc_researches == "on"):  ?>
					<!-- Research -->
					<div class="sidebar-box sidebar-book-vertical bg-darker">
						<div class="position-relative sidebar-box-title bg-darker py-3">
							<span class="text-white"><?php echo _t('كتب Word'); ?></span>
							<!-- Slick Navigation Vertical -->
							<div class="section-navs-v position-absolute">
								<div class="slick-arrow-v slick-small-arrow-v slick-next-v ml-auto smooth-transition animated bounceInLeft"><i class="fas fa-caret-up text-white fa-lg"></i></div>
								<div class="slick-arrow-v slick-small-arrow-v slick-prev-v  smooth-transition animated bounceInRight"><i class="fas fa-caret-down text-white fa-lg"></i></div>
							</div><!-- / Slick Navigation Vertical -->
						</div>
						<?php if ($research_posts): ?>
							<div class="row m-0 py-2">
								<div class="col-3 px-1 post-research-thumbs overflow-hidden">
									<?php foreach ($research_posts as $research_post): ?>
										<img src="<?php echo get_thumb($research_post["post_thumbnail"], ["h" => 320, "w" => 200]); ?>" class="img-fluid" />
									<?php endforeach; ?>
								</div>
								<div class="home-books-research-section col-9 px-1">
									<?php foreach ($research_posts as $research_post): ?>
										<!-- post research -->
										<div class="post-research position-relative mb-2">
											<img src="<?php echo get_thumb($research_post["post_thumbnail"], ["h" => 320, "w" => 200]); ?>" class="img-fluid w-100" />
											<div class="post-details position-absolute w-100 h-100 animated fadeIn">
												<div class="d-flex flex-column h-100">
													<div class="mb-auto w-100">
														<a href="<?php echo get_post_link($research_post); ?>" class="bg-primary text-white text-center h6 px-2 py-2 d-block"><?php esc_html(substr_str($research_post["post_title"], 30)); ?></a>
														<?php echo bookmark_opt($research_post["id"]); ?>
													</div>
													<div class="my-auto py-5 text-center w-100">
														<i class="fas fa-search fa-lg text-white fa-4x"></i>
													</div>
													<div class="d-flex p-2">
														<a href="<?php esc_html(get_author_in_post($research_post["post_author"])->link); ?>" class="link-light float-left author-in-post author-in-post-sm">
															<img src="<?php echo get_thumb(get_user_meta($research_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
															<?php esc_html(get_author_in_post($research_post["post_author"])->user_name); ?>
														</a>
														<!-- <span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($research_post["post_views"]); ?></span> -->
													</div>
												</div>
											</div>
										</div>
										<!-- post research -->
									<?php endforeach; ?>
								</div>
							</div>
						<?php else: no_content();
						endif; ?>
					</div><!-- / Research -->
				<?php endif; ?>
				<?php if ($get_ads_side): ?>
					<!-- ads area -->
					<div class="row">
						<?php foreach ($get_ads_side as $ad_3): ?>
							<div class="col-md-12 my-2">
								<?php
								update_ad_views($ad_3["ad_key"]);
								echo $ad_3["ad_code"];
								?>
							</div>
						<?php endforeach; ?>
					</div>
					<!-- /ads area -->
				<?php endif; ?>

			</div><!-- Sidebar -->
		</div>

		<div class="my-3"></div>

		<div class="row">
			<?php if ($display_homepage_bloc_images == "on"): ?>
				<!-- Image posts -->
				<div class="col-md-6 col-sm-12">
					<div class="home-section-title text-center">
						<span class="h5 bg-white px-3"><?php echo _t('حكاية صور'); ?></span>
					</div>
					<div class="my-3"></div>
					<div class="col-12 bg-darker p-2">
						<?php if ($image_posts): ?>
							<div class="home-images-section">
								<?php foreach ($image_posts as $image_post): ?>
									<!-- image post -->
									<div class="post-image post-image-<?php esc_html($image_post["id"]); ?> position-relative slick-content">
										<img src="<?php esc_html(get_thumb($image_post["post_thumbnail"], ["w" => 528, "h" => 320])); ?>" class="img-fluid" />
										<div class="post-details position-absolute w-100 h-100 animated fadeIn">
											<div class="d-flex flex-column h-100">
												<div class="mb-auto w-100">
													<a href="<?php echo get_post_link($image_post); ?>" class="bg-primary text-white text-center h6 px-2 py-2 d-block"><?php esc_html($image_post["post_title"]); ?></a>
													<?php echo bookmark_opt($image_post["id"]); ?>
												</div>
												<div class="my-auto py-5 text-center w-100">
													<i class="fas fa-search fa-lg text-white fa-4x"></i>
												</div>
												<div class="d-flex p-2">
													<a href="<?php esc_html(get_author_in_post($image_post["post_author"])->link); ?>" class="text-light float-left author-in-post author-in-post-sm">
														<img src="<?php echo get_thumb(get_user_meta($image_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
														<?php esc_html(get_author_in_post($image_post["post_author"])->user_name); ?>
													</a>
													<!-- <span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($image_post["post_views"]); ?></span> -->
												</div>
											</div>
										</div>
									</div><!-- / image post -->
								<?php endforeach; ?>
							</div>

							<!-- Image post nav -->
							<div class="position-relative images-posts-nav">
								<!-- Slick Navigation -->
								<div class="position-absolute w-100 h-100 section-navs-h">
									<div class="d-flex align-items-center h-100">
										<div class="slick-arrow-h slick-small-arrow-h slick-prev-h  smooth-transition animated bounceInRight"><i class="fas fa-arrow-right"></i></div>
										<div class="slick-arrow-h slick-small-arrow-h slick-next-h ml-auto smooth-transition animated bounceInLeft"><i class="fas fa-arrow-left"></i></div>
									</div>
								</div><!-- / Slick Navigation -->
								<div class="home-images-section-nav mt-2">
									<?php foreach ($image_posts as $image_post): ?>
										<div class="post-image-nav post-image-nav-<?php esc_html($image_post["id"]); ?> px-1">
											<img src="<?php esc_html(get_thumb($image_post["post_thumbnail"], ["w" => 280, "h" => 180])); ?>" class="img-fluid" />
										</div>
									<?php endforeach; ?>
								</div>
							</div><!-- / Image post nav -->
						<?php endif; ?>
					</div>
				</div><!-- / Image posts -->
			<?php endif; ?>

			<?php if ($display_homepage_bloc_videos == "on"): ?>
				<!-- Video posts -->
				<div class="col-md-6 col-sm-12 mt-3 mt-sm-0">
					<div class="home-section-title text-center">
						<span class="h5 bg-white px-3"><?php echo _t('حكاية فيديو'); ?></span>
					</div>
					<div class="my-3"></div>
					<div class="col-12 bg-darker p-2">
						<?php if ($video_posts): ?>
							<div class="home-videos-section">
								<?php
								foreach ($video_posts as $video_post):
								?>
									<!-- Video post -->
									<div class="post-video position-relative slick-content">
										<img src="<?php esc_html(get_thumb($video_post["post_thumbnail"], ["w" => 528, "h" => 320], true, $video_post["id"])); ?>" class="img-fluid" />
										<div class="post-details position-absolute w-100 h-100 animated fadeIn">
											<div class="d-flex flex-column h-100">
												<div class="mb-auto w-100">
													<a href="<?php echo get_post_link($video_post); ?>" class="bg-primary text-white text-center h6 px-2 py-2 d-block"><?php esc_html($video_post["post_title"]); ?></a>
													<?php echo bookmark_opt($video_post["id"]); ?>
												</div>
												<div class="my-auto py-5 text-center w-100">
													<i class="fas fa-search fa-lg text-white fa-4x"></i>
												</div>
												<div class="d-flex p-2">
													<a href="<?php //esc_html( get_author_in_post($video_post["post_author"])->link ); 
																		?>" class="text-light float-left author-in-post author-in-post-sm">
														<img src="<?php echo get_thumb(get_user_meta($video_post["post_author"], 'user_picture')); ?>" class="float-left mr-2 rounded-circle" width="18" height="18" />
														<?php esc_html(get_author_in_post($video_post["post_author"])->user_name); ?>
													</a>
													<!-- <span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($video_post["post_views"]); ?></span> -->
												</div>
											</div>
										</div>
									</div><!-- / Video post -->
								<?php endforeach; ?>
							</div>

							<!-- Video post nav -->
							<div class="position-relative videos-posts-nav">
								<!-- Slick Navigation -->
								<div class="position-absolute w-100 h-100 section-navs-h">
									<div class="d-flex align-items-center h-100">
										<div class="slick-arrow-h slick-small-arrow-h slick-prev-h  smooth-transition animated bounceInRight"><i class="fas fa-arrow-right"></i></div>
										<div class="slick-arrow-h slick-small-arrow-h slick-next-h ml-auto smooth-transition animated bounceInLeft"><i class="fas fa-arrow-left"></i></div>
									</div>
								</div><!-- / Slick Navigation -->
								<div class="home-videos-section-nav mt-2">
									<?php foreach ($video_posts as $video_post): ?>
										<div class="post-video-nav px-1">
											<img src="<?php esc_html(get_thumb($video_post["post_thumbnail"], ["w" => 280, "h" => 180], true, $video_post["id"])); ?>" class="img-fluid" />
										</div>
									<?php endforeach; ?>
								</div>
							</div><!-- / Video post nav -->
						<?php endif; ?>
					</div>
				</div><!-- / Video posts -->
			<?php endif; ?>
		</div>

		<div class="my-3"></div>
		<div class="row home-wisdom-section">
			<?php if ($display_homepage_bloc_names == "on"): ?>
				<!-- name post -->
				<div class="col-md-4 col-sm-12 post-wisdom mb-3 mb-sm-0">
					<div class="border h-100 position-relative">
						<div class="bg-darker his-wisdom d-flex flex-column"><span><?php echo _t("اسماء"); ?></span></div>
						<div class="wisdom-text mr-2 p-2">
							<span>
								<?php if($name_post): ?>
								<a href="<?php echo get_post_link($name_post); ?>" class="font-weight-bold text-danger"><?php esc_html($name_post["post_title"]); ?></a> : <?php echo strip_tags(substr_str($name_post["post_content"], 150)); ?>
								<?php else: ?>
									<span><?php echo _t("فارغ"); ?></span>
								<?php endif; ?>
							</span>
						</div>
					</div>
				</div><!-- / name post -->
			<?php endif; ?>
			<?php if ($display_homepage_bloc_quote == "on"): ?>
				<!-- quote post -->
				<div class="col-md-4 col-sm-12 post-wisdom mb-3 mb-sm-0">
					<div class="border h-100 position-relative">
						<div class="bg-darker his-wisdom d-flex flex-column"><span><?php echo _t("حكم"); ?></span></div>
						<div class="wisdom-text mr-2 p-2">
							<span>
								<?php if($quote_post): ?>
									<a href="<?php echo get_post_link($quote_post); ?>" class="font-weight-bold text-danger"><?php esc_html($quote_post["post_title"]); ?></a> : <?php echo strip_tags(substr_str($quote_post["post_content"], 150)); ?>
								<?php else: ?>
									<span><?php echo _t("فارغ"); ?></span>
								<?php endif; ?>
							</span>
						</div>
					</div>
				</div><!-- / quote post -->
			<?php endif; ?>
			<?php if ($display_homepage_bloc_dictionary == "on"): ?>
				<!-- dictionary post -->
				<div class="col-md-4 col-sm-12 post-wisdom mb-3 mb-sm-0">
					<div class="border h-100 position-relative">
						<div class="bg-darker his-wisdom d-flex flex-column"><span><?php echo _t("قاموس"); ?></span></div>
						<div class="wisdom-text mr-2 p-2">
							<span>
								<?php if($quote_post): ?>
									<a href="<?php echo get_post_link($dictionary_post); ?>" class="font-weight-bold text-danger"><?php esc_html($dictionary_post["post_title"]); ?></a> : <?php echo strip_tags(substr_str($dictionary_post["post_content"], 150)); ?>
								<?php else: ?>
									<span><?php echo _t("فارغ"); ?></span>
								<?php endif; ?>
							</span>
						</div>
					</div>
				</div><!-- / dictionary post -->
			<?php endif; ?>
		</div><!-- / quotes section -->
		<div class="my-3"></div>
		<?php if ($display_homepage_bloc_special_users == "on"): ?>
			<!-- Users -->
			<div class="home-users bg-light border">
				<div class="d-flex border-bottom p-3">
					<h5 class="mb-0"><?php echo _t("المدونون"); ?></h5>
					<div class="ml-auto d-flex">
						<div class="slick-prev-h"><button class="btn btn-warning"><i class="fas fa-caret-right"></i></button></div>
						<div class="slick-next-h ml-2"><button class="btn btn-warning"><i class="fas fa-caret-left"></i></button></div>
					</div>
				</div>
				<?php if ($get_users): ?>
					<div class="home-users-slide p-3">
						<?php foreach ($get_users['results'] as $user):  ?>
							<div class="p-1 text-center">
								<div class="user-slide border p-3">
									<div class="user-slider--picture mx-auto">
										<a href="<?= SITENAME; ?>/user/<?php esc_html($user["id"]); ?>"><img src="<?php echo get_thumb($user["user_picture"]); ?>" class="img-fluid rounded-circle" /></a>
									</div>
									<div class="user-slide--name py-2">
										<a href="<?= SITENAME; ?>/user/<?php esc_html($user["id"]); ?>">
											<h5 class="mb-0"><?php esc_html(substr_str($user["user_name"], 10, "")); ?></h5>
										</a>
									</div>
									<div class="user-slide--posts">
										<span><?php echo _t("مواضيع") . " " . count_user_posts($user["id"], 'trusted'); ?> </span>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="mt-3"></div>
			<!-- /Users -->
		<?php endif; ?>
		<?php
		if ($display_homepage_bloc_counter == "on"):
			$count_users = count_rows($dsql->dsql()->table('users')->where('user_status', 'active')->field('count(*)')->getRow());
			$count_posts = count_rows($dsql->dsql()->table('posts')->where('post_status', 'publish')->field('count(*)')->getRow());

			$count_visits = count_rows($dsql->dsql()->table('analytics')->where('analysis_key', 'post_views')->field('count(*)')->getRow());
		?>
			<!-- counter section -->
			<div class="home-counter-section bg-dark py-3">
				<div class="row">
					<!-- Counter -->
					<div class="col-md-4 col-sm-12 mb-3 mb-sm-0">
						<div class="col-12 text-center">
							<i class="fas fa-user fa-4x mb-2 color-primary"></i>
							<span class="h1 d-block text-white counter" data-count="<?php esc_html($count_users); ?>">0</span>
							<div class="counter-section-divider mx-auto mb-2"></div>
							<span class="h5 text-white"><?php echo _t("عدد المدونين"); ?></span>
						</div>
					</div><!-- Counter -->
					<!-- Counter -->
					<div class="col-md-4 col-sm-12 mb-3 mb-sm-0">
						<div class="col-12 text-center">
							<i class="fas fa-file fa-4x mb-2 color-primary"></i>
							<span class="h1 d-block text-white counter" data-count="<?php esc_html($count_posts); ?>">0></span>
							<div class="counter-section-divider mx-auto mb-2"></div>
							<span class="h5 text-white"><?php echo _t("عدد الصفحات"); ?></span>
						</div>
					</div><!-- Counter -->
					<!-- Counter -->
					<div class="col-md-4 col-sm-12 mb-3 mb-sm-0">
						<div class="col-12 text-center">
							<i class="fas fa-eye fa-4x mb-2 color-primary"></i>
							<span class="h1 d-block text-white counter" data-count="<?php esc_html($count_visits); ?>">0</span>
							<div class="counter-section-divider mx-auto mb-2"></div>
							<span class="h5 text-white"><?php echo _t("عدد الزيارات"); ?></span>
						</div>
					</div><!-- Counter -->
				</div>
			</div><!-- / counter section -->
		<?php endif; ?>
	</div>
	<div class="my-3"></div>
	<?php user_end_scripts(); ?>
	<?php get_footer(); ?>
	<!-- Typed.js -->
	<script src="<?php echo siteurl(); ?>/assets/lib/typed/typed.min.js"></script>
	<link href="<?php echo siteurl(); ?>/assets/lib/slick/slick.css" type="text/css" rel="stylesheet" />
	<script>
		$(window).scroll(function() {
			var wH = $(window).height();
			wS = $(this).scrollTop();
			if (wS > 80) {
				$('.to-top').show();
			} else {
				$('.to-top').hide();
			}
			<?php if ($display_homepage_bloc_counter == "on"): ?>
				var hT = $('.home-counter-section').offset().top;
				hH = $('.home-counter-section').outerHeight();

				if (wS > (hT + hH - wH)) {
					$('.counter').each(function() {
						var $this = $(this),
							countTo = $this.attr('data-count');

						$({
							countNum: $this.text()
						}).animate({
								countNum: countTo
							},

							{

								duration: 8000,
								easing: 'linear',
								step: function() {
									$this.text(Math.floor(this.countNum));
								},
								complete: function() {
									$this.text(this.countNum);
									//alert('finished');
								}

							});



					});
				}
			<?php endif; ?>
		});
		$(function() {
			<?php if ($display_homepage_bloc_st == "on"): ?>
				var typed = new Typed(".typed-element-1", {
					typeSpeed: 30,
					loop: true,
					fadeOut: true,
					startDelay: 2000,
					backDelay: 4000,
					backSpeed: 30,
					strings: <?php echo $bloc_st_text_js; ?>,
				});
			<?php endif; ?>
			// [.home-images-section-nav,home-videos-section-nav] slick response settings
			var home_vids_imgs_nav_slick_responsive = [
				// Tablets
				{
					breakpoint: 780,
					settings: {
						slidesToShow: 3,
						slidesToScroll: 1
					}
				},
				//Phones
				{
					breakpoint: 576,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 1,
						//centerMode : false,
					}
				}
			];

			// [.home-books-section] slick response settings
			var home_books_slick_responsive = [
				//Small Laptops
				{
					breakpoint: 992,
					settings: {
						//slidesToShow: 2,
						//slidesToScroll: 2
					}
				},
				// Tablets
				{
					breakpoint: 780,
					settings: {
						slidesToShow: 2,
						//slidesToScroll: 2
					}
				},
				//Phones
				{
					breakpoint: 576,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						centerMode: false,
					}
				}
			];
			// [.home-featured-section] slick response settings
			var home_feature_slick_responsive = [
				//Small Laptops
				{
					breakpoint: 992,
					settings: {
						//slidesToShow: 2,
						//slidesToScroll: 2
					}
				},
				// Tablets
				{
					breakpoint: 780,
					settings: {
						slidesToShow: 2,
						//slidesToScroll: 2
					}
				},
				//Phones
				{
					breakpoint: 576,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						centerMode: false,
						prevArrow: false,
						nextArrow: false,
					}
				}
			];

			$('.home-books-section').slick({
				dots: false,
				slidesToShow: 3,
				slidesToScroll: 1,
				rtl: <?php esc_html(is_rtl()); ?>,
				prevArrow: $('.book-posts .slick-prev-h'),
				nextArrow: $('.book-posts .slick-next-h'),
				centerMode: true,
				responsive: home_books_slick_responsive,

			});

			$(".home-users-slide").slick({
				dots: false,
				slidesToShow: 7,
				slidesToScroll: 1,
				rtl: <?php esc_html(is_rtl()); ?>,
				prevArrow: $('.home-users .slick-prev-h'),
				nextArrow: $('.home-users .slick-next-h'),
				responsive: home_books_slick_responsive,
			});

			$(".home-featured-section").slick({
				dots: false,
				slidesToShow: 3,
				slidesToScroll: 1,
				autoplay: true,
				rtl: <?php esc_html(is_rtl()); ?>,
				prevArrow: $('.featured-posts .slick-prev-h'),
				nextArrow: $('.featured-posts .slick-next-h'),
				responsive: home_feature_slick_responsive,
				lazyLoad: 'ondemand',
			});

			$(".home-slide-section").slick({
				infinite: true,
				autoplay: true,
				dots: false,
				slidesToShow: 1,
				slidesToScroll: 1,
				rtl: <?php esc_html(is_rtl()); ?>,
				prevArrow: $('.slide-posts .slick-prev-h'),
				nextArrow: $('.slide-posts .slick-next-h'),
				lazyLoad: 'ondemand',
				speed: 500,
				cssEase: 'linear',
				customPaging: function(slider, i) {
					var thumb = $(slider.$slides[i]).data();
					return '' + (i + 1) + '';
				},
			});

			$(".home-images-section").slick({
				dots: false,
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: false,
				asNavFor: '.home-images-section-nav',
			});

			$(".home-images-section-nav").slick({
				dots: false,
				autoplay: true,
				arrows: true,
				slidesToShow: 4,
				slidesToScroll: 4,
				asNavFor: ".home-images-section",
				prevArrow: $('.images-posts-nav .slick-prev-h'),
				nextArrow: $('.images-posts-nav .slick-next-h'),
				centerMode: true,
				focusOnSelect: true,
				responsive: home_vids_imgs_nav_slick_responsive
			});

			$(".home-videos-section").slick({
				dots: false,
				slidesToShow: 1,
				slidesToScroll: 1,
				prevArrow: $('.videos-posts-nav .slick-prev-h'),
				nextArrow: $('.videos-posts-nav .slick-next-h'),
				asNavFor: '.home-videos-section-nav',
			});

			$(".home-videos-section-nav").slick({
				dots: false,
				autoplay: true,
				slidesToShow: 4,
				slidesToScroll: 4,
				asNavFor: '.home-videos-section',
				arrows: false,
				centerMode: true,
				focusOnSelect: true,
				responsive: home_vids_imgs_nav_slick_responsive
			});

			$(".sidebar-box-author-articles > .sidebar-box-body").slick({
				dots: false,
				autoplay: true,
				slidesToShow: 4,
				slidesToScroll: 1,
				prevArrow: $('.sidebar-box-author-articles .slick-prev-v'),
				nextArrow: $('.sidebar-box-author-articles .slick-next-v'),
				vertical: true,
			});

			$(".timeline").slick({
				dots: false,
				autoplay: true,
				slidesToShow: 5,
				slidesToScroll: 1,
				variableHeight: true,
				//prevArrow: $('.sidebar-box-articles-no-img .slick-prev-v'),
				//nextArrow: $('.sidebar-box-articles-no-img .slick-next-v'),		
				arrows: false,
				vertical: true,
			});

			$(".post-research-thumbs").slick({
				dots: false,
				autoplay: true,
				slidesToShow: 3,
				slidesToScroll: 1,
				prevArrow: $('.sidebar-book-vertical .slick-prev-v'),
				nextArrow: $('.sidebar-book-vertical .slick-next-v'),
				vertical: true,
				centerMode: true,
				centerPadding: '0px',
				focusOnSelect: true,
				asNavFor: '.home-books-research-section',
			});

			$(".home-books-research-section").slick({
				dots: false,
				autoplay: true,
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: false,
				vertical: true,
				//adaptiveHeight: true,
				asNavFor: '.post-research-thumbs',
			});
		});
	</script>
</body>

</html>