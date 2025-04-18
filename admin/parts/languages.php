	<?php 
	$action = $_GET["action"] ?? "";
	if(!$action): 
	?>
    <div class="dash-part-form">
        <div class="full-width">
        <div class="page-action">
            <div class="pull-right">
            <a href="dashboard/advanced_settings?section=languages&action=add" id="btn_link">إضافة</a>    
            </div>
            <div class="pull-left">
            <div class="line-elm-flex">
			<!--
                <div class="7r-width">
                <input name="q" placeholder="إبحث عن لغة" type="text">
                </div>
                <div class="r3-width">
                <button form="form_filter" id="search_btn"><i class="fa fa-search"></i></button>
                </div>
				-->
            </div>
            </div>
            </div>
            <div class="clear"></div>
        <div class="table-responsive">
        <table class="table_parent">
           <tbody class="ds">
            <tr>
                <th></th>
                <th>إسم اللغة</th>
                <th>رمز اللغة</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
            <?php
            foreach(get_langs(null,null) as $get_lang_allK=>$get_lang_allV) {
            $lang_visibility = $get_lang_allV["lang_visibility"];
                $lang_icon = $get_lang_allV["lang_icon"];
                if($lang_visibility == "on") {
                    $lock_btn_class = "fa-lock";
                    $lock_action = "lock";
                }elseif($lang_visibility == "off"){
                    $lock_btn_class = "fa-unlock";
                    $lock_action = "unlock";
                }
                ?>
            <tr>
                <td><img src="<?php echo get_thumb($lang_icon); ?>" class="meduim_icon"/></td>
                <td><?php echo $get_lang_allV["lang_name"]; ?></td>
                <td><?php esc_html($get_lang_allV["lang_code"]); ?></td>
                <td><?php if($get_lang_allV["lang_visibility"] == "on") {echo "مفعل";}else{echo "غير مفعل";} ?></td>
                <td>
                    <table class="table_child">
                        <tr>
                            
                            <td><button class="action_stg edit-st-btn open-url" title="تعديل" data-url="dashboard/advanced_settings?section=languages&action=edit&lang_code=<?php esc_html($get_lang_allV["lang_code"]); ?>" id=""><i class="fa fa-cog"></i></button></td>
                            <td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=languages&id=<?php esc_html($get_lang_allV["lang_code"]); ?>"><i class="fa fa-trash"></i></button></td> 
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
        </div>
    </div>
	<?php endif; ?>


	<?php 
	if($action == "edit" || $action == "add"): 
	$lang_code = $_GET["lang_code"] ?? "";
	$get_lang = get_langs($lang_code,false);
	$lang_id = $get_lang["id"] ?? "";
	$lang_name = $get_lang["lang_name"] ?? "";
	$lang_icon = $get_lang["lang_icon"] ?? "";
	$lang_code = $get_lang["lang_code"] ?? "";
	$lang_dir = $get_lang["lang_dir"] ?? "";
	$lang_letters = $get_lang["lang_letters"] ?? "";
	$lang_visibility = $get_lang["lang_visibility"] ?? "";
	$lang_content = $get_lang["content_lang"] ?? "";
	?>
    <div class="dash-part-form">
        <div class="full-width">
            <form method="post" id="form_data" enctype="multipart/form-data">
               <p class="required_field">(إجباري)</p>
                <div class="half-width">
                    <label for="lang_name" class="required_field">إسم اللغة</label>
                    <input type="text" name="lang_name" placeholder="اللغة" value="<?php esc_html($lang_name); ?>"/>
                </div>
                <div class="half-width input-label-noline">
                    <label for="lang_icon">أيقونة اللغة</label>
                    <input type="hidden" name="lang_icon" id="lang_icon" value="<?php esc_html($lang_icon); ?>"/>
					<button class="upload-btn" data-input="#lang_icon"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
					<div class="clear"></div>
					<div class="img-preview">
						<img src="<?php echo get_thumb($get_lang["lang_icon"]); ?>" id="lang_icon_prv"/>
					</div>							
                </div>
                <div class="half-width">
                    <label for="lang_code" class="required_field">رمز اللغة (ex:ar,en)</label>
                    <input type="text" name="lang_code" placeholder="رمز اللغة" value="<?php esc_html($lang_code); ?>"/>
                </div>                
				<div class="half-width">
                    <label for="lang_code" class="required_field">ملف الترجمة(mo)</label>
                    
                    <div class="notices">
						<p>* يحب الحرص أن يكون إسم الملف نفسه رمز اللغة.</p>
						<p>أمثلة لكيف يجب تسمية الملف (en_US, en_UK, ar_MA, ...)</p>
					</div>
					<div class="clear"></div>
                </div>
                <div class="half-width">
                    <label for="dir" class="required_field">إتجاه</label>
                    <select name="dir">
                        <option value="rtl" <?php selected_val($lang_dir,"rtl"); ?> >RTL (من اليمين إلى اليسار)</option>
                        <option value="ltr" <?php selected_val($lang_dir,"ltr"); ?> >LTR (من اليسار إلى اليمين)</option>
                    </select>
                </div>
                <div class="half-width">
                    <label for="letters">حروف اللغة</label>
                    <select name="letters">
                        <option value=""></option>
                        <option value="arabic_letters" <?php selected_val($lang_letters,"arabic_letters"); ?> >حروف عربية</option>
                        <option value="latin_letters" <?php selected_val($lang_letters,"latin_letters"); ?> >حروف لاتينية</option>
                        <option value="kurdi_letters" <?php selected_val($lang_letters,"kurdi_letters"); ?> >حروف كردية</option>
                        <option value="farisi_letters" <?php selected_val($lang_letters,"farisi_letters"); ?> >حروف فارسية</option>
                    </select>
                </div>                
                <div class="full-width">
                    <label for="">إعتماد اللغة</label>
                        <div class="col-s-setting">
                            <input type="checkbox" name="lang_visibility" id="checkbox1" class="ios-toggle" <?php checked_val($lang_visibility,"on"); ?> >
                            <label for="checkbox1" class="checkbox-label"></label>
                        </div>
                    <div class="notices">
                    <p>ممكن إضافة اللغة بدون ظهورها للمستخدمين لسبب ما</p>
                    </div>
                </div>
                <div class="full-width">
                    <label for="">لغة محتوى</label>
                        <div class="col-s-setting">
                            <input type="checkbox" name="lang_content" id="checkbox2" class="ios-toggle" <?php checked_val($lang_content,"on"); ?> >
                            <label for="checkbox2" class="checkbox-label"></label>
                        </div>
                </div>

                <input type="hidden" name="method" value="add_edit_lang"/>
				<input type="hidden" name="action" value="<?php esc_html($action); ?>"/>
                <input type="hidden" name="lang_id" value="<?php esc_html($lang_id); ?>"/>
				<button id="submit_form" class="saveData">أضف</button>				
                </form>
        </div>
    </div>
    <script>
        
    </script>
	<?php endif; ?>