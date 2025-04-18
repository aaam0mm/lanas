<?php
$current_user = get_current_user_info();
?>
<!-- Modal -->
<div class="modal animated bounceInDown" id="langSettingsModal" tabindex="-1" role="dialog" aria-labelledby="langSettingsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _t("إعدادات اللغة"); ?></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<form method="POST" id="lang-form">
					<div class="form-group">
						<label for="site_lang"><?php echo _t("لغة العرض"); ?>&nbsp;<i class="fas fa-exclamation-circle" data-toggle="tooltip" title="<?php echo _t("يؤدي هذا إلى تغيير اللغة التي يتم فيها عرض الموقع بهذه اللغة.. ولن تتغير (لغة المحتوى) التي تظهر عندك"); ?>"></i></label>
						<select name="site_lang" class="form-control custom-select">
							<?php foreach(get_langs() as $lang): ?>
							<option value="<?php esc_html($lang["lang_code"]); ?>" <?php selected_val(current_lang(),$lang["lang_code"]); ?> ><?php esc_html($lang["lang_name"]); ?></option>
							<?php endforeach; ?>
						</select>
					</div>				
					<div class="form-group">
						<label for="content_lang"><?php echo _t("لغة المحتوى"); ?>&nbsp;<i class="fas fa-exclamation-circle" data-toggle="tooltip" title="<?php echo _t("يؤدي هذا إلى تغيير لغة المحتوى المقترح قراءتها من قبلك.. ولن تتغير (لغة العرض)التي يتم فيها عرض الموقع"); ?>"></i></label>
						<select name="content_lang" class="form-control custom-select">
							<?php foreach(get_langs(null,"on",true) as $lang): ?>
							<option value="<?php esc_html($lang["lang_code"]); ?>" <?php selected_val(current_content_lang(),$lang["lang_code"]); ?> ><?php esc_html($lang["lang_name"]); ?></option>
							<?php endforeach; ?>					
						</select>
					</div>
					<input type="hidden" name="method" value="lang_settings"/>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary save-lang-settings" data-media="" data-value=""><?php echo _t("حفظ"); ?></button>
			</div>
		</div>
	</div>
</div>