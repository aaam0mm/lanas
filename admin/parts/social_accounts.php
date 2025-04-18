<?php
$action = $_GET["action"] ?? "";
$plat = $_GET["plat"] ?? "";
$plat_icon = $plat_link = "";

if((!$action) || $action == "edit") {
    $get_socialaccounts_settings = @unserialize(get_settings("s_c_settings"));
    if($action == "edit" && $plat) {
        $plat_info = $get_socialaccounts_settings[$plat];
        if($plat_info) {
			$plat_fa_icon = "";
            $plat_icon = (int) $plat_info["icon"];
			if(empty($plat_icon)) {
				$plat_fa_icon = $plat_info["icon"];
			}
            $plat_link = $plat_info["account_link"];
        }else{
            exit();
        }
    }
}
/*  */
?>
    <div class="dash-part-form">
        <div class="full-width">

        <?php if($action == "edit" or $action == "add"): ?>
        <form method="post" id="form_data">
			<div class="half-width">
				<div class="notices">
					<p>قم بالدخول على الرابط التالي : <a href="https://fontawesome.com/icons/" target="_blank">Fontawesome</a></p>
					<p>إختر أيقونة و أكتب إسم الأيقونة مثل</p>
					<p>fab fa-iconname</p>
				</div>
				<div class="clear"></div>
					<input type="text" name="socialaccounts_settings[icon]" value="<?php esc_html($plat_fa_icon); ?>"/>
				<div class="clear"></div>
				<div class="notices">
					<p>قم بتحميل صورة من الحاسوب</p>
					<p>الصيغة المفضلة svg</p>
				</div>
				<div class="clear"></div>
				<input type="hidden" id="plat_icon" name="plat_icon" value="<?php esc_html($plat_icon); ?>"/>
				<button class="upload-btn" data-input="#plat_icon"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
				<div class="clear"></div>
				<div class="img-preview">
					<img src="<?php esc_html(get_thumb($plat_icon)); ?>" id="plat_icon_prv"/>
				</div>							

				<div class="clear"></div>
                <div class="plat_url">
                   <label for="account_link">رابط الحساب</label>
                    <input type="text" name="socialaccounts_settings[account_link]" placeholder="http://" value="<?php esc_html($plat_link); ?>" />
                </div>
			</div>
			 <button id="submit_form" class="saveData">أضف</button>
            <input type="hidden" name="method" value="social_accounts"/>
            <input type="hidden" name="plat" value="<?php esc_html($plat); ?>"/>
        </form>
        <div class="clear"></div>
        <script>
        $(document).ready(function() {
            // get className from html string to display icon name
           var  html_icon_code = $('<?php echo $plat_icon; ?>');
            var icon_fa_code = html_icon_code.attr("class").split(' ')[1];
            $("[name='socialaccounts_settings[icon]']").val(icon_fa_code);
        });
        </script>
        <?php
        endif;
        if(!$action):     
        ?>	
        <div class="clear"></div>
        <div class="page-action">
        <div class="pull-right">
        <a href="dashboard/social_accounts#" id="btn_link" class="add_new_s_c">إضافة</a>    
        </div>
        </div>
		<div class="clear"></div>
        <?php
        if($get_socialaccounts_settings) {    
        ?>
        <div class="table-responsive">
        <table class="table_parent">
            <tbody>
               <tr>
                <th>إسم المنصة</th>
                <th>أيقونة</th>
                <th>الإجراءات</th>
                </tr>
                <?php 
            foreach($get_socialaccounts_settings as $s_c_k=>$s_c_v) {
                $plat_icon = $s_c_v["icon"];
                ?>
                <tr>
                    <td><?php esc_html($s_c_k); ?></td>
                    <td><?php echo $plat_icon; ?></td>
                    <td>
        <table class="table_child">
        <tr>  
        <td><button class="action_stg edit-st-btn open-url" title="تعديل" data-url="dashboard/social_accounts?action=edit&plat=<?php esc_html($s_c_k); ?>" id=""><i class="fa fa-cog"></i></button></td>    
        <td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete&type=social_account&id=<?php esc_html($s_c_k); ?>" id=""><i class="fa fa-trash"></i></button></td> 
        </tr>
        </table>
                </td>
                <?php
            }
                ?>
            </tbody>
        </table>
        </div>
        <?php
        }else{
			?>
			<div class="no_posts">لاتوجد أي منصات حاليا.</div>
			<?php
		}
        ?>
        <script>
        $(document).ready(function() {
           $(".add_new_s_c").click(function() {
              var plat = prompt("أدخل إسم المنصة (eg:facebook,twitter,...)"); 
               if(plat) {
                   window.location.href = location.pathname+"?action=add&plat="+plat;
               }
           });
        });  
        </script>
        <?php
        endif; 
        ?>
        </div>
    </div>