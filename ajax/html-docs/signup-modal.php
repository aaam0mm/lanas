<?php
$plat = $_GET["plat"] ?? "";
$userID = $_GET["user_id"] ?? "";
$access_token = $_GET["access_token"] ?? "";
$data = file_get_contents("https://graph.facebook.com/");
//$data = json_decode($data);
var_dump($data);
$user_email = $data->email ?? "";
$user_name = $data->name ?? "";

?>
	<!-- Modal sign up -->
	<div class="modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body">

					<form method="POST">
						<div class="form-body px-md-5 px-3">
							<div class="form-group">
								<label for="username"><?php echo _t("إسم المستخدم بحروف انجليزية"); ?></label>
								<input type="text" id="username" name="username" class="form-control rounded-0 form-control-lg"/>
								<div id="username_error_txt" class="invalid-feedback"></div>
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
											<?php foreach(generate_nums(1,31) as $day): ?>
											<option value="<?php esc_html($day); ?>"><?php esc_html($day); ?></option>
											<?php endforeach; ?>
										</select>
										<select id="user_birth_month" name="user_birth_month" class="form-control rounded-0 form-control-lg custom-select">
											<option selected="true" disabled="true"><?php echo _t("الشهر"); ?></option>
											<?php foreach(months_names() as $month_num=>$month_name): ?>
											<option value="<?php esc_html($month_num); ?>"><?php esc_html($month_name); ?></option>
											<?php endforeach; ?>
										</select>
										<select id="user_birth_year" name="user_birth_year" class="form-control rounded-0 form-control-lg custom-select">
											<option selected="true" disabled="true"><?php echo _t("السنة"); ?></option>
											<?php foreach(generate_nums(1940,date("Y"),"desc") as $year): ?>
											<option value="<?php esc_html($year); ?>"><?php esc_html($year); ?></option>
											<?php endforeach; ?>											
										</select>
									</div>
								</div>
							</div>							
							<div class="form-group">
								<label for="user_country"><?php echo _t("الدولة"); ?></label>
								<select id="user_country" name="user_country" class="form-control rounded-0 form-control-lg custom-select">
								    <?php foreach( sort_json(get_countries(),"country_name","asc") as $country ): ?>
									<option value="<?php esc_html($country["country_code"]); ?>"><?php esc_html($country["country_name"]); ?></option>
									<?php endforeach; ?>
								</select>
								<div id="user_country_error_txt" class="invalid-feedback"></div>
							</div>
							<!--
							<div class="form-group form-row">
								<div class="col-md-6 mb-3">
									<label for="user_pwd"><?php echo _t("كلمة المرور"); ?></label>
									<input type="password" id="user_pwd" name="user_pwd" class="form-control rounded-0 form-control-lg"/>
									<div id="user_pwd_error_txt" class="invalid-feedback"></div>
								</div>
								<div class="col-md-6">
									<label for="user_re_pwd"><?php echo _t("تأكيد كلمة المرور"); ?></label>
									<input type="password" id="user_re_pwd" name="user_re_pwd" class="form-control rounded-0 form-control-lg"/>
									<div id="user_re_pwd_error_txt" class="invalid-feedback"></div>
								</div>
							</div>
							-->
							<div class="form-group sign-btns">
								<button class="btn btn-lg btn-sign-green rounded-0"><?php echo _t("أكمل التسجيل"); ?></button>							
							</div>
						</div>
						<input type="hidden" name="user_name" value="<?php esc_html($user_name); ?>"/>
						<inpu type="hidden" name="user_email" value="<?php esc_html($user_email); ?>"/>
						<input type="hidden" name="plat" value="<?php esc_html($plat); ?>"/>
						<input type="hidden" name="plat_user_id" value="<?php esc_html($userID); ?>"/>
						<input type="hidden" name="access_token" value="<?php esc_html($access_token); ?>"/>
						<input type="hidden" name="method" value="user_ajax"/>
						<input type="hidden" name="request" value="signup"/>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script>
	    $(function() {
	       	$("#signupModal").modal('show'); 
	    });
	</script>