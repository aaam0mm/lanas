<?php

use OpenCafe\Datium;

/**
 * functions.php
 * This file contain all most used and necessary function for both admin & user
 */

require_once(dirname(__FILE__) . "/inc/functions/tables_field_fn.php");
require_once(dirname(__FILE__) . "/inc/functions/core_fn.php");
require_once(dirname(__FILE__) . "/inc/functions/analytics_fn.php");
require_once(dirname(__FILE__) . "/inc/functions/badge_distribute.php");
require_once(dirname(__FILE__) . "/inc/functions/user_fn.php");
require_once(dirname(__FILE__) . "/inc/functions/post_fn.php");
require_once(dirname(__FILE__) . "/inc/functions/meta_fn.php");
require_once(dirname(__FILE__) . "/inc/functions/cache_fn.php");
require_once(dirname(__FILE__) . "/inc/functions/header_buttons.php");
require_once(dirname(__FILE__) . "/inc/functions/user_box_module.php");
require_once(dirname(__FILE__) . "/inc/functions/comments.php");


function img_tag($src, $attrs = '')
{
	return '<img data-src="' . $src . '" src="' . siteurl() . '/assets/images/loading-sm.gif" ' . $attrs . ' />';
}

/**
 * Load scripts in end of page
 *
 * @param   array   $files
 * @return  HTML Markup
 */

function enqueue_scripts($src)
{
	return '<script src="' . siteurl() . "/" . $src . '" type="text/javascript"></script>';
}


// Load scripts only once
if (!function_exists('load_scripts')) {
	function load_scripts() {
			echo '
			<script src="' . siteurl() . '/assets/js/audio/config.js"></script>
			<script src="' . siteurl() . '/assets/js/audio/utils.js"></script>
			<script src="' . siteurl() . '/assets/js/audio/audioController.js"></script>
			<script src="' . siteurl() . '/assets/js/audio/progressController.js"></script>
			<script src="' . siteurl() . '/assets/js/audio/playlistController.js"></script>
			<script src="' . siteurl() . '/assets/js/audio/controlsController.js"></script>
			<script src="' . siteurl() . '/assets/js/audio/uiController.js"></script>
			<script src="' . siteurl() . '/assets/js/audio/main.js"></script>
			<script>
				function updateListen(btnTarget) {
						let uid = btnTarget.data("uid"),
								pid = btnTarget.data("pid");
						$.ajax({
							url: `user-ajax.php`,
							type: "POST",
							data: {action: \'updatelisten\', user_id: uid, post_id: pid},
							success: function(response) {
								if(parseInt(response) > 0) {
									btnTarget.find(\'span.count\').text(response)
								}
							}
						});
					}
					$(document).ready(function() {
							// Open the player
							$(\'#book-audio-btn\').click(function() {
								if($(this).hasClass("audio-readable")) {
									let btnTarget = $(this);
										$.ajax({
												url: \'/player.php\', // Endpoint to handle player state
												method: \'POST\',
												data: { action: \'open\', post_id: $(this).data(\'pid\') },
												success: function(response) {
														// Update the player container with the new player HTML
														$(\'#audio-player-container\').html(response);
														updateListen(btnTarget);
												}
										});
									} else {
										swal({
											text: \'لا يوجد ملف صوتي\',
											icon: "error",
											buttons: {
												حسنا: true,
											},
										});
									}
							});

							// Close the player
							$(document).on(\'click\', \'#close-player\', function() {
									$.ajax({
											url: \'/player.php\', // Endpoint to handle player state
											method: \'POST\',
											data: { action: \'close\' },
											success: function(response) {
													// Remove the player from the DOM
													if(!$(`.play-button > .pause-icon`).hasClass("hidden")) {
														$(`.play-button`).click();
													}
													$(\'#audio-player-container\').empty();
											}
									});
							});
					});
			</script>
			';
	}
}

/**
 * call scripts file in user-end
 *
 */

function user_end_scripts()
{
	echo enqueue_scripts('assets/lib/jquery/jquery.min.js');
	echo enqueue_scripts('assets/lib/sweetalert/sweetalert.min.js');
	echo enqueue_scripts('assets/js/core.js');
	echo enqueue_scripts('assets/lib/bootstrap/js/popper.min.js');
	echo enqueue_scripts('assets/lib/bootstrap/js/bootstrap.min.js');
	echo enqueue_scripts('assets/lib/slick/slick.min.js');
	echo enqueue_scripts('assets/lib/lozadjs/lozad.min.js');
	echo enqueue_scripts('assets/js/emojionearea.min.js');
	echo enqueue_scripts('assets/js/site.js');
	echo enqueue_scripts('assets/js/custom.js');
	load_scripts();
	
	echo '<div id="audio-player-container">';
    if (isset($_SESSION['audio_files'])):
			require_once ROOT . "/player.php";
    endif;
	echo '</div>';

?>
	<script>
		$(document).ready(function() {
			$("#preload").remove();
		})

		<?php if (is_login_in()) { ?>
			var isTabActive;

			window.onfocus = function() {
				isTabActive = true;
			};

			window.onblur = function() {
				isTabActive = false;
			};

			// test
			setInterval(function() {
				if (window.isTabActive) {
					ajax_poll();
				}
			}, 10000);
		<?php } ?>
	</script>
	<?php
}

function get_gergorian_year($from = 'gregorian')
{

	if ($from == 'gregorian') {
		return date('Y');
	}

	$y = date('Y');
	$m = date('m');
	$d = date('d');

	return Datium::create($y, $m, $d)->to($from)->get('Y');
}

if (!function_exists("sort_json")) {
	/**
	 * This function sort json depending on a sort_key 
	 *
	 * @param array $array
	 * @param string $sort_key
	 * $param string $sort_by
	 */
	function sort_json($array, $sort_key, $sort_monotony = "asc", $string_lang = false)
	{

		if (!is_array($array)) {
			return false;
		}

		if (!$string_lang) {
			$string_lang = current_lang();
		}

		$new_array = [];

		foreach ($array as $key => $val) {
			$val[$sort_key] = trim(strtolower(json_decode($val[$sort_key])->$string_lang));
			if (!empty($val[$sort_key])) {
				$new_array[$val[$sort_key]] = $val;
			}
		}

		if ($sort_monotony == "asc") {
			ksort($new_array);
		} elseif ($sort_monotony == "desc") {
			krsort($new_array);
		}


		return $new_array;
	}
}

if (!function_exists("translate_const")) {
	function translate_const($string)
	{
		$arr = [
			"male" => _t('ذكر'),
			"female" => _t('أنثى')
		];
		return $arr[$string] ?? null;
	}
}




function get_terms_conditions_page()
{
	$general_settings = @unserialize(get_settings("site_general_settings"));
	$condition_page = $general_settings['condition_page'] ?? null;
	$get_page = get_pages($condition_page);
	$link = siteurl() . '/page/' . $get_page['id'] . '/' . $get_page['page_title'];
	return ['text' => _t("شروط إستخدام"), "link" => $link];
}

if (!function_exists("global_js")) {
	/**
	 * global_js()
	 *
	 
	 */
	function global_js()
	{
		$general_settings = @unserialize(get_settings("site_general_settings"));
		if (empty($general_settings["site_max_upload"])) {
			$general_settings["site_max_upload"] = 0;
		}
		echo '<script>
				var gbj = {
					siteurl : "' . siteurl() . '",
					confirm_delete_text : "' . _t('هل تريد حدف هذا المحتوى ؟') . '",
					new_notif_text : "' . _t('تنبيه جديد') . '",
					new_msg_text : "' . _t('رسالة جديدة') . '",
					loading : "' . _t('جاري التحميل ...') . '",
					delete_success : "' . _t('تم حدف بنجاح') . '",
					subscribe : "' . _t('إشترك') . '",
					file : "' . _t('الملف') . '",
					unsubscribe : "' . _t('إلغاء الإشتراك') . '",
					success_request : "' . _t("تمت العملية بنجاح") . '",
					failed_request : "' . _t('حدث خطأ المرجو إعادة المحاولة مرة أخرى') . '",
                    ok_text : "' . _t("حسنا") . '",
                    cancel_text : "' . _t("إلغاء") . '",
                    warning_text : "' . _t("إنتباه") . '",
                    close_all_text : "' . _t("غلق الجميع") . '",
                    open_all_text : "' . _t("فتح الجميع") . '",
                    site_max_upload : ' . $general_settings["site_max_upload"] . ',
                    ext_max_upload: ' . json_encode($general_settings["ext_max_upload"]) . ',
                    file_not_uploaded_text : "' . _t("لم يتم رفع الملف. المرجو التأكد من الحجم و الإمتداد") . '",
                    big_file_text: "' . _t("المعدرة حجم الملف كبير") . '"
				};
				</script>
		';
	}
}

if (!function_exists("multi_action")) {
	/**
	 * multi_action
	 * 
	 */
	function multi_action()
	{

		$response = ["success" => false];

		$action = $_POST["action"] ?? "";
		$target = $_POST["target"] ?? "";

		/** @var array $id  */
		$id = $_POST["id"] ?? "";

		if (empty($target) || empty($action) || !is_array($id)) {
			$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			echo json_encode($response);
			return '';
		}
		if ($action == "delete") {
			$delete = new Delete($id);
			if ($target == "comments") {
				$delete->delete_comments();
			} elseif ($target == "posts") {
				$delete->delete_posts();
				if (is_null($delete->errors())) {
					$response["success"] = true;
					$response["msg"] = _t("تم حدف بنجاح");
				} else {
					$response["errors"] = $delete->errors();
				}
			} elseif ($target == "post_infos") {
				$delete->delete_link();
				if (is_null($delete->errors())) {
					$response["success"] = true;
					$response["msg"] = _t("تم حدف بنجاح");
				} else {
					$response["errors"] = $delete->errors();
				}
			} elseif ($target == "users") {
				$delete->delete_users();
			} elseif ($target == "ads") {
				$delete->delete_ads();
			} elseif ($target == "files") {
				$delete->delete_files();
			} elseif ($target == "authors") {
				if ($delete->delete_authors()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($target == "boot_comments") {
				if ($delete->delete_boot_comments()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($target == "boots") {
				if ($delete->delete_boots()) {
					$response["success"] = "تم حدف بنجاح.";
				} else {
					$response["error"] = "المعذرة حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			}
			if (is_null($delete->errors())) {
				$response["success"] = true;
				$response["msg"] = _t("تم حدف بنجاح");
			} else {
				$response["errors"] = $delete->errors();
			}
		} elseif ($action == "lock" || $action == "publish") {
			if ($target == "comments") {
				foreach ($id as $comment_id) {
					un_lock_comment($comment_id, $action);
				}
			} elseif ($target == "posts") {
				foreach ($id as $post_id) {
					un_lock_post($post_id, $action);
				}
			} elseif ($target == "users") {
				foreach ($id as $user_id) {
					un_lock_user($user_id, $action);
				}
			} elseif ($target == "ads") {
				foreach ($id as $ad_id) {
					un_lock_ad($ad_id, $action);
				}
			}
			$response["success"] = true;
			$response["msg"] = _t("تم عمل التنفيد الجماعي بنجاح");
		} elseif ($action == "verify" || $action == "unverify") {
			if ($target == "users") {
				foreach ($id as $user_id) {
					un_verify_user($user_id, $action);
				}
			}
			$response["success"] = true;
			$response["msg"] = _t("تم عمل التنفيد الجماعي بنجاح");
		} elseif ($action == "summary") {
			global $dsql;
			$success = 0;
			$fails = 0;
			$output = [];
			if ($target == "posts") {
				foreach ($id as $post_id) {
					if (!$post_id || $post_id == 0) {
						error_log("Invalid post_id: $post_id");
						continue;
					}
					$general_settings = @unserialize(get_settings("site_general_settings"));
					$post_type = get_post_field($post_id, 'post_type');
					$book_title = get_post_field($post_id, 'post_title');
					$book_author = get_post_meta($post_id, 'book_author');
					$book_lang = get_post_field($post_id, 'post_lang') == "ar" ? 'العربية' : 'الكردية';
					$email = $general_settings['chat_gpt_email'] ?? 'salahbellal394@gmail.com';
					$password = $general_settings['chat_gpt_password'] ?? '(oQl3dkbG3%BYdRQ5K';
					$headless = $general_settings["headless"] == "on" ? 'true' : "false";
					
					$get_taxonomy = get_taxonomy(6);
					$taxo_settings = @unserialize($get_taxonomy["taxo_settings"] ?? []);
					if (is_array($taxo_settings)) {
							extract($taxo_settings);
					}
					$chat_text = $chat_gpt_text ?? '';

					if ($post_type == "book") {
						$books_paths = '';
						$books_ids = @unserialize(get_post_meta($post_id, "books_ids")) ?? [];
						if (is_array($books_ids) && count($books_ids) > 0) {
							foreach ($books_ids as $book_id) {
								$book = get_file($book_id, false, true);
								if ($book) {
									$book = UPLOAD_DIR . get_file($book_id, false, true);
									if (file_exists($book)) {
										$books_paths .= $book . " ";
									}
								}
							}
						}
						// Path to your Python script
						$pythonScript = PY . 'bypassCloudflare.py';

						// Escape all dynamic arguments
						$escaped_title = escapeshellarg($book_title);
						$escaped_author = escapeshellarg($book_author);
						$escaped_lang = escapeshellarg($book_lang);
						$escaped_email = escapeshellarg($email);
						$escaped_password = escapeshellarg($password);
						$escaped_chat_text = escapeshellarg($chat_text);
						// $escaped_headless = escapeshellarg($headless);

						$cmd = "HEADLESS=$headless /usr/local/bin/python3 " . escapeshellarg($pythonScript) .
							" $escaped_title $escaped_author $escaped_lang $escaped_email $escaped_password $escaped_chat_text $books_paths";

						exec($cmd, $out, $returnVar);
						$cmd .= " 2>&1";
						// putenv("PATH=/usr/local/bin:/usr/bin:/bin");
						if ($returnVar === 0) {
							$output[$post_id] = $out;
							$success++;
						} else {
							$fails++;
						}
					}
				}
			}
			if ($success > 0) {
				$msg = 'تم النتفيذ بدون نتيجة';
				if (count($output) > 0) {
					$msg = '';
					$s_success = 0;
					$s_fails = 0;
					foreach ($output as $post_id => $out) {
						$summary = implode("\n", $out);
						if (!empty($summary) && !preg_match("/false/i", $summary)) {
							$book_summary = $dsql->dsql()
								->table('post_meta')
								->where('post_id', $post_id)
								->where('meta_key', 'book_summary')
								->limit(1)->getRow();

							if ($book_summary) {
								$save_summary = $dsql->dsql()->table('post_meta')
									->set(["meta_value" => $summary])
									->where('meta_key', 'book_summary')
									->where('post_id', $post_id)
									->update();
							} else {
								$save_summary = $dsql->dsql()->table('post_meta')
									->set(["meta_key" => 'book_summary', "post_id" => $post_id, "meta_value" => $summary])
									->insert();
							}
							$msg = 'العملية تمت';
							if ($save_summary) {
								$s_success++;
							} else {
								$msg = "";
								$s_fails++;
							}
							$msg .= " الكتب الملخصة (" . $s_success . "), الكتب التي لم تلخص (" . $s_fails . ") ";
						} else {
							$msg .= $summary;
							// $s_fails++;
						}
					}
					// $msg .= " الكتب الملخصة (" . $s_success . "), الكتب التي لم تلخص (" . $s_fails . ") ";
				}
				$response["success"] = true;
				$response["msg"] = _t($msg);
				$response["data"] = $output;
			} else {
				$response["success"] = false;
				$response["msg"] = _t("فشل في تنفيذ البرنامج النصي Python");
			}
		} elseif ($action == "move") {
			if ($target == "posts") {
				$category = $_POST['category'] ?? null;
				if (is_numeric($category)) {
					$post_categories = [$category];
					foreach (explode(",", get_category_parents($category)) as $cat_id) {
						if (!empty($cat_id)) {
							$post_categories[] = $cat_id;
						}
					}
					foreach ($post_categories as $post_category) {
						move_posts($id, $post_category);
					}
				}
			}
			$response["success"] = true;
			$response["msg"] = _t("تم نقل المواضيع بنجاح");
		} elseif($action == "set") {
			if ($target == "authors") {
				global $dsql;
				if(is_array($id) && count($id) > 0) {
					$stats = $_POST['stats'] ?? [];
					$success = 0;
					foreach($id as $index => $author_id) {
						$stat = $stats[$index] == 'en' ? 1 : 0;
						$update = $dsql->dsql()->table('authors')->set(["author_stat" => $stat])->where("id", $author_id)->update();
						if ($update) {
							$success++;
						}
					}
					if($success == count($id)) {
						$response["success"] = true;
						$response["msg"] = _t("العملية تمت بنجاح تم التعديل على $success حالة");
					} else {
						$response["success"] = false;
						$response["msg"] = _t("العملية فشلت");
					}
				} else {
					$response["success"] = false;
					$response["msg"] = _t("قم بتحديد العناصر اولا");
				}
			} elseif($target == "posts") {
				global $dsql;
				if(is_array($id) && count($id) > 0) {
					$stats = $_POST['stats'] ?? [];
					$success = 0;
					foreach($id as $index => $post_id) {
						$stat = $stats[$index] == 'off' ? 'on' : "off";
						$update = $dsql->dsql()->table('posts')->set(["share_authority" => $stat])->where("id", $post_id)->update();
						if ($update) {
							$success++;
						}
					}
					if($success == count($id)) {
						$response["success"] = true;
						$response["msg"] = _t("العملية تمت بنجاح تم التعديل على $success حالة");
					} else {
						$response["success"] = false;
						$response["msg"] = _t("العملية فشلت");
					}
				} else {
					$response["success"] = false;
					$response["msg"] = _t("قم بتحديد العناصر اولا");
				}
			}
		}
		echo json_encode($response);
	}
}

function get_posts_categories_name($categories)
{

	$titles = [];
	foreach ($categories as $post_id => $cats) {
		foreach ($cats as $cat) {
			$titles[$post_id][] = $cat['cat_title'];
		}
	}

	return $titles;
}

function array_value_to_key($array, $key)
{

	$new_array = [];

	foreach ($array as $arr) {
		$new_array[$arr[$key]][] = $arr;
	}

	return $new_array;
}

if (!function_exists("multi_action_form")) {
	/**
	 * multi_action_form()
	 *
	 * @param string $target
	 */
	function multi_action_form($target)
	{
	?>
		<!-- Action form -->
		<form action="" method="GET" id="action-form">
			<div class="btn-group">
				<select class="custom-select rounded-0" name="action">
					<option value="" disabled="true" selected="true"></option>
					<option value="delete"><?php echo _t("حدف"); ?></option>
					<option value="lock"><?php echo _t("تعطيل"); ?></option>
					<option value="publish"><?php echo _t("نشر"); ?></option>
				</select>
				<button class="btn btn-danger border-0 rounded-0 submit-action"><?php echo _t("تنفيد"); ?></button>
			</div>
			<input type="hidden" name="method" value="multi_action" />
			<input type="hidden" name="target" value="<?php esc_html($target); ?>" />
		</form>
		<!-- / Action form -->
		<?php
	}
}

if (!function_exists("load_more_btn")) {
	/**
	 * load_more_btn()
	 *
	 * @param int $results
	 * @param string $area ( DOM #ID or .CLASS )
	 * @param array $data
	 * @return mixed (HTML markup on success|boolean on failure)
	 */
	function load_more_btn($results, $area, $data)
	{

		if (empty($results) || empty($area)) {
			return false;
		}

		$data = (object) $data;
		$per_page = (int) ($_GET["per_page"] ?? RESULT_PER_PAGE);
		if ($per_page > 250 || $per_page == 0) {
			$per_page = RESULT_PER_PAGE;
		}
		$paged = $_GET["paged"] ?? 1;
		$pag_count = page_max($results, $per_page);
		if ($pag_count > $paged && $paged == 1) {
			ob_start();
		?>
			<!-- Load more button -->
			<div id="load_more_area" class="d-flex justify-content-center border-top py-2">
				<button class="btn btn-warning load-more" data-request='<?php echo json_encode($data); ?>' data-paged="<?php esc_html($paged); ?>" data-paged-max="<?php esc_html($pag_count); ?>" data-area="<?php esc_html($area); ?>"><?php echo _t("عرض المزيد"); ?></button>
			</div><!-- / Load more button -->
		<?php
			return ob_get_clean();
		}
	}
}

if (!function_exists("get_pagination")) {

	/**
	 * @param $query_result_count
	 * @param int $cur_page
	 * @param string $dir
	 * @param int $per_page = const RESULT_PER_PAGE (by default) RESULT_PER_PAGE(config.php)
	 * @return mixed
	 */
	function get_pagination($results_count, $per_page = 12)
	{
		if ($results_count == 0) {
			return false;
		}

		$cur_page = $_GET['paged'] ?? 1;

		$request_uri = $_SERVER['REQUEST_URI'];
		$request_url = $_SERVER['REDIRECT_URL'];
		$query_string = $_SERVER['QUERY_STRING'];

		parse_str($query_string, $queries);

		unset($queries['page']);
		unset($queries['paged']);

		$qm = '?';
		if (!empty($queries)) {
			$qm = '&';
		}

		if (isset($request_url) && !empty($request_url)) {
			$dir = $request_url;
		} else {
			$dir = strtok($request_uri, '?');
		}

		$dir .= '?' . http_build_query($queries);

		$no_of_paginations = ceil($results_count / $per_page);
		if ($cur_page >= 3) {
			$start_loop = $cur_page - 1;
			if ($no_of_paginations > $cur_page + 1)
				$end_loop = $cur_page + 1;
			else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 2) {
				$start_loop = $no_of_paginations - 2;
				$end_loop = $no_of_paginations;
			} else {
				$end_loop = $no_of_paginations;
			}
		} else {
			$start_loop = 1;
			if ($no_of_paginations > 3)
				$end_loop = 3;
			else
				$end_loop = $no_of_paginations;
		}
		echo '<nav aria-label="pagination" class="mt-3">';
		echo '<ul class="pagination">';
		if ($cur_page <= $no_of_paginations) {
			for ($i = $start_loop; $i <= $end_loop; $i++) {

				if ($cur_page == $i)
					echo  '<li class="page-item disabled"><a href="#" class="page-link">' . $i . '</a></li>';
				else
					echo '<li class="page-item"><a href="' . $dir . $qm . 'paged=' . $i . '" class="page-link">' . $i . '</a></li>';
			}
		}
		echo '<li class="page-item disabled"><a href="#" class="page-link">صفحة ' . $cur_page . ' من ' . $no_of_paginations . '</a></li>';
		echo "</ul>";
		echo "</nav>";
	}
}

// if (!function_exists("share_link")) {
// 	/**
// 	 * share_link()
// 	 *
// 	 * @param string $plat
// 	 * @param string $url
// 	 * @return mixed (string on success|boolean on failure)
// 	 */
// 	function share_link($plat, $url)
// 	{
// 		//$url = filter_var($url,FILTER_VALIDATE_URL);
// 		if (empty($plat) || empty($url)) {
// 			return false;
// 		}

// 		if ($plat == "facebook") {
// 			if (is_mobile()) {
// 				$link_schema = "fb://faceweb/f?href=https://www.facebook.com/sharer/sharer.php?u=" . $url;
// 			} else {
// 				$link_schema = "https://www.facebook.com/sharer/sharer.php?u=" . $url;
// 			}
// 		} elseif ($plat == "twitter") {
// 			$link_schema = "https://twitter.com/intent/tweet?url=" . $url;
// 		}
// 		return $link_schema;
// 	}
// }

if (!function_exists("share_link")) {
	/**
	 * share_link()
	 *
	 * @param string $plat
	 * @param string $url
	 * @return mixed (string on success|boolean on failure)
	 */
	function share_link($plat, $url)
	{
			//$url = filter_var($url,FILTER_VALIDATE_URL);
			if (empty($plat) || empty($url)) {
					return false;
			}

			if ($plat == "facebook") {
					if (is_mobile()) {
							$link_schema = "fb://faceweb/f?href=https://www.facebook.com/sharer/sharer.php?u=" . $url;
					} else {
							$link_schema = "https://www.facebook.com/sharer/sharer.php?u=" . $url;
					}
			} elseif ($plat == "twitter") {
					$link_schema = "https://twitter.com/intent/tweet?url=" . $url;
			} elseif ($plat == "telegram") {
					$link_schema = "https://t.me/share/url?url=" . $url;
			} elseif ($plat == "whatsapp") {
					if (is_mobile()) {
							$link_schema = "whatsapp://send?text=" . $url;
					} else {
							$link_schema = "https://web.whatsapp.com/send?text=" . $url;
					}
			} else {
					return false;
			}

			return $link_schema;
	}
}


function gravity2coordinates($image)
{
	// theoretically this should work
	// $im->setImageGravity( Imagick::GRAVITY_SOUTHEAST );
	// but it doesn't so here goes the workaround
	$wm = unserialize(get_option("watermark"));
	$x_margin = ($image->getImageWidth() * $wm["pos_x"]) / 100;
	$y_margin = ($image->getImageHeight() * $wm["pos_y"]) / 100;

	$x = $x_margin;
	$y = $y_margin;
	$watermark_width = ($image->getImageWidth() * $wm["w"]) / 100;

	return array(
		'x' => (int) abs($x),
		'y' => (int) abs($y),
		'watermark_width' => (int) $watermark_width,
		'opacity' => $wm["opacity"],
		'image' => (int) $wm['image'],
		"visible" => $wm["visible"] ?? "off"
	);
}

if (!function_exists("resize")) {
	/**
	 * resize()
	 *
	 * @param string $original_file_dir
	 * @param array $d (dimension)
	 * @return 
	 */
	function resize($file_name, $original_file_dir, $d, $crop = true, $file_type = '')
	{

		if (empty($original_file_dir) || empty($file_name)) {
			return false;
		}
		if (!file_exists($original_file_dir . $file_name)) {
			return false;
		}

		$image = new Imagick($original_file_dir . $file_name);

		$file_name_ext = explode(".", $file_name);
		$file_name = $file_name_ext[0];
		$file_ext = $file_name_ext[1];

		if (is_string($d)) {
			$d = images_sizes($d);
			$d = ["w" => $d["w"], "h" => $d["h"]];
		}

		$outfile = $original_file_dir . $file_name . "-" . $d["w"] . "-" . $d["h"] . "." . $file_ext;
		if ($crop) {
			$im = $image->cropThumbnailImage($d["w"], $d["h"]);
		} else {
			$im = $image->resizeImage($d["w"], $d["h"], Imagick::FILTER_POINT, 1);
		}

		$wm = false;
		$wm_opt = gravity2coordinates($image);
		if (is_array($wm_opt) && $wm_opt["visible"] == "on" && $file_type == "post_attachment") {
			$wm_image = $wm_opt["image"];
			if ($wm_image) {
				$w_img = UPLOAD_DIR . get_file_field($wm_image, "file_dir") . DIRECTORY_SEPARATOR . get_file_field($wm_image, "file_name");
				if (file_exists($w_img)) {
					// Open the watermark
					$watermark = new Imagick($w_img);
					$watermark->resizeImage($wm_opt["watermark_width"], false, Imagick::FILTER_POINT, 1);
					$wm = true;
				}
			}
		}

		if ($im) {
			if ($wm) {
				$image->compositeImage($watermark, $watermark->getImageCompose(), $wm_opt["x"], $wm_opt["y"]);
			}
			if ($image->writeImage($outfile)) {
				return true;
			}
		}
		return false;
	}
}

if (!function_exists("images_sizes")) {
	/**
	 * images_sizes()
	 *
	 * @param string $size
	 * @return mixed(array on success|boolean on failure)
	 */
	function images_sizes($size = "md")
	{
		$sizes = [
			"sm" => ["w" => 120, "h" => 120],
			"md" => ["w" => 480, "h" => 480],
			"lg" => ["w" => 720, "h" => 720],
			"x_lg" => ["w" => 1024, "h" => 1024]
		];
		return $sizes[$size] ?? false;
	}
}

if (!function_exists("poll_vote")) {
	/**
	 * poll_vote()
	 *
	 * @param int $poll
	 * @param int $vote
	 * @return boolean
	 */
	function poll_vote($poll, $vote)
	{

		if (empty($poll) || empty($vote)) {
			return false;
		}

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;

		$check = $dsql->dsql()->table('vote_sys')->where('poll_id', $poll)->where('user_id', $current_user->id)->limit(1)->getRow();

		if ($check) {
			return false;
		}

		$insert_vote = $dsql->dsql()->table('vote_sys')->set(["poll_id" => $poll, "user_id" =>  $current_user->id, "vote" => $vote])->insert();

		if ($insert_vote) {
			return true;
		}

		return false;
	}
}

if (!function_exists("get_votes")) {
	/**
	 * get_votes()
	 *
	 * @param int $poll_id
	 * @return 
	 */
	function get_votes($poll_id)
	{

		if (empty($poll_id)) {
			return false;
		}

		global $dsql;
		$get_poll_votes = $dsql->dsql()->table('vote_sys')->where('poll_id', $poll_id)->field('vote,user_id')->get();

		if ($get_poll_votes) {
			$vote_for = "";
			$votes = $votes_count = array();
			$current_user = get_current_user_info();
			foreach ($get_poll_votes as $get_poll_vote) {
				$votes_count["votes"][] = $get_poll_vote["vote"];
				if (@$current_user->id == $get_poll_vote["user_id"]) {
					$vote_for = $get_poll_vote["vote"];
				}
			}

			$votes_count_val = array_count_values($votes_count["votes"]);
			$count_all_votes = array_sum($votes_count_val);
			foreach ($votes_count_val as $vote_count_k => $vote_count_v) {
				$vote_percent  = intval(($vote_count_v / $count_all_votes) * 100);
				$votes[$vote_count_k] = array("votes" => $vote_count_v, "percent" => $vote_percent);
			}
			$votes["voted"] = $vote_for;
			return $votes;
		}

		return false;
	}
}

if (!function_exists("popover_post")) {
	/**
	 * popover_post()
	 *
	 * @param int $post_id
	 * @return HTML Markup
	 */
	function popover_post($post_id)
	{
		if (empty($post_id)) {
			return false;
		}
		ob_start();
		?>
		<ul class="list-unstyled bg-warning d-flex flex-row p-2 mb-0">
			<li class="mr-2 send-message-modal" data-user="<?php esc_html(get_post_field($post_id, "post_author")); ?>"><i class="fas fa-envelope"></i></li>
			<li class="mr-2 open-complain-form" data-post="<?php esc_html($post_id); ?>"><i class="fas fa-exclamation-triangle"></i></li>
			<li class="post-bk"><?php echo bookmark_opt($post_id); ?></li>
		</ul>
	<?php
		return ob_get_clean();
	}
}

if (!function_exists("popover_post_new")) {
	/**
	 * popover_post_new()
	 *
	 * @param int $post_id
	 * @return HTML Markup
	 */
	function popover_post_new($post_id)
	{
		if (empty($post_id)) {
			return false;
		}
		ob_start();
	?>
		<!-- <div class="dropdown-divider"></div> -->
		<span class="dropdown-item post-bk" data-post="<?php esc_html($post_id); ?>">

			<?php
			$post_mark = bookmark_opt($post_id);
			if(empty($post_mark)) {
				$post_mark = ' <i class="fas fa-bookmark" data-id="0"></i>';
			}
			echo $post_mark;
			?>
			<span class="ml-2">حفظ المنشور</span>
		</span>
		<span class="dropdown-item follow-post" data-post="<?php esc_html($post_id); ?>">
			<i class="far fa-eye"></i>
			<span class="ml-2">متابعة المنشور</span>
		</span>
		<span class="dropdown-item open-complain-form" data-post="<?php esc_html($post_id); ?>">
			<i class="far fa-flag"></i>
			<span class="ml-2">الابلاغ</span>
		</span>
		<span class="dropdown-item short-link" data-post="<?php esc_html($post_id); ?>">
			<i class="fas fa-link"></i>
			<span class="ml-2">رابط مختصر</span>
		</span>
		<?php
		$current_user = get_current_user_info();

		$post = get_post_field($post_id, ["post_type", "post_in"]);
		$post["id"] = $post_id;

		$post_type = $post["post_type"];
		$post_in = $post["post_in"];
		$post_id = $post["id"];

		$post_author = get_post_field($post_id, "post_author");
		$post_status = get_post_field($post_id, "post_status");
		$post_in = get_post_field($post_id, "post_in");

		?>
		<?php if (($current_user->id == $post_author && $post_status != "blocked") || admin_authority()->posts == "on"): ?>
					<div class="dropdown-divider"></div>
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
				<script>
					$(`.dropdown-item a[href="#"]`).on('click', function(e) {
						e.preventDefault();
					});
				</script>
	<?php
		return ob_get_clean();
	}
}

if (!function_exists("render_slices_single_post")) {
	/**
	 * render_slices_single_post()
	 *
	 * @param array $slices
	 * @return HTML Markup
	 */
	function render_slices_single_post($slices)
	{
		if (!is_array($slices)) {
			return false;
		}
		ob_start();
	?>
		<div class="load-slices px-5 border-top py-3">
			<?php
			foreach ($slices as $slice):
				$slice_content = json_decode($slice["slice_content"]);
			?>
				<div class="slice-type-<?php esc_html($slice["slice_type"]); ?>">
					<div class="slice-title">
						<h4><?php esc_html($slice_content->title); ?></h4>
					</div>
					<div class="slice-desc">
						<?php echo validate_html($slice_content->desc); ?>
					</div>
					<div class="slice-thumb">
						<?php
						$get_thumb = $slice_content->image ?? "";
						if ($get_thumb) {
						?>
							<img src="<?php echo get_thumb($get_thumb, ["w" => 650, "h" => 420]); ?>" class="img-thumbnail img-fluid mx-auto d-block" />
						<?php
						}
						?>
					</div>
					<?php

					switch ($slice["slice_type"]):
						case "embed":
							$embera = new \Embera\Embera();
					?>
							<div class="embed-slice embed-responsive embed-responsive-16by9">
								<?php echo $embera->autoEmbed($slice_content->embed); ?>
							</div>
						<?php
							break;
						case "map":
							echo $slice_content->map_url;
							break;
						case "vote":
							$get_votes = get_votes($slice["id"]);
							$show_votes = false;
							$class = '';
							if (!empty($get_votes["voted"])) {
								$show_votes = true;
								$class .= 'vote-post-dsbl ';
							}
						?>
							<div class="vote-slice mt-3">
								<ul class="list-unstyled">
									<?php foreach ($slice_content->options as $vote_opt): ?>
										<li class="position-relative vote-option-post bg-light font-weight-bold p-3 mb-3 <?php if ($get_votes["voted"] == $vote_opt->text) {
																																																				echo 'voted-option ';
																																																			}
																																																			echo $class; ?>" data-poll="<?php esc_html($slice["id"]); ?>" data-vote="<?php esc_html($vote_opt->text); ?>">
											<div class="vote-option-percent position-absolute h-100 right-0 top-0" style="width:<?php if ($show_votes) {
																																																						esc_html($get_votes[$vote_opt->text]["percent"] ?? 0);
																																																					} ?>%"></div>
											<?php esc_html($vote_opt->text); ?>
											<span class="float-right poll-votes-count"><?php if ($show_votes) {
																																		esc_html($get_votes[$vote_opt->text]["votes"] ?? 0);
																																	} ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php
							break;
						case "quizz":
						?>
							<ul class="list-unstyled quizz-answers">
								<?php
								$answer_queue = 1;
								foreach ($slice_content->answer as $answer):
								?>
									<li class="p-2 mt-3 quizz-answer-<?php esc_html($slice["id"]); ?>" data-answer-queue="<?php esc_html($answer_queue); ?>" data-answer="<?php esc_html($answer->text->value); ?>" data-quiz="<?php esc_html($slice["id"]); ?>"><span><?php esc_html($answer->text->value); ?></span></li>
								<?php $answer_queue++;
								endforeach; ?>
							</ul>
							<!-- Quizzes script -->
							<script>
								$(function() {
									var is_participate_<?php esc_html($slice["id"]); ?> = false;
									$(".quizz-answer-<?php esc_html($slice["id"]); ?>").on("click", function(e) {
										if (is_participate_<?php esc_html($slice["id"]); ?>) {
											return;
										}
										var quizz = <?php echo json_encode($slice_content->answer); ?>;
										var $t = $(this);
										var data_queue = $t.data("answer-queue");
										if (quizz[data_queue].text.is_true == "on") {
											$t.addClass("true-answer");
										} else {
											$t.addClass("wrong-answer");
										}
										is_participate_<?php esc_html($slice["id"]); ?> = true;
										e.preventDefault();
									});
								});
							</script>
							<!-- / Quizzes script -->
					<?php
							break;
					endswitch;
					?>
				</div>
				<div class="my-4"></div>
			<?php
			endforeach;
			?>
		</div>
		<?php
		echo ob_get_clean();
	}
}


if (!function_exists("get_files_categories")) {
	/**
	 * get_files_categories()
	 * get file categories()
	 *
	 * @param int $cat_id
	 * @return array
	 */
	function get_files_categories($cat_id = null)
	{

		global $dsql;

		$get = $dsql->dsql()->table('files_categories');
		if ($cat_id) {
			$get->where('id', $cat_id);
		}
		$get = $get->get();
		return $get;
	}
}

if (!function_exists("get_files_category_name")) {
	/**
	 * get_files_category_name()
	 * get name of category from object
	 *
	 * @param object $category_name
	 * @return string
	 */
	function get_files_category_name($category_name = null)
	{

		if (!is_null($category_name)) {
			$category_name = @json_decode($category_name);
		}

		if (is_object($category_name)) {
			$current_lang = current_lang();
			return $category_name->$current_lang;
		}
	}
}

if (!function_exists("error_page")) {
	/**
	 * error_page()
	 * ...
	 *
	 * @param array $errors
	 * @param boolean|HTML markup $custom
	 * @return HTML Markup
	 */
	function error_page($errors, $custom = false)
	{
		if (!is_array($errors)) {
			return false;
		}
		ob_start();
		if ($custom === false) {
		?>
			<!DOCTYPE html>
			<html>

			<head>
				<?php get_head(); ?>
			</head>

			<body>
				<div class="position-absolute w-100 h-100 top-0 right-0">
					<div class="d-flex align-items-center justify-content-center h-100">
						<div class="error-icon">
							<i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
							<div class="error-text">
								<ul class="list-unstyled mt-3">
									<?php foreach ($errors as $error) { ?>
										<li class="h5">
											<?php echo $error["error"]; ?>
										</li>
									<?php } ?>
								</ul>
							</div>
							<a href="<?php siteurl(); ?>" class="btn btn-warning"><?php echo _t("أكمل إلى الرئيسية"); ?><i class="fas fa-arrow-left ml-2"></i></a>
						</div>
					</div>
				</div>
			</body>

			</html>
	<?php
		} else {
			echo $custom;
		}
		return ob_get_clean();
	}
}

if (!function_exists("get_cnvs")) {
	/**
	 * get_cnvs()
	 * load user conversation
	 *
	 * @param int $msg_id
	 * @param int $user_id
	 * @return mixed(array on success|boolean on failure)
	 */
	function get_cnvs($msg_id, $user_id)
	{
		$current_user = get_current_user_info();
		if (!$current_user || empty($msg_id) || empty($user_id)) {
			return false;
		}

		if ($current_user->id != $user_id && admin_authority()->users != "on") {
			return false;
		}

		global $dsql;
		$get_cnvs = $dsql->dsql()->table('conversations_sys')->where('msg_id', $msg_id)->order('msg_date', 'asc');
		if ($get_cnvs) {
			$cnvs = [];
			foreach ($get_cnvs as $cnv) {
				/**
				 * @var string $display_place
				 * choose to message in left (message sent by not loggin user) or right (message sent by loggin user) side 
				 */
				$display_place = "";
				/**
				 * @var int $to
				 * get message reciever
				 */
				$to = null;

				if ($user_id == $cnv["msg_from"]) {
					$display_place = "right";
					$to = $cnv["msg_to"];
				} else {
					$display_place = "left";
					$to = $cnv["msg_from"];
				}
				$cnvs["to"] = $to;
				$cnvs["cnv"][] = [
					"msg" => $cnv["msg"],
					"read_case" => $cnv["read_case"],
					"msg_date" => get_timeago(strtotime($cnv["msg_date"])),
					"display_place" => $display_place
				];
			}
			return $cnvs;
		}
		return false;
	}
}

if (!function_exists("un_rate")) {
	/**
	 * un_rate()
	 *
	 * @param int $post_id
	 * @param int $user_id
	 * @param int $rate_stars
	 * @return (array on success|boolean on failure)
	 */
	function un_rate($post_id, $user_id, $rate_stars)
	{

		if (empty($post_id) || empty($user_id) || empty($rate_stars)) {
			return false;
		}
		global $dsql;

		$check = $dsql->dsql()->table('rating_sys')->where('user_id', $user_id)->where('post_id', $post_id)->field('id')->getRow();
		if ($check) {
			$id = $check["id"];
			$delete_rate = $dsql->dsql()->table('rating_sys')->where('id', $id)->delete();
			if (!$delete_rate) {
				return false;
			}
		}

		$insert_rate = $dsql->dsql()->table('rating_sys')->set(['post_id' => $post_id, 'user_id' => $user_id, 'rate_stars' => $rate_stars])->insert();
		if ($insert_rate) {
			$post_author = get_post_field($post_id, "post_author");
			insert_notif($user_id, $post_author, $post_id, "post_rate");
			if ($rate_stars == 5) {
				$points_remaining = get_user_info($user_id)->points_remaining;
				$new_remaining_points = $points_remaining + distribute_points("rate5s", "add", $post_author);
				if ($new_remaining_points > $points_remaining) {
					update_user_meta($post_author, "points_remaining", $new_remaining_points);
				}
			}
			return get_rates($post_id);
		}
		return false;
	}
}

if (!function_exists("get_rates")) {
	/**
	 * get_rates()
	 *
	 * @param int $post_id
	 * @param int $user_id
	 * @return mixed (array on success|boolean on false)
	 */
	function get_rates($post_id, $user_id = null)
	{

		global $dsql;
		/** Get user rate stars for post */
		if ($user_id) {
			$get_user_rate = $dsql->dsql()->table('rating_sys')->where('post_id', $post_id)->where('user_id', $user_id)->limit(1)->getRow();
			if ($get_user_rate) {
				$get_user_rate = $get_user_rate["rate_stars"];
			} else {
				$get_user_rate = 0;
			}
		}

		$get_rates = $dsql->dsql()->table('rating_sys')->where('post_id', $post_id)->get();
		if (!$get_rates) {
			return false;
		}

		$rate_values = [];
		foreach ($get_rates as $rate_k => $rate_v) {
			$rate_values[] = (int) $rate_v["rate_stars"];
		}
		$rate_values = array_count_values($rate_values);
		$count_all_rates = array_sum($rate_values);
		$rate_stars = ["stars" => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]];

		foreach ($rate_values as $rate_value_k => $rate_value_v) {
			$star_rate_percent  = intval(($rate_value_v / $count_all_rates) * 100);
			$rate_stars["stars"][$rate_value_k] = ["rates" => $rate_value_v, "percent" => $star_rate_percent];
		}
		if (isset($get_user_rate)) {
			$rate_stars["user_rate"] = $get_user_rate;
		}
		return $rate_stars;
	}
}

if (!function_exists("paged")) {
	/**
	 * ...
	 *
	 * @param   int $per_page
	 *
	 * @return  string
	 */
	function paged($offset = null, $per_page = RESULT_PER_PAGE)
	{

		$p = (int) ($_GET["paged"] ?? 1);

		$end = $per_page;
		$start = ($p - 1) * $end;

		return $$offset;
	}
}

if (!function_exists('page_max')) {
	/**
	 * Count pagination max page
	 *
	 * @param int $count
	 * @return int
	 */
	function page_max(int $count, int $per_page = RESULT_PER_PAGE): int
	{
		return (int) ceil($count / paged('per_page', $per_page));
	}
}

if (!function_exists("image_sizes")) {
	/**
	 * image_sizes
	 * 
	 * @param int $size
	 * @return mixed (array on success|boolean on failure)
	 */
	function image_sizes($size = null)
	{

		$sizes = [
			"x_large" => ["w" => 1024, "h" => 1024],
			"large" => ["w" => 750, "h" => 750],
			"meduim" => ["w" => 640, "h" => 640],
			"small" => ["w" => 480, "h" => 480],
			"extra_small" => ["w" => 240, "h" => 240],
		];

		if (!empty($size)) {
			return $sizes[$size] ?? false;
		}
		return $sizes;
	}
}

if (!function_exists("verify_thumb")) {
	/**
	 * verify_thumb()
	 * Verify if user submit a valid thumbnail
	 *
	 *
	 * @param int $thumb_id
	 * @return int on success | boolean on failure
	 */
	function verify_thumb($thumb_id)
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		$thumb_id = absint($thumb_id);

		if (empty($thumb_id)) {
			return false;
		}

		global $dsql;

		$check = $dsql->dsql()->table('files');
		/* 
		 * get if file publish in untrusted post
		 * if file attached to a trusted post we don't need to check file uploader because file is public in media library can used by everyone
		 **/
		$check_thumb = $dsql->dsql()->table('posts')->where('post_thumbnail', $thumb_id)->limit(1)->getRow();
		if ($check_thumb) {
			$post_in = $check_thumb["post_in"];
			if ($post_in == "untrusted") {
				$check->where('file_uploader', $current_user->id);
			}
		}
		$check->field('count(*)', 'records');
		$check = $check->limit(1)->getRow();

		if ($check['records'] != 0) {
			return $thumb_id;
		}

		return false;
	}
}

if (!function_exists("points_manage")) {
	function points_manage($type, $action)
	{
		if (empty($type) || empty($action)) {
			return false;
		}

		$points = @unserialize(get_option("points"));
		return $points[$action][$type];
	}
}

if (!function_exists("distribute_points")) {
	function distribute_points($type, $action, $user_id = false)
	{
		if (empty($type) || empty($action)) {
			return 0;
		}

		if (!$user_id) {
			if (is_login_in()) {
				$user_id = get_current_user_info()->id;
			} else {
				return 0;
			}
		} elseif ($user_id == get_current_user_info()->id) {
			return 0;
		}

		$condition = (int) points_manage($type, "condition");

		if ($condition > 1) {

			$user_queue = (int) get_user_meta($user_id, "points_" . $action . "_queue_" . $type);

			if ($user_queue + 1 == $condition) {
				if (update_user_meta($user_id, "points_" . $action . "_queue_" . $type, 0)) {
					return (float) points_manage($type, $action);
				}
			} else {
				update_user_meta($user_id, "points_" . $action . "_queue_" . $type, $user_queue + 1);
			}
		} else {
			return (float) points_manage($type, $action);
		}

		return 0;
	}
}

if (!function_exists("get_taxonomies")) {
	/**
	 * Return taxonomies type
	 *
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_taxonomies()
	{

		$get_taxonomies = get_all_taxonomies('taxo_type');

		if ($get_taxonomies) {
			return array_column($get_taxonomies, 'taxo_type');
		}

		return false;
	}
}

if (!function_exists("get_external_links")) {
	/**
	 * get_external_links()
	 *
	 * @param int $link_id
	 * @return mixed
	 */
	function get_external_links($link_id = null)
	{

		global $dsql;

		$query = $dsql->dsql()->table('site_links');

		if (!empty($link_id)) {
			$query->where('id', $link_id);
		}

		$get_links = $query->get();

		if ($get_links) {
			if (!empty($link_id)) {
				return $get_links[0];
			}
			return $get_links;
		}

		return false;
	}
}

if (!function_exists("get_taxonomy")) {
	/**
	 * get_taxonomy()
	 *
	 * @param int $taxonomy_id
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_taxonomy($taxonomy, $fields = '')
	{

		global $dsql;

		$query = $dsql->dsql()->table('taxonomies');
		$query->where($query->orExpr()->where('id', $taxonomy)->where('taxo_type', $taxonomy));
		if ($fields) {
			$query->field($fields);
		}
		$results = $query->limit(1)->getRow();
		return !is_array($fields) && !empty($fields) ? $results[$fields] : $results;
	}
}

if (!function_exists("get_all_taxonomies")) {
	/**
	 * get_all_taxonomies()
	 *
	 * get taxonomies from taxonomies table
	 * @param	mixed	$fields
	 * @return array
	 */
	function get_all_taxonomies($fields = '')
	{

		global $dsql;

		$query = $dsql->dsql()->table('taxonomies');

		if (!empty($fields)) {
			$query->field($fields);
		}

		$results = $query->get();

		return $results;
	}
}

if (!function_exists("get_taxonomy_title")) {
	/**
	 * get_taxonomy_title()
	 * get name of taxonomy depending on taxonomy type
	 *
	 * @param string|array $taxonomy
	 * @return string
	 */
	function get_taxonomy_title($taxonomy, $lang = false)
	{

		$current_lang = is_string($lang) ? $lang : current_content_lang();

		if (is_array($taxonomy)) {
			if (may_object($taxonomy['taxo_title'])) {
				$taxo_title =  @json_decode($taxonomy['taxo_title']);
			}
			return $taxo_title->$current_lang ?? '';
		}

		$get_taxonomy_title = get_taxonomy($taxonomy, 'taxo_title');

		if (!$get_taxonomy_title) {
			return '';
		}

		$taxo_title = @json_decode($get_taxonomy_title);
		if ($taxo_title) {

			return $taxo_title->$current_lang ?? '';
		}

		return '';
	}
}

if (!function_exists("get_taxonomy_notice")) {
	/**
	 * get_taxonomy_notice()
	 * get notice of taxonomy depending on taxonomy type
	 *
	 * @param string $taxonomy_type
	 * @return string
	 */
	function get_taxonomy_notice($taxonomy_type)
	{

		$get_taxonomy = get_taxonomy($taxonomy_type, 'taxo_notice');
		if (!$get_taxonomy) {
			return '';
		}

		$taxo_title = @json_decode($get_taxonomy);
		if ($taxo_title) {
			$current_lang = current_lang();
			return $taxo_title->$current_lang ?? "";
		}

		return '';
	}
}

if (!function_exists("formatWithSuffix")) {
	/**
	 * formatWithSuffix()
	 * convert numbers
	 *
	 * @param double $num
	 * @return double
	 */
	function formatWithSuffix($num)
	{
		if ($num > 10000) {

			$x = round($num);
			$x_number_format = number_format($x);
			$x_array = explode(',', $x_number_format);
			$x_parts = array('k', 'm', 'b', 't');
			$x_count_parts = count($x_array) - 1;
			$x_display = $x;
			$x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
			$x_display .= $x_parts[$x_count_parts - 1];

			return $x_display;
		}

		return $num;
	}
}

if (!function_exists("un_lock_ad")) {
	/**
	 * un_lock_ad
	 *
	 */
	function un_lock_ad($ad_id, $default_status = false)
	{

		if (empty($ad_id)) {
			return false;
		}

		$ad_case = get_ad_field($ad_id, "ad_case");
		if (!$ad_case) {
			return false;
		}
		global $dsql;
		$case = 1;
		if ($ad_case == 1) {
			$case = 2;
		}

		/** Specific case this case is reserved for multi_action() function */
		if ($default_status) {
			if ($default_status == "publish") {
				$case = 1;
			} else {
				$case = 2;
			}
			$status = $default_status;
		}
		$update = $dsql->dsql()->table('ads')->set(["ad_case" => $case])->where('id', $ad_id)->update();
		if ($update) {
			return true;
		}
		return false;
	}
}



// [substr_str] Function : This function returns a part of a string
function substr_str($str, int $allow_carc, $dots = '...')
{
	$str = strip_tags($str);
	if (mb_strlen($str) > $allow_carc) {
		$substr = mb_substr($str, 0, $allow_carc, "utf-8");
		return $substr . $dots;
	} else {
		return preg_replace('/\s+/', ' ', $str);
	}
}


if (!function_exists("get_roles")) {
	/**
	 * get_roles()
	 *
	 * @param int $role_id
	 * @return array
	 */
	function get_roles($role_id = null, $fields = '')
	{

		global $dsql;

		$query = $dsql->dsql()->table('roles_permissions');

		if (!empty($role_id)) {
			$query->where('id', $role_id);
		}

		if (!empty($fields)) {
			$query->field($fields);
		}

		$get_roles = $query->get();
		if (!empty($role_id)) {
			if (!is_array($fields) && !empty($fields)) {
				return $get_roles[0][$fields];
			}
			return $get_roles[0];
		}
		return $get_roles;
	}
}

if (!function_exists("get_followers")) {
	/**
	 * get_followers()
	 *
	 * @param int $user_id
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_followers($user_id)
	{

		global $dsql;
		$count_followers = $get_followers = $dsql->dsql()->table('subscribe_sys', 'ss')->join('users.id', 'ss.subscriber')->where('ss.user_id', $user_id)->where('users.user_status', 'active');
		$get_followers = $get_followers->limit(paged('end'), paged('start'))->get();
		$count_followers = $count_followers->field('count(*)', 'rows')->getRow();
		if ($get_followers) {
			return ["results" => $get_followers, "rows" => $count_followers["rows"]];
		}
		return false;
	}
}


if (!function_exists("get_role_name")) {
	/**
	 * get_role_name()
	 *
	 * @param int $role_id
	 * @param string $lang
	 * @return string
	 */
	function get_role_name($role_id, $lang = false)
	{
		if (empty($role_id)) {
			return false;
		}
		if (!$lang) {
			$lang = current_lang();
		}
		$get_role_name = get_roles($role_id, "role_title");
		$role_name = @json_decode($get_role_name);

		return $role_name->$lang ?? $role_name->M_L;
	}
}

if (!function_exists("get_langs")) {
	/**
	 * get_langs()
	 *
	 * @param string $lang_code
	 * @param string $lang_visibility
	 * @return array|boolean
	 */
	function get_langs($lang_code = null, $lang_visibility = "on", $content_lang = false)
	{

		global $dsql;

		$query = $dsql->dsql()->table('languages');

		if ($lang_visibility) {
			$query->where('lang_visibility', $lang_visibility);
		}

		if (!empty($lang_code)) {
			$query->where('lang_code', $lang_code);
		}

		if ($content_lang && admin_authority()->advanced_settings != "on") {
			$query->where('content_lang', 'on');
		}

		$get_langs = $query->get();

		if ($get_langs) {
			if (!empty($lang_code)) {
				return $get_langs[0];
			}
			return $get_langs;
		}

		return false;
	}
}

if (!function_exists("get_badges")) {
	/**
	 * get_badges()
	 *
	 * @param int $badge_id
	 * @return array
	 */
	function get_badges($badge_id = null, $case = 1, $limit = null)
	{

		global $dsql;

		$query = $dsql->dsql()->table('badges');

		if (!empty($case)) {
			$query->where('badge_case', $case);
		}
		if (!empty($badge_id)) {
			$query->where('id', $badge_id);
		}

		if (!empty($limit)) {
			$query->limit($limit);
		}

		$get_badges = $query->get();
		if (!empty($badge_id)) {
			return $get_badges[0];
		}
		return $get_badges;
	}
}

if (!function_exists("get_countries")) {
	/**
	 * get_countries()
	 *
	 * @param string $country_code
	 * @return array
	 */
	function get_countries($country_code = null)
	{

		global $dsql;

		$query = $dsql->dsql()->table('countries');

		if (!empty($country_code)) {
			$query->where('country_code', $country_code);
		}

		$get_countries = $query->get();
		if (!empty($country_code)) {
			return $get_countries[0];
		}
		return $get_countries;
	}
}

if (!function_exists("get_authors")) {
	/**
	 *
	 */
	function get_authors($author_id = '', $limit = null)
	{

		global $dsql;

		$query = $dsql->dsql()->table('authors');
		if (!empty($author_id)) {
			return $query->where('id', $author_id)->getRow();
		}
		$query = $query->order('id', 'desc');
		if(!is_null($limit) && $limit > 0) {
			$query = $query->limit($limit);
		}
		return $query->get();
	}
}

if (!function_exists("get_translators")) {
	/**
	 *
	 */
	function get_translators($translator_id = '', $limit = null)
	{

		global $dsql;

		$query = $dsql->dsql()->table('translators');
		if (!empty($translator_id)) {
			return $query->where('id', $translator_id)->getRow();
		}
		$query = $query->order('id', 'desc');
		if(!is_null($limit) && $limit > 0) {
			$query = $query->limit($limit);
		}
		$get_translators = $query->get();

		return $get_translators;
	}
}

if (!function_exists("get_categories")) {
	/**
	 *
	 */
	function get_categories($cat_taxo = null, $cat_id = null, $lang = false)
	{

		global $dsql;

		$query = $dsql->dsql()->table('categories');

		if (!empty($cat_taxo)) {
			$query->where('cat_taxonomy', $cat_taxo);
		}
		if (!empty($cat_id)) {
			$query->where('id', $cat_id);
		}
		if ($lang) {
			$query->where('cat_lang', $lang);
		}

		$get_cats = $query->get();
		if (!empty($cat_id)) {
			return $get_cats[0];
		}

		return $get_cats;
	}
}

if (!function_exists("get_category_by_ids")) {
	/**
	 * get_category_by_ids()
	 * Return categories info
	 *
	 * @param array $ids
	 * @return mixed(array on success|boolean on false)
	 */
	function get_category_by_ids($ids)
	{

		if (!is_array($ids)) {
			$ids = [$ids];
		}

		global $dsql;
		$get_categories = $dsql->dsql()->table('categories')->where('id', $ids)->get();
		return $get_categories;
	}
}

if (!function_exists("get_pages")) {
	/**
	 * get_pages()
	 *
	 * @param int $page_id
	 * @param boolean $main_page (true => load only no translated pages)
	 * @return array
	 */
	function get_pages($page_id = null, $main_page = true, $lang = true)
	{

		global $dsql;

		$query = $dsql->dsql()->table('pages')->where('page_case', 'on');

		if ($lang) {
			$query->where('page_lang', current_lang());
		}

		if (!empty($page_id)) {
			$query->where($query->orExpr()->where('id', $page_id)->where('page_translate', $page_id));
		}

		$get_pages = $query->get();
		if (!empty($page_id)) {
			return $get_pages[0];
		}
		return $get_pages;
	}
}

if (!function_exists("insert_notif")) {
	/**
	 * insert_notif()
	 * insert user to push in user interface
	 *
	 * @param int $from
	 * @param int $to
	 * @param string $content
	 * @param string $type
	 * @param int $case
	 * @return boolean
	 */
	function insert_notif($from = 0, $to = '', $content = "", $type = '', $case = 1)
	{
		$current_user = get_current_user_info();
		if ($current_user === false || $current_user->id == $to) {
			return false;
		}

		$notifs = @json_decode(get_user_meta($to, "notifs_settings"));

		if ($notifs->comment != 'on' && $type == 'post_comment') {
			return false;
		}
		/*
		if($notifs->reply != 'on') {
		    return false;
		}
		*/

		if ($notifs->subscribe != 'on' && $type == "follow_user") {
			return false;
		}

		if ($notifs->reaction != 'on' && $type == "post_reaction") {
			return false;
		}

		if (@$notifs->rate != 'on' && $type == "post_rate") {
			return false;
		}

		/**
		 * 1 - If notif @param ($type != "site_management" || "group_alert"), we check if notification already inserted
		 * 2 - If notification already exist we update same notif case and re send it to user
		 */
		global $dsql;
		$date = gmdate("Y-m-d H:i:s");
		if ($type != "site_management" || $type != "group_alert") {
			$notif_exist = $dsql->dsql()->table('notifications_sys')->where('notif_from', $from)->where('notif_to', $to)->where('notif_content', $content)->where('notif_type', $type)->field('id')->limit(1)->getRow();
		} else {
			$notif_exist = false;
		}


		if ($notif_exist) {

			$notif_id = $notif_exist["id"];
			$update = $dsql->dsql()->table('notifications_sys')->set(["notif_case" => $case, "notif_date" => $date])->where('id', $notif_id)->update();

			return $update;
		} else {
			$insert = $dsql->dsql()->table('notifications_sys')->set(["notif_from" => $from, "notif_to" => $to, "notif_content" => $content, "notif_type" => $type, "notif_case" => $case, "notif_date" => $date])->insert();
			return $insert;
		}
	}
}

function order_by_btn($order_by = 'desc')
{
	$order_by = strtolower($order_by);
	$icon = 'fa-arrow-up';
	if ($order_by == 'asc') {
		$icon = 'fa-arrow-down';
	}
	ob_start();
	?>
	<a href="#" class="btn rounded-0 border order-by-button" data-order="<?php esc_html($order_by); ?>"><i class="fas <?php esc_html($icon); ?>"></i></a>
	<?php
	return ob_get_clean();
}

if (!function_exists("notifs_settings")) {
	/**
	 * notifs_settings()
	 * get user notif_settings
	 *
	 * @param int $user_id
	 * @return mixed (object on success|boolean on failure)
	 */
	function notifs_settings($user_id)
	{
		if (empty($user_id)) {
			return false;
		}
		$get_settings = get_user_meta($user_id, "notifs_settings");
		if ($get_settings) {
			$settings = json_decode($get_settings);
			return $settings;
		}
		return false;
	}
}

if (!function_exists("read_notif")) {
	/**
	 * @param int $notif_id
	 * @param string $notif_type
	 * @param int $notif_content
	 * @param int $notif_from
	 * @return string
	 */
	function read_notif($notif_id, $notif_type, $notif_content = null, $notif_from = null)
	{
		if (empty($notif_id)) {
			return false;
		}
		$notif_types = [
			"follow_user" => [
				"notif_content_id" => false,
				"notif_content" => _t("عمل لك متابعة"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var]",
			],

			"post_comment" => [
				"notif_content_id" => true,
				"notif_content" => _t("قام بالتعليق على"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] <a href='[var] notif_content_link [/var]' class='text-warning'>[var] notif_content_title [/var]</a>",
			],
			"publish_post" => [
				"notif_content_id" => true,
				"notif_content" => _t("تم نشر"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] <span class='text-warning'>[var] notif_content_title [/var]</span></a>",
			],
			"block_post" => [
				"notif_content_id" => true,
				"notif_content" => _t("تم قفل"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] <span class='text-warning'>[var] notif_content_title [/var]</span></a>",
			],
			"add_new_points" => [
				"notif_content_id" => false,
				"notif_content" => _t("تم إضافة نقاط إلى حسابك"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] </a>",
			],
			"add_new_badge" => [
				"notif_content_id" => false,
				"notif_content" => _t("حصلت على وسام جديد"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] </a>",
			],
			"add_new_role" => [
				"notif_content_id" => false,
				"notif_content" => _t("حصلت على رتبة جديدة"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] </a>",
			],

			"in_feautred_posts" => [
				"notif_content_id" => true,
				"notif_content" => _t("مبروك تم إضافة إلى المواضيع المميزة"),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] <span class='text-warning'>[var] notif_content_title [/var]</span></a>",
			],
			"group_alert" => [
				"notif_content_id" => false,
				"notif_content" => strip_tags($notif_content),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] </a>",

			],
			"site_management" => [
				"notif_content_id" => false,
				"notif_content" => strip_tags($notif_content),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] </a>",

			],
			"cv_badge_notif" => [
				"notif_content_id" => false,
				"notif_content" => strip_tags($notif_content),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] </a>",

			],
			"post_notice" => [
				"notif_content_id" => true,
				"notif_content" => _t("تم إضافة تنبيه ل "),
				"notif_from" => _t("الإدارة"),
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] <span class='text-warning'>[var] notif_content_title [/var]</span></a>",
			],
			"post_reaction" => [
				"notif_content_id" => true,
				"notif_content" => _t("رد فعل على"),
				"notif_from" => $notif_from,
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] <span class='text-warning'>[var] notif_content_title [/var]</span></a>",
			],
			"post_rate" => [
				"notif_content_id" => true,
				"notif_content" => _t("تقييم ل"),
				"notif_from" => $notif_from,
				"notif_format" => "<span class='font-weight-bold text-warning'>[var] notif_from [/var]</span> : [var] notif_content [/var] <span class='text-warning'>[var] notif_content_title [/var]</span></a>",
			]
		];

		if (isset($notif_types[$notif_type])) {
			$n = $notif_types[$notif_type];
			if ($n["notif_content_id"] !== false) {
				$n["notif_content_title"] = '<a href="' . get_post_link($notif_content) . '">' . get_post_field($notif_content, "post_title") . '</a>';
			}

			if (!empty($notif_from)) {
				$n["notif_from"] = '<a href="' . siteurl() . "/user/" . $notif_from . '">' . get_user_field($notif_from, "user_name") . "</a>";
			}

			preg_match_all("~\[var\](.*?)\[\/var\]~", $n["notif_format"], $m);

			$s = [];
			foreach ($m[1] as $key => $val) {
				$val = trim($val);
				$s[$val] = $n[$val];
			}
			return str_replace($m[0], $s, $n["notif_format"]) . "</br>";
		}
	}
}



if (!function_exists("get_files")) {
	/**
	 * get_files()
	 *
	 * @param string $file_type
	 * @param int $file_id
	 * @param int $file_cat
	 * @param string $order_by
	 * @param boolean $user_files
	 * @param mixed $mime_type
	 * @return array
	 */
	function get_files($args, $order_by = "DESC", $get = 'user_files', $mime_type = false, $order_column = 'files.id')
	{

		global $dsql;

		$id = $args['id'] ?? null;
		$file_uploader = $args['file_uploader'] ?? null;
		$file_key = $args['file_key'] ?? null;
		$file_type = $args['file_type'] ?? null;
		$mime_type = $args['mime_type'] ?? null;
		$file_cat = $args['file_cat'] ?? null;
		$limit = $args['limit'] ?? RESULT_PER_PAGE;
		$file_type__not = $args['file_type__not'] ?? null;

		$get_files = $dsql->dsql()->table('files');

		if ($id) {
			$get_files->where('files.id', $id);
		}
		if ($file_key) {
			$get_files->where('files.file_key', $file_key);
		}
		if ($file_type) {
			$get_files->where('files.file_type', $file_type);
		}
		if ($mime_type) {
			$get_files->where('files.mime_type', $mime_type);
		}
		if ($file_cat) {
			$get_files->where('files.file_category', $file_cat);
		}
		if ($get == 'my') {
			$get_files->where('files.file_uploader', $file_uploader);
		} elseif ($get == 'trusted') {
			$get_files->join('posts.post_thumbnail', 'files.id', 'inner');
			$get_files->where('posts.post_in', 'trusted')->where('posts.post_status', 'publish')->where('files.file_type', '!=', 'site_images');
		} elseif ($get == 'gallery') {
			$get_files->where('files.file_type', 'site_images');
		}
		if ($file_type__not) {
			$get_files->where('files.file_type', 'not in', $file_type__not);
		}

		$offset = paged('start', $limit);
		$order_column = "files.id";
		if ($order_by == 'rand') {
			$order_by = 'desc';
		}
		$get_files->order($order_column, $order_by);
		$get_files->field($get_files->expr('DISTINCT SQL_CALC_FOUND_ROWS files.*'));
		$get_files->limit($limit, $offset);
		$get_files = $get_files->get();
		return $get_files;
	}
}
if (!function_exists("get_trusted_files")) {
	/**
	 * get_trusted_files()
	 * get files uploaded in post_type = 'trusted'
	 *
	 * @param int $file_cat
	 * @return array
	 */
	function get_trusted_files($file_cat = null, $mime_type = false)
	{

		global $dsql;


		$get_posts_thumbnails = $dsql->dsql()->table('posts')->where('post_status', 'publish')->where('post_in', 'trusted')->field('post_thumbnail')->get();

		if ($get_posts_thumbnails) {

			$thumbs = array_column($get_posts_thumbnails, 'post_thumbnail');

			$get_thumbs = $dsql->dsql()->table('files')->where('file_type', 'post_attachment')->where('id', $thumbs);

			if ($file_cat) {
				$get_thumbs->where('file_cat', $file_cat);
			}

			if ($mime_type) {
				$get_thumbs->where('mime_type', $mime_type);
			}

			$get_thumbs->field($get_thumbs->expr('SQL_CALC_FOUND_ROWS files.*'));

			$results = $get_thumbs->limit(paged('end'), paged('start'))->get();
			return $results;
		}

		return false;
	}
}

if (!function_exists("tempnam_sfx")) {
	/**
	 * tempnam_sfx()
	 *
	 * @param string $path
	 * @param string $suffix
	 * @return string
	 */
	function tempnam_sfx($path, $suffix)
	{
		do {
			$file = $path . mt_rand() . $suffix;
			$fp = @fopen($file, 'x');
		} while (!$fp);
		fclose($fp);
		return $file;
	}
}

if (!function_exists("siteurl")) {
	/**
	 * siteurl()
	 *
	 * @return string
	 */
	function siteurl()
	{
		//$g = @unserialize( get_settings("site_general_settings") );
		return SITEURL;
	}
}

if (!function_exists("display_fbComments")) {
	/**
	 * display_fbComments()
	 *
	 * @return boolean
	 */
	function display_fbComments()
	{
		$g = @unserialize(get_settings("site_general_settings"));
		$fb_comments = $g["fb_comments"] ?? "off";
		if ($fb_comments == "on") {
			return true;
		}
		return false;
	}
}

if (!function_exists("upload_files")) {
	/**
	 * upload_files()
	 *
	 * @param array $files
	 * @param string $file_type
	 * @param array $allowed_ext
	 * @param string $upload_dir
	 * @param int $file_category (uncategorized)
	 * @param boolean $save
	 * @return array|bool
	 */
	function upload_files($files, $file_type, $allowed_ext = null, $upload_dir = UPLOAD_DIR, $file_category = 1, $save = true, $custom_folder = false)
	{
		$current_user = get_current_user_info();

		if (empty($files) || empty($file_type) || $current_user === false) {
			return false;
		}

		global $dsql;

		$alw_types = ["library_media", "post_attachment", "user_attachment", "admin_attachment", "book", "site_images", "audio"];

		if (!in_array($file_type, $alw_types)) {
			return false;
		}
		$general_settings = @unserialize(get_settings("site_general_settings"));

		if (is_null($allowed_ext)) {
			if ($file_type == 'library_media') {
				$allowed_ext = $general_settings["library_allowed_ext"] ?? null;
			} elseif ($file_type == 'book') {
				$allowed_ext = ['pdf'];
			} elseif ($file_type == 'audio') {
				$allowed_ext = ['mp3'];
			} else {
				$allowed_ext = $general_settings["site_allowed_ext"] ?? null;
			}
		}

		$ext_max_upload = $general_settings["ext_max_upload"] ?? null;
		$site_max_upload = $general_settings["site_max_upload"];
		$file_uploader = $current_user->id;

		foreach ($files as $file_key => $file_value) {

			$fileName = basename($files[$file_key]["name"]);
			if ((empty($fileName)) || $files[$file_key]["error"] != 0) {
				break;
				return false;
			}

			$file_size = convert_size($files[$file_key]["size"]);

			$extention = pathinfo($fileName, PATHINFO_EXTENSION);

			if (isset($ext_max_upload[$extention]) && $ext_max_upload[$extention] != 0) {
				$site_max_upload = $ext_max_upload[$extention];
			}

			if ($site_max_upload < $file_size) {
				return false;
			}

			if (!is_null($allowed_ext) && !in_array($extention, $allowed_ext)) {
				return false;
			}

			if ($custom_folder) {
				$folder_name = $custom_folder;
			} else {
				$folder_name = generateRandomString();
				if ($extention == "pdf") {
					$folder_name = "book/" . $folder_name;
				} elseif ($extention == "mp3") {
					$folder_name = "audio/" . $folder_name;
				}
			}

			mkdir($upload_dir . $folder_name);

			$upload_dir = $upload_dir . $folder_name . '/';

			$uploadfile = tempnam_sfx($upload_dir, "." . $extention);

			if (move_uploaded_file($files[$file_key]['tmp_name'], $uploadfile)) {
				if ($save) {
					$file_access_key = generateRandomString(32);
					$mime_type = esc_html__($files[$file_key]["type"]);

					$data = [
						"file_name" => basename($uploadfile),
						"file_original_name" => $fileName,
						"file_dir" => $folder_name,
						"file_key" => $file_access_key,
						"mime_type" => $mime_type,
						"file_upload_date" => gmdate("Y-m-d h:i:s"),
						"file_uploader" => $file_uploader,
						"file_type" => $file_type,
						"file_category" => $file_category,
					];

					$insert = $dsql->dsql()->table('files')->set($data)->insert();
					// $extention == "mp3";
					if ($insert) {
						$data["file_id"] = get_last_inserted_id();

						return $data;
					}
				} else {
					return true;
				}
			} else {
				return false;
			}
		}
	}
}

if (!function_exists("upload_ajax")) {
	/**
	 * upload_ajax()
	 *
	 * @return object
	 */
	function upload_ajax($image = [])
	{
		global $dsql;
		$response = [];
		if (count($image) > 0) {
			$file_type = $image["type"] ?? "";
			$file_category = (int) ($image["file_category"] ?? 0);
			$file_category = !empty($file_category) ? $file_category : 1;
			$size = $image["size"] ?? "md";
		} else {
			$file_type = $_POST["type"] ?? "";
			$file_category = (int) ($_POST["file_category"] ?? 0);
			$file_category = !empty($file_category) ? $file_category : 1;
			$size = $_POST["size"] ?? "md";
		}
		if ($file_type == 'audio') {
			$get = $dsql->dsql()
				->table('files_categories')
				->where('JSON_UNQUOTE(JSON_EXTRACT(category_title, "$.ar"))', 'LIKE', "%صوت%")
				->mode('select')
				->order('id', 'desc')
				->limit(1);
			$id = $get->getOne();
			if ($id > 0) {
				$file_category = $id;
			}
		}
		if (!get_files_categories($file_category)) {
			return false;
		}

		$handle_files = upload_files($_FILES, $file_type, null, UPLOAD_DIR, $file_category);
		if ($handle_files === false) {
			$response["success"] = false;
			return false;
		}



		$file_key =  $handle_files["file_key"];
		$file_id =  $handle_files["file_id"];
		$file_mimetype =  $handle_files["mime_type"];
		$file_original_name = $handle_files['file_original_name'];
		$file_dir = $handle_files['file_dir'];
		$file_name = $handle_files['file_name'];
		$response["success"] = true;
		if (strpos($file_mimetype, "image") !== false) {
			$file_url = get_thumb($file_id, $size);
		} else {
			$file_url = siteurl() . "/assets/images/icons/files_format/pdf.png";
		}

		$response["file_url"] = $file_url;
		$response["file_id"] = $file_id;
		$response["file_key"] = $file_key;
		$response['file_original_name'] = $file_original_name;
		$response['file_dir'] = $file_dir;
		$response['file_name'] = $file_name;
		echo json_encode($response);
	}
}

if (!function_exists("switch_message_display_user")) {
	/**
	 * switch_message_display_user()
	 *
	 * @param int $msg_from
	 * @param int $msg_to
	 * @return mixed(array on success|boolean on failure)
	 */
	function switch_message_display_user($msg_from, $msg_to)
	{
		$current_user = get_current_user_info();
		if (!$current_user || empty($msg_from) || empty($msg_to)) {
			return false;
		}
		$user_id = $current_user->id;
		$display_id = null;
		if ($msg_from == $user_id) {
			$display_id = $msg_to;
		} else {
			$display_id = $msg_from;
		}
		$display_name = !empty(get_user_field($display_id, "user_name")) ? get_user_field($display_id, "user_name") : $current_user->user_name;
		$display_picture = !empty(get_user_field($display_id, "user_picture"))  ? get_user_field($display_id, "user_picture") : get_option("default_male_picture");

		return [
			"user_id" => $display_id,
			"user_name" => $display_name,
			"user_picture" => $display_picture
		];
	}
}

if (!function_exists("get_box_messages")) {
	/**
	 * get_box_messages()
	 *
	 * Get message to print it in header box menu
	 */
	function get_box_messages($read_case = null, $limit = null)
	{

		$current_user = get_current_user_info();
		if ($current_user === false) {
			return false;
		}

		global $dsql;

		$get_last_message = $dsql->dsql()->table('conversations_sys');
		$get_last_message->where($get_last_message->orExpr()->where('msg_to', $current_user->id)->where('msg_from', $current_user->id))->field('MAX(id) as last_cnv')->group('msg_id');
		if ($read_case) {
			$get_last_message->where('read_case', $read_case);
		}
		$get_last_message = $get_last_message->get();
		if (!$get_last_message) {
			return false;
		}

		$last_ids = array_column($get_last_message, 'last_cnv');

		$get_messages = $dsql->dsql()->table('conversations_sys');
		$get_messages->where($get_messages->orExpr()->where('msg_to', $current_user->id)->where('msg_from', $current_user->id))->where('id', $last_ids)->order('msg_date', 'desc');

		if (!is_null($read_case)) {
			$get_messages->where('read_case', $read_case);
		}

		$get_messages->field('id,msg_id,msg_from,msg_to,msg,msg_date');
		if (!is_null($limit)) {
			$get_messages->limit($limit);
		}

		$get_messages = $get_messages->get();
		return $get_messages;
	}
}

if (!function_exists("get_category_settings")) {
	/**
	 * get_category_settings()
	 *
	 * @param string $taxonomy
	 * @param int $cat_id
	 * @return mixed (object on success|boolean on failure)
	 */
	function get_category_settings($cat_id)
	{

		global $dsql;

		$settings = [];

		$get_settings = $dsql->dsql()->table('categories')->where('id', $cat_id)->field('cat_desc,cat_settings,cat_keywords')->limit(1)->getRow();
		if ($get_settings) {
			$cat = $get_settings;
			$cat_settings = @unserialize($cat["cat_settings"]);

			$settings["option"]["visible"] = $cat_settings["visible"] ?? "yes";
			$settings["option"]["sort"] = $cat_settings["sort"] ?? "latest";

			$settings["keywords"] = $cat["cat_keywords"];
			$settings["desc"] = $cat["cat_desc"];
		}
		return (object) $settings;
	}
}

if (!function_exists("get_taxonomy_terms")) {
	/**
	 * get_taxonomy_terms()
	 *
	 * @param string $taxonomy
	 * @return mixed (object on success|boolean on failure)
	 */
	function get_taxonomy_terms($taxonomy)
	{
		global $dsql;
		$terms = new stdClass();
		$get_terms = $dsql->dsql()->table('taxonomies')->where('taxo_type', $taxonomy)->field('taxo_terms')->limit(1)->getRow();
		if ($get_terms) {
			$terms = @json_decode($get_terms["taxo_terms"]);
		}
		return $terms;
	}
}

if (!function_exists("get_taxonomy_settings")) {
	/**
	 * get_taxonomy_settings()
	 *
	 * @param string $taxonomy
	 * @return mixed (object on success|boolean on failure)
	 */
	function get_taxonomy_settings($taxonomy)
	{
		global $dsql;
		$settings = [];
		$get_terms = $dsql->dsql()->table('taxonomies')->where('taxo_type', $taxonomy)->field('taxo_settings')->limit(1)->getRow();
		if ($get_terms) {
			$settings = @unserialize($get_terms["taxo_settings"]);
		}
		return $settings;
	}
}

if (!function_exists("get_category_parents")) {
	/**
	 * get_category_parents()
	 *
	 * @param int|array $cat_id
	 * @return mixed (string on success | boolean on failure)
	 */
	function get_category_parents($cat_id)
	{

		if (!is_array($cat_id)) {
			$cat_id = [$cat_id];
		}

		global $dsql;

		$parent_ids = "";
		$select_parents = $dsql->dsql()->table('categories')->where('id', $cat_id)->field('id,cat_title,cat_parent')->get();
		if ($select_parents) {
			foreach ($select_parents as $cat) {
				if (!empty($cat["cat_parent"])) {
					$parent_ids .= $cat["cat_parent"] . ",";
					$parent_ids .= get_category_parents($cat["cat_parent"]);
				}
			}
		}

		return $parent_ids;
	}
}

if (!function_exists("get_category_childs")) {
	/**
	 * get_category_childs()
	 * get sub categories
	 *
	 * @param string $taxonomy
	 * @param int $cat_id
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_category_childs($taxonomy, $cat_id = null)
	{

		if (empty($taxonomy)) {
			return false;
		}

		global $dsql;

		$get_childs = $dsql->dsql()->table('categories')->where('cat_taxonomy', $taxonomy)->where('cat_lang', current_content_lang());

		if (!is_null($cat_id)) {
			$get_childs->where('cat_parent', $cat_id);
		}

		$get_childs->field('id,cat_title,cat_settings');

		$get_childs = $get_childs->get();

		return $get_childs;
	}
}

if (!function_exists("load_categories_structured")) {
	/**
	 * load_categories_structured()
	 * Get All categories in taxonomy
	 *
	 * @param string $taxo_type
	 * @return array on success | boolean false on failure 
	 */
	function load_categories_structured($taxo_type, $content_lang = false)
	{

		global $dsql;

		if (!$content_lang) {
			$content_lang = current_content_lang();
		}

		$query = $dsql->dsql()->table('categories')->where('cat_taxonomy', $taxo_type)->where('cat_lang', $content_lang)->field('id,cat_title,cat_parent,cat_settings')->get();
		if (!$query) {
			return false;
		}

		$reStruct = [];
		foreach ($query as $category) {
			$cat_settings = @unserialize($category["cat_settings"]);
			if (isset($cat_settings["visible"]) && $cat_settings["visible"] == "yes") {
				$reStruct[$category["id"]] = [
					"id" => $category["id"],
					"title" => $category["cat_title"],
					"parent" => $category["cat_parent"],
				];
			}
		}

		return $reStruct;
	}
}
if (!function_exists("multi_level_childs")) {
	/**
	 * multi_level_childs()
	 * make multi-dimensional array with n level
	 *
	 * @param array $d
	 * @param int $r
	 * @param string $pk
	 * @param string $k
	 * @param string $c
	 * @return mixed (array on success|null on failure)
	 */
	function multi_level_childs($d, $r = 0, $pk = 'parent', $k = 'id', $c = 'children')
	{
		if (!is_array($d)) {
			return;
		}
		$m = array();
		foreach ($d as $e) {
			isset($m[$e[$pk]]) ?: $m[$e[$pk]] = array();
			isset($m[$e[$k]]) ?: $m[$e[$k]] = array();
			$m[$e[$pk]][$e["id"]] = array_merge($e, array($c =>  &$m[$e[$k]]));
		}
		return $m[$r];
	}
}


if (!function_exists("makeNestedList")) {
	/**
	 * makeNestedList()
	 * Render categories to html tree list
	 *
	 * @param array $categories
	 * @param string $base_url
	 * @return string HTML markup
	 */
	function makeNestedList($categories, $base_url = "", $area = "")
	{
		if ($area == "tree_list") {
			$Output = '<ul>';
		} elseif ($area == "categories_dropdown") {
			$Output = '<ul class="m-0">';
		} elseif ($area == "select2") {
		}
		foreach ($categories as $category) {
			if (count($category["children"]) > 0 && $area == "tree_list") {
				$icon = '<span class="mr-1"><i class="fas fa-folder"></i></span>';
			} else {
				$icon = '';
			}
			if ($area == "tree_list" || $area == "categories_dropdown") {
				$Output .= '<li>';
				$Output .= $icon . '<a href="' . $base_url . '?category=' . $category["id"] . '" class="ml-1">' . $category["title"] . '</a>';
				if (count($category["children"]) > 0) {
					$Output .= makeNestedList($category["children"], $base_url, $area);
				}
				$Output .= '</li>';
			} elseif ($area == "select2") {
			}
		}
		if ($area == "tree_list" || $area == "categories_dropdown") {
			$Output .= '</ul>';
		} elseif ($area == "select2") {
		}
		return $Output;
	}
}
if (!function_exists("_t")) {
	/**
	 * _t()
	 * translate all text wraped inside _t()
	 *
	 * @param string $text
	 * @return string
	 */
	function _t($text, $lang = false)
	{
		if ($lang === false) {
			$current_lang = current_lang();
		} else {
			$current_lang = $lang;
		}

		putenv('LANG=' . $current_lang . '');
		setlocale(LC_ALL, $current_lang);
		bindtextdomain($current_lang, ROOT . '/locale/nocache');
		bindtextdomain($current_lang, ROOT . "/locale/");
		textdomain($current_lang);
		bind_textdomain_codeset($current_lang, 'UTF-8'); // Tell the system out MO files will return UTF8
		return gettext($text);
	}
}


if (!function_exists("get_ads")) {
	/**
	 * get_ads()
	 *
	 * @param string $ad_area
	 * @param int $ad_case
	 * @return mixed (array on success|boolean on failure)
	 */
	function get_ads($ad_area = "", $ad_case = 1)
	{
		global $dsql;

		$query = $dsql->dsql()->table('ads')->where('ad_case', $ad_case)->where('ad_lang', [current_content_lang(), "0"])->order('id', 'desc');

		if (!empty($ad_area)) {
			$query->where('ad_area', $ad_area);
		}

		$query = $query->get();

		if ($query) {
			foreach ($query as $key => $value) {
				if (((int) $value['ad_code']) > 0) {
					$value['ad_code'] = $query[$key]["ad_code"] = '<img src="' . get_thumb($value['ad_code'], null) . '" class="img-fluid"/>';
				}

				if (!empty($value['ad_link'])) {
					$query[$key]["ad_code"] = '<a href="' . siteurl() . "/ad_redirect.php?key=" . $value["ad_key"] . '">' . $value["ad_code"] . "</a>";
				}
			}
		}

		return $query;
	}
}

if (!function_exists("update_ad_views")) {
	/**
	 * update_ad_views
	 * 
	 * @param string $ad_key
	 */
	function update_ad_views($ad_key)
	{
		global $dsql;
		$get_ad = $dsql->dsql()->table('ads')->where('ad_key', $ad_key)->field('ad_views')->limit(1)->getRow();
		if ($get_ad) {
			$ad = $get_ad;
			$ad_views = (int) $ad["ad_views"];
			$ad_views = $ad_views + 1;
			$dsql->dsql()->table('ads')->set(["ad_views" => $ad_views])->where('ad_key', $ad_key)->update();
		}
	}
}

if (!function_exists("no_content")) {
	/**
	 * no_content()
	 *
	 * @param string $text
	 * @param string HTML markup $before
	 * @param string HTML markup $after
	 * @echo string
	 */
	function no_content($text = null, $before = '<p class="d-flex justify-content-center self-align-center h5">', $after = '</p>')
	{
		if (is_null($text)) {
			$text = _t("لا يوجد أي محتوى حاليا");
		}
		echo $before . $text . $after;
	}
}

if (!function_exists("validate_html")) {
	/**
	 * @param string $string
	 * @param array $tags
	 * @return string
	 */
	function validate_html($string, $tags = "<h1><h2><h3><h4><h5><h6><span><p><img><a><ul><li><dl><strong><italic><sup><div><blockquote><br>")
	{
		$string = strip_tags($string, $tags);
		return $string;
	}
}

if (!function_exists("validateDate")) {

	function validateDate($date, $format = 'Y-m-d')
	{
		$d = DateTime::createFromFormat($format, $date);
		// The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
		return $d && $d->format($format) === $date;
	}
}

if (!function_exists("convert_images")) {
	function convert_images($image_name, $image_folder)
	{
		foreach (image_sizes() as $folder_name => $size) {
			if (!file_exists(UPLOAD_DIR . $image_folder . "/" . $folder_name)) {
				mkdir(UPLOAD_DIR . $image_folder . "/" . $folder_name);
				WideImage::load(UPLOAD_DIR . $image_folder . "/" . $image_name)->resize($size["w"], $size["h"])->crop()->saveToFile(UPLOAD_DIR . $image_folder . "/" . $folder_name . "/" . $image_name);
			}
		}
	}
}

if (!function_exists("get_thumb")) {
	/**
	 * get_thumb()
	 * get image link
	 * 
	 * @param int $thumb_id
	 * @param mixed (array|null) $size
	 * @return mixed
	 */
	function get_thumb($thumb_id, $d = "sm", $crop = true, $post_id = false)
	{
		global $dsql;
		if (empty($thumb_id)) {
			if ($post_id) {
				return get_post_meta($post_id, "video_thumbnail");
			}
			return false;
		}
		$get_thumb = get_cache($thumb_id, 'files');

		if (!$get_thumb) {
			$get_thumb = $dsql->dsql()->table('files')->where('id', $thumb_id)->field('file_name,file_dir,file_key,mime_type,file_type')->limit(1)->getRow();
			add_cache($thumb_id, 'files', $get_thumb);
		}



		if ($get_thumb) {
			$thumb = $get_thumb;
			$file_key = $thumb["file_key"];
			$file_name = $thumb["file_name"];
			$file_dir = $thumb["file_dir"];
			$mime_type = $thumb["mime_type"];
			$file_type = $thumb["file_type"];
			$outfile = false;
			// svg file
			if (is_null($d) || strpos($mime_type, "svg+xml")) {
				$outfile = "uploads/" . $file_dir . "/" . $file_name;
			} else {
				if (is_string($d)) {
					$d = images_sizes($d);
				}
				$file_name_ext = explode(".", $file_name);
				$file_name = $file_name_ext[0];
				$file_ext = $file_name_ext[1];
				$outfile = UPLOAD_DIR . $file_dir . "/" . $file_name . "-" . $d["w"] . "-" . $d["h"] . "." . $file_ext;
				if (!file_exists($outfile) && in_array($file_ext, ["png", "jpg", "jpeg"])) {
					resize($file_name . "." . $file_ext, UPLOAD_DIR . $file_dir . "/", $d, $crop, $file_type);
				}
				$outfile = "uploads/" . $file_dir . "/" . $file_name . "-" . $d["w"] . "-" . $d["h"] . "." . $file_ext;
			}
		} else {
			return siteurl() . "/assets/images/no-image.svg";
		}
		return siteurl() . "/" . $outfile;
	}
}

if (!function_exists("get_file")) {
	/**
	 * get_file()
	 * 
	 * @param int $file_id
	 * @return mixed
	 */
	function get_file($file_id, $domain = true, $withount_prefix = false)
	{
		global $dsql;
		$get_file = $dsql->dsql()->table('files')->where('id', $file_id)->field('file_name,file_dir,file_key,mime_type')->limit(1)->getRow();

		if (!$get_file) {
			return false;
		}

		$file = $get_file;
		$file_key = $file["file_key"];
		$file_name = $file["file_name"];
		$file_dir = $file["file_dir"];
		$mime_type = $file["mime_type"];
		if ($domain) {
			return siteurl() . "/uploads/" . $file_dir . "/" . $file_name;
		} else {
			if ($withount_prefix) {
				return $file_dir . "/" . $file_name;
			} else {
				return "uploads/" . $file_dir . "/" . $file_name;
			}
			// return UPLOAD_DIR . $file_dir . "/" . $file_name;
		}
	}
}

if (!function_exists("get_file_key")) {
	/**
	 * get_file_key()
	 * 
	 * @param int $file_id
	 * @return mixed
	 */
	function get_file_key($file_id)
	{

		global $dsql;

		$file = $dsql->dsql()->table('files')->where('id', $file_id)->field('file_key')->getRow();
		if (!$file) {
			return "";
		}

		return $file["file_key"];
	}
}

if (!function_exists("get_author_in_post")) {
	/**
	 * get_author_in_post()
	 * get user name & picture to display it in post
	 *
	 * @param int $author_id
	 * @return HTML Markup
	 */
	function get_author_in_post($author_id)
	{

		if (empty($author_id)) {
			return false;
		}
		$user_name = get_user_field($author_id, "user_name");
		$user_picture = get_thumb(get_user_field($author_id, "user_picture"), ["w" => 80, "h" => 80]);
		return (object) ["user_name" => mb_substr($user_name, 0, 13), "user_picture" => $user_picture, "link" => siteurl() . "/user/" . $author_id];
	}
}

if (!function_exists("is_follower")) {
	/**
	 * is_follower()
	 *
	 * Check if current user / some user follow some user (check by id)
	 *
	 * @param int $user_id
	 * @param int $c
	 * @return boolean
	 */
	function is_follower($user_id, $c = null)
	{

		if (!is_null($c)) {
			$c_id = $c;
		} else {
			$current_user = get_current_user_info();
			if (!$current_user) {
				return false;
			}
			$c_id = $current_user->id;
		}

		if ($c_id == $user_id) {
			return -1;
		}

		global $dsql;

		$check = $dsql->dsql()->table('subscribe_sys')->where('user_id', $user_id)->where('subscriber', $c_id)->limit(1)->getRow();

		return $check;
	}
}

if (!function_exists("is_follower_h_c")) {
	/**
	 * is_follower_h_c()
	 * if is_follower() return false this function echo {not-followed} to use it in follow button class
	 *
	 * @param int $user_id
	 * @echo string (HTML class name)
	 */
	function is_follower_h_c($user_id)
	{
		if (is_follower($user_id) === false) {
			echo "not-followed";
		}
	}
}

if (!function_exists("is_post_publish_h_c")) {
	/**
	 * is_post_publish_h_c()
	 *
	 * @param int $post_id
	 * @echo string (HTML class name)
	 */
	function is_post_publish_h_c($post_id)
	{
		$post_status = get_post_field($post_id, "post_status");
		if ($post_status != "publish") {
			echo "post-locked btn-warning";
		} else {
			echo "btn-success";
		}
	}
}

if (!function_exists("bookmark_opt")) {
	/**
	 * bookmark_opt()
	 *
	 * @param int $post_id
	 * @return string on success | boolean on failure
	 */
	function bookmark_opt($post_id, $user_id = null, $custom_html = "")
	{

		if (!empty($custom_html)) {
			return $custom_html;
		}

		if (is_null($user_id)) {
			$current_user = get_current_user_info();
			if (!$current_user) {
				return '';
			}

			$user_id = $current_user->id;
		}

		$get_meta_bookmark = get_user_meta($user_id, 'post_bookmark_' . $post_id);

		$class_attr = '';
		if ($get_meta_bookmark) {
			$class_attr = 'post-bookmared';
		}

		$html = ' <button class="bookmark-post un_bookmark btn btn-transparent p-0 ' . $class_attr . '" data-id="' . $post_id . '" style="line-height:1;"></button>';
		return $html;
	}
}

if (!function_exists("is_subscribe_to_taxonomy")) {
	/**
	 * is_subscribe_to_taxonomy()
	 * check whether is a user subsribed in a taxonomy or not
	 * 
	 * @param string $taxonomy
	 * @param int $user_id
	 * @return boolean
	 */
	function is_subscribe_to_taxonomy($taxonomy, $user_id = null)
	{

		if (is_null($user_id)) {
			$get_current_user_info = get_current_user_info();
			if (!$get_current_user_info) {
				return false;
			}
			$user_id = $get_current_user_info->id;
		}

		$get_meta_subscribe = get_user_meta($user_id, 'taxonomy_subscribe__' . $taxonomy);
		if (empty($get_meta_subscribe) or $get_meta_subscribe == 'no') {
			return false;
		}
		return true;
	}
}



if (!function_exists("is_subscribe_to_taxonomy_html_attr")) {
	/**
	 * is_subscribe_to_taxonomy_html_attr()
	 * 
	 * @param string $taxonomy
	 * @return string (HTML Attribute)
	 */
	function is_subscribe_to_taxonomy_html_attr($taxonomy)
	{
		if (empty($taxonomy)) {
			return false;
		}
		if (is_subscribe_to_taxonomy($taxonomy)) {
			return (object) ["attr" => 'data-request="unsubscribe"', "btn_html" => '<i class="fas fa-times mr-2"></i>' . _t("إلغاء الإشتراك")];
		} else {
			return (object) ["attr" => 'data-request="subscribe"', "btn_html" => '<i class="fas fa-check mr-2"></i>' . _t("إشترك")];
		}
	}
}

if (!function_exists("profile_page_post_lyt")) {
	/**
	 * profile_page_post_lyt()
	 *
	 * @param array $posts
	 * @return mixed (HTML Markup on success|boolean on failure)
	 */
	function profile_page_post_lyt($posts)
	{
		if (empty($posts)) {
			return false;
		}
		ob_start();
		foreach ($posts as $post):
	?>
			<!-- post -->
			<div class="profile-user-post mb-3">
				<div class="profile-user-post-author d-flex">
					<img src="<?php echo get_author_in_post($post["post_author"])->user_picture; ?>" width="48" height="48" />
					<div class="ml-2">
						<a href="<?php echo siteurl() . "/user/" . $post["post_author"]; ?>" class="font-weight-bold"><?php esc_html(get_author_in_post($post["post_author"])->user_name); ?></a><br />
						<small><?php echo get_timeago(strtotime($post["post_date_gmt"])); ?></small>
					</div>
				</div>
				<a href="<?php echo get_post_link($post["id"]); ?>" class="h5 d-block my-2 modal-link-post" data-id="<?php esc_html($post["id"]); ?>"><?php esc_html($post["post_title"]); ?></a>
				<div class="profile-user-post-thumb position-relative">
					<div class="bg-dark h-100">
						<img src="<?php echo get_thumb($post["post_thumbnail"], ["w" => 480, "h" => 360], true, $post["id"]); ?>" class="w-100 h-100 object-fit mx-auto d-block" />
					</div>
					<div class="profile-post-abs position-absolute w-100 h-100 top-0 right-0 p-3">
						<div class="d-flex">
							<?php get_post_in_html($post["post_in"], "fa-2x"); ?>
							<div class="ml-auto">
								<?php echo bookmark_opt($post["id"]); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="d-flex bg-light p-1">
					<span class="font-weight-bold"><?php esc_html(get_taxonomy_title($post["post_type"])); ?></span>
					<div class="ml-auto">
						<ul class="nav">
							<li class="nav-item mr-2"><i class="fas fa-share mr-2"></i><?php esc_html($post["post_share"]); ?></li>
							<!-- <li class="nav-item"><i class="fas fa-eye mr-2"></i><?php esc_html($post["post_views"]); ?></li> -->
						</ul>
					</div>
				</div>
			</div><!-- / post -->
<?php
		endforeach;
		return ob_get_clean();
	}
}

if (!function_exists("get_the_menu")) {
	function get_the_menu($menu)
	{
		if (!is_array($menu)) {
			return false;
		}

		$list_arr = [];

		foreach ($menu as $link) {
			if (isset($link["category"])) {
				$list_arr[] = [
					"id" => $link["category"],
					"title" => get_category_field($link["category"], "cat_title"),
					"link" => siteurl() . "/posts/" . get_category_field($link["category"], "cat_taxonomy") . "?category=" . $link["category"],
					"type" => "category",
					"target" => ""
				];
			}
			if (isset($link["link"])) {
				$get_link = get_external_links($link["link"]);
				$link_title = $get_link["link_title"];
				$list_arr[] = [
					"id" => $link["link"],
					"title" => $link_title,
					"link" => $get_link["link_url"],
					"type" => "link",
					"target" => $get_link["link_target"]
				];
			}
			if (isset($link["page"])) {
				$get_link = get_pages($link["page"], true, false);
				$link_title = $get_link["page_title"];
				$list_arr[] = [
					"id" => $link["page"],
					"title" => $link_title,
					"link" => siteurl() . "/page/" . $get_link["id"] . "/" . $get_link["page_title"],
					"type" => "page",
					"target" => ""
				];
			}
		}
		return $list_arr;
	}
}

if (!function_exists("switch_blocs")) {
	/**
	 * switch_blocs() 
	 * display blocs settings
	 *
	 * @param string $bloc_page
	 * @param string $bloc_name
	 * @return mixed
	 */
	function switch_blocs($bloc_page, $bloc_name = "")
	{
		if (empty($bloc_page)) {
			return false;
		}
		global $dsql;
		$get_blocs = $dsql->dsql()->table("meta_settings")->where('meta_key', 'LIKE', 'bloc_settings_[' . $bloc_page . '_' . $bloc_name . '_%');

		if ($get_blocs === false) {
			return false;
		}

		$blocs = array();
		foreach ($get_blocs	as $meta_value) {
			$meta_bloc_name = $meta_value["meta_key"];
			preg_match_all("~\[(.*?)\]~", $meta_bloc_name, $blocs_name);
			$bloc_name = $blocs_name[1][0];

			$bloc_value =  unserialize($meta_value["meta_value"]);
			$bloc_display = $bloc_value["display"] ?? "off";
			$bloc_sortby = $bloc_value["sort_by"] ?? "";
			$post_show = $bloc_value["post_show"] ?? "";
			if (empty($post_show)) {
				$post_show = 10;
			}
			$bloc_value["post_show"] = $post_show;

			foreach ($bloc_value as $x => $y) {
				$blocs[$x . "_" . $bloc_name] = $y;
			}

			$blocs["order_by_" . $bloc_name] = $blocs["args_" . $bloc_name] = [];

			switch ($bloc_sortby) {
				case "latest":
					$blocs["order_by_" . $bloc_name] = ["post_date_gmt", "DESC"];
					break;

				case "random":
					$blocs["order_by_" . $bloc_name] = "rand()";
					break;

				case "special":
					$blocs["args_" . $bloc_name] = ["in_special" => 'on'];
					$blocs["order_by_" . $bloc_name] = ["post_date_gmt", "DESC"];
					break;
				default:
					$blocs["order_by_" . $bloc_name] = $bloc_sortby;
			}
		}
		return $blocs;
	}
}

function is_bot()
{
	$botlist = array(
		"Teoma",
		"alexa",
		"froogle",
		"Gigabot",
		"inktomi",
		"looksmart",
		"URL_Spider_SQL",
		"Firefly",
		"NationalDirectory",
		"Ask Jeeves",
		"TECNOSEEK",
		"InfoSeek",
		"WebFindBot",
		"girafabot",
		"crawler",
		"www.galaxy.com",
		"Googlebot",
		"Scooter",
		"Slurp",
		"msnbot",
		"appie",
		"FAST",
		"WebBug",
		"Spade",
		"ZyBorg",
		"rabaz",
		"Baiduspider",
		"Feedfetcher-Google",
		"TechnoratiSnoop",
		"Rankivabot",
		"Mediapartners-Google",
		"Sogou web spider",
		"WebAlta Crawler",
		"TweetmemeBot",
		"Butterfly",
		"Twitturls",
		"Me.dium",
		"Twiceler",
		"facebookexternalhit"
	);
	foreach ($botlist as $bot) {
		if (strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
			return true;	// Is a bot
	}
}

function is_mobile()
{
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		$is_mobile = false;
	} elseif (
		strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
	) {
		$is_mobile = true;
	} else {
		$is_mobile = false;
	}

	return $is_mobile;
}
