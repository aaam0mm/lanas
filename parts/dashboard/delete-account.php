<?php
/**
 * delete-account.php
 * User dashboard page
 */
 $delete_account_code = get_user_meta(get_current_user_info()->id,"delete_account_code");
?>
<div class="user-dashboard-delete-account position-relative">
	<div class="my-5"></div>
	<p class="alert alert-danger"><?php echo _t("حدف الحساب خطوة لا يمكن التراجع عنها"); ?></p>
	<?php if($delete_account_code): ?>
	<form action="" method="POST">
	    <div class="form-group">
	        <input type="text" name="delete_code" placeholder="<?php echo _t("الرمز"); ?>" id="delete-code" class="form-control rounded-0"/>
	        <small class="form-text text-muted"><?php echo _t("الرمز الذي وصلت على البريد الإلكتروني"); ?></small>
	        <div class="invalid-feedback" id="delete-code-feedback"></div>
	    </div>
	    <div class="form-group">
	        <input type="password" name="pwd" class="form-control rounded-0" id="pwd" placeholder="<?php echo _t("كلمة السر"); ?>"/>
	        <div class="invalid-feedback" id="pwd-feedback"></div>
	    </div>
	    <div class="form-group">
	        <button class="btn btn-danger delete-account"><i class="fas fa-times"></i>&nbsp;<?php echo _t("حدف الحساب"); ?></button>
	    </div>
	    <div class="form-group">
	        <?php echo _t("لم يصل رمز التفعيل ؟"); ?><a class="re-send-delete-code" href="#"><?php echo _t("أعد إرسال"); ?></a>
	    </div>
	    <input type="hidden" name="method" value="delete_account"/>
	</form>
	<?php else: ?> 
	<div class="d-flex justify-content-center">
		<button class="btn btn-danger delete-account-request"><i class="fas fa-times"></i>&nbsp;<?php echo _t("حدف الحساب"); ?></button>
	</div>
	<?php endif; ?>
	<small class="text-muted"><?php echo _t("هذه الخطوة لا يمكن التراجع عنها"); ?>. <a href="<?php echo get_terms_conditions_page()["link"]; ?>" class="color-link"><?php echo get_terms_conditions_page()["text"]; ?></a></small>
</div>