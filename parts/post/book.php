<?php
$books_ids = @unserialize(get_post_meta($post_id, "books_ids"));
$audios_ids = @unserialize(get_post_meta($post_id, "audios_ids"));
$book_author = get_post_meta($post_id, "book_author");
$book_translator = get_post_meta($post_id, "book_translator");
$book_published_year = get_post_meta($post_id, "book_published_year");
$book_pages = get_post_meta($post_id, "book_pages") ?? 0;
$books_links = @unserialize(get_post_meta($post_id, "books_links"));
$post_lang = get_post_field($post_id, 'post_lang');
?>
<input type="file" class="position-absolute top-0" id="upload_book" accept="application/pdf">
<input type="hidden" id="upload_thumbnail_from_book" name="post_meta[upload_thumbnail_from_book]">

<style>
	#post-categoy-select2 .select2-container--default .select2-selection--multiple .select2-selection__clear {
		display: none !important;
	}
</style>
<div class="form-group form-row">
	<div class="col-md-6 col-12">
		<div id="post-categoy-select2" class="form-group">
			<label for="post_category" class="font-weight-bold"><?php echo _t("فروع الأقسام"); ?></label>
			<select name="post_category[]" id="post_category" class="form-control" multiple>
				<?php foreach ($cats as $category): ?>
					<option value="<?php esc_html($category["main"]["id"]); ?>" <?php if (in_array($category["main"]["id"], $post_category)) { ?> selected="true" <?php } ?>>
						<?php esc_html($category["main"]["cat_title"]); ?>
					</option>
					<?php foreach ($category["sub"] as $cat_sub): ?>
						<option value="<?php esc_html($cat_sub["id"]); ?>" <?php if (in_array($cat_sub["id"], $post_category)) { ?> selected="true" <?php } ?>>
							- <?php esc_html($cat_sub["cat_title"]); ?>
						</option>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</select>
			<div id="post-categoy-select2_error_txt" class="invalid-feedback d-block"></div>
		</div>
	</div>
	<div class="col-md-6 col-12">
		<div id="post_lang_parent" class="form-group">
			<label for="lang" class="font-weight-bold"><?php echo _t("اللغة"); ?></label>
			<select name="post_lang" id="post_lang" class="form-control rounded-0">
					<option value="0" selected="true" disabled="true">إختر اللغة</option>
					<?php
					foreach (get_langs() as $lang_k => $lang_v) {
							$lang_code = $lang_v["lang_code"];
							$lang_name = $lang_v["lang_name"];
							$selected_language = $lang_v["lang_code"];
					?>
							<option <?= $post_lang == $lang_code ? 'selected="true"' : (current_lang() == $lang_code ? 'selected="true"' : '');?> value="<?php esc_html($lang_code); ?>"><?php echo $lang_name; ?></option>
					<?php
					}
					?>
			</select>
			<div id="post-lang-select2_error_txt" class="invalid-feedback d-block"></div>
		</div>
	</div>
</div>


<div class="form-group">
	<label for="post_title" class="font-weight-bold"><?php echo _t("عنوان الكتاب"); ?><sup class="text-danger">*</sup></label>
	<input type="text" name="post_title" id="post_title" class="form-control rounded-0" value="<?php esc_html($post_title); ?>" />
	<div id="post_title_error_txt" class="invalid-feedback"></div>
</div>

<div class="form-group form-row">
	<div class="col-md-6 col-12">
		<label for="is_book_author" class="font-weight-bold"><?php echo _t("هل انت مؤلف هذا الكتاب؟"); ?><sup class="text-danger"> * </sup></label>
		<?php get_post_meta($post_id, "is_book_author"); ?>
		<select name="post_meta[is_book_author]" id="is_book_author" class="form-control rounded-0">
			<option value="0" selected="true"><?php echo _t("اختر ..."); ?></option>
			<option <?php echo get_post_meta($post_id, "is_book_author") == get_current_user_info()->id ? 'selected="true"' : ''; ?> data-name="<?= get_current_user_info()->user_name ;?>" value="<?= get_current_user_info()->id ;?>"><?php echo _t("أنا مؤلف هذا الكتاب"); ?></option>
			<option <?php echo get_post_meta($post_id, "is_book_author") == 'no' ? 'selected="true"' : ''; ?> value="no"><?php echo _t("لست مؤلف هذا الكتاب فقط انشر الكتاب"); ?></option>
		</select>
		<div id="is_book_author_error_txt" class="invalid-feedback"></div>
	</div>
	<div class="col-md-6 col-12">
		<label for="is_book_translator" class="font-weight-bold"><?php echo _t("هل انت مترجم هذا الكتاب؟"); ?></label>
		<select name="post_meta[is_book_translator]" id="is_book_translator" class="form-control rounded-0">
			<option value="0" selected="true"><?php echo _t("اختر ..."); ?></option>
			<option <?php echo get_post_meta($post_id, "is_book_translator") == get_current_user_info()->id ? 'selected="true"' : ''; ?> value="<?= get_current_user_info()->id ;?>"><?php echo _t("أنا مترجم هذا الكتاب"); ?></option>
			<option <?php echo get_post_meta($post_id, "is_book_translator") == 'new' ? 'selected="true"' : ''; ?> value="new"><?php echo _t("تحديد المترجم"); ?></option>
			<option <?php echo get_post_meta($post_id, "is_book_translator") == 'no' || !get_post_meta($post_id, "is_book_translator") ? 'selected="true"' : ''; ?> value="no"><?php echo _t("الكتاب ليس مترجم"); ?></option>
		</select>
		<div id="is_book_translator_error_txt" class="invalid-feedback"></div>
	</div>
</div>

<?php
$get_authors = get_authors('', 10);
usort($get_authors, function($a, $b) {
	return strcmp($a['name'], $b['name']);
});
$get_translators = get_translators('', 10);
usort($get_translators, function($a, $b) {
	return strcmp($a['name'], $b['name']);
});
$old_book_author = get_post_meta($post_id, 'book_author', true);
$old_book_author_id = get_post_meta($post_id, 'book_author_id', true);
$old_book_translator = get_post_meta($post_id, 'book_translator', true);
$old_book_translator_id = get_post_meta($post_id, 'book_translator_id', true) ?? $old_book_translator;
?>
<script>
    const oldBookAuthor = <?php echo json_encode($old_book_author); ?>;
    const old_book_author_id = <?php echo json_encode($old_book_author_id); ?>;
		const oldBookTranslator = <?php echo json_encode($old_book_translator); ?>;
    const old_book_translator_id = <?php echo json_encode($old_book_translator_id); ?>;
</script>

<div class="form-group form-row">
	<div class="col-md-6 col-12">
		<div id="book-author-form-data" class="form-group d-none">
			<label for="book_author" class="font-weight-bold"><?php echo _t("أكتب اسم المؤلف او اختره"); ?></label>
			<input 
				type="text" 
				id="book_author" 
				class="form-control rounded-0" 
			/>
			<input type="hidden" name="post_meta[book_author]" value="0">
			<div 
				id="author-list" 
				style="max-height: 250px; overflow-y:auto" 
				class="list-group d-none">
				<?php
				foreach ($get_authors as $author) {
					$authorSelected = '';
						// echo get_post_meta($post_id, 'book_author') == $author["name"];
					$authorSelected = $author["name"] == get_current_user_info()->user_name ? 'data-selected="true"' : (get_post_meta($post_id, 'book_author') == $author["name"] ? 'selected="true"' : '');
						echo '<button '. $authorSelected .' data-value="'. $author['id'] .'" type="button" class="list-group-item list-group-item-action">'
						. htmlspecialchars($author['name']) 
						. '</button>';
				}
				if(empty($authorSelected)) {
					echo '<button '. $authorSelected .' data-value="'. get_current_user_info()->user_name .'" data-uname="'. get_current_user_info()->user_name .'" type="button" class="list-group-item list-group-item-action">'
						. htmlspecialchars(get_current_user_info()->user_name) 
						. '</button>';
				}
				?>
			</div>
		</div>
	</div>
	<div class="col-md-6 col-12">
		<div id="book-translator-form-data" class="form-group d-none">
			<label for="book_translator" class="font-weight-bold"><?php echo _t("أكتب اسم المترجم او اختره"); ?></label>
			<input 
				type="text"
				id="book_translator" 
				class="form-control rounded-0" 
			/>
			<input type="hidden" name="post_meta[book_translator]" value="0">
			<div 
				id="translator-list" 
				style="max-height: 250px; overflow-y:auto" 
				class="list-group d-none">
				<?php
				foreach ($get_translators as $translator) {
					$translatorSelected = '';
						// echo get_post_meta($post_id, 'book_translator') == $translator["name"];
					$translatorSelected = $translator["name"] == get_current_user_info()->user_name ? 'data-selected="true"' : (get_post_meta($post_id, 'book_translator') == $translator["name"] ? 'selected="true"' : '');
						echo '<button '. $translatorSelected .' data-value="'. $translator['id'] .'" type="button" class="list-group-item list-group-item-action">'
						. htmlspecialchars($translator['name']) 
						. '</button>';
				}
				if(empty($translatorSelected)) {
					echo '<button '. $translatorSelected .' data-value="'. get_current_user_info()->user_name .'" data-uname="'. get_current_user_info()->user_name .'" type="button" class="list-group-item list-group-item-action">'
						. htmlspecialchars(get_current_user_info()->user_name) 
						. '</button>';
				}
				?>
			</div>
		</div>
	</div>
</div>
<div class="form-group form-check">
	<input <?php echo !is_null(get_post_meta($post_id, "is_for_read")) && get_post_meta($post_id, "is_for_read") == 'on' ? 'checked="true"' : ''; ?> type="checkbox" class="form-check-input" id="is_for_read" name="post_meta[is_for_read]">
	<label class="form-check-label" for="is_for_read"><?php echo _t("الكتاب للمراجعة فقط وليس للتحميل"); ?></label>
</div>

<div class="form-group off-d-none d-none on-req">
	<label for="book_pages" class="font-weight-bold"><?php echo _t("عدد الصفحات"); ?></label>
	<input type="text" data-name="post_meta[book_pages]" name="post_meta[book_pages]" id="book_pages" value="<?php esc_html($book_pages); ?>" class="form-control rounded-0" />
</div>
<div class="form-group off-d-none d-none on-req">
	<label for="book_published_year" class="font-weight-bold"><?php echo _t("سنة النشر"); ?></label>
	<input type="text" name="post_meta[book_published_year]" value="<?php esc_html($book_published_year); ?>" id="book_published_year" class="form-control rounded-0" />
</div>
<div class="form-group off-d-none d-none on-req">
	<label for="book_published_name" class="font-weight-bold"><?php echo _t("اسم دار النشر"); ?></label>
	<input type="text" name="post_meta[book_published_name]" value="<?= get_post_meta($post_id, "book_published_name") ?? '' ;?>" id="book_published_name" class="form-control rounded-0" />
</div>
<div class="form-group on-d-none on-req">
	<label for="books_ids" class="font-weight-bold mb-0"><?php echo _t("تحميل رابط الكتاب"); ?><sup class="text-danger">*</sup></label>
	<small class="form-text text-muted mb-3"><?php echo _t("يمكنك(ي) إضافة عدة أجزاء من الكتاب"); ?></small>
	<button id="books_ids" class="btn btn-primary form-control rounded-0 border-0 upload-book" data-media="file"><i class="fas fa-cloud mr-2"></i><?php echo _t("رفع كتاب"); ?></button>
	<div class="progress progress-book mt-3">
		<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
	</div>
	<div id="books_ids_error_txt" class="invalid-feedback"></div>
</div>
<?php if (user_authority()->upload_links == 'on'): ?>
	<div class="form-group text-center on-d-none on-req">
		<h5><?php echo _t('أو'); ?></h5>
	</div>
	<div class="form-group on-d-none on-req">
		<textarea class="form-control" name="post_meta[books_links]"><?php echo is_array($books_links) ? implode(PHP_EOL, $books_links) : ''; ?></textarea>
	</div>
<?php endif; ?>
<div class="form-group book-to-upload on-d-none on-req">
	<ul class="list-group media-file-show list-group-flush">
		<?php
		if (is_array($books_ids)) : foreach ($books_ids as $book_id):
				$get_file_info = get_files(['id' => $book_id]);
		?>
				<li class="file-area list-group-item d-flex align-items-center">
					<i class="fas fa-file mr-2"></i>
					<span><?php esc_html($get_file_info[0]["file_original_name"]); ?></span>
					<span class="ml-auto remove-file" data-toggle="tooltip"><i class="fas fa-times"></i></span>
					<input name="post_meta[books_ids][]" value="<?php esc_html($book_id); ?>" type="hidden">
				</li>
		<?php endforeach;
		endif; ?>
	</ul>
</div>

<?php post_thumbnail_html('main-post-thumb', 'post_thumbnail', 'post_thumbnail', $post_thumbnail, _t("إضافة صورة"), $post_id); ?>
<div class="form-group on-req" id="post_content">
	<label class="font-weight-bold"><?php echo _t("وصف الكتاب"); ?></label>
	<textarea class="tinymce-area" name="post_content"><?php echo validate_html($post_content_editor); ?></textarea>
</div>
<div id="post_content_error_txt" class="invalid-feedback"></div>

<div class="form-group my-4">
	<label class="font-weight-bold"><?php echo _t("اضافة الصوت"); ?></label>
	<div class="accordion" id="accordionExample">
		<div class="card bg-transparent border-0">
			<div class="card-header p-0" id="headingOne">
				<h2 class="mb-0">
					<button style="z-index: 3;position:relative;" class="btn btn-danger btn-sm btn-block text-center p-2" type="button" data-toggle="collapse" data-target="#collapseAudio" aria-expanded="true" aria-controls="collapseAudio">
						<i class="fas fa-caret-down fa-lg"></i>
					</button>
				</h2>
			</div>

			<div id="collapseAudio" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
				<div class="card-body px-0">
					<div class="form-group">
						<input type="text" name="post_meta[book_audio_reader]" id="book_audio_reader" class="form-control rounded-0" placeholder="<?php echo _t("القارئ"); ?>" value="<?= get_post_meta($post_id, "book_audio_reader") ?? '' ;?>" />
					</div>
					<div class="form-group">
						<input type="text" name="post_meta[book_audio_maker]" id="book_audio_maker" class="form-control rounded-0" placeholder="<?php echo _t("مونتاج"); ?>" value="<?= get_post_meta($post_id, "book_audio_maker") ?? '' ;?>" />
					</div>
					<div class="form-group">
						<input type="text" name="post_meta[book_audio_source]" id="book_audio_source" class="form-control rounded-0" placeholder="<?php echo _t("المصدر"); ?>" value="<?= get_post_meta($post_id, "book_audio_source") ?? '' ;?>" />
					</div>
					<div class="form-group">
						<button class="add-audio btn btn-primary rounded-0 btn-block mb-3"><?php echo _t("دمج صوت خلفية مع الصوت"); ?></button>
						<div class="audio-tmpl-append"></div>
						<input data-id="upload_audio" style="opacity: 0;z-index:1;" type="file" class="position-absolute top-0" id="upload_audio" accept="audio/*">
					</div>
					<div class="form-group audio-to-upload">
						<ul class="list-group media-audio-file-show list-group-flush">
							<?php
							if (is_array($audios_ids)) : foreach ($audios_ids as $audio):
									$audio_array = json_decode($audio, true);
									// $get_file_info = get_files(['id' => $audio_array['file_id']]);
									if(get_file($audio_array['file_id'], false, true)) {
										?>
										<li class="file-area list-group-item d-flex align-items-center">
											<i class="fas fa-file mr-2"></i>
											<span><?php esc_html($audio_array['track_name']); ?></span>
											<span class="ml-auto remove-audio-file" data-toggle="tooltip"><i class="fas fa-times"></i></span>
											<input name="post_meta[audios_ids][]" value="<?php esc_html(json_encode($audio_array)); ?>" type="hidden">
										</li>
										<?php
									}
								endforeach;
							endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- book reader scripts -->
<script src="<?php echo siteurl(); ?>/assets/js/pdf.worker.min.js"></script>
<script src="<?php echo siteurl(); ?>/assets/js/pdf.min.js"></script>
<!-- book reader scripts -->
<script type="x-tmpl-mustache" id="tmpl-audio">
		{{#tmpl}}
			<div style="background-color: #c6c6c6;" class="form-row audio-row form-group w-100 m-auto audio-queue-{{ count }} p-3 position-relative">
				<div class="col-sm-12 mb-3 d-flex">
					<input type="text" name="audio_name" value="{{ text }}" class="form-control rounded-0" placeholder="<?php echo _t("عنوان التراك"); ?>" />
					<button class="btn btn-warning rounded-circle btn-sm remove-slice ml-2" data-remove=".audio-queue-{{ count }}"><i class="fas fa-times fa-sm"></i></button>
				</div>
				<div class="col-sm-12 input-group flex-wrap">
					<button id="audios_ids" class="btn btn-primary form-control rounded-0 border-0 upload-audio text-left" data-media="file"><i class="fas fa-upload mr-2"></i><?php echo _t("ارفع ملف الصوت"); ?></button>
					<div class="progress progress-audio mt-3 col-12 p-0">
						<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
					</div>
					<div id="audios_ids_error_txt" class="invalid-feedback"></div>
				</div>
			</div>
		{{/tmpl}}
	</script>

	<script>
		$(".add-audio").on("click", function(e) {
			var audio_count = $(".audio-row").length + 1;
			var audio_data = {
				"tmpl": [{
					count: audio_count,
					text: "",
					url: ""
				}]
			};
			var template = $('#tmpl-audio').html();
			Mustache.parse(template); // optional, speeds up future uses
			var rendered = Mustache.render(template, audio_data);
			if($(".audio-row").length === 0) {
				$(".audio-tmpl-append").append(rendered);
			}
			e.preventDefault();

			let file_name = null;

			$(".upload-audio").unbind().click(function (e) {
					e.preventDefault();
					file_name = $(`[name="audio_name"]`).val();
					if (file_name === '') {
							swal({
									title: 'ادخل اسم الصوت',
									icon: "warning",
									button: 'حسنا'
							});
					} else {
							$("#upload_audio").click();
					}
			});

			// let uploadedFiles = [];

			$('#upload_audio').unbind().on('change', function (e) {
					var $t = $(this);

					// Process the file(s) using your upload_input function
					$t.upload_input(
							function (r) {
									if (r.success === true) {
											$(".progress").hide();

											// Append new file data to the list
											$(".media-audio-file-show").append(
													'<li class="file-area list-group-item d-flex align-items-center justify-content-between">' +
													'<i class="fas fa-file mr-2"></i>' +
														// '<span>' + r.file_original_name + '</span>' +
													'<span>' + file_name + '</span>' +
													'<span class="ml-auto remove-audio-file" data-toggle="tooltip"><i class="fas fa-times"></i></span>' +
													'<input name="post_meta[audios_ids][]" value=\'' + JSON.stringify({'track_name': file_name, 'file_id': r.file_id}) + '\' type="hidden" /></li>'
											);

											$(".media-audio-file-show").sortable({
													update: function(event, ui) {
															// When the order changes, log the new order
															var sortedIDs = $(this).sortable("toArray", {attribute: 'data-id'});
													}
											});

											$(`[name="audio_name"]`).val('');
											file_name = null;
									}
							},
							$(".progress-audio"),
							"audio"
					);
			});

		});
	</script>