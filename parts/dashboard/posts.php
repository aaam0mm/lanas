<?php

/**
 * posts.php
 * User dashboard page
 */
$current_user = get_current_user_info();
$user_id = $current_user->id;
$q = $_GET["q"] ?? null;
$post_type = $_GET["post_type"] ?? null;
$per_page = (int) ($_GET["per_page"] ?? RESULT_PER_PAGE);
if ($per_page > 250 || $per_page < 50) {
	$per_page = RESULT_PER_PAGE;
}
$order_by = $_GET["order_by"] ?? "desc";
$post_in = $_GET["post_in"] ?? false;

$query_posts = new Query_post([
	"post_title" => $q,
	"post_type" => $post_type,
	"post_in" => $post_in,
	"post_author" => $user_id,
	'post_status' => false,
	'post_lang' => false,
	'post_status__not' => 'auto-draft',
	'order' => [
		'id',
		$order_by
	],
	'limit' => $per_page
]);
$query_posts->do_query_count = true;
$get_posts = $query_posts->get_posts();
if (user_authority()->publish_in == "all") {
	$get_taxonomies = get_all_taxonomies();
} else {
	$get_taxonomies = $dsql->dsql()->table('taxonomies')->where('taxo_type', user_authority()->publish_in)->get();
}
$posts_categories = $get_posts ? get_posts_categories(array_column($get_posts, 'id')) : [];
$posts_categories_title = get_posts_categories_name($posts_categories);
?>
<div class="user-dashboard-posts">
	<div class="my-5"></div>
	<div class="d-md-flex">
		<div class="mr-2"><?php multi_action_form("posts"); ?></div>
		<div>
			<?php if (!empty($post_type) && user_authority()->move_multi_posts == true): ?>
				<form action="" method="GET" id="move-form">
					<div class="btn-group">
						<select class="custom-select rounded-0" name="category">
							<option value="" disabled="true" selected="true"><?php echo _t("نقل"); ?></option>
							<?php foreach (get_categories($post_type, null, current_content_lang()) as $cat_info_k => $cat_info_v): ?>
								<option value="<?php echo $cat_info_v["id"]; ?>"><?php echo $cat_info_v['cat_title']; ?></option>
							<?php endforeach; ?>
						</select>
						<button class="btn btn-danger border-0 rounded-0 submit-action"><?php echo _t("تنفيد"); ?></button>
					</div>
					<input type="hidden" name="action" value="move" />
					<input type="hidden" name="method" value="multi_action" />
					<input type="hidden" name="target" value="posts" />
				</form>
			<?php endif; ?>
		</div>
	</div>
	<div class="my-3"></div>
	<!-- filter form -->
	<div class="filter-form">
		<form action="" method="GET" id="filter-form">
			<div class="d-sm-flex">
				<div class="mr-auto">
					<div class="form-row">
						<div class="col-12 col-sm-6">
							<input type="text" name="q" value="<?php esc_html($q); ?>" class="form-control rounded-0" placeholder="<?php echo _t("بحث عن مشاركة"); ?>" />
						</div>
						<div class="col-12 col-sm-3 mt-3 mt-sm-0">
							<select class="custom-select rounded-0 form-control select-post-type" name="post_type">
								<option selected="true" disabled="true"><?php echo _t("إختر الصنف"); ?></option>
								<option value=""><?php echo _t("الكل"); ?></option>
								<?php
								if ($get_taxonomies):
									foreach ($get_taxonomies as $taxo):
										$taxo_type = $taxo["taxo_type"];
										echo '<option value="' . $taxo_type . '" ' . selected_val__($post_type, $taxo_type) . ' >' . get_taxonomy_title($taxo) . '</option>';
									endforeach;
								endif;
								?>
							</select>
						</div>
						<div class="col-12 col-sm-3 mt-3 mt-sm-0">
							<select class="custom-select rounded-0 form-control select-post-type" name="post_in">
								<option selected="true" disabled="true"><?php echo _t("موثقة / حرة"); ?></option>
								<option value=""><?php echo _t("الكل"); ?></option>
								<option value="trusted" <?php selected_val($post_in, "trusted"); ?>><?php echo _t("موثقة"); ?></option>
								<option value="untrusted" <?php selected_val($post_in, "untrusted"); ?>><?php echo _t("حرة"); ?></option>
							</select>
						</div>

					</div>
				</div>
				<div class="ml-auto mt-3 mt-sm-0">
					<div class="input-group">
						<select class="custom-select rounded-0 form-control form-control mr-2 select-per-page" name="per_page">
							<option value=""></option>
							<option value="50" <?php selected_val($per_page, 50); ?>>50</option>
							<option value="100" <?php selected_val($per_page, 100); ?>>100</option>
							<option value="250" <?php selected_val($per_page, 250); ?>>250</option>
						</select>
						<?php echo order_by_btn($order_by); ?>
					</div>
				</div>
			</div>
	</div>
	<input type="hidden" name="order_by" value="desc" />
	</form>
</div><!-- / filter-form -->
<div class="my-5"></div>
<div class="">
	<?php if ($get_posts): ?>
		<table class="table table-responsive-sm table-posts">
			<thead>
				<th>
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input check-all-multi" id="select-all-checkbox">
						<label class="custom-control-label" for="select-all-checkbox"></label>
					</div>
				</th>
				<th><?php echo _t("عنوان"); ?></th>
				<th><?php echo _t("فئة"); ?></th>
				<th><?php echo _t("المشاهدات"); ?></th>
				<th><?php echo _t("الإجراءات"); ?></th>
			</thead>
			<tbody>
				<?php
				foreach ($get_posts as $post):
					$post_cats = '';
					if (isset($posts_categories_title[$post["id"]]) && is_array($posts_categories_title[$post["id"]])) {
						$post_cats = '(' . implode(',', $posts_categories_title[$post["id"]]) . ')';
					}
				?>
					<tr>
						<td>
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input check-box-action" data-id="<?php esc_html($post["id"]); ?>" id="select-all-checkbox-<?php esc_html($post["id"]); ?>">
								<label class="custom-control-label" for="select-all-checkbox-<?php esc_html($post["id"]); ?>"></label>
							</div>
						</td>
						<td>
							<a href="<?php echo get_post_link($post["id"]); ?>" class="color-link" title="<?php esc_html($post["post_title"]); ?>">
								<?php
								esc_html(substr_str($post["post_title"], 40));
								echo get_post_in_html($post["post_in"]);
								?>
							</a>&nbsp;<?php echo $post_cats; ?>
						</td>
						<td><?php esc_html(get_taxonomy_title($post["post_type"])); ?></td>
						<td><span class="badge badge-primary"><?php esc_html(formatWithSuffix($post["post_views"])); ?></span></td>
						<td><?php get_post_actions($post); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		echo load_more_btn(
			$query_posts->count_results(),
			".table-posts tbody",
			[
				"request" => "dashboard-ajax",
				"data" => "load-posts",
				"q" => esc_html__($q),
				"post_type" => esc_html__($post_type),
				"per_page" => esc_html__($per_page),
				"order_by" => esc_html__($order_by),
				"post_in" => esc_html__($post_in),
			]
		);
		?>
	<?php else: ?>
		<?php no_content(); ?>
	<?php endif; ?>
</div>
</div>