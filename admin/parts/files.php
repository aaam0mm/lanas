<?php
$per_page = $_GET["per_page"] ?? 50;
$upload_by = $_GET["upload_by"] ?? "";
$upload_date = $_GET["upload_date"] ?? "";
$upload_post_id = $_GET["upload_post_id"] ?? "";
$file_cat = $_GET['attachment_category'] ?? "";
$action = $_GET["action"] ?? "";
$get_files_categories = get_files_categories();
$cat_title = "";
if($action == "edit" || $section == "categories") {
	
	$cat_id = $_GET["cat_id"] ?? null;

	$get_categories_gallery = get_files_categories($cat_id);
	$cat_title = $get_categories_gallery[0]["category_title"];
    $cat_title = json_encode($cat_title);
}

$args = [
    "file_type__not" => ["user_attachment","admin_attachment"],
    "limit" => $per_page
];

if($upload_by) {
    $args['upload_user'] = $upload_by;
}

if($upload_date) {
    $args['upload_date'] = $upload_date;
}

if($upload_post_id) {
    $args['post_id'] = $upload_post_id;
}
if($file_cat) {
    $args['file_cat'] = $file_cat;
}
$get_gallery = get_files($args,'desc',null);
$count_files = count_last_query();
?>
   <?php if($action == "add" or $action == "edit") { ?>

    <div class="model-main model-gcat-msg">
        <div class="model">
            <div class="model-content">
                <div class="model-ver-a">
                    <div class="model-top-title">
                        <h3>تصنيفات</h3>
                    </div>
                    <div class="model-content-e">
                    <div class="clear"></div>
                    <form action="" method="post">
                        <?php multi_input_languages("files_cats","text",json_decode($cat_title)); ?>
                        <input type="hidden" name="action" value="<?php esc_html($action); ?>"/>
                        <input type="hidden" name="cat_id" value="<?php esc_html($cat_id); ?>"/>
                        <input type="hidden" name="method" value="add_edit_files_category"/>
						<div class="model-settings-btns">
						   <div class="model-btns">
								<button class="confirm-model saveData">تطبيق</button>
								<button class="cancel-model">إلغاء</button>
							</div>
						</div>
					</form>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <?php } ?>
    <div class="dash-part-form">
    <?php if(!$section) { ?>
    <div class="full-width">
        <div class="page-action">
           <form action="" method="get" id="form_filter">
            <div class="panel_filter">
            <div class="pull-right line-elm-flex">
				<div class="up-upload-input">
					<button class="upload-btn" data-input="#media_library"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
				</div>
				<div>
					<select name="attachment_category" class="attachment_category_class">
						<option selected="true" disabled="true"><?php echo _t("إختر صنف الملف"); ?></option>
						<?php foreach($get_files_categories as $cat): ?>
						<option value="<?php esc_html($cat["id"]); ?>"><?php esc_html( get_files_category_name($cat["category_title"]) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
            </div>
            <div class="pull-left line-elm-flex">
            <div>
            <select name="per_page" class="on_change_submit">
                <option value="50" <?php if($per_page == 50) { echo 'selected="true"'; } ?> >50</option>
                <option value="100" <?php if($per_page == 100) { echo 'selected="true"'; } ?> >100</option>
                <option value="250" <?php if($per_page == 250) { echo 'selected="true"'; } ?> >250</option>
            </select>   
            </div>
            <div>
					<select name="attachment_category" class="on_change_submit">
						<option selected="true" disabled="true"><?php echo _t("إختر صنف الملف"); ?></option>
						<?php foreach($get_files_categories as $cat): ?>
						<option <?php selected_val($cat["id"],$file_cat); ?> value="<?php esc_html($cat["id"]); ?>"><?php esc_html( get_files_category_name($cat["category_title"]) ); ?></option>
						<?php endforeach; ?>
					</select>
            </div>            
            </div>
            </div>
			<input type="hidden" id="upload_by_param" name="upload_by" value="<?php esc_html($upload_by); ?>"/>
			<input type="hidden" id="upload_post_id_param" name="upload_post_id" value="<?php esc_html($upload_post_id); ?>"/>
			<input type="hidden" id="upload_date_param" name="upload_date" value="<?php esc_html($upload_date); ?>"/>
            </form>
        </div>
        <div class="clear"></div>
			<div class="panel_filter">
				<form method="get" action="dashboard/delete" id="action-form">
					<div class="pull-right r3-width">
						<div class="line-elm-flex">
							<div class="r3-width">
								<select name="action" class="">
									<option value="delete">حدف</option>
								</select>
							</div>
							<div class="r3-width">
								<input type="submit" value="تنفيذ" class="btn_action submit-action"/>
							</div>
						</div>
						<input type="hidden" name="target" value="files"/>
						<input name="method" value="multi_action" type="hidden">
					</div>
				</form>
			</div>
			<div class="clear"></div>

        <div class="full-width">
		<ul class="filters_param">
		<?php
		if($upload_by) {
			?>
			<li><a href="dashboard/users?user_id=<?php echo $upload_by; ?>">حساب صاحب المحتوى</a></li>
			<?php
		}
		if($upload_date) {
			?>
			<li><?php echo $upload_date; ?></li>
			<?php
		}
		if($upload_post_id) {
			?>
			<li><a href="dashboard/posts?post_id=<?php echo $upload_post_id; ?>">المقال المنشور به المحتوى</a></li>
			<?php
		}
		?>
		</ul>
			<?php if($get_gallery) { ?>
           <div class="table-responsive">
            <table class="table_parent">
              <tr>
                  <th><input type='checkbox' class="select-checkbox-all check-all-multi"/></th>
                  <th></th>
                  <th>الصورة</th>
                  <th>رفع من طرف</th>
                  <th>تاريخ الرفع</th>
                  <th>الإجراءات</th>
              </tr>
                <?php
                foreach($get_gallery as $gallery) {
                    $gallery_id = $gallery["id"];
                    $gallery_upload_date = $gallery["file_upload_date"];
                    $upload_user_name = get_user_field($gallery["file_uploader"],"user_name");
                    $upload_user_id = get_user_field($gallery["file_uploader"],"id");
                    if(strpos($gallery["mime_type"],"image") === false) {
                        $thumb = siteurl()."/assets/images/icons/files_format/pdf.png";
                    }else{
                        $thumb = get_thumb($gallery_id);
                    }
                    ?>
                    <tr>
                    <td><input type='checkbox' class="select-checkbox check-box-action" data-id="<?php esc_html($gallery_id); ?>"/></td>
                    <td><?php esc_html($gallery_id); ?></td>
                    <td>
					<img src="<?php echo $thumb; ?>"/>
					</td>    
                    <td>
					<a href="<?php echo siteurl()."/user/".$upload_user_id; ?>" class="filter_link" data-value="<?php esc_html($upload_user_id); ?>" data-input-hidden="#upload_by_param">
					<?php esc_html($upload_user_name); ?>
					</a>
					</td>    
                    <td>
					<span>
					<?php esc_html($gallery_upload_date); ?>
					</span>
					</td>
                    <td>
                        <table class="child_table">
                        <tr>  
                        <td><button class="action_stg delete-btn open-url" data-url="dashboard/delete?type=files&id=<?php esc_html($gallery_id); ?>" title="حدف"><i class="fa fa-trash"></i></button></td> 
                        </tr>                          
                        </table>
                    </td>    
                    </tr>
                    <?php
                }
                ?>
            </table>
            </div>
           <?php get_pagination($count_files,$per_page); ?>
			<?php 
			}else{
				?>
				<p class="no_posts">لاتوجد أي صور حاليا.</p>
				<?php
			}			
			?>
        </div>
        </div>
        <?php }elseif($section == "categories") { ?>
            <div class="table-responsive">
            <table class="table_parent">
                <tr>
                    <th></th>
                    <th>إسم القسم</th>
                    <th>الإجراءات</th>
                </tr>
                <?php 
                foreach($get_categories_gallery as $gallery_cat) { 
                    $cat_id = $gallery_cat["id"];
					$main_lang = M_L;
                    $cat_title = json_decode($gallery_cat["category_title"])->$main_lang;
                ?>
                
                <tr>
                    <td></td>
                    <td><?php esc_html($cat_title); ?></td>
                    <td>
                        <table class="table_child">
                        <tr> 
                        <td><button class="action_stg edit-st-btn open-url" title="تعديل" data-url="dashboard/files?action=edit&cat_id=<?php esc_html($cat_id); ?>"><i class="fa fa-cog"></i></button></td>
                        <td>
                        <button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=file_category&id=<?php esc_html($cat_id); ?>"><i class="fa fa-trash"></i></button>
                        </td> 
                        </tr>
                        </table>
                    </td>
                </tr>
                <?php } ?>
            </table>
            </div>
        <?php } ?>
    </div>
	<script>
	$(document).ready(function() {
		$(".filter_link").click(function(e) {
			e.preventDefault();
			$($(this).data("input-hidden")).val($(this).data("value"));
			$("#form_filter").submit();
		});
	});
	</script>