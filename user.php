<?php
require_once 'init.php';
$user_id = $_GET["user_id"] ?? "";

if (!is_user_id($user_id)) {
	if (preg_match('/\s/', $user_id)) {
		header("location:/user/" . str_replace(' ', '-', $user_id));
		exit(0);
	}
	$user_id = get_user_id_from_username(str_replace('-', ' ', $user_id));
} else {
	header("location:/user/" . str_replace(' ', '-', get_user_field($user_id, 'username', 'id')));
}

$post_type = $_GET["post_type"] ?? "";
if (empty($post_type)) {
	$post_type = false;
}
if (empty($user_id)) {
	exit(http_response_code(404));
}

$get_user_info = get_user_info($user_id);

if (!$get_user_info || $get_user_info->user_status == 'blocked') {
	$get_block_reason = get_user_meta($user_id, "block_reason");
	if ($get_block_reason) {
		echo error_page([0 => ["error" => $get_block_reason]]);
	} else {
		echo error_page([0 => ["error" => _t("حساب هذا المستخدم محظور")]]);
	}
	exit(0);
}

$user_role_icon = get_thumb(get_roles($get_user_info->user_role, "role_icon"));
$get_followers = get_followers($user_id);
$current_user = get_current_user_info();
$user_social_accounts = @json_decode(get_user_meta($user_id, "user_social_accounts"));

$query_my_posts = new Query_post([
	"post_in" => '',
	"post_author" => $user_id,
	"post_type__not" => ['name', 'wisdom', 'dictionary', 'quote', 'history'],
	"post_type" => $post_type,
	"post_in" => false,
	"post_lang" => false,
	"order" => ['posts.id', 'desc']
]);

$query_my_posts->do_query_count = true;

$get_my_posts = $query_my_posts->get_posts();
$get_my_posts_rows = $query_my_posts->count_results();
$get_user_bookmared_posts = get_user_bookmared_posts_id($user_id);
if (!empty($get_user_bookmared_posts)) {
	$query_fav_posts = new Query_post([
		"post_id" => $get_user_bookmared_posts,
		"post_in" => '',
		"post_lang" => false,
		"order" => ['posts.id', 'desc']
	]);

	$query_fav_posts->do_query_count = true;
	$get_fav_posts = $query_fav_posts->get_posts();
	$get_fav_posts_rows = $query_fav_posts->count_results();
} else {
	$get_fav_posts = [];
	$get_fav_posts_rows = 0;
}
if ($current_user->id == $user_id) {
	$get_news_feed = $dsql->dsql()->table('subscribe_sys', 'ss')->join('posts.post_author', 'user_id')->where('ss.subscriber', $current_user->id)->where('posts.post_status', 'publish')->where('posts.post_in', 'trusted')->order('posts.id', 'desc');
	$get_news_feed_rows = count_rows($get_news_feed->field('count(*)')->getRow());
	$get_news_feed->reset('getRow')->reset('field');
	$get_news_feed = $get_news_feed->limit(paged('end'), paged('start'))->get();

	$get_taxo_feed = $dsql->dsql()->table('user_meta')->join('posts.post_type', 'user_meta.meta_value')->where('user_meta.meta_key', 'like', 'taxonomy_subscribe__%')->where('user_meta.meta_value', '!=', 'no')->where('user_meta.user_id', $current_user->id)->where('posts.post_status', 'publish')->where('posts.post_in', 'trusted')->order('posts.id', 'desc');
	$get_taxo_feed_rows = count_rows($get_taxo_feed->field('count(*)')->getRow());
	$get_taxo_feed->reset('getRow')->reset('field');
	$get_taxo_feed = $get_taxo_feed->limit(paged('end'), paged('start'))->get();
} else {
	$query_news_feed = new Query_post([
		"post_author" => $user_id,
		"post_lang" => false,
		"order" => ['posts.id', 'desc'],
		"post_in" => false
	]);
	$query_news_feed->do_query_count = true;
	$get_news_feed = $query_news_feed->get_posts();
	$get_news_feed_rows = $query_news_feed->count_results();
}
$printcv = $_GET["printcv"] ?? false;

if (user_authority($user_id)->publish_in == "all" || is_login_in() === false) {
	$get_taxonomies = get_all_taxonomies();
} else {
	$get_taxonomies = $dsql->dsql()->table('taxonomies')->where('taxo_type', user_authority($user_id)->publish_in)->get();
}

?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<title><?php esc_html($get_user_info->user_name); ?></title>
	<link href="<?php echo siteurl(); ?>/assets/css/user.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/printjs/print.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="user-profild-body">
	<?php include ROOT . '/parts/media-uploader.php'; ?>
	<?php if ($printcv): ?>
		<div class="bg-white">
			<?php echo get_user_cv($user_id, true); ?>
		</div>
		<script>
			window.print();
		</script>
	<?php else: ?>
		<?php get_header("top"); ?>
		<div class="user-profil py-3">
			<div class="container h-100">
				<!-- row -->
				<div class="prf-elm-area d-flex row align-items-center h-100">
					<!-- Widget 1 -->
					<div class="col-lg-3 col-12 widget-profil widget-profile-user pr-lg-0 mb-md-3 mb-lg-0">
						<div class="bg-white first-col h-100">
							<!-- row -->
							<div class="row p-3">

								<!-- user image -->
								<div class="col-lg-12">
									<div class="profil-image mt-4 mx-auto">
										<img src="<?php echo get_thumb($get_user_info->user_picture); ?>" class="rounded-circle w-100 h-100" />
									</div>
								</div><!-- / user image -->
								<div class="col-lg-12 mt-4">
									<!-- profil-info -->
									<div class="profil-info mx-auto text-center">
										<h1 class="h5"><?php esc_html($get_user_info->user_name); ?>
											<?php if ($user_role_icon): ?>
												&nbsp;<img src="<?php echo $user_role_icon; ?>" width="18" height="18" data-toggle="tooltip" title="<?php esc_html(get_role_name($get_user_info->user_role)); ?>" />
											<?php endif; ?>
										</h1>
										<p class="font-weight-bold"><?php esc_html(get_user_meta($get_user_info->id, "bio")); ?></p>
									</div>
									<div class="row text-center">
										<div class="col">
											<span class="h5"><?php echo formatWithSuffix(get_user_meta($get_user_info->id, "points_remaining")); ?></span><br />
											<span class="h6"><?php echo _t("النقاط"); ?></span>
										</div>
										<div class="col">
											<span class="h5"><?php echo formatWithSuffix(count_user_posts($get_user_info->id)); ?></span><br />
											<span class="h6"><?php echo _t("المواضيع"); ?></span>
										</div>
										<div class="col">
											<span class="h5"><?php echo formatWithSuffix(count_user_followers($get_user_info->id)); ?></span><br />
											<span class="h6"><?php echo _t("المتابعين"); ?></span>
										</div>
									</div><!-- / profil-info -->
									<?php if ($user_id != $current_user->id): ?>
										<div class="row mx-0 my-3">
											<?php if (is_follower($user_id) !== -1): ?>
												<div class="col-12 col-sm-6 pl-sm-0 pr-sm-1 mb-sm-0 mb-2"><button class="btn btn-primary rounded-0 w-100 follow-btn <?php is_follower_h_c($get_user_info->id); ?>" data-user="<?php esc_html($get_user_info->id); ?>"><?php echo _t("متابعة"); ?></button></div>
											<?php endif; ?>
											<div class="col-12 col-sm-6 pr-sm-0 pl-sm-1"><button class="btn btn-warning rounded-0 w-100 send-message-modal" data-user="<?php esc_html($get_user_info->id); ?>"><i class="fas fa-envelope mr-2"></i><?php echo _t("أرسل رسالة"); ?></button></div>
										</div>
									<?php endif; ?>
									<!-- profil badges -->
									<div class="profil-badges py-2 my-3">
										<ul class="mb-0 list-unstyled d-flex flex-row justify-content-center">
											<?php
											$prf_badges =  badges_distribute($user_id);
											$prf_badges_ids = array_column($prf_badges, 'badge_id');
											foreach (array_slice($prf_badges, 0, 5, true) as $prf_badge_k => $prf_badge_v) {
												$badge_icon = $prf_badge_v["badge_icon"] ?? "";
												$badge_name = $prf_badge_v["badge_name"] ?? "";
												$badge_desc = $prf_badge_v["badge_desc"] ?? "";
												if (is_array($prf_badge_v["badge_id"])) {
													foreach ($prf_badge_v as $manual_badge) {
														$prf_badges_ids[] = $manual_badge["badge_id"];
														$badge_icon = $manual_badge["badge_icon"] ?? "";
														$badge_name = $manual_badge["badge_name"] ?? "";
														$badge_desc = $manual_badge["badge_desc"] ?? "";
													}
												}
											?>
												<li class="mr-2"><img src="<?php echo get_thumb($badge_icon, ["w" => 32, "h" => 32]); ?>" data-toggle="tooltip" data-html="true" title="<?php esc_html("<h6>" . $badge_name . "</h6>" . $badge_desc); ?>" class="img-fluid" /></li>
											<?php
											}
											?>
										</ul>
									</div><!-- profil badges -->
									<!-- profil social -->
									<ul class="row profile-social list-unstyled p-3 d-flex flex-row justify-content-center mb-0">
										<li class="social-account-link">
											<a href="<?php @esc_html($user_social_accounts->telegram); ?>" class="rounded-circle smooth-transition text-secondary"><img src="<?php echo siteurl(); ?>/assets/images/icons/social/1.svg" class="img-fluid" /></a>
										</li>

										<li class="social-account-link">
											<a href="<?php @esc_html($user_social_accounts->fb); ?>" class="rounded-circle smooth-transition text-secondary"><img src="<?php echo siteurl(); ?>/assets/images/icons/social/2.svg" class="img-fluid" /></a>
										</li>

										<li class="social-account-link">
											<a href="<?php @esc_html($user_social_accounts->tw); ?>" class="rounded-circle smooth-transition text-secondary"><img src="<?php echo siteurl(); ?>/assets/images/icons/social/3.svg" class="img-fluid" /></a>
										</li>

										<li class="social-account-link">
											<a href="<?php @esc_html($user_social_accounts->yt); ?>" class="rounded-circle smooth-transition text-secondary"><img src="<?php echo siteurl(); ?>/assets/images/icons/social/5.svg" class="img-fluid" /></a>
										</li>

										<li class="social-account-link">
											<a href="<?php @esc_html($user_social_accounts->insta); ?>" class="rounded-circle smooth-transition text-secondary"><img src="<?php echo siteurl(); ?>/assets/images/icons/social/6.svg" class="img-fluid" /></a>
										</li>
									</ul><!-- profil social -->
								</div>

							</div><!-- row -->

						</div>

					</div><!-- / Widget 1 -->

					<!-- Widget 2 -->
					<div class="col-lg-9 col-12 widget-profil">
						<!-- row -->

						<div class="row m-0 h-100">
							<div class="tab-content col-md-11 col-12 mt-md-0 mt-3 h-100 pl-0 pr-md-3 pr-sm-0 pr-0" id="myTabContent">
								<div class="tab-content bg-white p-3 h-100" style="overflow:auto;">

									<div class="tab-profile-toggler d-block d-md-none" data-target="#posts-users"><img src="<?php echo siteurl(); ?>/assets/images/icons/speaker-64.svg" class="img-fluid" />&nbsp;<?php echo _t("حائط المواضيع"); ?></div>

									<div class="tab-pane fade tab-pane-c active show" id="posts-users" role="tabpanel">
										<!-- post users tab panel -->
										<ul class="nav nav-posts w-100 text-center mb-0 d-none d-md-table" id="posts-users-nav" role="tablist">
											<?php if (get_current_user_info()->id == $user_id): ?>
												<li class="nav-item border d-table-cell">
													<a class="nav-link active" id="users-posts-tab" data-toggle="pill" href="#users-posts" role="tab" aria-controls="users-posts" aria-selected="true"><?php echo _t("مشاركات الأعضاء"); ?></a>
												</li>
												<li class="nav-item border d-table-cell">
													<a class="nav-link" id="my-posts-add-tab" data-toggle="pill" href="#my-posts-add" role="tab" aria-controls="my-posts-add" aria-selected="false"><?php echo _t("مشاركات من الأقسام"); ?></a>
												</li>
												<li class="nav-item border d-table-cell">
													<a class="nav-link" id="posts-favorites-tab" data-toggle="pill" href="#posts-favorites" role="tab" aria-controls="posts-favorites" aria-selected="false"><?php echo _t("مفضلتي"); ?></a>
												</li>
												<li class="nav-item border d-table-cell">
													<a class="nav-link" id="posts-featured-tab" data-toggle="pill" href="#posts-featured" role="tab" aria-controls="posts-featured" aria-selected="false"><?php echo _t("مواضيع مميزة"); ?></a>
												</li>
											<?php endif; ?>
										</ul>
										<select class="custom-select tab-profile-select d-block d-md-none mb-3">
											<option value="#users-posts"><?php echo _t("مشاركات الأعضاء"); ?> </option>
											<option value="#my-posts-add"><?php echo _t("مشاركات من الأقسام"); ?></option>
											<option value="#posts-favorites"><?php echo _t("مفضلتي"); ?></option>
											<option value="#posts-featured"><?php echo _t("مواضيع مميزة"); ?></option>
										</select>
										<?php if (get_current_user_info()->id != $user_id): ?>
											<div class="border-bottom">
												<h5><i class="fas fa-file mr-2"></i><?php echo _t("مواضيع جديدة لصاحب الملف"); ?></h5>
											</div>
											<div class="my-3"></div>
										<?php endif; ?>
										<div class="tab-content" id="pills-tabContent">
											<div class="tab-pane fade show active" id="users-posts" role="tabpanel" aria-labelledby="pills-home-tab">
												<?php
												if ($get_news_feed):
													echo profile_page_post_lyt($get_news_feed);
													echo load_more_btn($get_news_feed_rows, "#users-posts", ["request" => "profile-ajax", "data" => "posts-users-tab", "tab" => "news-feed", "user_id" => $user_id]);
												else:
													no_content();
												endif;

												?>
											</div>

											<div class="tab-pane fade" id="my-posts-add" role="tabpanel" aria-labelledby="pills-profile-tab">
												<div class="load-results">
													<?php
													if ($get_taxo_feed) {
														echo profile_page_post_lyt($get_taxo_feed);
													} else {
														no_content();
													}
													?>
												</div>
												<?php echo load_more_btn($get_taxo_feed_rows, "#my-posts-add .load-results", ["request" => "profile-ajax", "data" => "posts-users-tab", "tab" => "taxo-posts", "user_id" => $user_id]); ?>
											</div>
											<div class="tab-pane fade" id="posts-favorites" role="tabpanel" aria-labelledby="pills-contact-tab">
												<div class="load-results">
													<?php
													if ($get_fav_posts):
														echo profile_page_post_lyt($get_fav_posts);
													else:
														no_content();
													endif;
													?>
												</div>
												<?php echo load_more_btn($get_fav_posts_rows, "#posts-favorites .load-results", ["request" => "profile-ajax", "data" => "posts-users-tab", "tab" => "fav-posts", "user_id" => $user_id]); ?>
											</div>
											<div class="tab-pane fade" id="posts-featured" role="tabpanel" aria-labelledby="pills-contact-tab">
												<div class="load-results">
													<?php
													$query_featured_posts = new Query_post(["post_status" => "publish", "in_special" => "on", "post_lang" => false, 'order' => ['posts.id', 'desc']]);
													$query_featured_posts->do_query_count = true;
													$get_featured_posts = $query_featured_posts->get_posts();
													if ($get_featured_posts) {
														echo profile_page_post_lyt($get_featured_posts);
													}
													?>
												</div>
												<?php echo load_more_btn($query_featured_posts->count_results(), "#posts-featured .load-results", ["request" => "profile-ajax", "data" => "posts-users-tab", "tab" => "featured-posts", "user_id" => $user_id]); ?>
											</div>
										</div>
									</div>

									<div class="tab-profile-toggler d-block d-md-none" data-target="#cv"><img src="<?php echo siteurl(); ?>/assets/images/icons/target-64.svg" />&nbsp;<?php echo _t("السيرة الذاتية"); ?></div>

									<!-- cv tab panel -->
									<div class="tab-pane fade tab-pane-c" id="cv" role="tabpanel" aria-labelledby="cv-tab">
										<div class="border-bottom">
											<h5><i class="fas fa-id-badge mr-2"></i><?php echo _t("السيرة الذاتية"); ?></h5>
										</div>

										<div class="my-5"></div>
										<div id="cv-infos">
											<?php echo get_user_cv($user_id); ?>
										</div>
									</div><!-- / cv tab panel -->

									<div class="tab-profile-toggler d-block d-md-none" data-target="#followers"><img src="<?php echo siteurl(); ?>/assets/images/icons/agreement-64.svg" />&nbsp;<?php echo _t("الاعضاء - المتابعون"); ?></div>

									<!-- followers tab panel -->
									<div class="tab-pane fade tab-pane-c" id="followers" role="tabpanel" aria-labelledby="contact-tab">
										<div class="border-bottom">
											<h5><i class="fas fa-id-badge mr-2"></i><?php echo _t("الأعضاء المتابعون"); ?></h5>
										</div>
										<div class="my-5"></div>
										<?php if ($get_followers): ?>
											<div class="row followers-row">
												<?php foreach ($get_followers["results"] as $follow): ?>
													<div class="col-lg-6 col-sm-12 text-center user-profile px-2 mb-3">
														<?php echo user_box_module($follow); ?>
													</div>
												<?php endforeach; ?>
											</div>
										<?php
										else:
											no_content(_t("لايوجد أي متابعين"));
										endif;
										?>
										<?php echo @load_more_btn($get_followers["rows"], "#followers .followers-row", ["request" => "profile-ajax", "data" => "followers", "user_id" => $user_id]); ?>
									</div><!-- / followers tab panel -->

									<div class="tab-profile-toggler d-block d-md-none" data-target="#my-posts"><img src="<?php echo siteurl(); ?>/assets/images/icons/performance-64.svg" />&nbsp;<?php echo _t("تصنيف لكل مواضيع"); ?></div>

									<!-- my posts tab panel -->
									<div class="tab-pane fade tab-pane-c" id="my-posts" role="tabpanel" aria-labelledby="my-posts-tab">
										<div class="border-bottom">
											<h5><i class="fas fa-sitemap mr-2"></i><?php echo _t("تصنيف لكل المواضيع"); ?></h5>
										</div>
										<div class="my-3"></div>
										<div class="select-drp-taxonomies">
											<form action="" method="GET" id="my-posts-filter">
												<select id="taxonomies-profile" class="custom-select" name="post_type">
													<option value="" selected=""><?php echo _t("الكل"); ?></option>
													<?php
													foreach ($get_taxonomies as $taxo):
													?>
														<option value="<?php esc_html($taxo["taxo_type"]); ?>" <?php selected_val($taxo["taxo_type"], $post_type); ?>><?php esc_html(get_taxonomy_title($taxo)); ?></option>
													<?php
													endforeach;
													?>
												</select>
											</form>
										</div>
										<div class="my-3"></div>
										<?php if ($get_my_posts): ?>
											<div class="row my-posts-artc">
												<?php foreach ($get_my_posts as $my_post): ?>
													<!--  post -->
													<div class="col-lg-3 col-md-4 col-sm-12 px-1 mb-2">
														<div class="posts-post h-100 position-relative overflow-hidden">
															<a href="<?php echo get_post_link($my_post); ?>" class="modal-link-post" data-id="<?php esc_html($my_post["id"]); ?>"><img src="<?php esc_html(get_thumb($my_post["post_thumbnail"], "md", true, $my_post["id"])); ?>" class="img-fluid object-fit w-100 h-100" /></a>
															<div class="post-details position-absolute w-100 h-100 top-0 right-0 animated fadeIn">
																<div class="d-flex flex-column h-100">
																	<div class="mb-auto w-100">
																		<a href="<?php echo get_post_link($my_post); ?>" data-id="<?php esc_html($my_post["id"]); ?>" class="modal-link-post bg-primary text-white text-center h6 px-2 py-2 d-block"><?php echo substr_str($my_post["post_title"], 16); ?></a>
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
																		<!-- <span class="ml-auto text-white"><i class="fas fa-eye mr-2"></i><?php esc_html($my_post["post_views"]); ?></span> -->
																	</div>
																</div>
															</div>
														</div>
													</div><!-- / post -->
												<?php endforeach; ?>
											</div>
										<?php echo load_more_btn($get_my_posts_rows, ".my-posts-artc", ["request" => "profile-ajax", "data" => "my-posts-tab", "user_id" => $user_id, "post_type" => $post_type]);
										else:
											no_content();
										endif;
										?>
									</div><!-- / my posts tab panel -->

									<div class="tab-profile-toggler d-block d-md-none" data-target="#badges"><img src="<?php echo siteurl(); ?>/assets/images/icons/medal-64.svg" />&nbsp;<?php echo _t("الأوسمة"); ?></div>

									<!-- Badges tab panel -->
									<div class="tab-pane fade tab-pane-c" id="badges" role="tabpanel" aria-labelledby="badges-tab">
										<div class="border-bottom">
											<h5><i class="fas fa-certificate mr-2"></i><?php echo _t("الأوسمة"); ?></h5>
										</div>
										<div class="mb-3">
											<div class="border-bottom">
												<!-- badges -->
												<div class="badges py-3 my-3">
													<ul class="mb-0 list-unstyled d-flex flex-wrap">
														<?php
														$c_l = current_lang();
														$get_badges = get_badges(null, 1);
														foreach ($get_badges as $badge) {
															$badge_title = @json_decode($badge["badge_name"])->$c_l;
															$badge_desc = @json_decode($badge["badge_desc"])->$c_l;
														?>
															<li class="mr-2 position-relative mb-2">
																<?php if (!in_array($badge["id"], $prf_badges_ids)): ?>
																	<div class="badge-lock-bg w-100 h-100 position-absolute right-0 top-0"></div>
																<?php endif; ?>
																<img src="<?php echo get_thumb($badge["badge_icon"], ["w" => 48, "h" => 48]); ?>" data-toggle="tooltip" data-html="true" title="<h6><?php esc_html($badge_title . "</h6>" . $badge_desc); ?>" class="img-fluid" />
															</li>
														<?php
														}
														?>
													</ul>
												</div><!-- badges -->

											</div>
										</div>
									</div><!-- / Badges tab panel -->

								</div>
							</div>

							<!-- Menu -->
							<div class="col-md-1 col-12 mt-md-0 mt-3 p-0 d-md-block d-none nav-prf bg-white">
								<ul class="nav flex-column h-100 text-md-center text-sm-left">
									<li class="nav-item">
										<a class="nav-link active" id="posts-users-tab" data-toggle="tab" href="#posts-users" role="tab" aria-controls="posts-users" aria-selected="true">
											<img src="<?php echo siteurl(); ?>/assets/images/icons/speaker-64.svg" class="w-100 p-1" />
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="cv-tab" data-toggle="tab" href="#cv" role="tab" aria-controls="cv" aria-selected="false">
											<img src="<?php echo siteurl(); ?>/assets/images/icons/target-64.svg" class="w-100 p-1" />
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="followers-tab" data-toggle="tab" href="#followers" role="tab" aria-controls="followers" aria-selected="false">
											<img src="<?php echo siteurl(); ?>/assets/images/icons/agreement-64.svg" class="w-100 p-1" />
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="my-posts-tab" data-toggle="tab" href="#my-posts" role="tab" aria-controls="my-posts" aria-selected="false">
											<img src="<?php echo siteurl(); ?>/assets/images/icons/performance-64.svg" class="w-100 p-1" />
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="badges-tab" data-toggle="tab" href="#badges" role="tab" aria-controls="badges" aria-selected="false">
											<img src="<?php echo siteurl(); ?>/assets/images/icons/medal-64.svg" class="w-100 p-1" />
										</a>
									</li>
								</ul>
							</div><!-- / Menu -->

						</div><!-- row -->

					</div><!-- Widget 2 -->

				</div><!--/ row -->
			</div>
		</div>
	<?php endif; ?>
	<?php user_end_scripts(); ?>
	<script src="<?php echo siteurl(); ?>/assets/lib/printjs/print.min.js"></script>
	<script>
		function profile_area_align(w_h, w_w) {

			if (w_h < 650) {
				$(".prf-elm-area").removeClass("align-items-center my-5");
			} else {
				$(".prf-elm-area").addClass("align-items-center my-5");
			}

		}

		$(window).on('load', function() {
			var w_h = $(this).height();
			var w_w = $(this).width();
			if (w_w < 650) {
				$('#posts-users').removeClass('active show');
				$("#posts-users-tab").removeClass('active');
			}

			//profile_area_align(w_h);
		});

		var w_h = $(window).height();
		var w_w = $(window).width();

		//$(window).on('resize', function () {
		if (w_w < 650) {
			$('#posts-users').removeClass('active show');
			$("#posts-users-tab").removeClass('active');
		}

		//profile_area_align(w_h);
		//});

		$(function() {

			<?php if (!empty($post_type)) : ?>
				$('#my-posts-tab').tab('show');
			<?php endif; ?>

			$(".tab-profile-select").on("change", function() {
				var $t = $(this);
				$(".nav-posts a[href='" + $t.val() + "']").tab("show");
			});

			$("#taxonomies-profile").on("change", function() {
				$("#my-posts-filter").submit();
			});

			$(".tab-profile-toggler").click(function(e) {
				var $t = $(this);
				$(".tab-profile-toggler").removeClass("active");
				$('.tab-pane-c').removeClass("active show");

				if ($t.hasClass("active")) {} else {
					$t.addClass("active");
					$('a[href="' + $t.data("target") + '"]').tab('show');
					//$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {});
				}
				e.preventDefault();
			});

		});
	</script>
</body>

</html>