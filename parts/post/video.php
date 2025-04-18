<?php
$video_url = get_post_meta($post_id,"video_url");
$video_thumbnail = get_post_meta($post_id,"video_thumbnail");
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
    <label for="post_title" class="font-weight-bold"><?php echo _t("عنوان الموضوع"); ?> </label>
    <input type="text" name="post_title" id="post_title" class="form-control rounded-0" value="<?php esc_html($post_title); ?>" />
    <div id="post_title_error_txt" class="invalid-feedback"></div>
</div>
<div class="form-group post-add-video-url p-2">
    <label for="video_url" class="font-weight-bold"><?php echo _t("رابط الفيديو"); ?> </label>
    <input type="text" name="post_meta[video_url]" id="video_url" class="form-control rounded-0" value="<?php esc_html($video_url);  ?>" />
	<div id="video_url_error_txt" class="invalid-feedback"></div>
	<div class="supported-platforms mt-2">
		<ul class="list-unstyled d-flex mb-0">
			<li class="mr-3"><i class="fab fa-youtube"></i></li>
			<li class="mr-3"><i class="fab fa-vimeo-v"></i></li>
			<li class="mr-3"><i class="fab fa-vine"></i></li>
			<li class="mr-3"><i class="fab fa-facebook-f"></i></li>
			<li><i class="fab fa-instagram"></i></li>
		</ul>
	</div>
</div>
<?php post_thumbnail_html( 'main-post-thumb','post_thumbnail', 'post_thumbnail',$post_thumbnail,_t("إضافة صورة"),$post_id ); ?>
<div class="form-group" id="post_content">
    <label class="font-weight-bold"><?php echo _t("وصف الفيديو"); ?></label>
    <textarea class="tinymce-area" name="post_content"><?php echo validate_html($post_content_editor); ?></textarea>
</div>
<div id="post_content_error_txt" class="invalid-feedback"></div>
<!-- Suggest image -->
<div id="suggested-corner" class="position-fixed animated bounceInDown shadow">
    <div class="card" style="width: 18rem;">
        <img class="card-img-top suggested-corner-image" src="" alt="">
        <div class="card-body">
            <p class="card-text">
                <?php echo _t("هل تريد إستعمال هذه الصورة ؟"); ?>
            </p>
            <button class="btn btn-primary use-plat-image"><?php echo _t("نعم"); ?></button>
            <button class="btn btn-danger no-use-plat-image"><?php echo _t("لا شكرا"); ?></button>
        </div>
    </div>
</div><!-- /Suggest image -->
<input type="hidden" name="post_meta[video_thumbnail]" id="video_thumbnail" value="<?php esc_html($video_thumbnail); ?>"/>
<script>
$(function() {
	var thumbnail_url = "";
	$("#video_url").on("paste",function() {
	    var $this = $(this);
	    setTimeout(function() {
    		$.get("ajax/ajax-html.php",{request : "video-thumbnail",url : $this.val()},function(data,status) {
    			var d = JSON.parse(data);
    			if(status == "success" && typeof(d.thumbnail_url) != "undefined") {
    				thumbnail_url = d.thumbnail_url;
    				$(".suggested-corner-image").attr("src",thumbnail_url);
    				$("#suggested-corner").show();
    			}
    		});
	    });
	});
	
	$(".use-plat-image").click(function(e) {
		$("#video_thumbnail").val(thumbnail_url);
		$(".post-add-media-library .media-preview").css({ "background-image" : "url("+thumbnail_url+")" });
		$("#suggested-corner").hide();
		e.preventDefault();
	});
	
	$(".no-use-plat-image").click(function(e) {
		$(".suggested-corner-image").attr("src","");
		$("#suggested-corner").hide();		
		e.preventDefault();
	});
	
	
});
</script>