<?php
$q = $_GET["q"] ?? "";
$action = $_GET["action"] ?? "";
$filter_ad_area = $_GET["ad_area"] ?? "";
$filter_ad_lang = $_GET["ad_lang"] ?? "";
$where_args = [];
$get_ads_info = $dsql->dsql()->table('ads');
if(!empty($filter_ad_lang)) {
    $get_ads_info->where('ad_lang',$filter_ad_lang);
}
if(!empty($filter_ad_area)) {
    $get_ads_info->where('ad_area',$filter_ad_area);
}
if(!empty($q)) {
    $get_ads_info->where('ad_title','LIKE',$q);    
}
$get_ads_info = $get_ads_info->get();
?>
    <div class="dash-part-form">
       <?php
        if(!$action) {
        ?>
        <div class="full-width">
            <form action="" method="get" id="form_filter">
            <div class="page-action">
            <div class="pull-right">
            <a href="dashboard/ads?action=add" id="btn_link">إضافة</a>    
            </div>
            <div class="pull-left">
            <div class="line-elm-flex">
                <div class="7r-width">
                <input type="text" name="q" placeholder="إبحث بالإسم" value="<?php echo $q; ?>"/>
                </div>
                <div class="r3-width">
                <button form="form_filter" id="search_btn"><i class="fa fa-search"></i></button>
                </div>
            </div>
            </div>
            </div>
            <div class="clear"></div>
            <div class="panel_filter">
            <div class="pull-left line-elm-flex">
			<div class="7r-width">
            <select name="ad_lang" class="on_change_submit">
                <option value="">كل اللغات</option>
                <?php
                foreach(get_langs() as $lang_k=>$lang_v) {
                    $lang_code = $lang_v["lang_code"];
                    $lang_name = $lang_v["lang_name"];
                    $selected_attr = "";
                    if($lang_code == $filter_ad_lang) {
                        $selected_attr = 'selected="true"';
                    }
                    
                    ?>
                    <option value="<?php esc_html($lang_code); ?>" <?php echo $selected_attr; ?> ><?php echo $lang_name; ?></option>
                    <?php
                }
                ?>
            </select>			
            </div>			
            <div class="7r-width">
			<select class="on_change_submit" name="ad_area">
			<option value="">الجميع</option>
            <optgroup label="الرئيسية">
                <option value="ad_unit_index_top_big" <?php if($filter_ad_area == "ad_unit_index_top_big") { echo 'selected="true"'; } ?> >إعلان كبير فوق</option>
                <option value="ad_unit_index_115_560" <?php if($filter_ad_area == "ad_unit_index_115_560") { echo 'selected="true"'; } ?> >حجم 115 × 560</option>
                <option value="ad_unit_index_under_feature" <?php if($filter_ad_area == "ad_unit_index_under_feature") { echo 'selected="true"'; } ?> >إعلان تحت مميز</option>
                <option value="ad_unit_index_side" <?php if($filter_ad_area == "ad_unit_index_side") { echo 'selected="true"'; } ?> >إعلان جانبي</option>
            </optgroup>
            <optgroup label="الأقسام"> 
                <option value="ad_unit_cat_280_280_side" <?php if($filter_ad_area == "ad_unit_cat_280_280_side") { echo 'selected="true"'; } ?> >حجم 280 × 280</option>
            </optgroup>
            <optgroup label="المحتوى">
                <option value="ad_unit_content_280_280_side" <?php if($filter_ad_area == "ad_unit_content_280_280_side") { echo 'selected="true"'; } ?> >حجم 280 × 280</option>
                <option value="ad_unit_content_top_big" <?php if($filter_ad_area == "ad_unit_content_top_big") { echo 'selected="true"'; } ?> >حجم كبير فوق</option>
                <option value="ad_unit_content_bottom_big" <?php if($filter_ad_area == "ad_unit_content_bottom_big") { echo 'selected="true"'; } ?> >حجم كبير تحت</option>            
                <option value="ad_unit_content_inside" <?php if($filter_ad_area == "ad_unit_content_inside") { echo 'selected="true"'; } ?> >وسط الموضوع</option>            
            </optgroup>
            </select>   
            </div>        
            </div>
            </div>
            </form>
            <div class="clear"></div>
            <div class="panel_filter">
				<form method="get" action="dashboard/delete" id="action-form">
					<div class="pull-right r3-width">
						<div class="line-elm-flex">
							<div class="r3-width">
								<select name="action" class="">
									<option value="delete">حدف</option>
									<option value="lock">حظر</option>
									<option value="publish">نشر</option>
								</select>
							</div>
							<div class="r3-width">
								<input type="submit" value="تنفيذ" class="btn_action submit-action"/>
							</div>
						</div>
						<input type="hidden" name="target" value="ads"/>
						<input name="method" value="multi_action" type="hidden">
					</div>
				</form>
                
            </div>
            <div class="clear"></div>
			<?php 
			if($get_ads_info) {
			?>
           <div class="table-responsive">
            <table class="table_parent">
               <tbody class="ds">
                <tr>
                    <th><input type='checkbox' class="select-checkbox-all check-all-multi"/></th>
                    <th>إسم الإعلان</th>
                    <th>مكان الظهور</th>
                    <th>المشاهدات</th>
                    <th>النقرات</th>
                    <th>الإجراءات</th>
                </tr>
                <?php 
                   foreach($get_ads_info as $ad_dashk=>$ad_dash_v) {
                       $ad_id = $ad_dash_v["id"];
                       $ad_title = $ad_dash_v["ad_title"];
                       $ad_area = $ad_dash_v["ad_area"];
                       $ad_views = $ad_dash_v["ad_views"];
                       $ad_clicks = $ad_dash_v["ad_clicks"];
                    if($ad_dash_v["ad_case"] == 1) {
                        $lock_btn_class = "fa-lock-open";
                        $lock_action = "lock";
                    }elseif($ad_dash_v["ad_case"] == 2){
                        $lock_btn_class = "fa-lock";
                        $lock_action = "unlock";
                    }

                ?>
                <tr>
                    <td><input type='checkbox' class="select-checkbox check-box-action" data-id="<?php echo $ad_id; ?>" value="<?php esc_html($ad_id); ?>"/></td>
                    <td><?php esc_html($ad_title); ?></td>
                    <td>
					<?php 
					if($ad_area == "ad_unit_index_115_560") {
						echo str_replace("ad_unit_index","الرئيسية",$ad_area);
					}elseif($ad_area == "ad_unit_cat_280_280_side") {
						echo str_replace("ad_unit_cat","الأقسام",$ad_area);
					}elseif($ad_area == "ad_unit_content_280_280_side") {
						echo str_replace("ad_unit_content","المحتوى",$ad_area);
					}else{
						echo $ad_area;
					}					
					?>
					</td>
                    <td><?php esc_html($ad_views); ?></td>
                    <td><?php esc_html($ad_clicks); ?></td>
                    <td>                
                        <table class="table_child">
                        <tr>
                        <td><button class="action_stg lock-btn updateData" data-id="<?php esc_html($ad_id); ?>" data-method="un_lock_ad_ajax"><i class="fa <?php echo $lock_btn_class; ?>"></i></button></td>  
                        <td><button class="action_stg edit-st-btn open-url" data-url="dashboard/ads?action=edit&ad_id=<?php esc_html($ad_id); ?>"><i class="fa fa-cog"></i></button></td>
                        <td><button class="action_stg delete-btn open-url" data-url="dashboard/delete?type=ads&id=<?php esc_html($ad_id); ?>"><i class="fa fa-trash"></i></button></td> 
                        </tr>
                        </table>
                </td>
                </tr>
                <?php
                   }
                ?>
                </tbody>
            </table>
            </div>
			<?php 
			}else{
				?>
				<p class="no_posts">لاتوجد أي إعلانات حاليا</p>
				<?php
			}
			?>
        </div>
        <?php
        }elseif($action == "add" or $action == "edit") {
            $ad_id = $_GET["ad_id"] ?? "";
			$get_ad = $dsql->dsql()->table('ads')->where('id', $ad_id)->limit(1)->getRow();
			$ad = $get_ad;
			$ad_title = $ad["ad_title"] ?? "";
			$ad_code = $ad["ad_code"] ?? "";
			$ad_lang = $ad["ad_lang"] ?? "";
			$ad_link = $ad["ad_link"] ?? "";
			$ad_area = $ad["ad_area"] ?? "";
            $ad_areas_arr = array(
                "index" => array(
                    "title" => "الرئيسية",
                    "ads_area" => array(
                        "ad_unit_index_top_big" => "إعلان كبير فوق",
                        "ad_unit_index_115_560" => "حجم 115 × 560",
                        "ad_unit_index_under_feature" => "تحت مميز",
                        "ad_unit_index_side" => "جانبي"
                    ),
                ),
                
                "category" => array(
                    "title" => "الأقسام",
                    "ads_area" => array(
                        "ad_unit_cat_280_280_side" => "حجم 280 × 280",
                    ),
                ),
                
                "content" => array(
                    "title" => "المحتوى",
                    "ads_area" => array(
                        "ad_unit_content_280_280_side" => "حجم 280 × 280",
                        "ad_unit_content_big_top" => "فوق كبير",
                        "ad_unit_content_big_bottom" => "تحت كبير",
                        "ad_unit_content_inside" => "وسط الموضوع"
                    ),
                ),
            );
        ?>
        <div class="r7-width">
        <div class="r7-width">
            <div class="add_new_badge">
                <h2>أضف إعلان</h2>
                <form method="post" id="form_data" enctype="multipart/form-data">
                <div class="full-width input-label-noline">
                 <label for="ad_title">إسم الإعلان</label>
                    <input type="text" name="ad_title" value="<?php esc_html($ad_title); ?>"/>
                </div>
                <div class="full-width input-label-noline">
                 <label for="ad_link">رابط الإعلان</label>
                    <input type="text" name="ad_link" value="<?php esc_html($ad_link); ?>"/>
                </div>
                <div class="clear"></div>
                <div class="notices">
                    <p>إذا كان إعلان عبارة عن صور المرجو ترك حقل الكود فارغ</p>
                </div>
                <div class="full-width input-label-noline">
                 <label for="ad_code">كود الإعلان</label>
                    <textarea name="ad_code"><?php if( ( (int) $ad_code) == 0 ) { esc_html($ad_code); } ?></textarea>
                </div>
                
                        <div class="full-width input-label-noline">
                            <label for="ad_image"></label>
                            <input type="hidden" id="ad_image" name="ad_image" value="<?php if( ( (int) $ad_code) > 0 ) { echo $ad_code; } ?>"/>
							<button class="upload-btn" data-input="#ad_image"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
							<div class="clear"></div>
								<div class="img-preview">
								    <?php if( ( (int) $ad_code) > 0 ): ?>
									<img src="<?php echo get_thumb($ad_code,null); ?>" id="ad_image_prv"/>
									<?php else: ?>
									<img src="" id="ad_image_prv"/>
									<?php endif; ?>
							</div>							
                        </div>
                
                <div class="full-width input-label-noline">
                 <label for="ad_lang">اللغة</label>
                    <select name="ad_lang">
                        <option value="0">الكل</option>
                        <?php
                        foreach(get_langs() as $langk=>$langv) {
                            ?>
                            <option value="<?php esc_html($langv["lang_code"]); ?>" <?php selected_val($langv["lang_code"],$ad_lang); ?> ><?php esc_html($langv["lang_name"]); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                
                <div class="full-width input-label-noline">
                <label for="ad_place">مكان الإعلان</label>
                   <div class="notices">
                       <p>المرجو الإنتباه جيدا لحجم الإعلان و تناسبه مع المكان الذي سيظهر به</p>
                   </div>
                   <div class="clear"></div>
                    <select name="ad_area">
                       <?php
                    foreach($ad_areas_arr as $ad_area_k=>$ad_area_v) {
                        $ad_area_label = $ad_area_v["title"];
                        ?>
                        <optgroup label="<?php echo $ad_area_label; ?>">
                        <?php
                        foreach($ad_area_v["ads_area"] as $ad_info_key=>$ad_info_val) {
                            
                            $selected_attr = "";
                            if($ad_info_key == $ad_area) {
                                $selected_attr  = 'selected="true"';
                            }
                            
                        ?>
                        <option value="<?php echo $ad_info_key; ?>" <?php echo $selected_attr; ?> ><?php echo $ad_info_val; ?></option>
                        <?php
                        }
                        ?>
                        </optgroup>
                        <?php
                    }
                        ?>
                    </select>
                </div>
				<button id="submit_form" class="saveData">أضف</button>
                <input type="hidden" name="method" value="ads"/>
                <input type="hidden" name="action" value="<?php esc_html($action); ?>"/>
                <?php
                if(isset($ad_id)) {
                    ?>
                    <input type="hidden" name="ad_id" value="<?php echo $ad_id; ?>"/>
                    <?php
                }
                ?>
                </form>
            </div>
            </div>
        </div>
        <?php               
        }
        ?>
    </div>