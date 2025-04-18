<?php
$role_title_jv = $role_permissions = "";
$action = $_GET["action"] ?? "";

if(!$action): 
?>
    <div class="dash-part-form">
    <div class="full-width">
        <div class="page-action">
        <div class="pull-right">
        <a href="dashboard/advanced_settings?section=roles&action=add" id="btn_link">إضافة</a>    
        </div>
        <div class="pull-left">
        <div class="line-elm-flex">
		<!--
            <div class="7r-width">
            <input name="q" placeholder="إبحث عن رتبة" type="text">
            </div>
            <div class="r3-width">
            <button form="form_filter" id="search_btn"><i class="fa fa-search"></i></button>
            </div>
			-->
        </div>
        </div>
        </div>
        <div class="clear"></div>
        <div class="full-width">
        <div class="table-responsive">
        <table class="table_parent">
        <tr>
        <th>الرتبة</th>    
        <th>الأيقونة</th>   
        <th>الإجراءات</th>    
        </tr> 
        <?php
        foreach(get_roles() as $get_roles_allK=>$get_roles_allV) {
            $role_icon = get_thumb($get_roles_allV["role_icon"]);
            $role_title = json_decode($get_roles_allV["role_title"]);
            // case 1 active role
            // case 2 unactive role
            $role_case  = $get_roles_allV["role_case"];
            if($role_case == 1) {
                $lock_btn_class = "fa-lock";
                $lock_action = "lock";
            }elseif($role_case == 2){
                $lock_btn_class = "fa-unlock";
                $lock_action = "unlock";
            }
        ?>
        <tr>
        <td><?php echo $role_title->{M_L}; ?></td>
        <td>
           <?php
            if(!empty($role_icon)) {
            ?>
            <img src="<?php esc_html($role_icon); ?>" class="meduim_icon"/>
            <?php
            }else{
                echo "-";
            }
            ?>
        </td>
        <td>
        <table class="table_child">
        <tr>  
        <td><button class="action_stg edit-st-btn open-url" data-url="dashboard/advanced_settings?section=roles&action=edit&role_id=<?php esc_html($get_roles_allV["id"]); ?>" id=""><i class="fa fa-cog"></i></button></td>
		<td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=roles&id=<?php esc_html($get_roles_allV["id"]); ?>"><i class="fa fa-trash"></i></button></td> 
        </tr>
        </table>    
        </td>
        </tr>
        <?php
        }
        ?>
        </table>
        </div>
        </div>
    </div>
<?php endif; ?>


	<?php 
	if($action == "edit" || $action == "add"): 
	$role_id = $_GET["role_id"] ?? "";
	$get_role = get_roles($role_id);
	$role_permissions = $get_role["role_permissions"] ?? "";
	$role_permissions = json_decode($role_permissions);
	$role_icon = $get_role["role_icon"];
	$role_title = $get_role["role_title"] ?? "";
	$post_per_day = $role_permissions->post_per_day ?? "";
	$auto_approve = $role_permissions->auto_approve ?? "";
	$publish_in = $role_permissions->publish_in ?? "";
	$access_adminpanel = $role_permissions->access_adminpanel ?? "";
	$read_sources = $role_permissions->read_sources ?? "";
	$move_multi_posts = $role_permissions->move_multi_posts ?? false;
	$upload = $role_permissions->upload ?? false;
	$upload_links = $role_permissions->upload_links ?? false;
	$cv_badge = $role_permissions->cv_badge ?? false;
	$delete_content = $role_permissions->delete ?? false;
	?>
    <div class="dash-part-form">
        <div class="full-width">
            <div class="half-width">
                <div class="add_new_role">
                    <h2>إضافة رتبة جديدة</h2>
                    <form id="form_data" method="POST">
                        <div class="full-width input-label-noline">
                            <label for="role_icon">أيقونة الرتبة</label>
                            <input type="hidden" id="role_icon" name="role_icon" value="<?php esc_html($role_icon); ?>"/>
							<button class="upload-btn" data-input="#role_icon"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
							<div class="clear"></div>
							<div class="img-preview">
								<img src="<?php echo get_thumb($role_icon); ?>" id="role_icon_prv"/>
							</div>							
                        </div>
                        <div class="full-width input-label-noline">
                            <label for="role_name">إسم الرتبة</label>
                            <?php multi_input_languages("role_name","text",json_decode($role_title)); ?>
                        </div>
                        <h4>صلاحيات العضو</h4>
                        <p>الصلاحيات التي سيتم إتاحتها للمستخدم الحامل للرتبة كمستخدم في الموقع</p>
                        <div class="r3-width">
                            <label for="post_per_day">المقالات في اليوم الواحد</label>
                            <input type="number" min="1" name="post_per_day" value="<?php echo $post_per_day; ?>"/>
                            <div class="col-s-setting">
                                <span>غير محدود</span>
                                <input type="checkbox" name="post_per_day_nolimit" id="checkbox2" class="ios-toggle" <?php checked_val($post_per_day,"unlimited"); ?>>
                                <label for="checkbox2" class="checkbox-label"></label>
                            </div>
                        </div>
                        <div class="r3-width">
                            <label for="move_multi_posts">إتاحة نقل المواضيع</label>
                            <div class="col-s-setting">
                                <span></span>
                                <input type="checkbox" name="move_multi_posts" id="checkbox11" class="ios-toggle" <?php checked_val($move_multi_posts,true); ?>>
                                <label for="checkbox11" class="checkbox-label"></label>
                            </div>
                        </div>
                        <div class="r3-width">
                            <label for="move_multi_posts">إتاحة رفع الملفات </label>
                            <div class="col-s-setting">
                                <span></span>
                                <input type="checkbox" name="upload" id="checkbox8" class="ios-toggle" <?php checked_val($upload,true); ?>>
                                <label for="checkbox8" class="checkbox-label"></label>
                            </div>
                        </div>
                        
                        <div class="full-width">
                            <label for="">الموافقة التلقائية</label>
                            <div class="col-s-setting">
                                <input type="checkbox" name="auto_approve" id="checkbox4" class="ios-toggle" <?php checked_val($auto_approve,"true"); ?>>
                                <label for="checkbox4" class="checkbox-label"></label>
                            </div>
                            <div class="notices">
                                <p>الموافقة تلقائيا على نشر المقالات الموثوقة. هذا الشرط لا يؤخد بعين الإعتبار إذا كان مسموح بالوصول إلى المقالات الموثوقة</p>
                            </div>
                        </div>                        
						<div class="full-width">
                            <label for="">الإطلاع على المصادر</label>
                            <div class="col-s-setting">
                                <input type="checkbox" name="read_sources" id="checkbox5" class="ios-toggle" <?php checked_val($read_sources,"true"); ?>>
                                <label for="checkbox5" class="checkbox-label"></label>
                            </div>
                        </div>
						<div class="full-width">
                            <label for="">إدراج روابط بالتحميل</label>
                            <div class="col-s-setting">
                                <input type="checkbox" name="upload_links" id="checkbox9" class="ios-toggle" <?php checked_val($upload_links,"on"); ?>>
                                <label for="checkbox9" class="checkbox-label"></label>
                            </div>
                        </div>
						<div class="full-width">
                            <label for="">الحصول على وسام السيرة الذاتية</label>
                            <div class="col-s-setting">
                                <input type="checkbox" name="cv_badge" id="checkbox10" class="ios-toggle" <?php checked_val($cv_badge,"on"); ?>>
                                <label for="checkbox10" class="checkbox-label"></label>
                            </div>
                        </div>
                        
                        <div class="full-width">
                            <label for="">صلاحيات النشر</label>
                            <p>إضغط CTRL + LEFT MOUSE BUTTON للإختيار عدة أقسام.</p>
                            <select name="publish_in[]" multiple style="min-height:200px;">
                                <?php
								foreach(get_all_taxonomies() as $website_departementk=>$website_departementv) {
									?>
									<option value="<?php esc_html($website_departementv["taxo_type"]); ?>" <?php if(@in_array($website_departementv["taxo_type"],$publish_in)) { echo 'selected="true"'; } ?>><?php esc_html($website_departementv["taxo_type"]); ?></option>
									<?php
								}
                                ?>
                            </select>
                            <p>الأقسام التي سيمكن للعضو الحامل للرتبة النشر بها</p>
                            <div class="col-s-setting">
                                <span>الكل</span>
                                <input type="checkbox" name="publish_in_all" id="checkbox7" class="ios-toggle" <?php checked_val($publish_in,"all"); ?>>
                                <label for="checkbox7" class="checkbox-label"></label>
                            </div>
                        </div>
                        <h4>صلاحيات الأدمن</h4>
                        <p>الصلاحيات التي سيتم إتاحتها للمستخدم الحامل للرتبة كأدمن في الموقع</p>
                        <div class="full-width">
                            <label for="">الوصول إلى لوحة الأدمن</label>
                            <div class="col-s-setting">
                                <input type="checkbox" name="access_adminpanel" id="checkbox6" class="ios-toggle" <?php checked_val($access_adminpanel,true); ?>>
                                <label for="checkbox6" class="checkbox-label"></label>
                            </div>
                            <div class="notices">
                              <p>إتاحة هذا الخيار ستتيح للعضو الحاصل على الرتبة بالوصول إلى خاصيات لوحة تحكم على حسب ما سيحدد له في ملفه</p>
                            </div>
                        </div>
                        <div class="full-width">
                            <label for="">حدف المحتوى الذي يحق به الوصول إليه</label>
                            <div class="col-s-setting">
                                <input type="checkbox" name="delete" id="checkbox12" class="ios-toggle" <?php checked_val($delete_content,true); ?>>
                                <label for="checkbox12" class="checkbox-label"></label>
                            </div>
                        </div>                        
						<button id="submit_form" class="saveData">أضف</button>	
						<input type="hidden" name="method" value="roles"/>
						<input type="hidden" name="action" value="<?php esc_html($action); ?>"/>
						<input type="hidden" name="role_id" value="<?php esc_html($role_id); ?>"/>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>