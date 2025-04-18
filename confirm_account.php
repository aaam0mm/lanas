<?php
require_once 'init.php';
if(!is_login_in()) {
	exit;
}

if(get_current_user_info()->user_status != "email_verify") {
    exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<title><?php echo _t("تفعيل الحساب"); ?></title>
	</head>
	<body class="body-sign-page">
		<?php get_header(); ?>
		<div class="my-5"></div>
		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="col-lg-6 col-10 sign-form bg-white px-0">
					<form method="POST">
						<div class="form-title text-center text-white py-3"><h4><i class="fas fa-user"></i>&nbsp;<?php echo _t("تفعيل الحساب"); ?></h4></div>
						<div class="my-5"></div>
						<div class="form-body px-md-5 px-3">
							<div class="form-group">
								<label for="verify_code"><?php echo _t("رمز التفعيل"); ?></label>
								<input type="text" id="verify_code" name="verify_code" class="form-control rounded-0 form-control-lg"/>
								<div id="verify_code_error_txt" class="invalid-feedback"></div>
								<small class="text-muted form-text"><?php echo _t("تم إرسال رمز التفعيل إلى بريدك إلكتروني"); ?></small>
							</div>

							<div class="form-group sign-btns">
								<button class="btn btn-lg btn-sign-green rounded-0 form-control"><?php echo _t("تفعيل"); ?></button>
							</div>
						</div>
						<input type="hidden" name="method" value="user_ajax"/>
						<input type="hidden" name="request" value="verify_account"/>
					</form>
				</div>
			</div>
		</div>
		<div class="my-5"></div>
		<?php user_end_scripts(); ?>
		<?php get_footer(); ?>
	</body>
	
</html>