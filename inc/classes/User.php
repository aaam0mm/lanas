<?php

class User
{

	private $errors = null;
	public $user_id  = null;
	function __construct($errors = null, $user_id = null)
	{
		$this->errors = $errors;
		$this->user_id = $user_id;
	}
	/**
	 * @param boolean $direct_signin
	 * 
	 * @return boolean
	 */
	public function signup($fields)
	{

		global $dsql;

		$user_name = @filter_var($fields["user_name"] ?? "", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$username = @filter_var($fields["username"] ?? "", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$user_email = $fields["user_email"] ?? "";
		$user_gender = $fields["user_gender"] ?? "";
		$user_birth_day = $fields["user_birth_day"] ?? "";
		$user_birth_month = $fields["user_birth_month"] ?? "";
		$user_birth_year = $fields["user_birth_year"] ?? "";
		$user_country = $fields["user_country"] ?? "";
		$user_pwd = $fields["user_pwd"] ?? "";
		$user_re_pwd = $fields["user_re_pwd"] ?? "";
		$direct_signin = $fields["direct_signin"] ?? false;

		$user_birth_day = filter_var(
			$user_birth_day,
			FILTER_VALIDATE_INT,
			[
				"options" => ["min_range" => 1, "max_range" => 31]
			]
		);

		if (empty($user_birth_month) || empty(months_names($user_birth_month))) {
			$this->errors[] = ["selector" => "#user_birth_month", "error" => ""];
		}

		$user_birth_year = filter_var($user_birth_year, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1940, "max_range" => date("Y")]]);

		$this->verify_username($username);
		$this->verify_email($user_email);
		$this->verify_user_name($user_name);

		if (!$user_birth_day) {
			$this->errors[] = ["selector" => "#user_birth_day", "error" => ""];
		}

		if (!$user_birth_year) {
			$this->errors[] = ["selector" => "#user_birth_year", "error" => ""];
		}

		if (!$user_country) {
			$this->errors[] = ["selector" => "#user_country", "error" => _t("المعذرة ! المرجو إختيار الدولة")];
		}

		if (!in_array($user_gender, ["male", "female"])) {
			$this->errors[] = ["selector" => "#user_gender", "error" => _t("المعذرة ! المرجو إختيار الجنس")];
		}

		if (is_null($this->errors) === false) {
			return false;
		}

		$user_joindate = gmdate("Y-m-d H:i");

		$user_lang = current_content_lang();
		$user_status = "active";
		$user_verify_key = generateRandomString(4);
		$user_login_identify = generateRandomString(64);
		$user_role = get_option("new_members_role");
		$birth_date = $user_birth_year . "-" . $user_birth_month . "-" . $user_birth_day;
		if ($user_gender == 'male') {
			$user_picture = get_option('default_male_picture');
		} else {
			$user_picture = get_option('default_female_picture');
		}

		$user_pwd = password_hash($user_pwd, PASSWORD_DEFAULT, array('cost' => 10));

		$user_metas = [
			"points_remaining" => points_manage("openaccount", "add"),
			"points_consumed" => 0,
			"verify_key" => $user_verify_key,
			"notifs_settings" => [
				'comment' => 'on',
				'reply' => 'on',
				'subscribe' => 'on',
				'unsubscribe' => 'on',
				'reaction' => 'on',
				'email' => 'on',
				'rate' => 'on'
			]
		];

		$user_metas['notifs_settings'] = json_encode($user_metas['notifs_settings']);

		$cols = [
			"user_name" => $user_name,
			"username" => $username,
			"user_email" => $user_email,
			"user_gender" => $user_gender,
			"birth_date" => $birth_date,
			"user_country" => $user_country,
			"user_picture"  => $user_picture,
			"user_role"  => $user_role,
			"user_pwd" => $user_pwd,
			"user_login_identify" => $user_login_identify,
			"user_status" => $user_status,
			"user_joindate" => $user_joindate,
			"user_lang" => $user_lang
		];

		if (@admin_authority()->users == "on") {
			$user_metas["points_remaining"] = $_POST["points_remaining"] ?? 0;
		}

		$user_metas["user_current_lang"] = current_lang();
		$insert_user = $dsql->dsql()->table('users')->set($cols)->insert();

		if ($insert_user) {
			$user_id = get_last_inserted_id();
			foreach ($user_metas as $meta_key => $meta_value) {
				update_user_meta($user_id, $meta_key, $meta_value);
			}
			if ($direct_signin) {
				if ($this->set_user_cookie($user_login_identify)) {
					//send_mail($user_email,'<p>'._t("رمز التفعيل").' : '.$user_verify_key.'</p>',_t("تفعيل الحساب"));
					return true;
				}
			}
			return true;
		}
	}

	public function verify_account()
	{

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		if ($current_user->user_status != "email_verify") {
			return false;
		}
		$verify_code = $_POST["verify_code"] ?? "";
		$get_user_verify_code = get_user_meta($current_user->id, "verify_key");
		if ($verify_code == $get_user_verify_code) {
			global $dsql;
			$update = $dsql->dsql()->table('users')->set(["user_status" => "active"])->where("id", $current_user->id)->update();
			if ($update) {
				remove_user_meta($current_user->id, 'verify_key');
			}
			return true;
		} else {
			$this->errors[] = ["selector" => "#verify_code", "error" => _t("المعذرة ! المرجو التأكد من رمز التفعيل")];
		}
		return false;
	}

	public function social_login($data) {}

	/**
	 *
	 */
	public function signin()
	{
		$process = true;
		$user_name_email = $_POST["user_name_email"];
		$user_pwd = $_POST["user_pwd"];
		if (empty($user_name_email)) {
			$this->errors[] = ["selector" => "#user_name_email", "error" => _t("المعذرة ! الحقل فارغ")];
			$process = false;
		}

		if (empty($user_pwd)) {
			$this->errors[] = ["selector" => "#user_pwd", "error" => _t("المعذرة ! الحقل فارغ")];
			$process = false;
		}

		if ($process === false) {
			return false;
		}

		global $dsql;
		$check = $dsql->dsql()->table('users');
		$check->where($check->orExpr()->where('user_name', $user_name_email)->where('user_email', $user_name_email));
		
		if (!$check) {
			$this->errors["msg"] = _t("المعذرة ! إسم المستخدم أو كلمة المرور خطأ");
			return false;
		}
		$user = $check->getRow();
		if($user) {
			if (password_verify($user_pwd, $user["user_pwd"])) {
				if ($this->set_user_cookie($user["user_login_identify"])) {
					return true;
				}
			} else {
				$this->errors[] = ["selector" => "#errors-area", "error" => _t("المعذرة ! إسم المستخدم أو كلمة المرور خطأ")];
				return false;
			}
		} else {
			$this->errors[] = ["selector" => "#errors-area", "error" => _t("المعذرة ! إسم المستخدم أو كلمة المرور خطأ")];
			return false;
		}
	}


	/**
	 * update_account_info()
	 * 
	 */
	public function update_account_info()
	{

		$current_user = $this->c_u();
		if (!$current_user) {
			return false;
		}
		$metas = [];
		$insert_new_points_notif = $insert_new_badge_notif = false;
		if (admin_authority()->users == "on") {
			if (!empty($_POST["points_remaining"])) {
				$metas["points_remaining"] =  (int) get_user_meta($current_user->id, "points_remaining") + (int) $_POST["points_remaining"];
				$insert_new_points_notif = true;
			}
			if (isset($_POST["manual_badges"])) {
				$metas["manual_badges"] = @serialize($_POST["manual_badges"]);
				$user_manual_badges = @unserialize(get_user_meta($current_user->id, "manual_badges"));
				if (@!empty(array_diff($_POST["manual_badges"], $user_manual_badges))) {
					$insert_new_badge_notif = true;
				}
			}

			if (isset($_POST["user_permissions"])) {
				$metas["user_permissions"] = @json_encode($_POST["user_permissions"]);
			}

			if (isset($_POST["bloc_reason"])) {
				$metas["bloc_reason"] = $_POST["bloc_reason"];
			}
		}

		$user_name = @filter_var($_POST["user_name"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$username = @filter_var($_POST["username"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$user_email = $_POST["user_email"];
		$user_country = $_POST["user_country"] ?? "";
		$user_gender = $_POST["user_gender"] ?? "";
		$user_birth_day = $_POST["user_birth_day"] ?? "";
		$user_birth_month = $_POST["user_birth_month"] ?? "";
		$user_birth_year = $_POST["user_birth_year"] ?? "";

		$user_birth_day = filter_var(
			$user_birth_day,
			FILTER_VALIDATE_INT,
			[
				"options" => ["min_range" => 1, "max_range" => 31]
			]
		);

		if (empty($user_birth_month) || empty(months_names($user_birth_month))) {
			$this->errors[] = ["selector" => "#user_birth_month", "error" => ""];
		}

		$user_birth_year = filter_var($user_birth_year, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1940, "max_range" => date("Y")]]);

		if (!$user_birth_day) {
			$this->errors[] = ["selector" => "#user_birth_day", "error" => ""];
		}

		if (!$user_birth_year) {
			$this->errors[] = ["selector" => "#user_birth_year", "error" => ""];
		}
		if (!$user_country) {
			$this->errors[] = ["selector" => "#user_country", "error" => _t("المعذرة ! المرجو إختيار الدولة")];
		}

		if (!in_array($user_gender, ["male", "female"])) {
			return false;
		}

		$this->verify_username($username, true);
		$this->verify_email($user_email, true);
		$this->verify_user_name($user_name);

		$birth_date = $user_birth_year . "-" . $user_birth_month . "-" . $user_birth_day;
		$current_user_pwd = $_POST["current_user_pwd"] ?? "";
		$user_pwd = $_POST["user_pwd"] ?? "";
		$user_re_pwd = $_POST["user_re_pwd"] ?? "";

		if (!empty($user_pwd)) {
			if (@admin_authority()->users != "on" && !empty($current_user_pwd)) {
				if (password_verify($current_user_pwd, $current_user->user_pwd)) {
					$this->verify_pwd($user_pwd, $user_re_pwd);
				} else {
					$this->errors[] = ["selector" => "#current_user_pwd", "error" => _t("المعذرة كلمة السر الحالية خطأ.")];
				}
			}
		}

		if (is_null($this->errors) === false) {
			return false;
		}

		global $dsql;
		$user_old_picture = get_user_field($current_user->id, "user_picture");
		$user_picture = $_POST["user_picture"] ?? $user_old_picture;

		$cols = ["username" => $username, "user_name" => $user_name, "user_email" => $user_email, "user_country" => $user_country, "user_gender" => $user_gender, "birth_date" => $birth_date];

		if (is_numeric($user_picture) && $user_picture != $user_old_picture) {
			$cols["user_picture"] = $user_picture;
		}

		if (!empty($user_pwd)) {
			$user_pwd = password_hash($user_pwd, PASSWORD_DEFAULT, array('cost' => 10));
			$cols["user_pwd"] = $user_pwd;
		}

		$update = $dsql->dsql()->table('users')->set($cols)->where('id', $current_user->id)->update();
		/**
		 * $update == 0 if user not changed any information it return 0 so however we show success message
		 */
		if ($update || $update == 0) {

			if ($user_old_picture != $user_picture  && $user_old_picture != get_option("default_{$current_user->user_gender}_picture")) {
				$delete = new Delete($user_old_picture);
				$delete->delete_profile_picture();
			}

			if (count($metas) > 0) {
				foreach ($metas as $meta_key => $meta_value) {
					update_user_meta($current_user->id, $meta_key, $meta_value);
				}
			}
			if ($insert_new_points_notif) {
				insert_notif(0, $current_user->id, null, "add_new_points", 1);
			}
			if ($insert_new_badge_notif) {
				insert_notif(0, $current_user->id, null, "add_new_badge", 1);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return boolean
	 */
	public function update_personal_info()
	{
		$personal_info = $_POST["personal_info"] ?? "";
		$personal_info = json_encode($personal_info);
		if (update_user_meta(get_current_user_info()->id, "user_personal_info", $personal_info)) {
			return true;
		}
		return false;
	}

	/**
	 * @return boolean
	 */
	public function update_cv_info()
	{
		$cv = $_POST["cv"];
		$achievements_publications = $cv["achievements_publications"] ?? "";

		$skills_experiences = $cv["skills_experiences"] ?? "";
		$activities_courses = $cv["activities_courses"] ?? "";
		$skills_degrees = $cv["skills_degrees"] ?? "";
		$meta_id = $_POST["meta_id"] ?? null;
		$update = false;
		if (!empty($meta_id)) {
			$update = true;
		}
		foreach ($cv as $meta_key => $meta_value) {
			$meta_value = json_encode($meta_value);
			if (!update_user_meta(get_current_user_info()->id, $meta_key, $meta_value, $update, $meta_id)) {
				break;
				return false;
			}
		}
		return true;
	}

	public function update_notifs_settings()
	{

		$notifs = $_POST["notifs"];

		$notifs_settings = [
			"comment" => $notifs["comment"] ?? "off",
			"subscribe" => $notifs["subscribe"] ?? "off",
			"reaction" => $notifs["reaction"] ?? "off",
			"rate" => $notifs["rate"] ?? "off"
		];

		$notifs_settings = json_encode($notifs_settings);
		if (update_user_meta(get_current_user_info()->id, "notifs_settings", $notifs_settings)) {
			return true;
		}
		return false;
	}

	/**
	 * @return boolean
	 */
	public function update_social_accounts()
	{
		$social_accounts = $_POST["s_c"] ?? [];
		$s_c = [];
		foreach ($social_accounts as $plat => $url) {
			if (!empty($url)) {
				if (filter_var($url, FILTER_VALIDATE_URL)) {
					$s_c[$plat] = $url;
				} else {
					$this->errors[] = ["selector" => "#" . $plat . "_url", "error" => _t("المعذرة ! الرابط غير صحيح")];
					return false;
				}
			}
		}
		$social_accounts = json_encode($s_c);
		if (update_user_meta(get_current_user_info()->id, "user_social_accounts", $social_accounts)) {
			return true;
		}
		return false;
	}

	/**
	 * Generate random key for user reset password
	 * @return mixed
	 */

	public function forget_password()
	{
		$email = $_POST["email"] ?? "";
		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		if (empty($email)) {
			$this->errors[] = ["selector" => "#user_email", "error" => _t("المعذرة ! المرجو إدخال بريد إلكتروني صحيح")];
		}
		$r = generateRandomString(64);
		global $dsql;
		$get_user_id = $dsql->dsql()->table('users')->where('user_email', $email)->field('id')->limit(1)->getRow();
		if ($get_user_id) {
			$user_id = $get_user_id["id"];
			if (update_user_meta($user_id, "recover_key", $r)) {
				$message = '<p>' . _t("إتبع الرابط التالي لإسترجاع كلمة مرور حسابك") . '</p><p><a href="' . siteurl() . '/reset_password.php?key=' . $r . '">' . _t('من هنا') . '</a></p>';
				send_mail($email, $message, _t("إسترجاع كلمة المرور"));
				return true;
			}
		}
		return false;
	}

	/**
	 * 
	 */

	public function reset_password()
	{
		$user_recover_key = $_POST["recover_key"];
		if (empty($user_recover_key)) {
			return false;
		}
		global $dsql;
		$check = $dsql->dsql()->table('user_meta')->where('meta_key', 'recover_key')->where('meta_value', $user_recover_key)->field('id,user_id')->getRow();
		if ($check) {
			$user_pwd = $_POST["user_pwd"] ?? "";
			$user_re_pwd = $_POST["user_re_pwd"] ?? "";
			$verify_pwd = $this->verify_pwd($user_pwd, $user_re_pwd);
			if (is_null($this->errors)) {
				$user_id = $check["user_id"];
				$meta_id = $check["id"];
				$user_pwd = password_hash($user_pwd, PASSWORD_DEFAULT, array('cost' => 10));
				$update = $dsql->dsql()->table('users')->set(["user_pwd" => $user_pwd])->where('id', $user_id)->update();
				if ($update) {
					remove_user_meta($user_id, 'recover_key');
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * return @propery $errors value
	 * @return object
	 */
	public function get_errors()
	{
		return $this->errors;
	}

	/**
	 * @param string $cookie_value
	 * @param string $expiry
	 *
	 * @return boolean
	 */
	private function set_user_cookie($cookie_value, $expiry = "+30 days")
	{
		if (is_ssl()) {
			setcookie("user", $cookie_value, strtotime($expiry), "/", "", true, true); // This cookie store in user device for 30 days
		} else {
			setcookie("user", $cookie_value, strtotime($expiry), "/", "", false, true); // This cookie store in user device for 30 days
		}
		return true;
	}

	/**
	 * @param string $pwd
	 * @return mixed (string on success | boolean on failure)
	 */
	private function verify_pwd($pwd, $compare = false)
	{
		if (!is_bool($compare)) {
			$compare = (string) $compare;
		}
		if (mb_strlen($pwd) > 16 || mb_strlen($pwd) < 6) {
			$this->errors[] = ["selector" => "#user_pwd", "error" => sprintf(_t("يجب أن يكون إمتداد كلمة السر بين %s إلى %s"), 6, 16)];
		}
		if (is_string($compare) && $pwd != $compare) {
			$this->errors[] = ["selector" => "#user_re_pwd", "error" => _t("كلمتي السر غير متطابقتين")];
		}
	}

	private function verify_username($username, $is_logged = false)
	{
		if (mb_strlen($username) < 4) {
			$this->errors[] = ["selector" => "#username", "error" => _t("المعذرة ! إسم المستخدم قصير")];
		} else {
			global $dsql;

			$check = $dsql->dsql()->table('users')->where('username', $username);

			if ($is_logged) {
				$check->where('id', '!=', $this->c_u()->id);
			}
			$check->field('count(*)', 'records');
			$check->limit(1);
			if ($check->getRow()['records'] == 1) {
				$this->errors[] = ["selector" => "#username", "error" => _t("المعذرة ! إسم المستخدم مستعمل مسبقا")];
			}
		}
	}

	private function verify_user_name($user_name)
	{
		if (mb_strlen($user_name) < 4 ||  mb_strlen($user_name) > 25) {
			$this->errors[] = ["selector" => "#user_name", "error" => sprintf(_t("يجب أن يكون إمتداد الإسم بين %d-%d"), 4, 25)];
			$process = false;
		}
	}

	/**
	 * @param string $user_email
	 * @param boolean $is_logged
	 */
	private function verify_email($user_email, $is_logged = false)
	{
		$user_email = filter_var($user_email, FILTER_VALIDATE_EMAIL);
		if (!$user_email) {
			$this->errors[] = ["selector" => "#user_email", "error" => _t("المعذرة ! المرجو إدخال بريد إلكتروني صحيح")];
		} else {
			global $dsql;
			$check = $dsql->dsql()->table('users')->where('user_email', $user_email);
			if ($is_logged) {
				$check->where('id', '!=', $this->c_u()->id);
			}
			$check->field('count(*)', 'records');
			$check->limit(1);
			if ($check->getRow()['records'] == 1) {
				$this->errors[] = ["selector" => "#user_email", "error" => _t("المعذرة ! البريد الإلكتروني مستعمل مسبقا")];
			}
		}
	}
	/**
	 * We need to get user informations to check if he/she had permissions to edit some information
	 * this function works to give user info
	 */
	private function c_u()
	{
		// if catch @var int $user_id in request we need to check if logged user had authority to access users
		$user_id = $_POST["user_id"] ?? "";
		if (!empty($user_id) && admin_authority()->users == "on") {
			return get_user_info($user_id);
		} else {
			return get_current_user_info();
		}
	}
}
