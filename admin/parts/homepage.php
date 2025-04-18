<?php
$bloc = "";
if(isset($_GET["bloc"])) {
	/**
	 * ########### PAY ATTENTION ###########
	 * All blocs should be named following below example
	 * bloc_settings_[{bloc_page}_bloc_{bloc_name}]
	 * See switch_blocs() function in functions.php
	 */
    $bloc = htmlspecialchars($_GET["bloc"]);
    $blocs = array(
        "bloc_settings_[header_bloc]" => array(
            "display_area" => false,
            "sort_area" => false,
            "limit" => false,
            "title" => "الهيدر",
        ),
        "bloc_settings_[homepage_bloc_st]" => array(
            "display_area" => true,
            "sort_area" => false,
            "limit" => false,
            "title" => "بنر الأصفر",
        ),
        "bloc_settings_[homepage_bloc_history]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => false,
            "title" => "مناسبات,أحداث,وفايات اليوم",
        ),
        "bloc_settings_[homepage_bloc_slide]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "سليد",
        ),
        "bloc_settings_[homepage_bloc_feature]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "مميزة",
        ),
        "bloc_settings_[homepage_bloc_books]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "مكتبة الكتب",
        ),
        "bloc_settings_[homepage_bloc_varied]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "مواضيع متنوعة",
        ),
        "bloc_settings_[homepage_bloc_author_articles]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "مقالات الكاتب",
        ),
        "bloc_settings_[homepage_bloc_latest_views]" => array(
            "display_area" => true,
            "sort_area" => false,
            "limit" => true,
            "title" => "أكثر مشاهدة/جديد",
        ),
        "bloc_settings_[homepage_bloc_researches]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "بحوث الكاتب",
        ),
        "bloc_settings_[homepage_bloc_images]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "معرض الصور",
        ),
        "bloc_settings_[homepage_bloc_videos]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "معرض الفيديوهات",
        ),
        "bloc_settings_[homepage_bloc_quote]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "الحكم",
        ),
        "bloc_settings_[homepage_bloc_names]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "اسماء",
        ),
        "bloc_settings_[homepage_bloc_dictionary]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "قاموس",
        ),
        "bloc_settings_[homepage_bloc_counter]" => array(
            "display_area" => true,
            "sort_area" => false,
            "limit" => false,
            "title" => "العداد",
        ),
        "bloc_settings_[homepage_bloc_special_users]" => array(
            "display_area" => true,
            "sort_area" => true,
            "limit" => true,
            "title" => "أعضاء مميزين",
        ),
	"bloc_settings_[footer_bloc]" => array(
            "display_area" => false,
            "sort_area" => false,
            "limit" => false,
            "title" => "فوتر",
        ),
    );
    //if(in_array($bloc,$blocs)) { 
        $meta_key_look = $bloc;
        $bloc_settings = get_settings("bloc_settings_[".$bloc."]");
        $bloc_settings = unserialize($bloc_settings);
        $bloc_setting_display = $bloc_settings["display"] ?? "";
        $bloc_setting_sortby = $bloc_settings["sort_by"] ?? "";
        $bloc_setting_postshow = $bloc_settings["post_show"] ?? "";
    //}
}
?>
    <div class="dash-part-form">
        <div class="full-width">
           <div class="notices">
               <p>المرجو إختيار البلوك الذي تريد التعديل عليه</p>
           </div>
           <div class="clear"></div>
           <select id="homepage_blocks">
              <option selected="" disabled="">إختر</option>
               <option value="header_bloc">الهيدر</option>
               <option value="homepage_bloc_st">بنر الأصفر</option>
               <option value="homepage_bloc_history">مناسبات,أحداث,وفايات اليوم</option>
               <option value="homepage_bloc_slide">سليد</option>
               <option value="homepage_bloc_feature">مواضيع مميزة</option>
               <option value="homepage_bloc_varied">مواضيع متنوعة</option>
               <option value="homepage_bloc_books">مكتبة الكتب</option>
               <option value="homepage_bloc_author_articles">مقالات الكاتب</option>
               <option value="homepage_bloc_latest_views">أكثر مشاهدة/جديد</option>
               <option value="homepage_bloc_researches">بحوث الكاتب</option>
               <option value="homepage_bloc_images">معرض الصور</option>
               <option value="homepage_bloc_videos">معرض الفيديوهات</option>
               <option value="homepage_bloc_quote">الحكم</option>
               <option value="homepage_bloc_names">أسماء</option>
               <option value="homepage_bloc_dictionary">قاموس</option>
               <option value="homepage_bloc_counter">العداد</option>
               <option value="homepage_bloc_special_users">أعضاء مميزين</option>
               <option value="footer_bloc">فوتر</option>
           </select>
            <div class="r7-width">
                <div class="homepage_settings">
                    <form id="form_data" method="post">
                        <h4><?php esc_html("بلوك ".$blocs["bloc_settings_[".$bloc."]"]["title"]); ?></h4>
                        <?php if($blocs["bloc_settings_[".$bloc."]"]["display_area"] === true) { ?>
                        <div class="full-width">
                            <div class="col-s-setting">
                                <span>حالة ظهور البلوك</span>
                                <input type="checkbox" name="meta_settings[<?php echo $bloc; ?>][display]" id="checkbox2" class="ios-toggle" <?php if($bloc_setting_display == "on") { echo 'checked=""'; } ?> >
                                <label for="checkbox2" class="checkbox-label"></label>
                            </div>                            
                        </div>
                        <?php } ?>
                        <?php if($blocs["bloc_settings_[".$bloc."]"]["sort_area"] === true) { ?>
                        <div class="full-width homepage_block_settings">
                           <label for="block_settings">إعدادات ظهور المواضيع</label>
                           <div class="line-elm-flex">
                            <div class="r3-width">
                            <input type="radio" name="meta_settings[<?php echo $bloc; ?>][sort_by]" value="latest" <?php if($bloc_setting_sortby == "latest") { echo 'checked=""'; } ?>/><span>الجديد</span>
                            </div>
                            <div class="r3-width">
                                <input type="radio" name="meta_settings[<?php echo $bloc; ?>][sort_by]" value="random" <?php if($bloc_setting_sortby == "random") { echo 'checked=""'; } ?>/><span>العشوائي</span>
                            </div>
                            <?php if($bloc != "homepage_bloc_special_users"): ?>
                            <div class="r3-width">
                                <input type="radio" name="meta_settings[<?php echo $bloc; ?>][sort_by]" value="special" <?php if($bloc_setting_sortby == "special") { echo 'checked=""'; } ?>/><span>مميز</span>
                            </div>
                            <?php elseif($bloc = "homepage_bloc_special_users"): ?>
                            <div class="r3-width">
                                <input type="radio" name="meta_settings[<?php echo $bloc; ?>][sort_by]" value="posts" <?php if($bloc_setting_sortby == "posts") { echo 'checked=""'; } ?>/><span>اكثر المواضيع</span>
                            </div>
                            <div class="r3-width">
                                <input type="radio" name="meta_settings[<?php echo $bloc; ?>][sort_by]" value="points" <?php if($bloc_setting_sortby == "points") { echo 'checked=""'; } ?>/><span>اكثر النقاط</span>
                            </div>
                            <div class="r3-width">
                                <input type="radio" name="meta_settings[<?php echo $bloc; ?>][sort_by]" value="most_followed" <?php if($bloc_setting_sortby == "most_followed") { echo 'checked=""'; } ?>/><span>اكثر المتابعين</span>
                            </div> 
                            <div class="r3-width">
                                <input type="radio" name="meta_settings[<?php echo $bloc; ?>][sort_by]" value="post_views" <?php if($bloc_setting_sortby == "post_views") { echo 'checked=""'; } ?>/><span>اكثر المشاهدة للمواضعه</span>
                            </div>                              
                            <?php endif; ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if($blocs["bloc_settings_[".$bloc."]"]["limit"] === true) { ?>
                        <div class="full-width">
                           <label for="post_show">عدد المحتوى في البلوك</label>
                            <input type="number" name="meta_settings[<?php echo $bloc; ?>][post_show]" min="1" value="<?php echo $bloc_setting_postshow; ?>"/>
                        </div>
                        <?php } ?>
                          <input type="hidden" name="meta_settings[<?php echo $bloc; ?>][default]" value="default"/>
                        <?php 
                    if($bloc == "homepage_bloc_st") {
                                $bloc_st_vid_image = $bloc_settings["bloc_st_vid_image"] ?? "";
                                $bloc_st_text = $bloc_settings["bloc_st_text"] ?? "";
                                $bloc_st_vid = $bloc_settings["bloc_st_vid"] ?? "";
                        ?>
                        <div class="full-width">
                            <div class="up-upload-input">
                               <label for="video_image">صور الفيديو</label>
								<button class="upload-btn" data-input="#bloc_st_vid_image"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
								<div class="clear"></div>
								<div class="img-preview">
									<img src="<?php echo get_thumb($bloc_st_vid_image); ?>" id="bloc_st_vid_image_prv"/>
								</div>							
                                <input name="meta_settings[<?php echo $bloc; ?>][bloc_st_vid_image]" id="bloc_st_vid_image" value="<?php echo $bloc_st_vid_image; ?>" type="hidden">
                            </div>
                        </div>
                        <div class="full-width">
                            <label for="video_url">رابط الفيديو</label>
							<?php 
							$bloc_st_vid = (object) $bloc_st_vid;
							multi_input_languages("meta_settings[".$bloc."][bloc_st_vid]","text",$bloc_st_vid); 
							?>
                        </div>
                        <div class="full-width">
                            <label for="text">نص</label>
                            <?php 
                            $bloc_st_text = (object) $bloc_st_text; 
                            multi_input_languages("meta_settings[".$bloc."][bloc_st_text]","text",$bloc_st_text); 
                            ?>
                        </div>              
                        <?php
                        }
                        ?>
                        <?php 
                        $bloc_name = '';
                        if($bloc == "header_bloc") {
                            $bloc_name = 'header';
                            $bloc_header_text = (object)$bloc_settings["header_bloc_text"] ?? "";
                            $bloc_header_logo = $bloc_settings["site_logo"];
                        	$bloc_header_menu_{M_L} = $bloc_settings["bloc_header_menu_".M_L.""] ?? "";
							?>
							<label for="links">روابط</label>
							<select id="lang-link" data-bloc="header">
							    <?php foreach(get_langs() as $lang): ?>
							    <option value="<?php esc_html($lang["lang_code"]); ?>"><?php esc_html($lang["lang_name"]); ?></option>
							    <?php endforeach; ?>
							</select>
							<div class="clear"></div>
							<div class="choose-link-area sortable">
							    <?php foreach(get_the_menu($bloc_header_menu_{M_L}) as $menu_header): ?>
							    <li><?php esc_html($menu_header["title"]); ?>&nbsp;<i class="fas fa-times remove-parent" data-id="<?php esc_html($menu_header["id"]); ?>" data-type="<?php esc_html($menu_header["type"]); ?>"></i></li>
							    <?php endforeach; ?>
							</div>
							<div class="clear"></div>
							
							<div class="clear"></div>
							<select id="link-moveTo">
							    <option selected="" disabled="">إختر : </option>
								<option value="categories">أقسام</option>
								<option value="pages">صفحات</option>
								<option value="external_links">روابط خارجية</option>
							</select>
							<div class="clear"></div>
							
							<div class="link-in-m">
								<ul class="link-in-m-sort sortable"></ul>
							</div>							

                        <div class="full-width">
                            <label for="text">نص</label>
                            <?php multi_input_languages("meta_settings[".$bloc."][header_bloc_text]","text",$bloc_header_text); ?>
                        </div>
                     	<div class="full-width input-label-line">
		                <div class="up-upload-input">
		                   <label for="logo">شعار الموقع</label>
                            <input type="hidden" id="site_logo" name="meta_settings[<?php echo $bloc; ?>][site_logo]" value="<?php esc_html($bloc_header_logo); ?>"/>
							<button class="upload-btn" data-input="#site_logo"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
							<div class="clear"></div>
							<div class="img-preview">
								<img src="<?php esc_html(get_thumb($bloc_header_logo,null,false)); ?>" id="site_logo_prv"/>
							</div>							
		                </div>
		                </div>
                        <?php } ?>
						<?php
						if($bloc == "footer_bloc") {
						    $bloc_name = 'footer';
							$bloc_footer_notice = $bloc_settings["bloc_footer_notice"] ?? "";
							$bloc_footer_desc = $bloc_settings["bloc_footer_desc"] ?? "";
							$bloc_footer_logo = $bloc_settings["bloc_footer_logo"] ?? "";
							$ml = M_L;
							$bloc_footer_menu_{M_L} = $bloc_settings["bloc_footer_menu_".$ml.""] ?? "";
							?>
							<label for="links">روابط</label>
							<select id="lang-link" data-bloc="footer">
							    <?php foreach(get_langs() as $lang): ?>
							    <option value="<?php esc_html($lang["lang_code"]); ?>"><?php esc_html($lang["lang_name"]); ?></option>
							    <?php endforeach; ?>
							</select>
							<div class="choose-link-area sortable">
							    <?php foreach(get_the_menu($bloc_footer_menu_{M_L}) as $menu_footer): ?>
							    <li data-id="<?php esc_html($menu_footer["id"]); ?>" data-type="<?php esc_html($menu_footer["type"]); ?>"><?php esc_html($menu_footer["title"]); ?>&nbsp;<i class="fas fa-times remove-parent" data-id="<?php esc_html($menu_footer["id"]); ?>" data-type="<?php esc_html($menu_footer["type"]); ?>"></i></li>
							    <?php endforeach; ?>
							</div>
							<div class="clear"></div>
							
							<div class="clear"></div>
							<select id="link-moveTo">
							    <option selected="" disabled="">إختر : </option>
								<option value="categories">أقسام</option>
								<option value="pages">صفحات</option>
								<option value="external_links">روابط خارجية</option>
							</select>
							<div class="clear"></div>
							
							<div class="link-in-m">
								<ul class="link-in-m-sort sortable">
								    <?php
								    $get_categories = get_categories();
                                		foreach($get_categories as $category) {
                                			$category_id = $category["id"];
                                			$category_title = $category["cat_title"];
                                			?>
                                			<li class="droppable-links" data-id="<?php esc_html($category_id); ?>" data-type="category">
                                				<span><?php echo $category_title; ?></span>
                                			</li>
                                			<?php
                                		}
                                		?>
								</ul>
							</div>							
							<label for="alert">تنبيه مهم</label>
							<?php 
							$bloc_footer_notice = (object) $bloc_footer_notice;
							multi_input_languages("meta_settings[".$bloc."][bloc_footer_notice]","textarea",$bloc_footer_notice);
							?>
							<label for="alert">وصف الموقع</label>
							<?php
							$bloc_footer_desc = (object) $bloc_footer_desc;
							multi_input_languages("meta_settings[".$bloc."][bloc_footer_desc]","textarea",$bloc_footer_desc);
							
						
						?>
						<div class="clear"></div>
								<div class="full-width input-label-noline">
								<label>شعار الفوتر</label>
								<div class="up-upload-input">
									<input type="hidden" id="bloc_footer_icon" name="meta_settings[<?php esc_html($bloc); ?>][bloc_footer_logo]" value="<?php esc_html($bloc_footer_logo); ?>"/>
									<button class="upload-btn" data-input="#bloc_footer_icon"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
									<div class="clear"></div>
										<div class="img-preview">
											<img src="<?php esc_html(get_thumb($bloc_footer_logo,null)); ?>" id="bloc_footer_icon_prv"/>
									</div>							
								</div>
								</div>
                    <?php } ?>
					<div class="bloc_menu_inps">
					<?php 
					foreach(get_langs() as $lang):
					    $lang_code = $lang["lang_code"];
					    if(is_array($bloc_settings["bloc_{$bloc_name}_menu_{$lang_code}"])):
    					    foreach($bloc_settings["bloc_{$bloc_name}_menu_{$lang_code}"] as $menu):
    					        foreach($menu as $link_type=>$link_id):
    					        ?>
    					        <input type="hidden" name="meta_settings[<?php esc_html($bloc); ?>][bloc_<?php esc_html($bloc_name); ?>_menu_<?php echo $lang["lang_code"]; ?>][][<?php esc_html($link_type); ?>]" value="<?php esc_html($link_id); ?>" class="in-menu menu-item-<?php esc_html($link_type); ?>-<?php esc_html($link_id); ?>"/>
                                <?php 
                                endforeach; 
                            endforeach; 
                        endif;
                    endforeach; ?>
                    </div>
                    <input type="hidden" name="method" value="blocs_settings"/>
					<button id="submit_form" class="saveData">تعديل</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
$(document).ready(function() {
    $(".link-in-m-sort,.sortable").sortable({
      connectWith: ".sortable",
      receive: function( event, ui ) {
          if(ui.sender.hasClass("link-in-m-sort")) {
              var id = ui.item.data("id");
              var type = ui.item.data("type");
              var lang = $("#lang-link").val();
              var bloc = $("#lang-link").data("bloc");
              $("#form_data").append( '<input type="hidden" name="meta_settings['+bloc+'_bloc][bloc_'+bloc+'_menu_'+lang+'][]['+type+']" value="'+id+'" class="not-in-menu menu-item-'+type+'-'+id+'"/>' );
          }
      },
      remove: function(event, ui) {
        var id = ui.item.data("id");
        var type = ui.item.data("type");
        $('.menu-item-'+type+'-'+id+'').remove();
      }
    }).disableSelection();

	$("#link-moveTo").on("change",function() {
		var elm_loads = $(this).val();
		$.get(gbj.siteurl+"/ajax/ajax-html.php",{path : "menus_elms", section : elm_loads},function(data,status) {
		    $(".link-in-m-sort").html(data);	
		});
	});
	
    $("#homepage_blocks").on("change",function() {
        var bloc_selected = $(this).val();
        if(typeof(bloc_selected) == "string") {
            window.location.href = gbj.siteurl+"/admin/dashboard/advanced_settings?section=homepage&bloc="+bloc_selected;
        }
    });
    
    $("#lang-link").on("change",function() {
       var lang = $(this).val();
       var bloc = $(this).data("bloc");
       $.get(gbj.siteurl+"/ajax/ajax-html.php",{path : "bloc_menu",bloc_area : bloc,lang : lang},function(data) {
           $(".choose-link-area").html(data);
           $(".not-in-menu").remove();
       });
    });
    
});
</script>
