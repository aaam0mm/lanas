<?php
dd(get_settings("site_favicon"));
$site_title = get_settings("site_title");
$site_keywords = get_settings("site_keywords");
$site_desc = get_settings("site_desc");
$site_favicon = get_settings("site_favicon");
?>
    <div class="dash-part-form">
        <div class="r7-width">
        <form action="" method="POST" enctype="multipart/form-data" id="form_data">
            <div class="full-width input-label-noline">
                <label for="site_title">عنوان الموقع</label>
                <?php multi_input_languages("seo_settings[site_title]","text",$site_title); ?>
            </div>
            <div class="full-width input-label-noline">
                <label for="site_keywords">كلمات مفتاحية</label>
                <?php multi_input_languages("seo_settings[site_keywords]","text",$site_keywords); ?>
            </div>
            <div class="full-width input-label-noline">
                <label for="site_desc">وصف الموقع</label>
                <?php multi_input_languages("seo_settings[site_desc]","text",$site_desc); ?>
            </div>
            
			<div class="full-width input-label-noline">
                <label for="favicon">favicon</label>
                <div class="notices">
                    <p>الأيقونة المفضلة يتم عرضها في معظم متصفحات الويب الرسومية الرئيسية.</p>
                </div>
                <div class="clear"></div>
				<div class="up-upload-input">
					<input type="hidden" id="site_fav_icon" name="seo_settings[site_favicon]" value="<?php esc_html($site_favicon); ?>"/>
					<button class="upload-btn" data-input="#site_fav_icon"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
					<div class="clear"></div>
					<div class="img-preview">
						<img src="<?php esc_html(get_thumb($site_favicon,null)); ?>" id="site_fav_icon_prv"/>
					</div>							
				</div>
			</div>
            <input type="hidden" name="method" value="seo_settings"/>
            <button id="submit_form" class="saveData">تحديث</button>
        </form>
        </div>
    </div>