<?php
require_once 'init.php';
if(is_login_in()) {
	exit;
}
$key = $_GET["key"] ?? "";
if(mb_strlen($key) !== 64) {
	exit;
}


if(!$dsql->dsql()->table('user_meta')->where('meta_key','recover_key')->where('meta_value', $key)->getRow()) {
	exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<title><?php echo _t("تعيين كلمة مرور جديدة"); ?></title>
	</head>
	<body class="body-sign-page">
		<?php get_header(); ?>
		<div class="my-5"></div>
		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="col-lg-6 col-10 sign-form bg-white px-0">
					<form method="POST">
						<div class="form-title text-center text-white py-3"><h4><i class="fas fa-user"></i>&nbsp;<?php echo _t("تعيين كلمة مرور جديدة"); ?></h4></div>
						<div class="my-5"></div>
						<div class="form-body px-md-5 px-3">
							<div class="form-group">
								<label for="user_pwd"><?php echo _t("كلمة المرور الجديدة"); ?></label>
								<input type="password" id="user_pwd" name="user_pwd" class="form-control rounded-0 form-control-lg"/>
								<div id="user_pwd_error_txt" class="invalid-feedback"></div>
							</div>							
							<div class="form-group">
								<label for="user_re_pwd"><?php echo _t("تأكيد كلمة المرور"); ?></label>
								<input type="password" id="user_re_pwd" name="user_re_pwd" class="form-control rounded-0 form-control-lg"/>
								<div id="user_re_pwd_error_txt" class="invalid-feedback"></div>
							</div>

							<div class="form-group sign-btns">
							<button class="btn btn-lg btn-sign-green rounded-0 form-control"><?php echo _t("تغيير"); ?></button>
							</div>
						</div>
						<input type="hidden" name="recover_key" value="<?php esc_html($key); ?>"/>
						<input type="hidden" name="method" value="user_ajax"/>
						<input type="hidden" name="request" value="reset_password"/>
					</form>
				</div>
			</div>
		</div>
		<div class="my-5"></div>
		<?php user_end_scripts(); ?>
		<?php get_footer(); ?>
	</body>
</html>