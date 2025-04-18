<?php
$get_langs = get_langs(null,"on",false);
?>
	<div class="dash-part-form">
		<form action="" method="POST">
			<div class="half-width">
				<div class="full-width input-label-noline">
					<select name="lang">
						<option value="">الكل</option>
						<?php foreach($get_langs as $lang): ?>
						<option value="<?php esc_html($lang["lang_code"]); ?>"><?php esc_html($lang["lang_name"]); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="clear"></div>
				<div class="full-width input-label-noline">
					<select name="gender">
						<option value="">الكل</option>
						<option value="male">ذكور</option>
						<option value="female">إناث</option>
					</select>
				</div>
				<div class="clear"></div>
				<div class="full-width input-label-noline">
					<textarea name="alert" class="tinymce-area"></textarea>
				</div>
			</div>
			<button id="submit_form" class="saveData">أرسل</button>
            <input type="hidden" name="method" value="group_alert"/>
		</form>
	
	</div>