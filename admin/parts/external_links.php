<?php
$action = $_GET["action"] ?? "";
?>
    <div class="dash-part-form">
      <?php
        if(!$action):
        $get_external_links = get_external_links();
        ?>
		<form action="" method="get" id="form_filter">
			<div class="page-action">
				<div class="pull-right">
				<a href="dashboard/external_links?action=add" id="btn_link">إضافة</a>    
				</div>
			</div>
			<div class="clear"></div>
		</form>
		<div class="clear"></div>		
		<?php
		if($get_external_links) {
		?>
       <div class="table-responsive">
        <table class="table_parent">
            <tr>
                <th>عنوان الرابط</th>
                <th>الرابط</th>
                <th>الإجراءات</th>
            </tr>
			<?php 
			foreach($get_external_links as $links_val) {
				$link_id = $links_val["id"];
				$link_title = $links_val["link_title"];
				if($action == "add") {
					$link_title = "";
				}
			?>
            <tr>
                <td><?php echo $link_title; ?></td>
                <td><?php echo $links_val["link_url"]; ?></td>
                <td>
                    <table class="table_child">
                        <tr>
                            <td><button class="action_stg edit-st-btn open-url" title="تعديل" data-url="dashboard/external_links?action=edit&link_id=<?php esc_html($link_id); ?>"><i class="fa fa-cog"></i></button></td>    
                            <td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=external_links&id=<?php esc_html($link_id); ?>"><i class="fa fa-trash"></i></button></td> 
                        </tr>
                    </table>
                </td>
            </tr>
			<?php } ?>
        </table>
        </div>
        <?php
        }
        endif;
        ?>
       <?php if($action == "edit" or $action == "add") : 
		$link_id = $_GET["link_id"] ?? "";
		$get_link = get_external_links($link_id);
	   ?>
        <div class="half-width">
            <form method="post" id="form_data">
                <div class="full-width">
                    <label for="slug">عنوان الرابط</label>
                    <input type="text" name="link_title" value="<?php echo $get_link["link_title"]; ?>"/>
                </div>
                <div class="full-width">
                    <label for="link">الرابط</label>
                    <input type="text" name="link_url" value="<?php echo $get_link["link_url"]; ?>" placeholder="http://"/>
                </div>
                <div class="full-width">
                    <div class="r3-width">
                       <label for="link_target">قتح بصفحة جديدة</label>
                        <div class="col-s-setting">
                            <input type="checkbox" <?php checked_val($get_link["link_target"],"_blank"); ?> name="link_target" value="_blank" id="checkbox3" class="ios-toggle">
                            <label for="checkbox3" class="checkbox-label"></label>
                        </div>
                    </div>
                </div>
				<button id="submit_form" class="saveData">أضف</button>
				<input type="hidden" name="method" value="external_links"/>
                <input type="hidden" name="link_id" value="<?php esc_html($link_id); ?>"/>
                <input type="hidden" name="action" value="<?php echo $action; ?>"/>
            </form>
        </div>

        <?php endif; ?>
    </div>
