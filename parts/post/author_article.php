<?php
    $author_article_name = get_post_meta($post_id,"author_article_name");
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
    <label for="post_title" class="font-weight-bold"><?php echo _t("عنوان الموضوع"); ?><sup class="text-danger">*</sup></label>
    <input type="text" name="post_title" id="post_title" class="form-control rounded-0" value="<?php esc_html($post_title); ?>" />
    <div id="post_title_error_txt" class="invalid-feedback"></div>
</div>
<div class="form-group">
    <label for="author_article_name" class="font-weight-bold"><?php echo _t("كاتب(ة)ا"); ?> </label>
    <input type="text" id="author_article_name" name="post_meta[author_article_name]"  class="form-control rounded-0" value="<?php esc_html($author_article_name);  ?>" />
    <div id="author_article_name_error_txt" class="invalid-feedback"></div>
</div>
<?php post_thumbnail_html( 'main-post-thumb','post_thumbnail', 'post_thumbnail',$post_thumbnail,_t("إضافة صورة"),$post_id ); ?>
<div class="form-group" id="post_content">
    <label class="font-weight-bold"><?php echo _t("محتوى الموضوع"); ?><sup class="text-danger">*</sup></label>
    <textarea class="tinymce-area" name="post_content"><?php echo validate_html($post_content_editor); ?></textarea>
</div>
<div id="post_content_error_txt" class="invalid-feedback"></div>