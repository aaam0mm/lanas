<?php

/**
 * posts.php
 */
require_once 'init.php';

$post_type = $_GET["post_type"] ?? "";
if (!$post_type) {
	exit();
}
$category_id = $_GET["category"] ?? "";
$order_by = $_GET["order_by"] ?? "desc";
$cat_st = false;
$taxonomy_terms = get_taxonomy_terms($post_type);
$taxonomy_settings = get_taxonomy_settings($post_type);
$show_content = true;
$show_tree = true;

if (!empty($category_id)) {
	$cat_st = get_category_settings($category_id);
}
$match_calendar = $_GET["match_calendar"] ?? "gregorian";
if ($post_type == "history") {
	$history_calendars = [
		"hijri" => _t("هجري"),
		"gregorian" => _t("ميلادي"),
		"kurdish" => _t("كردي")
	];
	$show_tree = false;
}

if (@$cat_st->option["visible"] == "hidden" && @admin_authority()->categories != "on") {
	exit();
}
$tree_view = $_GET["tree_view"] ?? "";
if ($show_tree) {
	$get_category_list = multi_level_childs(load_categories_structured($post_type));
}
$get_ads = get_ads("ad_unit_cat_280_280_side");
if (@$cat_st->option["visible_to"] == "users" && !is_login_in()) {
	$show_content = false;
}


?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<meta name="keywords" content="<?php esc_html($cat_st->keywords); ?>">
	<title></title>
</head>

<body>
	<?php get_header(); ?>
	<nav aria-label="breadcrumb" class="breadcrumb-post-page">
		<ol class="breadcrumb container rounded-0">
			<li class="breadcrumb-item"><a href="#" class="color-link"><i class="fas fa-home"></i>&nbsp;<?php echo _t('الرئيسية'); ?></a></li>
			<li class="breadcrumb-item"><a href="#" class="color-link"><?php esc_html(get_taxonomy_title($post_type)); ?></a></li>
			<li class="breadcrumb-item active color-link" aria-current="page"></li>
		</ol>
	</nav>
	<div class="my-5"></div>
	<div class="container">
		<div class="form-filter">
			<div class="d-sm-flex">
				<form method="GET" id="filter-form" class="form-inline">
					<?php if ($tree_view != "true"): ?>
						<?php echo order_by_btn($order_by); ?>
					<?php endif; ?>
					<?php if ($show_tree && $tree_view == "true"): ?>
						<a href="<?php echo siteurl(); ?>/posts/<?php esc_html($post_type); ?>" class="btn btn-warning ml-sm-2 mt-2 mt-sm-0 form-control"><?php echo _t("التصنيف الإفتراضي"); ?></a>
					<?php elseif ($show_tree && $tree_view != "true"): ?>
						<a href="<?php echo siteurl(); ?>/posts/<?php esc_html($post_type); ?>?tree_view=true" class="btn btn-warning ml-sm-2 mt-2 mt-sm-0 form-control"><?php echo _t("التصنيف الشجري"); ?></a>
						<select id="posts-category-select" class="custom-select mt-2 mt-sm-0 ml-sm-2 form-control">
							<option value=""><?php echo _t("الكل"); ?></option>
							<?php
							foreach (get_category_childs($post_type, $category_id) as $cat):
								$cat_settings = @unserialize($cat["cat_settings"]);
								if (isset($cat_settings["visible"]) && $cat_settings["visible"] == "yes") {
							?>
									<option value="<?php esc_html($cat["id"]); ?>"><?php esc_html($cat["cat_title"]); ?></option>
							<?php }
							endforeach;
							?>
						</select>
						<input type="hidden" id="posts-category" name="category" value="<?php esc_html($category_id); ?>" />
					<?php endif; ?>
					<?php
					if ($post_type == "history"):
						$date_day = $_GET["date_day"] ?? date("d");
						$date_month = $_GET["date_month"] ?? date("m");
						$date_year = $_GET["date_year"] ?? date("Y");
						$date_type = $_GET["date_type"] ?? "gregorian";

					?>
						<div class="input-group ml-1 mt-md-0 mt-2">
							<select name="date_type" class="custom-select" id="history_calendar">
								<option selected="" disabled=""><?php echo _t("نوع التاريخ"); ?></option>
								<?php foreach ($history_calendars as $type_dates => $date_title): ?>
									<option value="<?php esc_html($type_dates); ?>" <?php selected_val($type_dates, $date_type); ?>><?php esc_html($date_title); ?></option>
								<?php endforeach; ?>
							</select>
							<select name="date_month" class="custom-select" id="history_month">
								<option selected="" value=""><?php echo _t("الكل"); ?> : <?php echo _t("إختر الشهر"); ?></option>
								<?php
								foreach (months_names(null, $date_type) as $month_num => $month_name):
								?>
									<option value="<?php esc_html($month_num); ?>" <?php selected_val($month_num, $date_month); ?>><?php esc_html($month_name); ?></option>
								<?php
								endforeach;
								?>
							</select>
							<input type="hidden" id="match-calender" name="match_calendar" value="<?php esc_html($match_calendar); ?>" />
							<input type="text" name="date_day" class="form-control" value="<?php esc_html($date_day); ?>" placeholder="<?php echo _t("أدخل اليوم"); ?>" />
							<button class="btn btn-succes form-control"><i class="fas fa-search"></i></button>
						</div>
					<?php elseif ($post_type == 'name' || $post_type == 'dictionary'):
						$letter = $_GET["letter"] ?? "";
						$gender = $_GET["gender"] ?? "";
					?>
						<input type="hidden" id="letter-filter" name="letter" value="<?php esc_html($letter); ?>" />
						<input type="hidden" id="gender-filter" name="gender" value="<?php esc_html($gender); ?>" />

					<?php endif; ?>
					<input type="hidden" name="order_by" value="<?php esc_html($order_by); ?>" />
				</form>
				<div class="ml-auto mt-2 mt-sm-0">
					<div class="form-inline">

						<!-- follow taxonomy -->
						<button class="btn bg-green mr-2 text-white un-subscribe-taxonomy" data-taxonomy="<?php esc_html($post_type); ?>" <?php //echo is_subscribe_to_taxonomy_html_attr($post_type)->attr; 
																																																															?>>
							<?php echo is_subscribe_to_taxonomy_html_attr($post_type)->btn_html; ?>
						</button>
						<!-- / follow taxonomy -->

						<?php if ($taxonomy_terms): ?>
							<?php if (!empty($taxonomy_terms->alert)): ?>
								<button class="btn btn-warning opentermsModal" data-toggle="tooltip" title="<?php echo _t('تنبيه'); ?>" data-taxonomy="<?php esc_html($post_type); ?>" data-term="alert"><i class="fas fa-bullhorn"></i></button>
							<?php endif; ?>

							<?php if (!empty($taxonomy_terms->terms)): ?>
								<button class="btn btn-warning ml-2 opentermsModal" data-toggle="tooltip" title="<?php echo _t('قوانين القسم'); ?>" data-taxonomy="<?php esc_html($post_type); ?>" data-term="terms"><i class="fas fa-gavel"></i></button>
							<?php endif; ?>

							<?php if (!empty($taxonomy_terms->authorized_sources)): ?>
								<button class="btn btn-warning ml-2 opentermsModal" data-toggle="tooltip" title="<?php echo _t('مصادر معتمدة'); ?>" data-taxonomy="<?php esc_html($post_type); ?>" data-term="authorized_sources"><i class="fas fa-link"></i></button>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php if ($show_content): ?>
			<div class="my-5"></div>
			<!-- posts content -->
			<div class="d-sm-flex">
				<div class="posts-right-col">
					<?php if ($show_tree && $tree_view == "true"): ?>
						<div class="tree well">
							<?php echo makeNestedList($get_category_list, "posts/" . $post_type . "", "tree_list"); ?>
						</div>
						<div class="my-5"></div>
					<?php endif; ?>
					<?php if ($tree_view != "true"): ?>
						<?php if ($post_type == 'name' || $post_type == 'dictionary'):
							$letter = $_GET["letter"] ?? "";
							$gender = $_GET["gender"] ?? "";
						?>
							<div class="name-form-filter">
								<ul class="list-unstyled letter-arabic-list position-relative d-flex flex-wrap">
									<?php
									$letters = @unserialize(get_option(get_language_field(current_content_lang(), 'lang_letters')));
									if ($letters):
										$i = 0;
										foreach ($letters as $letter_l):
									?>
											<li class="p-3 <?php if ($letter_l == $letter) {
																				echo " active ";
																			} ?> letter-filter-btn" data-letter="<?php esc_html($letter_l); ?>" data-sort="<?php esc_html($i); ?>">
												<?php esc_html($letter_l); ?>
											</li>
									<?php
											$i++;
										endforeach;
									endif;
									?>
								</ul>
								<div class="my-5"></div>
								<div class="d-sm-flex">
									<div class="btn open-all-tabs open-names align-items-center d-flex opened">
										<?php echo _t("فتح الجميع"); ?>
									</div>
									<?php if ($post_type == "name"): ?>
										<div class="ml-auto mb-3 mb-sm-0 filter-name-gender">
											<ul class="nav">
												<a href="#" data-gender="male" class="gender-filter-btn nav-link <?php if ($gender == "male") {
																																														echo 'active';
																																													} ?>">
													<?php echo _t("ذكر"); ?>
												</a>
												<a href="#" data-gender="female" class="gender-filter-btn nav-link ml-sm-1 <?php if ($gender == "female") {
																																																			echo 'active';
																																																		} ?>">
													<?php echo _t("أنثى"); ?>
												</a>
												<a href="#" data-gender="both" class="gender-filter-btn nav-link ml-sm-1 <?php if ($gender == "both") {
																																																		echo 'active';
																																																	} ?>">
													<?php echo _t("كلاهما"); ?>
												</a>
											</ul>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php get_category_posts($post_type, true); ?>
					<?php endif; ?>
				</div>
				<div class="posts-left-col ml-3">
					<!-- Sidebar -->
					<div class="sidebar-box sidebar-ad-area ad-area">
						<?php if (is_array($get_ads)): foreach ($get_ads as $ad): update_ad_views($ad["ad_key"]); ?>
								<div class="mb-2">
							<?php
								echo $ad["ad_code"] . '</div>';
							endforeach;
						endif;
							?>
								</div>
								<!-- /Sidebar -->
					</div>
				</div><!-- /posts content -->
			<?php endif; ?>
			</div>
			<div class="my-5"></div>
			<?php user_end_scripts(); ?>
			<?php get_footer(); ?>
			<script src="assets/lib/slick/slick.min.js"></script>
			<link href="assets/lib/slick/slick.css" type="text/css" rel="stylesheet" />
			<script>
				$(function() {
					<?php
					if (@$cat_st->option["visible_to"] == "users" && !is_login_in()):
					?>
						$("#signinModal").modal('show');
						$("#signinModal").on("hide.bs.modal", function(e) {
							window.location.href = "<?php echo siteurl() . '/signin.php'; ?>";
						});
					<?php endif; ?>

					$('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
					$('.tree li.parent_li > span').on('click', function(e) {
						var children = $(this).parent('li.parent_li').find(' > ul > li');
						if (children.is(":visible")) {
							children.hide('fast');
							$(this).attr('title', 'Expand this branch').find(' > i').addClass('fa-folder').removeClass('fa-folder-open');
						} else {
							children.show('fast');
							$(this).attr('title', 'Collapse this branch').find(' > i').addClass('fa-folder-open').removeClass('fa-folder');
						}
						e.stopPropagation();
					});

					<?php if ($taxonomy_settings["copy"] == "on"): ?>

						$('body').bind('cut copy', function(e) {
							e.preventDefault();
						});

					<?php endif; ?>

					$("#posts-category-select").change(function() {
						$('#posts-category').val($(this).val());
						$("#filter-form").submit();
					});
				});
			</script>
</body>

</html>