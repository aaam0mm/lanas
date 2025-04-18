<?php
require_once 'init.php';
if(is_login_in()) {
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<title><?php echo _t("إسترجاع كلمة المرور"); ?></title>
	</head>
	<body class="body-sign-page">
		<?php get_header(); ?>
		<div class="my-5"></div>
		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="col-lg-6 col-10 sign-form bg-white px-0">
					<form method="POST">
						<div class="form-title text-center text-white py-3"><h4><i class="fas fa-user"></i>&nbsp;<?php echo _t("إسترجاع كلمة المرور"); ?></h4></div>
						<div class="my-5"></div>
						<div class="form-body px-md-5 px-3">
							<div class="form-group">
								<label for="user_name_email"><?php echo _t("البريد الإلكتروني"); ?></label>
								<input type="email" id="user_email" name="email" class="form-control rounded-0 form-control-lg"/>
								<div id="user_email_error_txt" class="invalid-feedback"></div>
								<small class="text-muted form-text"><?php echo _t("أدخل البريد الإلكتروني الذي سجلت به لكي نرسل لك رابط إعادة تعيين كلمة المرور"); ?></small>
							</div>

							<div class="form-group sign-btns">
							<button class="btn btn-lg btn-sign-green rounded-0 form-control"><?php echo _t("أرسل"); ?></button>
							</div>
							<div class="form-group">
								<small><?php echo _t("ليس لديك حساب ?"); ?> <a href="signup.php" class="color-link"><?php echo _t("سجل حساب الآن"); ?></a></small>
							</div>
						</div>
						<input type="hidden" name="method" value="user_ajax"/>
						<input type="hidden" name="request" value="forget_password"/>
					</form>
				</div>
			</div>
		</div>
		<div class="my-5"></div>
		<?php user_end_scripts(); ?>
		<?php get_footer(); ?>
	</body>
	
</html>