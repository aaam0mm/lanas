<?php
require_once 'init.php';
$current_user = get_current_user_info();
$user_name = $current_user->user_name ?? false;
$user_email = $current_user->user_email ?? false;
?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<title><?php echo _t("إتصل بنا"); ?></title>
	</head>
	<body class="body-sign-page">
		<?php get_header(); ?>
		<div class="my-5"></div>
		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="col-lg-6 col-10 sign-form bg-white px-0">
					<form method="POST" id="contact-form">
						<div class="form-title text-center text-white py-3"><h4><i class="fas fa-envelope"></i>&nbsp;<?php echo _t("إتصل بنا"); ?></h4></div>
						<div class="my-5"></div>
						<div class="form-body px-md-5 px-3">
						
							<div class="form-group">
								<label for="contact_name"><?php echo _t("إسم الثنائي أو الثلاثي"); ?><sup class="text-danger">*</sup></label>
								<input type="text" id="contact_name" name="contact_name" value="<?php esc_html($user_name); ?>" <?php if($user_name) { echo 'readonly="true"'; } ?> class="form-control rounded-0 form-control-lg"/>
								<div id="contact_name_error_txt" class="invalid-feedback"></div>
							</div>		
							
							<div class="form-group">
								<label for="contact_email"><?php echo _t("البريد الإلكتروني"); ?><sup class="text-danger">*</sup></label>
								<input type="email" id="contact_email" name="contact_email" value="<?php esc_html($user_email); ?>" <?php if($user_email) { echo 'readonly="true"'; } ?> class="form-control rounded-0 form-control-lg"/>
								<div id="contact_email_error_txt" class="invalid-feedback"></div>
							</div>
							
							<div class="form-group">
								<label for="contact_subject"><?php echo _t("سبب المراسلة"); ?><sup class="text-danger">*</sup></label>
								<input type="text" id="contact_subject" name="contact_subject" class="form-control rounded-0 form-control-lg"/>
								<div id="contact_subject_error_txt" class="invalid-feedback"></div>
							</div>							
							
							<div class="form-group">
								<label for="contact_message"><?php echo _t("الرسالة"); ?><sup class="text-danger">*</sup></label>
								<textarea id="contact_message" name="contact_message" class="form-control rounded-0 form-control-lg"></textarea>
								<div id="contact_message_error_txt" class="invalid-feedback"></div>
							</div>
							
							<div class="form-group">
							<button class="btn btn-lg btn-primary rounded-0 btn-contact"><?php echo _t("أرسل");?></button>
							</div>
						</div>
						<input type="hidden" name="method" value="contact_form"/>
					</form>
				</div>
			</div>
		</div>
		<div class="my-5"></div>
		<?php user_end_scripts(); ?>
		<?php get_footer(); ?>
	</body>
	
</html>