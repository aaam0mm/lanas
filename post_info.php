<?php
require_once 'init.php';
require_once 'parts/post/post-info-slices.php';

if (is_login_in() === false) {
	exit;
}
$action = $_GET["action"] ?? "";
$post_type = $_GET["post_type"] ?? "";
$post_in = $_GET["post_in"] ?? "";

// dd($post_in);
if (!in_array($action, ["add", "edit"]) || !in_array($post_in, ["trusted", "untrusted"]) || !in_array($post_type, get_taxonomies())) {
	exit();
}
/** Declare all variables as empty to prevent error on add action */
$show_highlight = false;
$post_category = [];
$post_content_edit = $post_title = $post_thumbnail = $post_content = $post_keywords = $add_to_slide = $add_to_special = $disable_comments = $disable_copy = $source = $post_notice = "";
$query_save_post = new save_post_info(["post_in" => $post_in, "post_type" => $post_type]);
$content_lang = current_content_lang();

if ($action == "edit") {
	$info_id = $_GET["post_id"] ?? "";

	if (empty($info_id)) {
		exit();
	}

	// if ($query_save_post->can_edit_post_info($info_id) === false) {
	// 	exit();
	// }
	$query_post_info = new Query_post(false);
	$query_post_info->set_post_info_data(["info_id" => $info_id, "post_status" => '', "post_lang" => '', 'post_in' => '']);


	$info = $query_post_info->get_info();
	$info = $info[0] ?? $info;
	// dd($info);
	$info_category = $query_post_info->get_post_categories();

	// $info_title = $info["post_title"];
	// $info_thumbnail = $info["post_thumbnail"];
	// $info_content = $info["post_content"];
	$content_lang = $info["post_lang"];

	/** */
	// $info_content_edit = get_post_meta($info_id, "post_content_edit");
	// dd($info_content_edit);
	// $info_notice = get_post_meta($info_id, "notice");
	if (!empty($info["post_keywords"])) {
		$info_keywords = explode(",", $info["post_keywords"]);
	}
	// $add_to_slide = $info["in_slide"];
	// $add_to_special = $info["in_special"];
	// $disable_comments = get_post_meta($info_id, "disable_comments");
	// $disable_copy = get_post_meta($info_id, "disable_copy");
	// $source = get_post_meta($info_id, "source");
} elseif ($action == "add") {
	$info_id = $query_save_post->reserve_post();
	if (empty($info_id)) {
		echo error_page($query_save_post->get_errors());
		exit(0);
	}
}

$get_categories = get_categories($post_type, null, $content_lang);

$cats = [];

foreach ($get_categories as $category) {

	if (empty($category["cat_parent"])) {
		$cats[$category["id"]]["main"] =  $category;
	} else {
		$cats[$category["cat_parent"]]["sub"][] = $category;
	}
}
$current_lang = current_lang();
$current_content_lang = current_content_lang();
// $taxo_add_text =  @json_decode(get_taxonomy($post_type, 'taxo_add_text'))->$current_content_lang;

// if ($post_content_edit && admin_authority()->posts == "on") {
// 	$show_highlight = true;
// }

// $post_content_editor = $post_content;

// if ($post_content_edit) {
// 	$post_content_editor = $post_content_edit;
// }

?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<!-- SWITCHERY CSS -->
	<link href="<?php echo siteurl(); ?>/assets/lib/switchery/dist/switchery.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
</head>
<style>
	ins {
		background: lightgreen;
		text-decoration: none;
	}

	del {
		background: pink;
	}
</style>

<body>
	<?php include 'parts/media-uploader.php'; ?>
	<?php user_end_scripts(); ?>
	<script src="<?php echo siteurl(); ?>/assets/lib/tinymce/tinymce.min.js?apiKey=cddlpm4517sshn5nibdymuairnrhqi3hx7kyvcomimsfdc4m"></script>
	<script src="<?php echo siteurl(); ?>/assets/lib/select2/js/select2.min.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/lib/jqueryui/jquery-ui.min.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/lib/switchery/dist/switchery.min.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/lib/mustache/mustache.min.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/htmldiff.min.js"></script>
	<?php
	$diff = [
		"originalHTML" => "'" . validate_html(preg_replace('~>\s*\n\s*<~', '><', $post_content)) . "'",
		"newHTML" => "'" . validate_html(preg_replace('~>\s*\n\s*<~', '><', $post_content_edit)) . "'"
	];
	?>
	<div id="post-new">
		<div class="container my-5 mx-auto position-relative">
			<div class="position-absolute right-0 top-0 close-add-btn">
				<button class="btn btn-warning rounded-circle" data-toggle="tooltip" title="<?php echo _t("إغلاق الصفحة"); ?>" onclick="window.history.back();"><i class="fas fa-times"></i></button>
			</div>
			<div class="add-form">
				<?php if ($post_in == "trusted"): ?>
					<!--
					<div class="alert alert-success mb-3 notice-area">
						<div class="alert-text">
							<?php //echo $taxo_add_text; ?>
						</div>
						<div class="notice-area-expand text-center" style="display:none">
							<i class="fas fa-angle-down fa-lg"></i>
						</div>
					</div>
					-->
				<?php endif; ?>
				<?php
				if ($action == "edit"):
				?>
					<div class="alert alert-danger mb-3">
						<?php
						switch ($info["post_status"]):
							case "pending":
								echo _t("الرابط  بإنتظار الموافقة");
								break;
							case "approval":
								echo _t("الرابط بإنتظار المراجعة");
								break;
						endswitch;
						?>
					</div>
				<?php endif; ?>

				<?php if ($action == "edit" && $info["post_status"] == "blocked"):  ?>
					<div class="alert alert-danger bg-white mb-3">
						<?php echo _t("الرابط مغلق حاليا"); ?>
					</div>
				<?php endif; ?>


				<form action="editLink" method="post" class="pb-3 mb-3 pt-3">
					<div class="form-group m-2">
						<label for="post_lang">اختيار اللغة</label>
						<select class="form-control" name="post_lang" id="post_lang">
							<option selected="true" disabled="true">إختر اللغة</option>
							<?php
							foreach (get_langs() as $lang_k => $lang_v) {
								$lang_code = $lang_v["lang_code"];
								$lang_name = $lang_v["lang_name"];
								$selected_language = $info["post_lang"];
							?>
								<option value="<?php esc_html($lang_code); ?>" <?php selected_val($lang_code, $selected_language); ?>><?php echo $lang_name; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					<div class="form-group m-2">
						<label for="post_type_edit">اختيار الصنف</label>
						<select class="form-control" name="post_type_edit" id="post_type_edit">
							<option value="0" selected="true" disabled="true">إختر الصنف</option>
							<?php
							$types = [
								1 => 'article',
								2 => 'book',
							];
							$trans = [
								'article' => 'مقالات',
								'book' => 'كتب',
							];
							foreach ($types as $k => $v) {
							?>
								<option value="<?php esc_html($v); ?>" <?php selected_val($info['post_type'], $v); ?>><?php echo $trans[$v]; ?></option>
							<?php
							}
							?>
						</select>
					</div>

					<div class="form-group m-2">
						<label for="post_category">اختيار القسم</label>
						<select class="form-control" name="post_category" id="post_category">
							<option selected="true" disabled="true">إختر القسم</option>
							<?php
							foreach (get_categories() as $cat_k => $cat_v) {
								$cat_code = $cat_v["id"];
								$cat_name = $cat_v["cat_title"];
								$selected_catlang = $info["post_category"];
							?>
								<option value="<?php esc_html($cat_code); ?>" <?php selected_val($cat_code, $selected_catlang); ?>><?php echo $cat_name; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					<div id="post-categoy-select2" class="form-group m-2">
						<label for="post_author" class="font-weight-bold"><?php echo _t("اختيار حساب"); ?></label>
						<select class="form-control" name="post_author" id="post_author">
							<option selected="true" disabled="true">إختر حساب</option>
							<?php
							$users = get_users(null, "desc", ['limit'=>'all']);
							foreach ($users['results'] as $user_k => $user_v) {
								$user_code = $user_v["id"];
								$user_name = $user_v["user_name"];
								$user_lang = $info["post_author"];
							?>
								<option value="<?php esc_html($user_code); ?>" <?php selected_val($user_code, $user_lang); ?>><?php echo $user_name; ?></option>
							<?php
							}
							?>
						</select>
						<div id="post-categoy-select2_error_txt" class="invalid-feedback d-block"></div>
					</div>

					<div class="form-group m-2">
						<label for="post_fetch_url">الصق رابط الجلب هنا</label>
						<input type="text" class="form-control" name="post_fetch_url" id="post_fetch_url" placeholder="يدعم روابط الجلب (web scraping)" value="<?php @esc_html($info['post_fetch_url']); ?>" />
					</div>

					<div class="form-group m-2 categories-adv-s">
						<div class="form-group">
							<!-- ON/OFF -->
							<label for="" class="d-block font-weight-bold"><?php echo _t("نشر تلقائي"); ?></label>
							<input type="checkbox" name="post_status" class="js-switch" <?php checked_val($info['post_status'], "publish"); ?> />
							<!-- ON/OFF -->
						</div>
						<div data-action="toggle_show_article" class="form-group<?= $info['post_type'] == 'book' ? ' d-none' : ''; ?>">
							<!-- ON/OFF -->
							<label for="" class="d-block font-weight-bold"><?php echo _t("بدون الصورة"); ?></label>
							<input type="checkbox" name="post_show_pic" class="js-switch" <?php checked_val($info['post_show_pic'], "on"); ?> />
							<!-- ON/OFF -->
						</div>
						<div data-action="toggle_show_book" class="form-group<?= $info['post_type'] == 'article' ? ' d-none' : ''; ?>">
							<!-- ON/OFF -->
							<label for="" class="d-block font-weight-bold"><?php echo _t("كتاب للمراجعة فقط بدون pdf"); ?></label>
							<input type="checkbox" name="book_without_pdf" class="js-switch" <?php checked_val($info['book_without_pdf'], "on"); ?> />
							<!-- ON/OFF -->
						</div>
					</div>

					<div class="form-group m-2">
						<label for="number_fetch">عدد الجلب في اليوم</label>
						<input type="number" class="form-control" name="number_fetch" id="number_fetch" placeholder="-" value="<?php @esc_html($info['number_fetch']); ?>" />
					</div>

					<div class="form-group m-2">
						<label for="post_source_1">المصدر الأول</label>
						<input type="text" class="form-control" name="post_source_1" id="post_source_1" placeholder="اكتب المصدر الأول" value="<?php @esc_html($info['post_source_1']); ?>" />
					</div>

					<div class="form-group m-2">
						<label for="post_source_2">المصدر الثاني</label>
						<input type="text" class="form-control" name="post_source_2" id="post_source_2" placeholder="المصدر الثاني" value="<?php @esc_html($info['post_source_2']); ?>" />
					</div>

					<div class="form-group form-row p-2">
						<div class="col-sm-4">
							<button class="btn btn-primary btn-lg rounded-0 save-post only-edit"><?php echo _t("تعديل"); ?></button>
						</div>
					</div>

					<input type="hidden" name="post_type" value="<?php esc_html($post_type); ?>" />
					<input type="hidden" name="info_id" value="<?php esc_html($info_id); ?>" />
					<input type="hidden" name="post_in" value="<?php esc_html($post_in); ?>" />
					<input type="hidden" name="method" value="post_ajax" />
					<input type="hidden" name="request" value="save_post_info" />
					<input type="hidden" name="save_as" id="save_as" value="" />
				</form>

			</div>

		</div>
	</div>

	<script>
		let originalHTML = <?php echo $diff["originalHTML"]; ?>;

		let newHTML = <?php echo $diff["newHTML"]; ?>;
		// Diff HTML strings
		let output = htmldiff(originalHTML, newHTML);
		if (originalHTML && newHTML) {
			document.getElementById("post_diff").innerHTML = output;
		}

		$("iframe").load(function() {
			$(this).bind('cut copy paste', function(e) {
				e.preventDefault();
			});
		});
	</script>

	<script type="x-tmpl-mustache" id="tmpl-source">
		{{#tmpl}}
			<div class="form-row source-row form-group m-2 source-queue-{{ count }}">
				<div class="col-lg-6 col-sm-12">
					<input type="text" class="form-control" name="post_meta[source][{{ count }}][text]" value="{{ text }}" class="form-control rounded-0" placeholder="<?php echo _t("نص"); ?>" />
				</div>
				<div class="col-lg-6 col-sm-12 input-group">
					<input type="text" class="form-control" name="post_meta[source][{{ count }}][url]" value="{{ url }}" class="form-control rounded-0" placeholder="<?php echo _t("رابط المصدر"); ?>" />
					<button id="close_page" class="btn btn-warning rounded-circle btn-sm remove-slice ml-2" data-remove=".source-queue-{{ count }}"><i class="fas fa-times fa-sm"></i></button>
				</div>
			</div>
		{{/tmpl}}
	</script>
	<script>
		$(function() {

			$(`[action="editLink"] [name="post_type_edit"]`).unbind().on('change', function() {
				let form = $(this).parents('form');
				let value = $(this).val();
				if (value != 0) {
					if (value == 'book') {
						form.find(`[data-action="toggle_show_book"]`).removeClass('d-none');
						form.find(`[data-action="toggle_show_article"]`).addClass('d-none');
					} else {
						form.find(`[data-action="toggle_show_article"]`).removeClass('d-none');
						form.find(`[data-action="toggle_show_book"]`).addClass('d-none');
					}
				} else {
					form.find(`[data-action]`).addClass('d-none');
				}

			});

			var notice_h = $('.notice-area').height();
			if (notice_h > 350) {
				$('.notice-area .alert-text').css({
					height: "350px",
					overflow: "hidden"
				});
				$('.notice-area-expand').show();
			}

			$(document).on('click', '.notice-area-expand', function(e) {
				$(this).toggleClass('expanded');
				if ($(this).hasClass('expanded')) {
					$(this).children('i').removeClass('fa-angle-down').addClass('fa-angle-up');
					$('.notice-area .alert-text').css({
						height: "100%"
					});
				} else {
					$('.notice-area .alert-text').css({
						height: "350px"
					});
					$(this).children('i').removeClass('fa-angle-up').addClass('fa-angle-down');
				}
			});


			if (typeof(slices) != 'undefined') {
				$.each(slices, function(index, slice_type) {
					var template = $('#tmpl-slice-' + slice_type + '').html();
					Mustache.parse(template); // optional, speeds up future uses
					var rendered = Mustache.render(template, slice_data[index][slice_type]);
					$('#get_slices').prepend(rendered);
					if (slice_type == "quizz") {
						var quiz_answer_tmpl = $("#tmpl-quizz-answer").html();
						Mustache.parse(quiz_answer_tmpl);
						let l = 1;
						$.each(slice_data[index][slice_type]["tmpl"]["answer"], function(i, answer) {
							answer["answer_count"] = l;
							if (typeof(answer.text.is_true) != "undefined") {
								answer.attr_checked = 'checked';
							} else {
								answer.attr_checked = '';
							}
							slice_data[index][slice_type]["tmpl"]["answer"] = answer;
							var rendered_quiz_answer_tmpl = Mustache.render(quiz_answer_tmpl, slice_data[index][slice_type]);
							$(".quizz-questions.quizz-" + slice_data[index][slice_type]["tmpl"]["slice_count"] + "").append(rendered_quiz_answer_tmpl);
							l++;
						});
					} else if (slice_type == "vote") {
						var vote_option_tmpl = $("#tmpl-vote-option").html();
						Mustache.parse(vote_option_tmpl);

						$.each(slice_data[index][slice_type]["tmpl"]["options"], function(i, option) {
							slice_data[index][slice_type]["tmpl"]["options"] = option;
							var rendered_vote_option_tmpl = Mustache.render(vote_option_tmpl, slice_data[index][slice_type]);
							$(".vote-options-" + slice_data[index][slice_type]["tmpl"]["slice_count"] + "").append(rendered_vote_option_tmpl);
						});
					}
					$("#tinymce-" + slice_type + "-desc-" + slice_data[index][slice_type]["tmpl"]["slice_count"] + "").add_tinymce();
				});
			}
			$(".tinymce-area").add_tinymce();
			<?php if ($source): ?>
				var data_source_e = <?php echo $source; ?>;
				var template_sources = $('#tmpl-source').html();
				var data_source_render = {
					"tmpl": []
				};
				$.each(data_source_e, function(key, val) {
					val["count"] = key;
					data_source_render["tmpl"].push(val);
				});
				Mustache.parse(template_sources); // optional, speeds up future uses
				var rendered_sources = Mustache.render(template_sources, data_source_render);
				$(".source-tmpl-append").append(rendered_sources);
			<?php endif; ?>

			$(".add-source").on("click", function(e) {
				var source_count = $(".source-row").length + 1;
				var source_data = {
					"tmpl": [{
						count: source_count,
						text: "",
						url: ""
					}]
				};
				var template = $('#tmpl-source').html();
				Mustache.parse(template); // optional, speeds up future uses
				var rendered = Mustache.render(template, source_data);
				$(".source-tmpl-append").append(rendered);
				e.preventDefault();
			});

			$(".add-research").on("click", function(e) {
				var research_count = $(".research-page").length + 1;
				var research_data = {
					"tmpl": [{
						"title": "",
						"count": research_count
					}, ]
				};
				var template = $('#tmpl-research').html();
				Mustache.parse(template); // optional, speeds up future uses
				var rendered = Mustache.render(template, research_data);
				$(rendered).insertBefore(".add-research");
				// Apply tinymce Editor to textarea elements
				tinyMCE.execCommand('mceAddEditor', true, "tinymce-research-" + research_count + "");
				e.preventDefault();
			});

			$(".add-slice").on("click", function(e) {
				var t = $(this);
				var slice_type = t.data("type");
				var slice_title = t.data("title");
				var slice_count = $(".slice-type-" + slice_type + "").length + 1;
				var answer_count = $(".quizz-" + slice_count + " > .quiz-answer").length + 1;
				var slice_data = {
					"tmpl": [{
						"slice_type": slice_type,
						"slice_title": slice_title,
						"slice_count": slice_count
					}, ]
				};
				var quizz_data = {
					"tmpl": [{
						"slice_type": slice_type,
						"slice_title": slice_title,
						"slice_count": slice_count,

						"answer": [{
							text: "",
							is_true: "",
							answer_count: answer_count,
						}]
					}]
				};
				var template = $('#tmpl-slice-' + slice_type + '').html();
				Mustache.parse(template); // optional, speeds up future uses
				var rendered = Mustache.render(template, slice_data);
				$('#get_slices').prepend(rendered);
				if (slice_type == "quizz") {
					var quiz_answer_tmpl = $("#tmpl-quizz-answer").html();
					Mustache.parse(quiz_answer_tmpl);
					var rendered_quiz_answer_tmpl = Mustache.render(quiz_answer_tmpl, quizz_data);
					$(".quizz-questions.quizz-" + slice_count + "").append(rendered_quiz_answer_tmpl);

				}
				// Apply tinymce Editor to textarea elements
				tinyMCE.execCommand('mceAddEditor', true, "tinymce-" + slice_type + "-desc-" + slice_count + "");
				e.preventDefault();
			});

			$(document).on("click", ".add-question-answer", function(e) {
				var t = $(this);
				var data_quiz = t.data("quiz");
				var answer_in_quiz = $(".quizz-" + data_quiz + " > .quiz-answer").length;
				var quizz_data = {
					"tmpl": [{
						slice_type: "quizz",
						slice_count: data_quiz,
						answer_count: answer_in_quiz + 1,
						"answer": [{
							text: "",
							is_true: ""
						}]
					}]
				};
				var quiz_answer_tmpl = $("#tmpl-quizz-answer").html();
				Mustache.parse(quiz_answer_tmpl);
				var rendered_quiz_answer_tmpl = Mustache.render(quiz_answer_tmpl, quizz_data);
				$(".quizz-questions.quizz-" + data_quiz + "").append(rendered_quiz_answer_tmpl);
				e.preventDefault();
			});

			$(document).on("click", ".add-vote", function(e) {
				var t = $(this);
				var data_slice = t.data("slice");
				var template = $('#tmpl-vote-option').html();
				Mustache.parse(template); // optional, speeds up future uses
				var rendered = Mustache.render(
					template, {
						"tmpl": [{
							"options": [{
								slice_count: data_slice,
								slice_type: "vote",
								text: ""
							}]
						}]
					}
				);
				$(".vote-options-" + data_slice + "").append(rendered);
				e.preventDefault();
			});

			$(document).on("click", ".remove-slice,.remove-research-page", function(e) {
				$($(this).data("remove")).remove();
				e.preventDefault();
			});

			$(document).on("click", ".remove-vote-option", function(e) {
				$(this).parent("div").remove();
				e.preventDefault();
			});

			$(document).on("click", ".remove-quizz-answer", function(e) {
				$(this).closest(".quiz-answer").remove();
			});

			$(".save-post").click(function(e) {
				var btn = $(this);
				if (btn.hasClass('add-to-draft')) {
					$("#save_as").val("draft");
				} else if (btn.hasClass('only-edit')) {
					$("#save_as").val("edit");
				} else {
					$("#save_as").val("");
				}

				$(".invalid-feedback").text("");
				tinymce.triggerSave();
				btn.attr({
					"disabled": true,
					"data-loading": true
				});
				$(this).ajax_req(function(r) {
					if (r.success == false) {
						if (typeof(r.inputs_errors) == "object") {
							$(r.inputs_errors).each(function(i, v) {
								$(v.selector).addClass("is-invalid");
								$(v.selector + "_error_txt").html(v.error);
							});
						}
						btn.attr({
							"disabled": false,
							"data-loading": false
						});
					} else if (r.success == true) {
						swal({
								title: r.msg,
								icon: "success",
								button: gbj.ok_text
							})
							.then((value) => {
								window.location.href = r.post_link;
							});
					}
				});
				e.preventDefault();
			});
			$("#post_category, #post_keywords, #post_author").select2({
				tags: true,
				dir: "rtl",
				width: '100%',
				language: 'ar'
			});
		});

		var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
		elems.forEach(function(html) {
			var switchery = new Switchery(html);
		});
	</script>
</body>

</html>