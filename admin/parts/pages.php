<?php
$action = $_GET["action"] ?? "";
?>
    <div class="dash-part-form">
        <div class="full-width">
           <?php
            if(!$action) {
			$get_pages = $dsql->dsql()->table('pages');
			if($get_pages) {
            ?>
            <div class="table-responsive">
            <table class="table_parent">
            <tr>
            <th></th>    
            <th>إسم الصفحة</th>   
            <th>اللغة</th>   
            <th>الإجراءات</th>    
            </tr> 
            <?php
            foreach($get_pages as $get_pages_allK=>$get_pages_allV) {
                $page_id = $get_pages_allV["id"];
                $page_title = $get_pages_allV["page_title"];
                $page_lang = $get_pages_allV["page_lang"];
                $page_case= $get_pages_allV["page_case"];
				if($page_case == "on") {
					$lock_action = "lock";
					$lock_action_tooltip = "إغلاق";
					$lock_btn_class = "fa-lock-open";
				}elseif($page_case == "off") {
					$lock_action = "unlock";
					$lock_action_tooltip = "فتح";
					$lock_btn_class = "fa-lock";
				}
            ?>
            <tr>
                <td></td>
                <td><?php esc_html($page_title); ?></td>
                <td><?php esc_html($page_lang); ?></td>
                <td>
                    <table class="table_child">
                    <tr>  
                    <td><button class="action_stg edit-st-btn open-url" data-url="dashboard/pages?action=edit&page_id=<?php esc_html($page_id); ?>" id=""><i class="fa fa-cog"></i></button></td>
                    <td><button class="action_stg lock-btn updateData" title="<?php echo $lock_action_tooltip; ?>" data-method="un_lock_page_ajax" data-id="<?php esc_html($page_id); ?>"><i class="fa <?php echo $lock_btn_class; ?>"></i></button></td>      
                    <td><button class="action_stg delete-btn open-url" data-url="dashboard/delete?type=page&id=<?php esc_html($page_id); ?>" title="حدف"><i class="fa fa-trash"></i></button></td> 
                    </tr>
                    </table>                     
                </td>
            </tr>
            <?php     
            }
			?>
			</table>
            </div>
			<?php
            }else{
				echo "لاتوجد أي صفحات حاليا";
			}
            }
            if($action == "edit" or $action == "add"): 
			$page_id = $_GET["page_id"] ?? "";
			$get_page = get_pages($page_id,false,false);
			$load_no_translate_pages = get_pages(null,false);
			$page_title = $get_page["page_title"] ?? "";
			$page_lang = $get_page["page_lang"] ?? "";
			$page_translate = $get_page["page_translate"] ?? "";
			$page_content = $get_page["page_content"] ?? "";

            ?>
            <form method="post" id="form_data">
                <div class="full-width input-label-noline">
                    <label for="title">عنوان الصفحة</label>
                    <input type="text" name="page_title" placeholder="عنوان الصفحة" value="<?php esc_html($page_title); ?>"/>
                </div>
                <div class="full-width input-label-noline">
                    <label for="lang">لغة الصفحة</label>
                    <select name="page_lang">
                        <?php
                        foreach(get_langs() as $lang_key=>$lang_val) {
                            ?>
                            <option value="<?php esc_html($lang_val["lang_code"]); ?>" <?php selected_val($lang_val["lang_code"],$page_lang); ?> ><?php esc_html($lang_val["lang_name"]); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="clear"></div>
                <div class="notices">
                <p>إذا كانت هذه الصفحة ترجمة لأحدى الصفحات الموجودة المرجو إختيار هذه الصفحة من الأسفل</p>   
                </div>
                <div class="clear"></div>
                <div class="r3-width">
                    <select name="page_translate">
                        <option selected="" value="0"></option>
                        <?php
                        foreach($load_no_translate_pages as $no_translate_pk=>$no_translate_pv) {
                            $no_translate_p_id = $no_translate_pv["id"];
                            $no_translate_p_title = $no_translate_pv["page_title"];
                            ?>
                            <option <?php selected_val($no_translate_p_id,$page_translate); ?> value="<?php echo $no_translate_p_id; ?>"><?php esc_html($no_translate_p_title); ?></option>
                            <?php
                        }
                        ?>
                        
                    </select>
                </div>
                 <div class="clear"></div>
                <div class="notices">
                <ul>
                    <li>تعمل هذه الخاصية على ترجمة الصفحة عند تغيير اللغة من طرف المستخدم</li>
                    <li><em>ماذا يحدث عندما لا تكون ترجمة للصفحة بلغة المستخدم ؟</em></li>
                    <li>يتم إظهار الصفحة الأصلية</li>
                </ul> 
                </div>
                <div class="full-width input-label-noline">
                   <label for="content">محتوى الصفحة</label>
                    <textarea name="page_content" class="tinymce-area"><?php echo $page_content; ?></textarea>
                </div>
				<input type="hidden" name="method" value="pages"/>
				<input type="hidden" name="action" value="<?php echo $action; ?>"/>
				<input type="hidden" name="page_id" value="<?php esc_html($page_id); ?>"/>
				<button id="submit_form" class="saveData">أضف</button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>