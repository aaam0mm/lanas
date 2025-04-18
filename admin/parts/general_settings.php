	<?php
	$general_settings = @unserialize(get_settings("site_general_settings"));
	$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	?>
	<div class="dash-part-form">
		<div class="half-width">
			<form action="" method="POST" enctype="multipart/form-data" id="form_data">
              	<div class="full-width input-label-noline">
					<label for="timezone">موقع التوقيت</label>
					<select name="general_settings[timezone]">
                      <?php
                      	  foreach($tzlist as $list) {
							$selected = $general_settings["timezone"] == $list ? 'selected="true"' : '';
                            ?>
                      			<option <?= $selected ;?> value="<?= $list ;?>"><?= $list ;?></option>
                      		<?php
						  }
                      ?>
                  	</select>
				</div>
				<div class="full-width input-label-noline">
					<label for="max_upload">رابط الموقع</label>
					<input type="text" name="general_settings[siteurl]" value="<?php echo $general_settings["siteurl"]; ?>" />
				</div>
				<div class="full-width input-label-noline">
					<label for="max_upload">أقصى حجم للرفع</label>
					<div class="notices not-before">
						<p>الحجم يجب أن يكون بميغابايات (mega) كل 1024 ميغا تكافئ 1 جيجا</p>
					</div>
					<div class="clear"></div>
					<input type="number" name="general_settings[site_max_upload]" value="<?php echo $general_settings["site_max_upload"]; ?>" />
				</div>
				<div class="full-width input-label-noline">
					<label for="max_upload">حجم الرفع حسب نوع الملف</label>
					<div class="notices not-before">
						<p>الحجم يجب أن يكون بميغابايات (mega) كل 1024 ميغا تكافئ 1 جيجا</p>
					</div>
					<div class="clear"></div>
					<select id="select_type_ext">
						<?php foreach ($general_settings["site_allowed_ext"] as $ext): ?>
							<option value="<?php esc_html($ext); ?>"><?php esc_html($ext); ?></option>
						<?php endforeach; ?> ?>
					</select>
					<div class="clear"></div>
					<?php foreach ($general_settings["site_allowed_ext"] as $ext): ?>
						<input style="display:none;" type="number" class="custom-ext-size" name="general_settings[ext_max_upload][<?php esc_html($ext); ?>]" value="<?php esc_html($general_settings["ext_max_upload"][$ext]); ?>" />
					<?php endforeach; ?>
				</div>

				<div class="allowed_extension">
					<label for="">صيغ المتاحة</label>
					<div class="notices not-before">
						<p>المرجو الفصل بين إمتدادت ب فاصلة (,)</p>
						<p>تجنب كتابات مثل (Jpg,JPG,JPg)</p>
						<p>يحب أن تكون كل إمتدادت lowercase مثل (pdf,jpg,jpeg)</p>
					</div>
					<div class="clear"></div>
					<input type="text" name="general_settings[site_allowed_ext]" value="<?php echo implode(",", $general_settings["site_allowed_ext"]); ?>" />
					<div class="clear"></div>
					<div class="full-width">
						<label for="">صيغ المتاحة في مكتبة الوسائط</label>
						<select name="general_settings[library_allowed_ext][]" multiple style="height:200px">
							<?php foreach ($general_settings["site_allowed_ext"] as $ext): ?>
								<option value="<?php esc_html($ext); ?>" <?php selected_val($ext, $general_settings["library_allowed_ext"]); ?>><?php esc_html($ext); ?></option>
							<?php endforeach; ?> ?>
						</select>
					</div>
					<div class="full-width">
						<label for="">إغلاق الموقع</label>
						<div class="col-s-setting">
							<input type="checkbox" name="general_settings[lock_site]" id="checkbox1" class="ios-toggle" <?php checked_val($general_settings["lock_site"] ?? "", "on"); ?>>
							<label for="checkbox1" class="checkbox-label"></label>
						</div>

					</div>
					<div class="full-width">
						<label for="">تفعيل تعليقات الفايسبوك</label>
						<div class="col-s-setting">
							<input type="checkbox" name="general_settings[fb_comments]" id="checkbox2" class="ios-toggle" <?php checked_val($general_settings["fb_comments"] ?? "", "on"); ?>>
							<label for="checkbox2" class="checkbox-label"></label>
						</div>
					</div>

					<div class="full-width">
						<label for="">تفعيل تعلم البوت</label>
						<div class="col-s-setting">
							<input type="checkbox" name="general_settings[boot_learning]" id="checkbox_boot_learn" class="ios-toggle" <?php checked_val($general_settings["boot_learning"] ?? "", "on"); ?>>
							<label for="checkbox_boot_learn" class="checkbox-label"></label>
						</div>

					</div>

					<div class="full-width">
						<label for="">صفحة شروط الإستخدام</label>
						<select name="general_settings[condition_page]">
							<option selected="" value="0"></option>
							<?php
							foreach (get_pages($page_id, false, false) as $no_translate_pk => $no_translate_pv) {
								$no_translate_p_id = $no_translate_pv["id"];
								$no_translate_p_title = $no_translate_pv["page_title"];
							?>
								<option <?php selected_val($no_translate_p_id, $general_settings['condition_page']); ?> value="<?php echo $no_translate_p_id; ?>"><?php esc_html($no_translate_p_title); ?></option>
							<?php
							}
							?>

						</select>

					</div>
					<div class="full-width">
						<label for="">اميل حساب chatgpt</label>
						<input type="text" name="general_settings[chat_gpt_email]" value="<?php echo $general_settings["chat_gpt_email"] ?? ''; ?>" />
					</div>
					<div class="full-width">
						<label for="">كلمة سر حساب chatgpt</label>
						<input type="password" name="general_settings[chat_gpt_password]" value="<?php echo $general_settings["chat_gpt_password"] ?? ''; ?>" />
					</div>
					<div class="full-width">
						<label for="">اخفاء المتصفح اثناء عملية scraping</label>
						<div class="col-s-setting">
							<input type="checkbox" name="general_settings[headless]" id="headless" class="ios-toggle" <?php checked_val($general_settings["headless"] ?? "", "on"); ?>>
							<label for="headless" class="checkbox-label"></label>
						</div>
					</div>

					<button id="submit_form" class="saveData">أضف</button>
					<input type="hidden" name="method" value="general_settings" />
				</div>
			</form>
		</div>
	</div>