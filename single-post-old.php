<?php
// Initialize an array to store the audio data (track names and URLs)
$audio_files = [];

require_once 'init.php';
$embera = new \Embera\Embera();
$post_id = $_GET["post_id"] ?? "";
$post_type = $_GET["post_type"] ?? "";

$args = [
	"post_id" => $post_id,
	"post_status" => false,
	"post_in" => false,
	// "post_status__not" => ['auto-draft'],
	"post_status__not" => [],
	"post_lang" => false
];

$query_post = new Query_post($args);

$post = $query_post->get_post();
$current_user = get_current_user_info();
if ($post['post_status'] == 'auto-draft' && $current_user->id != $post['post_author'] && !is_admin()) {
	die('المنشور الذي طلبت غير موجود');
}
if (!is_lang_set()) {
	set_lang($post["post_lang"], false);
}

$get_post_categories = $query_post->get_post_categories();
if ($post) {
	/** @var array display errors to post author */
	$display_errors = [];
	switch ($post["post_status"]) {
		case "pending":
			$display_errors[] = ["error" => _t("المنشور بإنتظار الموافقة حاليا")];
			break;
		case "approval":
			$display_errors[] = ["error" => _t("المنشور  بإنتظار المراجعة حاليا")];
			break;
		case "blocked":
			$display_errors[] = ["error" => _t("المنشور مغلق حاليا")];
			break;
	}

	if ($post["post_status"] != "publish" && $current_user->id != $post["post_author"] && admin_authority()->posts != "on") {
		echo error_page($display_errors);
		exit(0);
	}

	/** Post meta's */
	$source = get_post_meta($post_id, "source");
	$post_notice = get_post_meta($post_id, "notice");
	$post_content_edit = get_post_meta($post_id, "post_content_edit");
	$disable_copy = get_post_meta($post_id, "disable_copy");
	if (!empty($post_content_edit) && ($post["post_author"] == $current_user->id || admin_authority()->posts == "on")) {
		$post["post_content"] = $post_content_edit;
	}
	/** Get post rates */
	if (is_login_in()) {
		$get_rates = get_rates($post_id, $current_user->id);
	} else {
		$get_rates = get_rates($post_id);
	}

	$get_user_rate = $get_rates["user_rate"] ?? 0;

	$query_similar_posts = new Query_post([
		"id__not" => $post_id,
		"post_type" => $post["post_type"],
		"post_lang" => $post["post_lang"],
		"limit" => 6,
		"post_category" => $get_post_categories,
		"order" => 'rand()'
	]);

	$similar_posts = $query_similar_posts->get_posts();
	$get_author_info = get_user_info($post["post_author"]);
} else {
	exit(0);
}

if ($post["post_type"] == "history") {
	$post_title = explode("-", $post["post_title"]);
	$history_calendar = get_post_meta($post_id, 'history_calendar');
	$post["post_title"] = $post_title[0] . "-" . months_names($post_title[1], $history_calendar) . "-" . $post_title[2];
}

insert_analytics("post_views", $post_id);

$get_ads = get_ads("ad_unit_content_280_280_side");
$get_ads_top_big = get_ads("ad_unit_content_big_top");
$get_ads_bottom_big = get_ads("ad_unit_content_big_bottom");
$ad_unit_content_inside = get_ads("ad_unit_content_inside");
$taxonomy_settings = get_taxonomy_settings($post_type);
$meta_desc_html = preg_replace("/<p[^>]*>[\s|&nbsp;]*<\/p>/", '', $post["post_content"]);
$meta_desc_start = strpos($meta_desc_html, '<p>');
$meta_desc_end = strpos($meta_desc_html, '</p>', 0);

?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<link href="<?php echo siteurl(); ?>/assets/css/emoji.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/css/bookblock.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/css/custom.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/css/jquery.jscrollpane.custom.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/js/modernizr.custom.79639.js"></script>
	<title><?php esc_html($post["post_title"]); ?></title>
	<meta name="description" content="<?php echo strip_tags(substr($meta_desc_html, $meta_desc_start, $meta_desc_end - $meta_desc_start + 4)); ?>" />
	<meta name="keywords" content="<?php esc_html($post["post_keywords"]); ?>" />
	<meta property="og:image" content="<?php esc_html(get_thumb($post["post_thumbnail"], null, false, $post_id)); ?>">
	<meta property="og:url" content="<?php echo get_post_link($post_id); ?>">
	<meta name="author" content="" />
</head>

<body style="background-color: #f4f4f4 !important;">
	<?php user_end_scripts(); ?>
	<?php if (display_fbComments()): ?>
		<div id="fb-root"></div>
		<script>
			(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s);
				js.id = id;
				js.src = 'https://connect.facebook.net/<?php echo current_lang(); ?>/sdk.js#xfbml=1&autoLogAppEvents=1&version=v3.1&appId=811664755689133';
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
	<?php
	endif;
	$header_name = null;
	if ($post["post_in"] == "untrusted") {
		$header_name = 'top';
	}
	get_header($header_name);
	?>
	<nav aria-label="breadcrumb" class="breadcrumb-post-page">
		<ol class="breadcrumb container rounded-0">
			<li class="breadcrumb-item"><a href="#" class="color-link"><i class="fas fa-home"></i>&nbsp;<?php echo _t("الرئيسية"); ?></a></li>
			<li class="breadcrumb-item active" aria-current="page" class="color-link"><?php esc_html(get_taxonomy_title($post["post_type"])); ?></li>
		</ol>
	</nav>
	<div class="container">
		<!-- modals -->
		<!-- modal of fetch details -->
		<div class="modal fade" id="shortLink" tabindex="-1" role="dialog" aria-labelledby="shortLinkLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="shortLinkLabel"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body text-center">
						<form action="createshortcut" id="urlshortform" method="POST">
							<div class="input-group input-group-sm mb-3">
								<button id="copyShortLink" style="border-radius: 0 3px 3px 0;" class="btn btn-outline-warning copy" type="button">نسخ</button>
								<button type="submit" class="btn btn-outline-warning rounded-0 short d-none" type="button">اختصار</button>
								<input id="shortenedUrl" type="text" class="form-control">
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- end of modal of fetch details -->


		<!-- end modals -->
	</div>

	<div class="my-2"></div>
	<div class="container">
		<div class="d-sm-flex justify-content-center flex-wrap">
			<?php
			if ($display_errors) {
				foreach ($display_errors as $error) {
			?>
					<div class="alert alert-danger single-right-col" role="alert">
						<i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error["error"]; ?>
					</div>
			<?php
				}
			}
			?>
			<!--  -->
			<?php if ($post["post_type"] == "book") : ?>
				<?php
				$display_book_success_alert = false;
				$is_for_read = get_post_meta($post_id, 'is_for_read');
				$is_book_author = get_post_meta($post_id, 'is_book_author');
				if ($is_for_read && $is_for_read == 'on') {
					$h4 = 'الكتاب للمراجعة فقط';
					$span = 'لا يمكن قراءة الكتاب أو تحميله لأنه حقوق النشر محفوظة';
					$display_book_success_alert = true;
				} elseif ($is_book_author && $is_book_author > 0) {
					$h4 = 'مؤلف/مترجم نشر الكتاب في الموقع';
					$span = 'الكتاب للقراءة أو التحميل فقط وليس لغرض التجاري';
					$display_book_success_alert = true;
				}
				if ($display_book_success_alert == false) {
					$h4 = 'هذا الكتاب ملكية عامة';
					$span = 'نُشر هذا الكتاب برخصة المشاع الإبداعي';
					$display_book_success_alert = true;
				}
				?>
				<?php if ($display_book_success_alert) : ?>
					<div class="alert alert-success single-right-col text-center" role="alert">
						<div class="text-dark">
							<span class="h4"><?= $h4; ?></span>
							<i class="fas fa-check-circle fa-lg mr-2 text-primary"></i>
						</div>
						<span><?= $span; ?></span>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<!--  -->
			<?php if (is_login_in() && admin_authority()->posts == "on"): ?>
				<div class="single-right-col bg-white mb-3 p-2" style="border: 2px solid #f0ae027a;border-top: 5px solid #f0ae02;border-radius: 14px;">
					<form method="POST">
						<div class="form-group">
							<label for="content_notice" class="font-weight-bold"><?php echo _t("تنبيه"); ?></label>
							<textarea cols="30" rows="5" class="form-control" name="notice"><?php esc_html($post_notice); ?></textarea>
						</div>
						<div class="form-group">
							<button class="btn btn-primary send-post-notice"><?php echo _t("إرسال"); ?></button>
						</div>
						<input type="hidden" name="method" value="post_notice_ajax" />
						<input type="hidden" name="post_id" value="<?php esc_html($post_id); ?>" />
					</form>
				</div>
			<?php elseif ($post_notice && $post["post_author"] == get_current_user_info()->id): ?>
				<div class="alert alert-danger"><?php esc_html($post_notice); ?></div>
			<?php endif; ?>
			<!--  -->
			<div class="single-right-col bg-white" style="border: 2px solid #f0ae027a;border-top: 5px solid #f0ae02;border-radius: 14px;">
				<?php
				$user_role_icon = get_thumb(get_roles($get_author_info->user_role, "role_icon"));
				?>
				<div class="row w-100 m-auto p-2 align-items-center border-bottom border-warning">
					<a style="width: 37px;" href="<?php echo get_user_link($get_author_info->id); ?>" class="mr-2">
						<img src="<?php esc_html(get_thumb($get_author_info->user_picture, "sm")); ?>" class="user-pic rounded-circle img-fluid w-100" />
					</a>
					<div class="d-flex flex-column justify-content-center">
						<span class="d-flex align-items-center">
							<a style="max-width: 89px;text-overflow: ellipsis;overflow: hidden;display: inline-block;white-space: nowrap;" href="<?php echo get_user_link($get_author_info->id); ?>" class="h6 mb-0">
								<?php esc_html($get_author_info->user_name); ?>
							</a>
							<a href="#" class="follow-btn <?php is_follower_h_c($post["post_author"]); ?> ml-2" data-user="<?php esc_html($post["post_author"]); ?>"><?php echo _t("متابعة"); ?></a>
						</span>
						<small class="text-muted"><i class="far fa-clock"></i>&nbsp;<?php echo get_timeago(strtotime($post["post_date_gmt"])); ?></small>
					</div>

					<!-- share && options && actions -->

					<div class="d-flex col-md justify-content-end">

						<!-- action -->
						<?php get_post_actions_new($post); ?>

						<!-- share -->
						<div class="mr-1">
							<button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-share-alt"></i></button>
							<div class="dropdown-menu">
								<span class="dropdown-item px-1">
									<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=facebook" class="share-btn d-flex justify-content-around text-dark" title="<?php echo _t("شارك على فايسبوك"); ?>">
										<?php echo _t("شارك على فايسبوك"); ?>
										<i class="fab fa-facebook-f text-primary"></i>
									</a>
								</span>
								<span class="dropdown-item px-1">
									<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=twitter" class="share-btn d-flex justify-content-around text-dark" title="<?php echo _t("شارك على تويتر"); ?>">
										<?php echo _t("شارك على تويتر"); ?>
										<i class="fab fa-twitter text-info"></i>
									</a>
								</span>
								<!-- telegrame -->
								<span class="dropdown-item px-1">
									<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=telegram" class="share-btn d-flex justify-content-around text-dark" title="<?php echo _t("شارك على تيليغرام"); ?>">
										<?php echo _t("شارك على تيليغرام"); ?>
										<i class="fab fa-telegram text-primary"></i>
									</a>
								</span>
								<!-- whatsapp -->
								<span class="dropdown-item px-1">
									<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=whatsapp" class="share-btn d-flex justify-content-around text-dark" title="<?php echo _t("شارك على واتساب"); ?>">
										<?php echo _t("شارك على واتساب"); ?>
										<i class="fab fa-whatsapp text-success"></i>
									</a>
								</span>
							</div>
						</div>

						<!-- options -->

						<div id="single-post" class="dropdown">
							<button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
							<div class="dropdown-menu">
								<?php echo popover_post_new($post_id); ?>
							</div>
						</div>
					</div>

				</div>
				<div class="ad-area">
					<?php if (is_array($get_ads_top_big)): foreach ($get_ads_top_big as $ad_2): update_ad_views($ad_2["ad_key"]); ?>
							<div class="my-2">
						<?php
							echo $ad_2["ad_code"] . '</div>';
						endforeach;
					endif;
						?>
							</div>
							<?php
							switch ($post_type):
								case "research":
									include 'parts/single-post/research.php';
									break;
								case "book":
									include 'parts/single-post/book.php';
									break;
								default:
							?>
									<?php
									if (in_array($post["post_type"], ["video", "audio"])):
									?>
										<div class="post-embed">
											<!-- 16:9 aspect ratio-->
											<div class="embed-responsive embed-responsive-16by9">
												<?php echo $embera->autoEmbed(get_post_meta($post_id, "video_url")); ?>
											</div>
										</div>
									<?php endif; ?>
									<div class="post-thumb">
										<?php
										// if($post['post_status']) {
										// 	echo '<span style="
										// 					position: absolute;
										// 					background-color: #ffc107;
										// 					padding: 5px;
										// 					color: #000;
										// 					z-index: 5;
										// 			">مسودة</span>';
										// }
										?>
										<?php
										$image = $post["post_thumbnail"] ? get_thumb($post["post_thumbnail"], null) : siteurl() . "/assets/images/no-image.svg";
										?>
										<img src="<?php esc_html($image); ?>" class="img-fluid w-100" />
									</div>
									<div class="post-quick-details">
										<div class="d-flex">
											<div class="mr-auto">
												<button class="plus-font-size btn btn-transparent"><i class="fas fa-search-plus"></i></button>
												<button class="minus-font-size btn btn-transparent"><i class="fas fa-search-minus"></i></button>
											</div>
											<div class="ml-auto">
												<ul class="list-unstyled p-2 mb-0">
													<li class="mr-3"><i class="fas fa-share"></i>&nbsp;<?php esc_html($post["post_share"]); ?></li>
													<li class="mr-3"><i class="fas fa-comments"></i>&nbsp;<?php esc_html($post["comments_count"]); ?></li>
													<li><i class="fas fa-eye"></i>&nbsp;<?php esc_html($post["post_views"]); ?></li>
												</ul>
											</div>
										</div>
									</div>
									<div class="my-2"></div>
									<div class="post-content">
										<?php
										if ($post["post_type"] == "author_article"):
											$author_article_name = get_post_meta($post["id"], "author_article_name");
											if ($author_article_name):
										?>
												<p class="font-weight-bold"><i class="fas fa-user mr-3"></i><?php echo _t("كاتب(ة)"); ?> : <?php esc_html($author_article_name); ?></p>
										<?php endif;
										endif; ?>
										<h1 class="h3 py-2"><?php esc_html($post["post_title"]); ?></h1>
										<div class="border-top border-warning"></div>
										<div class="my-2"></div>
										<div class="single-post post-details resizeable">
											<?php echo validate_html(str_replace("&nbsp;", " ", $post["post_content"])); ?>
										</div>

										<div class="my-4"></div>

										<!-- slices -->
										<div class="post-slices">
											<?php
											$get_post_slices = get_post_slices($post_id, "ASC");
											if ($get_post_slices):
												render_slices_single_post($get_post_slices);
											endif;
											$get_post_reaction = get_post_reaction($post_id);
											?>
										</div><!-- slices -->

										<div class="my-4"></div>

										<div class="post-sources">
											<a class="text-primary h5 font-weight-bold border-bottom d-block pb-2" data-toggle="collapse" href="#collapseSources" role="button" aria-expanded="false" aria-controls="collapseSources"><i class="fas fa-share-square mr-2"></i><?php echo _t("المصادر"); ?><i class="fas fa-caret-down ml-2"></i></a>
											<div class="collapse" id="collapseSources">
												<?php
												if ($source):
													if (is_login_in()):
														if (user_authority()->read_sources):
															$source = (array) json_decode($source);
												?>
															<ol class="list-unstyled ordered-source-list">
																<?php foreach ($source as $src): ?>
																	<li class="bg-light"><a href="<?php esc_html($src->url); ?>" target="_blank"><?php esc_html($src->text); ?></a></li>
																<?php endforeach; ?>
															</ol>
												<?php
														else:
															echo '<h4 class="text-danger">' . _t("المعذرة ! رتبتك لا تسمح لك بالإطلاع على المصادر") . '</h4>';
														endif;
													else:
														echo '<span class="text-danger">' . _t("قم بتسجيل الدخول لكي تتمكن من رؤية المصادر") . '</span>';
													endif;
												endif;
												?>
											</div>
										</div>

										<div>
											<?php
											$taxo_notice = get_taxonomy_notice($post_type);
											if ($taxo_notice):
											?>
												<div class="alert alert-danger">
													<?php echo $taxo_notice; ?>
												</div>
											<?php endif; ?>
										</div>
									</div>



							<?php endswitch; ?>



							<div class="border-top border-warning"></div>
								
							<div class="sidebar-box sidebar-box-rating border-0">
								<div class="row w-100 m-auto justify-content-center align-items-center" style="overflow: hidden;min-height: 251px;">
									<div class="col-md-5 row justify-content-center align-items-center">
										<h1 class="my-3">قيم الكتاب</h1>
										<div class="rating">
											<input type="radio" id="star5" name="rating" value="5" <?php checked_val($get_user_rate, 5); ?> />
											<label class="rate-content full" for="star5" title="Awesome - 5 stars" data-value="5" data-post="<?php esc_html($post_id); ?>"></label>

											<input type="radio" id="star4" name="rating" value="4" <?php checked_val($get_user_rate, 4); ?> />
											<label class="rate-content full" for="star4" title="Pretty good - 4 stars" data-value="4" data-post="<?php esc_html($post_id); ?>"></label>

											<input type="radio" id="star3" name="rating" value="3" <?php checked_val($get_user_rate, 3); ?> />
											<label class="rate-content full" for="star3" title="Meh - 3 stars" data-value="3" data-post="<?php esc_html($post_id); ?>"></label>

											<input type="radio" id="star2" name="rating" value="2" <?php checked_val($get_user_rate, 2); ?> />
											<label class="rate-content full" for="star2" title="Kinda bad - 2 stars" data-value="2" data-post="<?php esc_html($post_id); ?>"></label>

											<input type="radio" id="star1" name="rating" value="1" <?php checked_val($get_user_rate, 1); ?> />
											<label class="rate-content full" for="star1" title="Sucks big time - 1 star" data-value="1" data-post="<?php esc_html($post_id); ?>"></label>
										</div>
									</div>
									<div class="rat-stat col-md row">
										<div class="col">
											<?php
											$rating_stats = $dsql->expr("SELECT rate_stars, COUNT(*) as count, (COUNT(*) * 100 / (SELECT COUNT(*) FROM rating_sys WHERE post_id = $post_id)) as percentage FROM rating_sys WHERE post_id = $post_id GROUP BY rate_stars ORDER BY rate_stars DESC")->get();
											$mapped_stats = [];
											foreach ($rating_stats as $stat) {
												$mapped_stats[$stat['rate_stars']] = $stat;
											}
											// Ensure all stars are represented (5 to 1)
											$ratings = [];
											$total_ratings = array_sum(array_column($rating_stats, 'count'));
											for ($stars = 5; $stars >= 1; $stars--) {
												$ratings[$stars] = [
													'count' => $mapped_stats[$stars]['count'] ?? 0,
													'percentage' => $total_ratings > 0 ? (($mapped_stats[$stars]['count'] ?? 0) * 100 / $total_ratings) : 0,
												];
											}
											foreach ($ratings as $stars => $stat): ?>
												<div class="col-md-12 mb-3">
													<div class="d-flex align-items-center">
														<!-- Display Star Icons -->
														<div class="mr-3">
															<?php for ($i = 1; $i <= 5; $i++): ?>
																<i class="fa-star <?= $i <= $stars ? 'fas text-warning' : 'far text-muted' ?>"></i>
															<?php endfor; ?>
														</div>
														<!-- Display Progress Bar -->
														<div class="progress flex-grow-1" style="height: 17px;">
															<div data-value="<?= $stars ?>"
																class="progress-bar bg-warning text-dark progress-bar-striped progress-bar-animated"
																role="progressbar"
																style="width: <?= round($stat['percentage']) ?>%;"
																aria-valuenow="<?= round($stat['percentage']) ?>"
																aria-valuemin="0"
																aria-valuemax="100">
																<?= round($stat['percentage']) ?>%
															</div>
														</div>
														<!-- Display Vote Count -->
														<div class="ml-3 vote-count" data-value="<?= $stars ?>">
															(<?= $stat['count'] ?>) تقييم
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>

									</div>
								</div>
								<a class="d-block text-center collapse-rating collapsed" data-toggle="collapse" href="#collapseRating" role="button" aria-expanded="false" aria-controls="collapseRating"></a>
								<div class="rating-progress px-4 collapse" id="collapseRating">
									<?php
									if (!$get_rates["stars"]) {
										$get_rates["stars"] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
									}
									foreach ($get_rates["stars"] as $rate_k => $rate_v):
										if (is_array($rate_v)) {
											$star_rates = $rate_v["rates"];
											$star_percent = $rate_v["percent"];
										} else {
											$star_percent = 0;
											$star_rates = 0;
										}
									?>
										<div class="d-flex align-items-center">
											<div class="rating-star-count mr-3"><i class="fas fa-star text-warning mr-2"></i><span><?php esc_html($rate_k); ?></span></div>
											<div class="progress flex-grow-1">
												<div class="progress-rate-percent-<?php esc_html($rate_k); ?> progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark" role="progressbar" aria-valuenow="<?php echo $star_percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $star_percent; ?>%"><?= round($star_percent) ?>%</div>
											</div>
											<!-- Display Vote Count -->
											<div class="ml-3 vote-count" data-value="<?= $rate_k ?>">
												(<?= $star_rates ?>)
											</div>
										</div>
									<?php
									endforeach;
									?>
								</div>
							</div>
							<div class="my-4"></div>



							<div class="ad-area">
								<?php if (is_array($get_ads_bottom_big)): foreach ($get_ads_bottom_big as $ad_3): update_ad_views($ad_3["ad_key"]); ?>
										<div class="my-2">
									<?php
										echo $ad_3["ad_code"] . '</div>';
									endforeach;
								endif;
									?>
										</div>

										<div class="d-flex border-bottom border-warning pb-2 mx-3">
											<!-- post reaction -->
											<div class="reaction-area position-relative w-100">
												<button class="btn btn-transparent rounded-circle reaction-btn reaction-user animated" data-reacted="<?php esc_html($get_post_reaction["react"]); ?>" data-post="<?php esc_html($post_id); ?>"></button>

												<?php $get_post_reaction = get_post_reaction($post_id); ?>
												<?php if (is_array($get_post_reaction["reactions"])): ?>
													<ul class="list-unstyled d-flex flex-row mb-0 mt-2">
														<?php
														foreach ($get_post_reaction["reactions"] as $reaction => $index):
														?>
															<li class="reaction-user reactions-count" data-toggle="tooltip" title="<?php esc_html($reaction); ?>(<?php esc_html($index); ?>)" data-reacted="<?php esc_html($reaction); ?>"></li>
														<?php
														endforeach;
														?>
													</ul>
												<?php endif; ?>
												<div class="block-react-eem position-absolute shadow-sm p-1 bg-white animated">
													<div class="emoji  emoji--like react" data-reaction="like">
														<div class="emoji__hand">
															<div class="emoji__thumb"></div>
														</div>
													</div>
													<div class="emoji  emoji--love react" data-reaction="love">
														<div class="emoji__heart"></div>
													</div>
													<div class="emoji  emoji--haha react" data-reaction="haha">
														<div class="emoji__face">
															<div class="emoji__eyes"></div>
															<div class="emoji__mouth">
																<div class="emoji__tongue"></div>
															</div>
														</div>
													</div>
													<div class="emoji  emoji--wow react" data-reaction="wow">
														<div class="emoji__face">
															<div class="emoji__eyebrows"></div>
															<div class="emoji__eyes"></div>
															<div class="emoji__mouth"></div>
														</div>
													</div>
													<div class="emoji  emoji--sad react" data-reaction="sad">
														<div class="emoji__face">
															<div class="emoji__eyebrows"></div>
															<div class="emoji__eyes"></div>
															<div class="emoji__mouth"></div>
														</div>
													</div>
													<div class="emoji  emoji--angry react" data-reaction="angry">
														<div class="emoji__face">
															<div class="emoji__eyebrows"></div>
															<div class="emoji__eyes"></div>
															<div class="emoji__mouth"></div>
														</div>
													</div>
												</div>
											</div>
											<!-- / post reaction -->
										</div>
										<div class="my-4"></div>
										<div class="px-4">
											<?php
											if (get_post_meta($post_id, "disable_comments") == "off" && $taxonomy_settings["comment"] == "on"):
											?>
												<?php
												echo get_comments_form($post_id, true, get_thumb($current_user->user_picture, "sm"));
												// echo '<div class="my-3"></div>';
												get_post_comments($post_id);
												echo '<div class="my-4"></div>';
												if (display_fbComments()):
												?>
													<!-- Facebook comments -->
													<div class="fb-comments" data-href="<?php echo get_post_link($post); ?>" data-numposts="5" data-width="100%"></div>
													<!-- / Facebook comments -->
											<?php endif;
											endif;
											?>
										</div>

							</div>
							<!-- related posts -->
							<!-- title -->
							<div style="z-index: 3;height: 11px;border-left: unset !important;border-right: unset !important;" class="border border-warning mt-5 mb-4 d-flex justify-content-center align-items-center single-right-col">
								<h4 style="z-index: 5;background-color:#f4f4f4" class="m-0 px-2"><?php echo _t("مواضيع ذات صلة"); ?></h4>
							</div>
							<!-- content -->
							<div class="single-right-col bg-white d-flex flex-wrap" style="border: 2px solid #f0ae027a;border-top: 5px solid #f0ae02;border-radius: 14px;">
								<?php
								if ($similar_posts):
									foreach ($similar_posts as $post_similar):
								?>
										<!-- nav post -->
										<div class="sidebar-box-nav-post p-3 col-md-6">
											<div class="d-flex">
												<div class="nav-post-text mr-3 text-ellipsis">
													<a href="<?php echo get_post_link($post_similar); ?>" class="color-link h6"><?php esc_html($post_similar["post_title"]); ?></a><br />
													<!-- <a href="<?php esc_html(siteurl() . '/user/' . $post_similar["post_author"]); ?>" class="color-link small"><?php esc_html(get_user_field($post_similar["post_author"], "user_name")); ?></a> -->
												</div>
												<?php if (!empty($post_similar["post_thumbnail"])): ?>
													<!-- <div class="post-thumb ml-auto">
											<a href="<?php echo get_post_link($post_similar); ?>"><img src="<?php echo get_thumb($post_similar["post_thumbnail"]); ?>" class="" alt="" /></a>
										</div> -->
												<?php endif; ?>
											</div>
										</div>
										<!-- / nav post -->
								<?php
									endforeach;
								else:
									no_content();
								endif;
								?>
							</div>
				</div>
			</div>
			<div class="my-5"></div>
			<div class="sticky-stopper"></div>
			<?php
			include 'parts/media-uploader.php';
			if ($post["post_in"] == "trusted"):
				get_footer();
			endif;
			?>
			<?php if ($post_type == "research"): ?>
				<script src="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/js/jquery.bookblock.js"></script>
				<script src="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/js/jquery.jscrollpane.min.js"></script>
				<script src="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/js/jquery.mousewheel.js"></script>
				<script src="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/js/jquerypp.custom.js"></script>
				<script src="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/js/page.js"></script>
				<script>
					$(function() {
						Page.init();
					});
				</script>
			<?php endif; ?>
			<script>
				$(document).ready(function() {
					const ad_inside = <?php echo json_encode($ad_unit_content_inside); ?>;
					const push_ad_child = 15;
					if (ad_inside.length !== 0) {
						$.each(ad_inside, function(k, v) {
							const $lines = $('.single-post.post-details p');
							var ad = $('<div/>').html('<div class="col-md-12 my-3">' + v.ad_code + '</div>');
							$lines[push_ad_child].insertAdjacentHTML('beforeend', ad.html());
							(adsbygoogle = window.adsbygoogle || []).push({});
						});

					}
				});
			</script>

			<?php if ($taxonomy_settings["copy"] == "on" || $disable_copy == "on"): ?>
				<script type="text/javascript">
					$(document).ready(function() {

						$('body').bind('cut copy', function(e) {
							e.preventDefault();
						});

					});
				</script>
			<?php endif; ?>
</body>

</html>