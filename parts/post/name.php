<?php
$name_gender = get_post_meta($post_id,"name_gender");
$name_lang = get_post_meta($post_id,"name_lang");
?>
<div id="post-categoy-select2" class="form-group">
    <label for="post_category" class="font-weight-bold"><?php echo _t("فروع الأقسام"); ?></label>
    <select name="post_category[]" id="post_category" class="form-control" multiple>
		<?php foreach($cats as $category): ?>
		    <option value="<?php esc_html($category["main"]["id"]); ?>" <?php if(in_array($category["main"]["id"],$post_category)) { ?> selected="true" <?php } ?> >
		        <?php esc_html($category["main"]["cat_title"]); ?>
		    </option>
		    <?php foreach($category["sub"] as $cat_sub): ?>
		    <option value="<?php esc_html($cat_sub["id"]); ?>" <?php if(in_array($cat_sub["id"],$post_category)) { ?> selected="true" <?php } ?> >
		        - <?php esc_html($cat_sub["cat_title"]); ?>
		    </option>
		    <?php endforeach; ?>
		<?php endforeach; ?>
	</select>
    <div id="post-categoy-select2_error_txt" class="invalid-feedback d-block"></div>
</div>

<div class="form-group">
    <label for="post_title" class="font-weight-bold"><?php echo _t("الإسم أو الكلمة"); ?> </label>
    <input type="text" name="post_title" id="post_title" class="form-control rounded-0" value="<?php esc_html($post_title); ?>" />
    <div id="post_title_error_txt" class="invalid-feedback"></div>
</div>

<div class="form-group">
    <label class="font-weight-bold"><?php echo _t("إختر الجنس للإسم"); ?> </label>
	<div class="form-row">
		<div class="col-1">
			<div class="custom-control custom-radio">
				<input type="radio" class="custom-control-input" id="name_gender_male" name="post_meta[name_gender]" value="male" <?php checked_val($name_gender,"male"); ?>>
				<label class="custom-control-label" for="name_gender_male"><?php echo _t("ذكر"); ?></label>
			</div>
		</div>		
		<div class="col-1">
			<div class="custom-control custom-radio">
				<input type="radio" class="custom-control-input" id="name_gender_female" name="post_meta[name_gender]" value="female" <?php checked_val($name_gender,"female"); ?>>
				<label class="custom-control-label" for="name_gender_female"><?php echo _t("أنثى"); ?></label>
			</div>
		</div>		
		<div class="col-1">
			<div class="custom-control custom-radio">
				<input type="radio" class="custom-control-input" id="name_gender_both" name="post_meta[name_gender]" value="both" <?php checked_val($name_gender,"both"); ?>>
				<label class="custom-control-label" for="name_gender_both"><?php echo _t("كلاهما"); ?></label>
			</div>
		</div>
	</div>
    <div id="name_lang_error_txt" class="invalid-feedback"></div>
</div>
<div class="form-group" id="post_content">
    <label class="font-weight-bold"><?php echo _t("معنى الإسم أو الكلمة"); ?></label>
    <textarea class="tinymce-area" name="post_content"><?php echo validate_html($post_content_editor); ?></textarea>
</div>
<div id="post_content_error_txt" class="invalid-feedback"></div>