<?php
require_once 'init.php';
require_once 'parts/post/post-slices.php';
if (is_login_in() === false) {
	exit;
}

$action = $_GET["action"] ?? "";
$post_type = $_GET["post_type"] ?? "";
$post_in = $_GET["post_in"] ?? "";

if (!in_array($action, ["add", "edit"]) || !in_array($post_in, ["trusted", "untrusted"]) || !in_array($post_type, get_taxonomies())) {
	exit();
}

/** Declare all variables as empty to prevent error on add action */
$show_highlight = false;
$post_category = [];
$post_content_edit = $post_title = $post_thumbnail = $post_content = $post_keywords = $add_to_slide = $add_to_special = $disable_comments = $disable_copy = $source = $post_notice = "";
$query_save_post = new save_post(["post_in" => $post_in, "post_type" => $post_type]);
$content_lang = current_content_lang();
if ($action == "edit") {
	$post_id = $_GET["post_id"] ?? "";
	if (empty($post_id)) {
		exit();
	}

	if ($query_save_post->can_edit_post($post_id) === false) {
		exit();
	}
	$query_post = new Query_post(["post_id" => $post_id, "post_status" => '', "post_lang" => '', 'post_in' => '']);

	$post = $query_post->get_post();
	$post_category = $query_post->get_post_categories();
	$post_title = $post["post_title"];
	$post_thumbnail = $post["post_thumbnail"];
	$post_content = $post["post_content"];
	$content_lang = $post["post_lang"];
	
	/** */
	$post_content_edit = get_post_meta($post_id, "post_content_edit");
	$post_notice = get_post_meta($post_id, "notice");

	if (!empty($post["post_keywords"])) {
		$post_keywords = explode(",", $post["post_keywords"]);
	}
	$add_to_slide = $post["in_slide"];
	$reviewed = $post["reviewed"];
	$share_authority = $post["share_authority"];
	$add_to_special = $post["in_special"];
	$disable_comments = get_post_meta($post_id, "disable_comments");
	$disable_copy = get_post_meta($post_id, "disable_copy");
	$source = get_post_meta($post_id, "source");
} elseif ($action == "add") {
	$post_id = $query_save_post->reserve_post();
	if (empty($post_id)) {
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
$taxo_add_text =  @json_decode(get_taxonomy($post_type, 'taxo_add_text'))->$current_content_lang;
if ($post_content_edit && admin_authority()->posts == "on") {
	$show_highlight = true;
}

$post_content_editor = $post_content;

if ($post_content_edit) {
	$post_content_editor = $post_content_edit;
}

?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<!-- SWITCHERY CSS -->
	<link href="<?php echo siteurl(); ?>/assets/lib/switchery/dist/switchery.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/css/" rel="stylesheet" type="text/css/jquery-ui.css" />
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
		"newHTML" => "'" . validate_html(preg_replace('~>\s*\n\s*<~', '><', $post_content_edit ?? '')) . "'"
	];
	?>
	<div id="post-new">
		<div class="container my-5 mx-auto position-relative">
			<div class="position-absolute right-0 top-0 close-add-btn">
				<button class="btn btn-warning rounded-circle" data-toggle="tooltip" title="<?php echo _t("إغلاق الصفحة"); ?>" onclick="window.history.back();"><i class="fas fa-times"></i></button>
			</div>
			<div class="add-form">
				<?php if ($post_in == "trusted"): ?>
					<div class="alert alert-success mb-3 notice-area">
						<div class="alert-text"><?php echo $taxo_add_text; ?></div>
						<div class="notice-area-expand text-center" style="display:none">
							<i class="fas fa-angle-down fa-lg"></i>
						</div>
					</div>
				<?php endif; ?>
				<?php
				if ($action == "edit"):
				?>
					<div class="alert alert-danger mb-3">
						<?php
						switch ($post["post_status"]):
							case "pending":
								echo _t("المنشور  بإنتظار الموافقة");
								break;
							case "approval":
								echo _t("المنشور بإنتظار المراجعة");
								break;
						endswitch;
						?>
					</div>
				<?php endif; ?>

				<?php if ($action == "edit" && $post["post_status"] == "blocked"):  ?>
					<div class="alert alert-danger bg-white mb-3">
						<?php echo _t("المنشور مغلق حاليا"); ?>
					</div>
				<?php endif; ?>

				<form action="" method="POST" id="post_form" class="p-4">
					<div class="form-group">
						<sup class="text-danger">*</sup> (<?php echo _t("إجباري"); ?>)
					</div>
					<?php
					if (file_exists("parts/post/" . $post_type . ".php")):
						include "parts/post/post-thumbnail.php";
						include "parts/post/" . $post_type . ".php";
					endif;
					?>
					<div class="form-group">
						<?php if (!empty($show_highlight)): ?>
							<label class="font-weight-bold"><?php echo _t("تعديلات"); ?></label>
							<small><?php echo _t("الكتابة بالأحمر تم حدفها وتعويضها بالكتابة بالأخضر"); ?></small>
						<?php endif; ?>
						<div id="post_diff"></div>
					</div>

					<div class="form-group">
						<?php post_slices_html($post_type, $post_id); ?>
					</div>

					<div class="form-group">
						<label for="post_title" class="font-weight-bold"><?php echo _t("الكلمات المفتاحية"); ?></label>
						<select name="post_keywords[]" id="post_keywords" class="form-control rounded-0" multiple>
							<?php
							if (is_array($post_keywords)):
								foreach ($post_keywords as $keyword):
									echo '<option value="' . $keyword . '" selected="true">' . $keyword . '</option>';
								endforeach;
							endif;
							?>
						</select>
					</div>
					<?php if ($post_type != "research" && $post_type != "book"): ?>
						<div class="form-group">
							<label class="font-weight-bold"><?php echo _t("مصادر المنشور"); ?></label>
							<button class="add-source btn btn-primary rounded-0 btn-block mb-3"><?php echo _t("أضف مصدر"); ?></button>
							<div class="source-tmpl-append"></div>
						</div>
					<?php endif; ?>
					<?php if (admin_authority()->posts == "on" && $action == "edit"): ?>
						<div class="form-group form-row">
							<div class="col-sm-4">
								<button class="btn btn-primary btn-lg rounded-0 save-post only-edit"><?php echo _t("تعديل"); ?></button>
							</div>
							<div class="col-sm-2 ml-auto mt-3 mt-sm-0">
								<button class="btn btn-primary btn-block btn-lg rounded-0 save-post"><?php echo _t("نشر"); ?></button>
							</div>
						</div>
					<?php else: ?>
						<div class="form-group">
							<button class="btn btn-primary btn-lg rounded-0 save-post"><?php echo _t("نشر"); ?></button>
							<?php if ($action == "add"): ?>
								<button class="btn btn-primary btn-lg rounded-0 save-post add-to-draft"><?php echo _t("حفظ كمسودة"); ?></button>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if (admin_authority()->posts == "on"): ?>
						<?php
							if($post_type == 'book') {
								?>
								<div class="form-group">
									<!-- ON/OFF -->
									<label for="" class="d-block font-weight-bold"><?php echo _t("سحب حق النشر"); ?></label>
									<input type="checkbox" name="share_authority" class="js-switch" <?php checked_val($share_authority, "on"); ?> />
									<!-- ON/OFF -->
								</div>
								<?php
							}
						?>
						<div class="form-group">
							<!-- ON/OFF -->
							<label for="" class="d-block font-weight-bold"><?php echo _t("تمت المراجعة"); ?></label>
							<input type="checkbox" name="reviewed" class="js-switch" <?php checked_val($reviewed, "on"); ?> />
							<!-- ON/OFF -->
						</div>
						<div class="form-group">
							<!-- ON/OFF -->
							<label for="" class="d-block font-weight-bold"><?php echo _t("إضافة إلى سلايدر"); ?></label>
							<input type="checkbox" name="in_slide" class="js-switch" <?php checked_val($add_to_slide, "on"); ?> />
							<!-- ON/OFF -->
						</div>
						<div class="form-group">
							<!-- ON/OFF -->
							<label for="" class="d-block font-weight-bold"><?php echo _t("إضافة إلى مميز"); ?></label>
							<input type="checkbox" name="in_special" class="js-switch" <?php checked_val($add_to_special, "on"); ?> />
							<!-- ON/OFF -->
						</div>
						<div class="form-group">
							<!-- ON/OFF -->
							<label for="" class="d-block font-weight-bold"><?php echo _t("منع النسخ"); ?></label>
							<input type="checkbox" name="post_meta[disable_copy]" class="js-switch" <?php checked_val($disable_copy, "on"); ?> />
							<!-- ON/OFF -->
						</div>
					<?php endif; ?>
					<div class="form-group">
						<!-- ON/OFF -->
						<label for="" class="d-block font-weight-bold"><?php echo _t("تعطيل التعليقات"); ?></label>
						<input type="checkbox" name="post_meta[disable_comments]" class="js-switch" <?php checked_val($disable_comments, "on"); ?> />
						<!-- ON/OFF -->
					</div>
					<input type="hidden" name="post_type" value="<?php esc_html($post_type); ?>" />
					<input type="hidden" name="post_id" value="<?php esc_html($post_id); ?>" />
					<input type="hidden" name="post_in" value="<?php esc_html($post_in); ?>" />
					<input type="hidden" name="method" value="post_ajax" />
					<input type="hidden" name="request" value="save_post" />
					<input type="hidden" name="save_as" id="save_as" value="" />
				</form>
			</div>

		</div>
	</div>

	<script>
		let originalHTML = <?php echo $diff["originalHTML"]; ?>;

		// let uploadedFiles = [];

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
			<div class="form-row source-row form-group source-queue-{{ count }}">
				<div class="col-lg-6 col-sm-12">
					<input type="text" name="post_meta[source][{{ count }}][text]" value="{{ text }}" class="form-control rounded-0" placeholder="<?php echo _t("نص"); ?>" />
				</div>
				<div class="col-lg-6 col-sm-12 input-group">
					<input type="text" name="post_meta[source][{{ count }}][url]" value="{{ url }}" class="form-control rounded-0" placeholder="<?php echo _t("رابط المصدر"); ?>" />
					<button class="btn btn-warning rounded-circle btn-sm remove-slice ml-2" data-remove=".source-queue-{{ count }}"><i class="fas fa-times fa-sm"></i></button>
				</div>
			</div>
		{{/tmpl}}
	</script>

	<script>
		$(function() {

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
			
			$("#post_keywords").select2({
				tags: true,
				dir: "rtl",
				width: '100%',
				language: 'ar',
        placeholder: "<?php echo _t('الكلمات المفتاحية'); ?>",
        allowClear: true
			});

			$("#post_category").select2({
				// tags: true,
				dir: "rtl",
				width: '100%',
				language: 'ar',
        placeholder: "<?php echo _t('اختر قسما'); ?>",
				allowClear: true
			});

			$("#author").select2({
				tags: true,
				dir: "rtl",
				width: '100%',
				language: 'ar',
        placeholder: "<?php echo _t('اختر أو أضف مؤلفاً جديداً'); ?>",
				allowClear: true
			});

			$("#translator").select2({
				tags: true,
				dir: "rtl",
				width: '100%',
				language: 'ar',
        placeholder: "<?php echo _t('اختر أو أضف مترجما جديداً'); ?>",
				allowClear: true
			});

		});

		var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
		elems.forEach(function(html) {
			var switchery = new Switchery(html);
		});
	</script>
</body>

</html>