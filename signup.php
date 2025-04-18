<?php
require_once 'init.php';
if (is_login_in()) {
	exit;
}
?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<title><?php echo _t("تسجيل حساب جديد"); ?></title>
</head>

<body class="body-sign-page">
	<?php get_header(); ?>
	<div class="my-5"></div>
	<div class="container">
		<div class="row d-flex justify-content-center">
			<?php if (get_option("site_register") == "on"): ?>
				<div class="col-lg-6 col-10 sign-form bg-white px-0">
					<form method="POST">
						<div class="form-title text-center text-white py-3">
							<h4><i class="fas fa-user-plus"></i>&nbsp;<?php echo _t("تسجيل حساب جديد"); ?></h4>
						</div>
						<div class="my-5"></div>
						<div class="form-body px-md-5 px-3">
							<div class="form-group">
								<label for="user_name"><?php echo _t("إسمك مع (إسم الاب أو اللقب)"); ?></label>
								<input type="text" id="user_name" name="user_name" class="rounded-0 form-control form-control-lg" />
								<div id="user_name_error_txt" class="invalid-feedback"></div>
							</div>
							<div class="form-group">
								<label for="username"><?php echo _t("إسم المستخدم بحروف انجليزية"); ?></label>
								<input type="text" id="username" name="username" class="form-control rounded-0 form-control-lg" />
								<div id="username_error_txt" class="invalid-feedback"></div>
							</div>
							<div class="form-group">
								<label for="user_email"><?php echo _t("البريد الإلكتروني"); ?></label>
								<input type="text" id="user_email" name="user_email" class="form-control rounded-0 form-control-lg" />
								<div id="user_email_error_txt" class="invalid-feedback"></div>
							</div>
							<div class="form-group form-row">
								<div class="col-lg-4 mb-3">
									<label for="user_gender"><?php echo _t("الجنس"); ?></label>
									<select id="user_gender" name="user_gender" class="form-control rounded-0 form-control-lg custom-select">
										<option selected="true" disabled="true"><?php echo _t("إختر الجنس"); ?></option>
										<option value="male"><?php echo _t("ذكر"); ?></option>
										<option value="female"><?php echo _t("أنثى"); ?></option>
									</select>
									<div id="user_gender_error_txt" class="invalid-feedback"></div>
								</div>
								<div class="col-lg-8">
									<label for=""><?php echo _t("تاريخ الميلاد"); ?></label>
									<div class="input-group">
										<select id="user_birth_day" name="user_birth_day" class="form-control rounded-0 form-control-lg custom-select">
											<option selected="true" disabled="true"><?php echo _t("اليوم"); ?></option>
											<?php foreach (generate_nums(1, 31) as $day): ?>
												<option value="<?php esc_html($day); ?>"><?php esc_html($day); ?></option>
											<?php endforeach; ?>
										</select>
										<select id="user_birth_month" name="user_birth_month" class="form-control rounded-0 form-control-lg custom-select">
											<option selected="true" disabled="true"><?php echo _t("الشهر"); ?></option>
											<?php foreach (months_names() as $month_num => $month_name): ?>
												<option value="<?php esc_html($month_num); ?>"><?php esc_html($month_name); ?></option>
											<?php endforeach; ?>
										</select>
										<select id="user_birth_year" name="user_birth_year" class="form-control rounded-0 form-control-lg custom-select">
											<option selected="true" disabled="true"><?php echo _t("السنة"); ?></option>
											<?php foreach (generate_nums(1940, date("Y"), "desc") as $year): ?>
												<option value="<?php esc_html($year); ?>"><?php esc_html($year); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="user_country"><?php echo _t("الدولة"); ?></label>
								<select id="user_country" name="user_country" class="form-control rounded-0 form-control-lg custom-select">
									<?php foreach (sort_json(get_countries(), "country_name", "asc") as $country): ?>
										<option value="<?php esc_html($country["country_code"]); ?>"><?php esc_html($country["country_name"]); ?></option>
									<?php endforeach; ?>
								</select>
								<div id="user_country_error_txt" class="invalid-feedback"></div>
							</div>
							<div class="form-group form-row">
								<div class="col-md-6 mb-3">
									<label for="user_pwd"><?php echo _t("كلمة المرور"); ?></label>
									<input type="password" id="user_pwd" name="user_pwd" class="form-control rounded-0 form-control-lg" />
									<div id="user_pwd_error_txt" class="invalid-feedback"></div>
								</div>
								<div class="col-md-6">
									<label for="user_re_pwd"><?php echo _t("تأكيد كلمة المرور"); ?></label>
									<input type="password" id="user_re_pwd" name="user_re_pwd" class="form-control rounded-0 form-control-lg" />
									<div id="user_re_pwd_error_txt" class="invalid-feedback"></div>
								</div>
							</div>
							<div class="form-group sign-btns">
								<button class="btn btn-lg btn-sign-green rounded-0"><?php echo _t("تسجيل"); ?></button>
								<a href="<?php echo siteurl(); ?>/signin.php" class="btn btn-lg btn-sign-blue rounded-0"><?php echo _t("دخول"); ?></a>
							</div>
						</div>
						<input type="hidden" name="method" value="user_signup_ajax" />
						<input type="hidden" name="request" value="signup" />
					</form>
				</div>
			<?php else: ?>
				<span class="alert alert-danger"><?php echo _("التسجيل معطل حاليا") ?></span>
			<?php endif; ?>
		</div>
	</div>
	<div class="my-5"></div>
	<?php user_end_scripts(); ?>
	<?php get_footer(); ?>
</body>

</html>