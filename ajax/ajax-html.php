<?php

/**
 * ajax-html.php
 * Handle all request that need to return html response to show it in front-end
 *
 */

require_once(dirname(__FILE__) . "/../init.php");

/** Accept only GET request */
if (strtolower($_SERVER["REQUEST_METHOD"]) != "get") {
	exit();
}

require_once dirname(__FILE__) . "/admin/ajax-html.php";

/** @var Request identifier */
$request = $_GET["request"] ?? '';

if ($request == "add-post-modal") {
	include_once 'html-docs/add-post-modal.php';
}

if ($request == "signup-modal") {
	include_once 'html-docs/signup-modal.php';
}

if ($request == "media-library-load-more") {

	$source = $_GET["source"] ?? "my";
	$file_category = $_GET["file_category"] ?? null;
	$rows = $_GET["rows"] ?? 0;

	echo load_more_btn(
		$rows,
		".media-library-explore",
		[
			"request" => "media-library-content",
			"file_category" => $file_category,
			"source" => $source
		]
	);
}

if ($request == "media-library-content") {

	if (!is_login_in()) {
		return false;
	}

	$current_user_id = get_current_user_info()->id;

	$paged = $_GET['paged'] ?? 1;
	$file_category = $_GET["file_category"] ?? null;
	$source = $_GET["source"] ?? "my";
	$mime_type = $_GET["mime_type"] ?? "image";
	$file_type = null;
	if($source == 'my') {
		$file_type = 'library_media';
	}
	$order_by = 'desc';
	$col = $_GET['col'] ?? null;
	if (is_null($col)) {
		$cols = [
			"files.id",
			"files.file_name",
			"files.file_original_name",
			"files.file_dir",
			"files.file_key",
			"files.file_upload_date",
			"files.file_category"
		];

		$col = $cols[rand(0, count($cols) - 1)];
	}

	$args = ["file_type" => $file_type, "file_cat" => $file_category, "file_uploader" => $current_user_id];

	$get_files = get_files($args, $order_by, $source, false, $col);
	$results = count_last_query();
	$html = '';
	if ($paged == 1) {
		$html = '<div class="row">';
	}
	if (is_array($get_files)) {
		foreach ($get_files as $file) {
			$file_original_name = $file["file_original_name"];
			$html .= '<div class="col-lg-2 col-md-4 col-sm-6 mb-4 position-relative">';
			if (strpos($file["mime_type"], "image") !== false) {
				$html .= '<img src="' . get_thumb($file["id"], "md") . '" class="w-100 h-100 rounded library-media border" alt="" title="' . $file["file_original_name"] . '" data-id="' . $file["id"] . '" data-key="' . $file["file_key"] . '"/>';
			}
			$html .= '</div>';
		}
	} else {
		//$html = no_content();
	}
	if ($paged == 1) {
		$html .= '</div>';

		$html .= load_more_btn($results, '.media-library-explore .row', ['request' => $request, 'source' => $source, 'mime_type' => $mime_type, 'file_category' => $file_category, 'col' => $col]);
	}
	echo $html;
}

if ($request == "video-thumbnail") {
	$url = $_GET["url"] ?? "";
	$url = filter_var($_GET["url"], FILTER_VALIDATE_URL);
	if (!$url) {
		exit;
	}
	$config = [
		'allow' => ['Youtube', 'Vimeo', "Vine", 'Facebook', 'Instagram']
	];
	$embera = new \Embera\Embera();
	echo json_encode($embera->getUrlInfo($url)[$url]);
}

if ($request == "dashboard-ajax") {
	$data = $_GET["data"] ?? "";
	if (!empty($data)) {
		include_once 'html-docs/dashboard-ajax.php';
	}
}

if ($request == "users-ajax") {
	include 'html-docs/users-page.php';
}

if ($request == "posts-ajax") {
	$taxonomy = $_GET["taxonomy"] ?? "";
	$category = (int) ($_GET["category"] ?? 0);
	get_category_posts($taxonomy);
}

if ($request == "send-message-modal") {
	$user_id = $_GET["user_id"] ?? "";
	if (is_login_in()) :
		include 'html-docs/send-message-modal.php';
	else :
		echo "login-modal";
	endif;
}

if ($request == "categories") {
	$taxo_type = $_GET["taxo_type"] ?? "";
	if ($taxo_type) {
		$get_category_list = multi_level_childs(load_categories_structured($taxo_type));
		if ($get_category_list) {
			echo makeNestedList($get_category_list, siteurl() . "/posts/" . $taxo_type . "", "categories_dropdown");
		}
	}
}

if ($request == "complain-form") {
	include 'html-docs/complain-form.php';
}

if ($request == "taxonomy-terms") {
	include 'html-docs/taxonomy-terms-modal.php';
}

if ($request == "notification-modal") {
	include 'html-docs/notification-modal.php';
}

if ($request == "history-months") {
	$calendar = $_GET["calendar"] ?? "gregorian";
	foreach (months_names(null, $calendar) as $month_num => $month_name) {
		echo '<option value="' . $month_num . '">' . $month_name . '</option>';
	}
}

if ($request == "instant-search") {
	$q = $_GET["s"] ?? "";
	if (empty($q)) {
		exit(0);
	}
	$taxonomy = !empty($_GET["taxonomy"]) ? $_GET["taxonomy"] : false;
	$query_results = new Query_post([
		"post_title" => $q,
		"post_type" => $taxonomy
	]);

	$get_results = $query_results->get_posts();
	if ($get_results) {
		foreach ($get_results as $s) {
			?>
			<!-- instant result -->
			<div class="media mb-2 border-bottom pb-2">
				<div class="media-left mr-3">
					<a href="<?php echo get_post_link($s); ?>"><img src="<?php echo get_thumb($s["post_thumbnail"]); ?>" class="media-object" style="width:60px"></a>
				</div>
				<div class="media-body overflow-hidden">
					<a href="<?php echo get_post_link($s); ?>" class="media-heading"><?php esc_html($s["post_title"]); ?><span class="badge badge-primary badge-pill ml-2"><?php esc_html(get_taxonomy_title($s["post_type"])); ?></span></a>
					<?php if ($s["post_type"] != "research") : ?>
						<p class="text-muted small text-ellipsis"><?php echo strip_tags($s["post_content"]); ?></p>
					<?php endif; ?>
				</div>
			</div><!-- instant result -->
	<?php
			}
		} else {
			no_content();
		}
	}

	if ($request == "replies") {
		$comment_id = $_GET['comment_id'] ?? null;
		$paged = $_GET['paged'] ?? 1;
		$get_replies = $dsql->dsql()->table('comments')->where('comments.comment_parent', $comment_id);
		$get_replies->field($get_replies->expr('SQL_CALC_FOUND_ROWS comments.*'))->limit(paged('end', 5), paged('start', 5));
		$get_replies = $get_replies->get();
		$count = count_last_query();
		if ($get_replies) {
			echo post_comment_html($get_replies, true);
			echo load_more_btn(
				$count,
				".comment-childs-" . esc_html__($comment_id) . " .users-replies",
				[
					"request" => "replies",
					"comment_id" => $comment_id,
					"per_page" => 5
				]
			);
		}
	}

	if ($request == "history_navigate") {

		$v = $_GET["v"] ?? "";
		$add_date = "+0 day";
		if ($v == "tomorrow") {
			$add_date = "+1 day";
		} elseif ($v == "yesterday") {
			$add_date = "-1 day";
		}

		$date = strtotime(date('Y-m-d', strtotime($add_date)));
		$date_day = date("d", $date);
		$date_month = date("n", $date);
		$history_posts = get_history_posts('gregorian', $date_month, $date_day);
		?>
	<div class="accordion">
		<a href="#" class="btn btn-block btn-warning text-left btn-lg rounded-0 toggle-history-btn" data-tab="#today-history"><?php echo _t('في مثل هذا اليوم'); ?></a>
		<div class="history-tab-shown" id="today-history">
			<?php if ($history_posts) : ?>
				<ul class="timeline mb-0">
					<?php foreach ($history_posts["today"] as $history_post_today) : ?>
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
				<?php foreach ($history_posts["deaths"] as $history_post_deaths) : ?>
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
				<?php foreach ($history_posts["occasions"] as $history_post_occasions) : ?>
					<li>
						<a href="<?php echo get_post_link($history_post_occasions); ?>" class="float-left"><?php esc_html($history_post_occasions["post_title"]); ?></a><br />
						<p><?php echo substr_str(strip_tags($history_post_occasions["post_content"]), 120); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

	</div>
<?php
}

if ($request == "profile-ajax") {
	$data = $_GET["data"] ?? "";
	if (!empty($data)) {
		include_once 'html-docs/profile-ajax.php';
	}
}

if ($request == "homeVideo") {
	$url = $_GET['url'] ?? '';
	if (!empty($url)) {
		$embera = new \Embera\Embera(array('allow' => array('Youtube')));
		echo '<div class="embed-responsive embed-responsive-16by9">' . @$embera->autoEmbed($url) . '</div>';
	}
}

if ($request == "quick-post-view") {
	include 'html-docs/modal-post-view.php';
}
