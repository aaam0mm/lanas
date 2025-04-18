<?php
/**
 * post-thumbnail.php
 * Add/Edit post post thumbnail input HTML markup
 *
 */
 
 $min_resolution_image = get_settings("min_resolution_".$post_type."_image");
 $minWidth_image_resolution = $minHeight_image_resolution = "";
 if($min_resolution_image) {
	$min_resolution_image = unserialize($min_resolution_image);
	$minWidth_image_resolution = $min_resolution_image["width"];
	$minHeight_image_resolution = $min_resolution_image["height"];
 }
 
 if(!function_exists("post_thumbnail_html")) {
	/**
	 * post_thumbnail_html()
	 * HTML markup input of add media from media library
	 *
	 * @param string $addMediaTo (always should be class (.))
	 * @param string $addValto (DOM element(s) always should be id (#))
	 * @param int    $thumb_id
	 * @param string $label
	 * @return string HTML markup
	 */
	function post_thumbnail_html($addMediaTo, $addValto, $input_name, $thumb_id = null, $label = '', $post_id = false, $image_id = '')  {
		global $minWidth_image_resolution,$minHeight_image_resolution, $post_type;
		$thumb_id_int = (int) $thumb_id;
		if(absint($thumb_id_int)) {
			$thumb = get_thumb($thumb_id_int,"lg");
		}else{
			$thumb_id_int = !empty($image_id) ? $image_id : 0;
			$thumb = $thumb_id;
		}
		$book_classes = $post_type == "book" ? "off-d-none d-none on-req" : "";
		?>
		<!-- Post thumbnail -->
		<div class="form-group <?= $book_classes ;?>">
			<label for="post_thumbnail" class="font-weight-bold"><?php echo _t($label); ?></label>
			<button id="post-add-media-library" class="form-control open-media-library p-2 overflow-hidden" data-media=".<?php echo $addMediaTo; ?>" data-value="#<?php echo $addValto; ?>">
				<div class="bg-white text-center position-relative media-preview <?php echo $addMediaTo; ?>" style="background-image:url(<?php echo $thumb; ?>);">
					<?php if($minHeight_image_resolution && $minWidth_image_resolution): ?>
					<span class="bg-danger text-white py-2 px-3 d-inline-block" data-toggle="tooltip" title="<?php echo _t("الحجم الأدنى لحجم الصورة "); ?>">
						<?php esc_html($minHeight_image_resolution."×".$minWidth_image_resolution); ?>
					</span>
					<div class="my-3"></div>
					<?php endif; ?>
					<div class="py-4 text-muted image-library-open-icon">
						<i class="fas fa-camera fa-3x"></i>
						<h5 class="mt-4">
							<?php echo _t("أنقر لإضافة صورة"); ?>
						</h5>
					</div>
				</div>
			</button>
			<div id="post-add-media-library_error_txt" class="invalid-feedback"></div>
			<input type="hidden" name="<?php echo $input_name; ?>" id="<?php esc_html($addValto); ?>" value="<?php esc_html($thumb_id_int); ?>" />
		</div><!-- Post thumbnail -->
		<?php
	}
	
 }
?>