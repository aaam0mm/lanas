<?php

/**
 * functions.php
 *
 * Contain all functions that requesting via Ajax
 * To make function works here you need to add function name to array $allowed_methods
 * @see ajax_service.php
 */

require_once 'admin/functions.php';

if (!function_exists("user_signup_ajax")) {
	function user_signup_ajax() {

		$response = ["success" => false];
		$input_errors = [];

		$user_name = @filter_var($_POST["user_name"],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$username = @filter_var($_POST["username"],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$user_email = filter_var($_POST["user_email"],FILTER_VALIDATE_EMAIL);
		$user_gender = $_POST["user_gender"] ?? "";
		$user_birth_day = filter_var(@$_POST["user_birth_day"],FILTER_VALIDATE_INT,["options" => ["min_range" => 1, "max_range" => 31]]);
		$user_birth_month = $_POST["user_birth_month"] ?? "";
		$user_birth_year = filter_var(@$_POST["user_birth_year"],FILTER_VALIDATE_INT,["options" => ["min_range" => 1940, "max_range" => date("Y")] ]);
		$user_country = $_POST["user_country"] ?? "";
		$user_pwd = $_POST["user_pwd"] ?? "";
		$user_re_pwd = $_POST["user_re_pwd"] ?? "";

		if(empty($user_birth_month) || empty(months_names($user_birth_month))) {
			$input_errors[] = ["selector" => "#user_birth_month", "error" => ""];
		}
		
		if(!$user_birth_day) {
			$input_errors[] = ["selector" => "#user_birth_day", "error" => ""];
		}
		
		if(!$user_birth_year) {
			$input_errors[] = ["selector" => "#user_birth_year", "error" => ""];
		}

		if(!$user_country) {
			$input_errors[] = ["selector" => "#user_country", "error" => _t("المعذرة ! المرجو إختيار الدولة")];
		}

		if(!in_array($user_gender,["male","female"])) {
			$input_errors[] = ["selector" => "#user_gender", "error" => _t("المعذرة ! المرجو إختيار الجنس")];
		}

		if(!is_user_pwd_valid($user_pwd)) {
			$input_errors[] = ["selector" => "#user_pwd", "error" => sprintf(_t("يجب أن يكون إمتداد كلمة السر بين %s إلى %s"),6,16)];
		}elseif($user_pwd !== $user_re_pwd) {
			$input_errors[] = ["selector" => "#user_re_pwd", "error" => _t("كلمتي السر غير متطابقتين")];
		}

		if(!is_username_valid($username)) {
			$input_errors[] = ["selector" => "#username", "error" => _t("المعذرة ! إسم المستخدم قصير")];
		}elseif(is_username_exist($username)){
			$input_errors[] = ["selector" => "#username", "error" => _t("المعذرة ! إسم المستخدم مستعمل مسبقا")];
		}

		if(!$user_email) {
			$input_errors[] = ["selector" => "#user_email", "error" => _t("المعذرة ! المرجو إدخال بريد إلكتروني صحيح")];
		}elseif(is_user_email_exist($user_email)) {
			$input_errors[] = ["selector" => "#user_email", "error" => _t("المعذرة ! البريد الإلكتروني مستعمل مسبقا")];
		}

		if(!is_user_name_valid($user_name)) {
			$input_errors[] = ["selector" => "#user_name", "error" => sprintf(_t("يجب أن يكون إمتداد الإسم بين %d-%d"),4,25)];
		}

		$response["inputs_errors"] = $input_errors;
		
		if(!empty($input_errors)) {
			echo json_encode($response);
			return null;
		}
		
		$birth_date = $user_birth_year."-".$user_birth_month."-".$user_birth_day;
	
		$user_pwd = password_hash($user_pwd,PASSWORD_DEFAULT , array('cost' => 10));

		$user_meta = [
			"verify_key" => generateRandomString(4)
		];
		if(@admin_authority()->users == "on") {
			$user_meta["points_remaining"] = $_POST["points_remaining"] ?? 0;
		}

		$data = [
			"user_name" => $user_name, 
			"username" => $username, 
			"user_email" => $user_email, 
			"user_gender" => $user_gender, 
			"birth_date" => $birth_date, 
			"user_country" => $user_country, 
			"user_pwd" => $user_pwd,
			"user_meta" => $user_meta
		];

		$save_user = save_user($data);

		if($save_user !== false ) {
			set_user_cookie($save_user["user_login_identify"]);
			$response["success"] = true;
			$response["to_instruction"] = true;
		}
		
		echo json_encode($response);
		
	}
}

if (!function_exists("delete_conversation")) {
	/**
	 * delete_conversation()
	 */
	function delete_conversation()
	{
		$response = ["success" => false];
		$id = $_POST["id"] ?? "";
		if (empty($id)) {
			return false;
		}

		$delete = new Delete($id);
		if ($delete->delete_conversation()) {
			$response["success"] = true;
		} else {
			$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
		}
		echo json_encode($response);
	}
}

if (!function_exists("send_cv_badge_order")) {
	/**
	 * send_cv_badge_order()
	 */
	function send_cv_badge_order()
	{
		$response = ["success" => false];
		$process = true;
		$c = get_current_user_info();
		if (!$c) {
			return false;
		}

		if (!user_authority()->cv_badge) {
			return false;
		}

		$user_id = $c->id;
		$cv_badge_request = get_user_meta($user_id, "cv_badge_request");
		if (in_array($cv_badge_request,["pending","refuse"])) {
			return false;
		}

		$user_identify = $_POST["user_identify"] ?? null;

		if (get_file($user_identify) === false) {
			$response["msg"] = _t("المرجو رفع إتباث الهوية");
			$process = false;
		}

		if ($process) {
			if (update_user_meta($user_id, "cv_badge_request", "pending")) {
				update_user_meta($user_id, "user_identify", $user_identify);
				$response["msg"] = _t("تم إرسال الطلب بنجاح");
				$response["success"] = true;
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("post_notice_ajax")) {
	/**
	 * post_notice_ajax() 
	 */
	function post_notice_ajax()
	{

		$response = ["success" => false];

		$post_id = $_POST["post_id"] ?? null;
		if (empty($post_id)) {
			return false;
		}
		$notice = $_POST["notice"] ?? "";

		if (update_post_meta($post_id, "notice", $notice) !== false) {
			$post_author = get_post_field($post_id, "post_author");
			insert_notif(0, $post_author, $post_id, "post_notice");
			$response["success"] = true;
		}

		echo json_encode($response);
	}
}

if (!function_exists("delete_file_ajax")) {
	/**
	 * delete_file_ajax()
	 */
	function delete_file_ajax($id = null)
	{

		$response = ["success" => false];

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;
		$file_id = $id ?? $_POST["id"] ?? "";
		$get_file = $dsql->dsql()->table('files')->where('id', $file_id)->limit(1)->getRow();
		if (!$get_file) {
			return false;
		}

		// $file = $get_file;
		$delete = new Delete($file_id);
		if ($delete->delete_files()) {
			$response["success"]  = true;
		} else {
			$response['msg'] = _t('المعذرة ليس لك الصلاحيات لحدف الملف');
		}

		echo json_encode($response);
	}
}

if (!function_exists("get_analytics_nums")) {
	/**
	 * get_analytics_nums()
	 */
	function get_analytics_nums()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		$duration = $_POST["duration"] ?? "today";
		$views_analytics = get_analytics("post_views", "posts_views", $current_user->id, $duration);
		$shares_analytics = get_analytics("post_share", "posts_shares", $current_user->id, $duration);

		$trusted_posts_views = $views_analytics["trusted_views"] ?? 0;
		$untrusted_posts_views = $views_analytics["untrusted_views"] ?? 0;
		$posts_shares = $shares_analytics["shares"] ?? 0;
		$all_views = $views_analytics["all_views"] ?? 0;

		echo json_encode([
			"trusted_posts_views" => $trusted_posts_views,
			"untrusted_posts_views" => $untrusted_posts_views,
			"posts_shares" => $posts_shares,
			"all_views" => $all_views
		]);
	}
}

if (!function_exists("delete_account")) {
	/**
	 * delete_account()
	 * 
	 */
	function delete_account()
	{
		$c = get_current_user_info();
		if (!$c || is_super_admin()) {
			return false;
		}
		$delete_account_code = get_user_meta($c->id, "delete_account_code");
		if (!$delete_account_code) {
			return false;
		}

		$response = ["success" => false];
		$process = true;

		$delete_code = $_POST["delete_code"] ?? null;
		$pwd = $_POST["pwd"] ?? null;

		if ($delete_code != $delete_account_code) {
			$response["inputs_errors"][] = ["selector" => "#delete-code", "error" => _t("المرجو التأكد من الرمز")];
			$process = false;
		}

		if (!password_verify($pwd, $c->user_pwd)) {
			$response["inputs_errors"][] = ["selector" => "#pwd", "error" => _t("المرجوالتأكد من كلمة السر")];
			$process = false;
		}

		if ($process) {

			$delete = new Delete($c->id);

			if ($delete->delete_users()) {
				$response["success"] = true;
			} else {
				$response["msg"] = $delete->get_errors()[0];
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("delete_account_request")) {
	/**
	 * delete_account_request()
	 * 
	 */
	function delete_account_request()
	{
		if (!get_current_user_info()) {
			return false;
		}

		$response = ["success" => true];
		$code = generateRandomString(8);
		if (update_user_meta(get_current_user_info()->id, "delete_account_code", $code)) {
			$mail_msg = '<p style="font-size:12px;">' . _t("قم بنسخ الرمز ولصه بحقل التأكيد في الموقع") . '</p>';
			$mail_msg = '<span>' . $code . '</span>';
			send_mail(get_current_user_info()->user_email, $mail_msg, _t("رمز تأكيد حدف الحساب"));
			$response["msg"] = _t("تم إرسال رمز التأكيد إلى بريدك الإلكتروني");
		}
		echo json_encode($response);
	}
}

if (!function_exists("delete_comment")) {
	/**
	 * delete_comment()
	 */
	function delete_comment()
	{
		$comment_id = $_POST["comment_id"] ?? "";
		if (empty($comment_id)) {
			return false;
		}
		$response = ["success" => false];
		$delete = new Delete($comment_id);
		$delete->delete_comments();
		if (is_null($delete->errors())) {
			$response["success"] = true;
			$response["msg"] = _t("تم حدف بنجاح");
		} else {
			$response["errors"] = $delete->errors();
		}
		echo json_encode($response);
	}
}

if (!function_exists("delete_post")) {
	/**
	 * delete_post()
	 */
	function delete_post()
	{
		$post_id = $_POST["post_id"] ?? "";
		
		if (empty($post_id)) {
			return false;
		}
		$response = ["success" => false];
		$delete = new Delete($post_id);
		$delete->delete_posts();
		if (is_null($delete->errors())) {
			$response["success"] = true;
			$response["msg"] = _t("تم حدف بنجاح");
		} else {
			$response["errors"] = $delete->errors();
		}
		echo json_encode($response);
	}
}

if (!function_exists("close_notif")) {
	/**
	 * close_notif()
	 */
	function close_notif()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		$id = $_POST["id"] ?? "";
		if (empty($id)) {
			return false;
		}
		global $dsql;
		$update = $dsql->dsql()->table('notifications_sys')->where('notif_to', $current_user->id)->where('id', $id)->delete();
		if ($update) {
			return true;
		}
		return false;
	}
}

if (!function_exists("open_notifs")) {
	/**
	 * open_notifs()
	 */
	function open_notifs()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;
		$update = $dsql->dsql()->table('notifications_sys')->set(['notif_case' => 3])->where('notif_to', $current_user->id)->where('notif_case', 2)->update();
		return true;
	}
}

if (!function_exists("open_msgs")) {
	/**
	 * open_msgs()
	 */
	function open_msgs()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		global $dsql;
		$update = $dsql->dsql()->table('conversations_sys')->set(["read_case" => "seen"])->where('msg_to', $current_user->id)->update();
		return true;
	}
}

if (!function_exists("instant_notifs")) {
	function instant_notifs()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		global $dsql;
		// update user last active
		$dsql->dsql()->table('users')->set(["user_lastseen" => date("Y-m-d H:i:s")])->where('id', $current_user->id)->update();

		$case = 1;
		// load not delivered notifications
		$notifs = get_user_notifs($case);
		$msgs = get_box_messages('sent');
		$notifs_html = $msgs_html = '';
		if ($notifs) {
			foreach ($notifs as $notif) {
				$notifs_html .= '<div class="notif-id-' . $notif["id"] . ' px-2">' . read_notif($notif["id"], $notif["notif_type"], $notif["notif_content"], $notif["notif_from"]) . '</div><div class="dropdown-divider"></div>';
			}
		}

		if ($msgs) {
			foreach ($msgs as $msg) {
				$u_d = switch_message_display_user($msg["msg_from"], $msg["msg_to"]);
				$msgs_html .= '
		        <!-- Message -->
			    <div class="d-flex px-2">
					<a href="' . siteurl() . "/message/" . $msg["msg_id"] . '"><img class="dropdown-img" src="' . get_thumb($u_d["user_picture"]) . '" alt=""/></a>
					<div class="dropdown-content ml-2">
						<a href="' . siteurl() . "/message/" . $msg["msg_id"] . '" class="color-link">' . esc_html__($msg["msg"]) . '</a>
					    <div class="user-time small text-muted"><i class="fas fa-clock fa-sm mr-1"></i>' . get_timeago(strtotime($msg["msg_date"])) . '</div>
					</div>
				</div><!-- / Message -->
			<div class="dropdown-divider"></div>';
			}
		}

		$to_update_notif = $to_update_msg = false;
		switch ($case) {
			case 1:
				$to_update_notif = 2;
				break;
			case 2:
				$to_update_notif = 3;
				break;
		}

		$to_update_msg = "delivered";

		echo json_encode(
			["notif" => ["html" => $notifs_html, "count" => count_user_notifs(1)], "msg" => ["html" => $msgs_html, "count" => count_user_msgs('sent')]]
		);

		if (!is_bool($to_update_notif)) {
			$dsql->dsql()->table('notifications_sys')->set(["notif_case" => $to_update_notif])->where('notif_to', $current_user->id)->where('notif_case', 1)->update();
		}

		if (!is_bool($to_update_msg)) {
			$dsql->dsql()->table('conversations_sys')->set(["read_case" => "delivered"])->where('msg_to', $current_user->id)->update();
		}

		return true;
	}
}

if (!function_exists("un_reaction")) {
	/**
	 * un_reaction()
	 */
	function un_reaction()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		$response = ["success" => false];
		$reaction = $_POST["reaction"] ?? "";
		$reactions = ["like", "love", "haha", "wow", "sad", "angry"];
		$post_id = $_POST["post_id"] ?? "";
		if (!in_array($reaction, $reactions) || empty($post_id)) {
			return false;
		}

		$exits = get_user_meta($current_user->id, "post_reaction__" . $post_id);

		if (update_user_meta($current_user->id, "post_reaction__" . $post_id, $reaction)) {
			$user_id = get_post_field($post_id, "post_author");
			insert_notif($current_user->id, $user_id, $post_id, "post_reaction");
			if (!$exits) {
				$points_remaining = @get_user_info($user_id)->points_remaining;
				$new_remaining_points = $points_remaining + distribute_points("like", "add", $user_id);
				if ($new_remaining_points > $points_remaining) {
					update_user_meta($user_id, "points_remaining", $new_remaining_points);
				}
			}
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_subscribe_taxonomy")) {
	/**
	 * un_subscribe_taxonomy()
	 * (un)subscribe to a taxonomy
	 */
	function un_subscribe_taxonomy()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		$response = ["success" => false];
		$taxonomy = $_POST["taxonomy"] ?? "";
		if (empty($taxonomy)) {
			return false;
		}

		$val = $taxonomy;
		if (is_subscribe_to_taxonomy($taxonomy)) {
			$val = "no";
		}
		if (update_user_meta($current_user->id, "taxonomy_subscribe__" . $taxonomy, $val)) {
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("poll_vote_ajax")) {
	/**
	 * poll_vote_ajax()
	 */
	function poll_vote_ajax()
	{

		$response = ["success" => false];
		$process = true;
		$current_user = get_current_user_info();
		if (!$current_user) {
			$response["login_modal"] = true;
		}

		$poll = $_POST["poll_id"] ?? "";
		$vote = $_POST["vote"] ?? "";

		if (empty($poll) || empty($vote)) {
			$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			$process = false;
		}

		if ($process) {
			if (poll_vote($poll, $vote)) {
				$response["success"] = true;
				$response["msg"] = _t("تم إضافة التصويت بنجاح");
				$response["votes"] = get_votes($poll);
			} else {
				$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			}
		}

		echo json_encode($response);
	}
}

if (!function_exists("send_complain_ajax")) {
	/**
	 * send_complain_ajax()
	 *
	 */
	function send_complain_ajax()
	{

		$post_id = $_POST["post_id"] ?? "";
		if (empty($post_id)) {
			return false;
		}

		$response = ["success" => false];
		$process = true;

		$complain = $_POST["complain"] ?? "";
		if (empty(trim($complain["name"] ?? ""))) {
			$response["msg"] = _t("المرجو إدخال الإسم");
			$process = false;
		}
		if (empty(trim($complain["type"] ?? ""))) {
			$response["msg"] = _t("المرجو دكر نوع المخالفة");
			$process = false;
		}

		if ($process) {
			$complain = json_encode($complain);
			if (update_post_meta($post_id, "complain", $complain, false)) {
				$response["success"] = true;
				$response["msg"] = _t("شكرا على وقتك سيتم أخد هذا التبليغ بغاية الأهمية");
			} else {
				$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			}
		}

		echo json_encode($response);
	}
}

if (!function_exists("contact_form")) {
	/**
	 * contact_form()
	 */
	function contact_form()
	{

		$process = true;
		$response = ["success" => false];

		$user_id = null;
		$contact_name = $_POST["contact_name"] ?? "";
		$contact_email = $_POST["contact_email"] ?? "";
		$contact_subject = $_POST["contact_subject"] ?? "";
		$contact_message = $_POST["contact_message"] ?? "";


		if (mb_strlen($contact_subject) < 6) {
			$response["inputs_errors"][] = ["selector" => "#contact_subject", "error" => _t("النص جد قصيرة ! ")];
			$process = false;
		}

		if (mb_strlen($contact_message) < 10) {
			$response["inputs_errors"][] = ["selector" => "#contact_message", "error" => _t("الرسالة جد قصيرة !")];
			$process = false;
		}

		$current_user = get_current_user_info();
		if ($current_user) {
			$user_id = $current_user->id;
			$contact_name = $current_user->user_name;
			$contact_email = $current_user->user_email;
		} else {
			if (mb_strlen($contact_name) < 3) {
				$response["inputs_errors"][] = ["selector" => "#contact_name", "error" => _t("الإسم جد قصير !")];
				$process = false;
			}
			if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
				$response["inputs_errors"][] = ["selector" => "#contact_email", "error" => _t("البريد الإلكتروني غير صالح !")];
				$process = false;
			}
		}

		$cols = [
			"contact_name" => $contact_name,
			"contact_email" => $contact_email,
			"contact_subject" => $contact_subject,
			"contact_message" => $contact_message,
			"user_id" => $user_id
		];

		if ($process) {
			global $dsql;
			$insert = $dsql->dsql()->table('contact_form')->set($cols)->insert();
			if ($insert) {
				$response["success"] = true;
				$response["msg"] = _t("تم إرسال رسالة بنجاح سيتم الرد عليك في أقرب وقت ممكن. شكرا");
			} else {
				$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("lang_settings")) {
	/**
	
	 */
	function lang_settings()
	{
		$process = false;
		$response = ["success" => false];
		$site_lang = $_POST["site_lang"] ?? current_lang();
		$content_lang = $_POST["content_lang"] ?? M_L;

		if (get_langs($content_lang, "on", true) === false) {
			$content_lang = M_L;
		}

		if (!is_login_in()) {
			set_lang($site_lang, $content_lang);
			$response["success"] = true;
			$response["msg"] = _t("تم تحديث بنجاح");
			echo json_encode($response);
			return;
		}

		$current_user = get_current_user_info();
		update_user_meta($current_user->id, "user_current_lang", $site_lang);
		if (set_lang($site_lang)) {
			$process = true;
		}

		global $dsql;
		$update = $dsql->dsql()->table('users')->set(["user_lang" => $content_lang])->where('id', $current_user->id)->update();
		if ($update) {
			$process = true;
		}

		if ($process) {
			$response["success"] = true;
			$response["msg"] = _t("تم تحديث بنجاح");
		} else {
			$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
		}
		echo json_encode($response);
	}
}

if (!function_exists("save_post_comment_ajax")) {
	/**
	 * add_comment_ajax()
	 */
	function save_post_comment_ajax()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			echo json_encode(["login_modal" => true]);
			return;
		}

		$post_id = $_POST["post_id"] ?? null;
		if (empty($post_id)) {
			return;
		}

		$post_status = get_post_field($post_id, "post_status");
		$disable_comments = get_post_meta($post_id, "disable_comments");
		
		if ($post_status != "publish" || $disable_comments != "off") {
			return;
		}

		$process = true;
		$response = ["success" => false];

		$comment = $_POST["comment"] ?? "";
		$attachment = $_POST["comment_attachment"] && !empty($_POST["comment_attachment"]) ? $_POST["comment_attachment"] : null;
		$comment_id = $_POST["comment_id"] ?? null;
		$reply_to = $_POST['reply_to'] ?? null;

		if ((mb_strlen($comment) < 6) || (mb_strlen($comment) > 1000)) {
			$response["msg"] = sprintf(_t("التعليق يجب أن يكون بين %s - %s حرف"), 6, 1000);
			$process = false;
		}

		$comment_type = 'comment';

		if (!empty($reply_to)) {
			$comment_type = 'reply';
		}

		$comment_parent = 0;
		if (!empty($reply_to)) {
			$comment_parent = $reply_to;
		}

		$no_reply = false;
		if ($comment_type == 'reply') {
			$no_reply = true;
		}
		
		if (!$process) {
			echo json_encode(($response));
			return;
		}

		global $dsql;

		$data = [
			"id" => $comment_id,
			"comment" => $comment,
			"post_id" => $post_id,
			"comment_user" => $current_user->id,
			"comment_date" => gmdate("Y-m-d H:i:s"),
			"comment_type" => $comment_type,
			"comment_attachment" => $attachment,
			"comment_status" => "publish",
			"comment_parent" => $comment_parent
		];

		$post_comment = save_post_comment($data);

		if (is_array($post_comment)) {

			$comment_id = $post_comment["id"];
			$post_author = get_post_field($post_id, "post_author");
			$comments_count = (int) get_post_field($post_id, "comments_count");
			$comments_count = $comments_count + 1;
			if ($post_author) {
				$points_remaining = @get_user_info($post_author)->points_remaining;
				$new_remaining_points = $points_remaining + distribute_points("posts_comments", "add", $post_author);
				if ($new_remaining_points > $points_remaining) {
					update_user_meta($post_author, "points_remaining", $new_remaining_points);
				}
			}

			$dsql->dsql()->table('posts')->set(["comments_count" => $comments_count])->where('id', $post_id)->update();
			insert_notif($current_user->id, $post_author, $post_id, "post_comment");
			$data["id"] = $post_comment["id"];

			$response["success"] = true;
			$response["msg"] = _t("تم إضافة التعليق بنجاح");
			$response['comment_id'] = $comment_id;
			$response['comment_type'] = $comment_type;
			$response['comment_parent'] = $comment_parent;
			$response['html'] = post_comment_html([$data], $no_reply);

		} else {
			$response["msg"] = _t("المعذرة ! لم يتم إدخال التعليق المرجو إعادة المحاولة");
		}

		echo json_encode($response);
	}
}

if (!function_exists("un_rate_ajax")) {
	/**
	 * un_rate_ajax()
	 */
	function un_rate_ajax()
	{
		$current_user = get_current_user_info();
		if ($current_user === false) {
			echo json_encode(["login_modal" => true]);
			return;
		}

		$response = ["success" => false];
		$post_id = $_POST["post_id"] ?? "";
		$rate_value = $_POST["rate_value"] ?? "";
		if (empty($post_id) || empty($rate_value)) {
			return false;
		}
		$user_id = $current_user->id;
		$un_rate = un_rate($post_id, $user_id, $rate_value);
		if ($un_rate) {
			$response["success"] = true;

			/** un_rate() function return array we merge it with @var response array to prevent add new key */
			$response = array_merge($response, $un_rate);
		} else {
			$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_lock_comment_ajax")) {
	/**
	 * un_lock_ajax
	 */
	function un_lock_comment_ajax()
	{
		$comment_id = $_POST["comment_id"] ?? "";
		$response = ["success" => false];
		$un_lock = un_lock_comment($comment_id);
		if ($un_lock) {
			$response["success"] = true;
		} else {
			$response["msg"] = _t("المعذرة ! ليس لك الصلاحيات للقيام بهذا الإجراء");
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_lock_post_ajax")) {
	/**
	 * un_lock_ajax
	 */
	function un_lock_post_ajax()
	{
		$post_id = $_POST["id"] ?? "";
		$response = ["success" => false];

		$un_lock = un_lock_post($post_id);

		if ($un_lock) {
			$response["success"] = true;
		} else {
			$response["msg"] = _t("المعذرة ! ليس لك الصلاحيات للقيام بهذا الإجراء");
		}

		echo json_encode($response);
	}
}

if (!function_exists("un_bookmark")) {
	/**
	 * un_bookmark()
	 */
	function un_bookmark()
	{
		$current_user = get_current_user_info();
		if ($current_user === false) {
			return false;
		}
		$response = ["success" => false];
		$post_id = $_POST["post_id"] ?? "";
		if (empty($post_id)) {
			return false;
		}
		global $dsql;
		// check if post already bookmared

		$check = get_user_meta($current_user->id, 'post_bookmark_' . $post_id);

		if ($check) {
			// delete post from user bookmared posts
			$delete = remove_user_meta($current_user->id, 'post_bookmark_' . $post_id);
			if ($delete) {
				$response["success"] = true;
				$response["msg"] = _t("تم الإزالة من المفضلة");
			} else {
				$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			}
		} else {
			$insert = update_user_meta($current_user->id, 'post_bookmark_' . $post_id, $post_id);
			if ($insert) {
				$response["success"] = true;
				$response["msg"] = _t("تم الإضافة إلى المفضلة");
			} else {
				$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_follow")) {
	/**
	 * un_follow()
	 *
	 */
	function un_follow($u_id = null, $sub_id = null)
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			echo json_encode(["login_modal" => true]);
			return;
		}

		$subscriber_id = $sub_id ?? $current_user->id;

		$user_id = $_POST["user_id"] ?? $u_id ?? "";
		if ($user_id == $subscriber_id) {
			return false;
		}
		global $dsql;
		$response = [];

		// check if current user already followed user($user_id)
		$check = count_rows($dsql->dsql()->table('subscribe_sys')->where('user_id', $user_id)->where('subscriber', $subscriber_id)->field('count(*)')->limit(1)->getRow());
		if ($check == 0) {
			// follow
			$insert = $dsql->dsql()->table('subscribe_sys')->set(["user_id" => $user_id, "subscriber" => $subscriber_id])->insert();
			if ($insert) {
				$response["success"] = true;
				insert_notif($subscriber_id, $user_id, null, "follow_user");
			} else {
				$response["success"] = false;
				$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			}
		} else {
			// unfollow
			$get_id = $dsql->dsql()->table('subscribe_sys')->where('user_id', $user_id)->where('subscriber', $subscriber_id)->limit(1)->getRow();
			if ($get_id) {
				$id = $get_id["id"];
				$delete = $dsql->dsql()->table('subscribe_sys')->where('id', $id)->delete();
				if ($delete) {
					$response["success"] = true;
				} else {
					$response["success"] = false;
					$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
				}
			} else {
				return false;
			}
		}
		if($u_id > 0 && $sub_id > 0) {
			return $response["success"];
		} else {
			echo json_encode($response);
		}
	}
}

if (!function_exists("send_message")) {
	function send_message()
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		$user_id = $_POST["user_id"] ?? "";
		if (empty($user_id)) {
			return false;
		}
		$msg = $_POST["msg"] ?? "";
		$response = [];
		$process = true;
		if (mb_strlen($msg) < 4 || mb_strlen($msg) > 1000) {
			$response["success"] = false;
			$response["msg"] = sprintf(_t("الرسالة يجب أن تكون بين %d - %d حرف"), 4, 1000);
			$process = false;
		}
		if ($process === true) {
			global $dsql;
			$msg_date = gmdate("Y-m-d H:i:s");
			$read_case = "sent";
			$msg_from = $current_user->id;
			$msg_to = $user_id;

			// check if users has aleady open conversation
			$get_msg_id = $dsql->dsql()->table('messages_sys');
			$get_msg_id->where($get_msg_id->expr("(msg_to = " . $current_user->id . " && msg_from = $user_id) or (msg_from = " . $current_user->id . " && msg_to = $user_id)"));
			$get_msg_id = $get_msg_id->field('id')->limit(1)->getRow();
			if ($get_msg_id) {
				$msg_id = $get_msg_id["id"];
			} else {
				$insert = $dsql->dsql()->table('messages_sys')->set(["msg_from" => $msg_from, "msg_to" => $msg_to, "msg_date" => $msg_date])->insert();
				$msg_id = get_last_inserted_id();
			}

			if (empty($msg_id)) {
				return false;
			}

			$insert_conversation = $dsql->dsql()->table('conversations_sys')->set(["msg_id" => $msg_id, "msg_from" => $msg_from, "msg_to" => $msg_to, "msg" => $msg, "msg_date" => $msg_date, "read_case" => $read_case])->insert();
			if ($insert_conversation) {
				$response["success"] = true;
				$response["msg"] = _t("تم إرسال رسالة بنجاح");
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("user_ajax")) {

	function user_ajax()
	{
		$response = ["success" => false];
		$request = $_POST["request"];
		$user = new User();
		$allowed_request = ["signup", "signin", "update_cv_info", "update_personal_info", "update_account_info", "update_social_accounts", "update_notifs_settings", "forget_password", "reset_password", "social_login", "verify_account"];
		if (!in_array($request, $allowed_request)) {
			return false;
		}
		$msg = '';
		switch ($request) {
			case "signup":
				$msg = _t('تم إنشاء حساب بنجاح');
				break;
			case "verify_account":
				$msg = _t("تم تفعيل الحساب بنجاح");
				break;
			case "update_cv_info":
			case "update_personal_info":
			case "update_account_info":
			case "update_social_accounts":
			case "update_notifs_settings":
			case "social_login":
				$msg = _t("تم تحديث البيانات بنجاح");
				break;
			case "forget_password":
				$msg = _t("تم إرسال رابط إسترجاع كلمة المرور. قم(ي) بتفقد بريدك الإلكتروني");
				break;
		}


		if ($user->$request()) {
			$response["success"] = true;
			$response["msg"] = $msg;
		} else {
			$response["inputs_errors"] = $user->get_errors();
		}
		echo json_encode($response);
	}
}

if (!function_exists("post_ajax")) {
	/**
	 * post_ajax()
	 */
	function post_ajax()
	{
		$request = $_POST["request"] ?? "";
		$response = ["success" => false];
		if ($request == "save_post") {
			$query_save_post = new save_post(
				[
					"post_id" => $_POST["post_id"] ?? "",
					"post_title" => $_POST["post_title"] ?? "",
					"post_content" => $_POST["post_content"] ?? "",
					"post_thumbnail" => $_POST["post_thumbnail"] ?? "",
					"post_category" => $_POST["post_category"] ?? "",
					"post_keywords" => $_POST["post_keywords"] ?? "",
					"post_type" => $_POST["post_type"] ?? "",
					"in_slide" => $_POST["in_slide"] ?? "",
					"in_special" => $_POST["in_special"] ?? "",
					"post_meta" => $_POST["post_meta"] ?? [],
					"post_lang" => current_content_lang(),
					"save_as" => $_POST["save_as"]
				]
			);
			$save_post = $query_save_post->save_post();

			if ($save_post) {
				$response["success"] = true;
				$response["msg"] = _t("تم نشر المحتوى بنجاح");
				$response["post_link"] = $save_post["post_link"];
			} else {
				$response["inputs_errors"] = $query_save_post->get_errors();
			}
		} elseif ($request == "save_post_info") {
			$query_save_post = new save_post_info(
				[
					"info_id" => $_POST["info_id"] ?? "",
					"post_category" => $_POST["post_category"] ?? "",
					"post_type" => $_POST["post_type_edit"] ?? "",
					"post_status" => isset($_POST['post_status']) ? 'publish' : 'auto-draft',
					"post_author" => $_POST['post_author'],
					"post_lang" => $_POST['post_lang'] ?? current_content_lang(),
					"save_as" => $_POST['save_as'],
					'number_fetch' => $_POST['number_fetch'],
					'post_show_pic' => isset($_POST['post_show_pic']) ? 'on' : 'off',
					'book_without_pdf' => $_POST['book_without_pdf'] ?? 'off',
					'post_source_1' => $_POST['post_source_1'],
					'post_source_2' => $_POST['post_source_2'],
					'post_fetch_url' => $_POST['post_fetch_url'],
				]
			);

			$save_post = $query_save_post->save_post();

			if ($save_post) {
				$response["success"] = true;
				$response["msg"] = _t("تم نشر المحتوى بنجاح");
				$response["post_link"] = siteurl() . "/admin/dashboard/contents";
			} else {
				$response["inputs_errors"] = $query_save_post->get_errors();
			}
		} elseif ($request == "delete_post") {
			$post_id = $_POST["id"] ?? "";
			if (empty($post_id)) {
				return false;
			}
			$delete =  new Delete($post_id);
			if ($delete->delete_posts()) {
				$response["success"] = true;
				$response["msg"] = "تم حذف بنجاح.";
			} else {
				$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("remove_meta_user_ajax")) {

	function remove_meta_user_ajax()
	{
		$current_user = get_current_user_info();
		if ($current_user === false) {
			return false;
		}
		$response = [];
		$meta_id = $_POST["meta_id"] ?? "";
		if (empty($meta_id)) {
			return false;
		}
		if (remove_user_meta($current_user->id, null, null, $meta_id)) {
			$response["success"] = true;
			$response["msg"] = _t("تم حدف بنجاح");
		} else {
			$response["success"] = false;
			$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
		}
		echo json_encode($response);
	}
}

if (!function_exists("popover_post_bk_json")) {
	/**
	 * poll_vote_ajax()
	 */
	function popover_post_bk_json()
	{

		$response = ["success" => false];
		$process = true;
		$current_user = get_current_user_info();
		if (!$current_user) {
			$response["login_modal"] = true;
		}

		$post_id = $_POST["post_id"] ?? "";

		if ($process && $post_id) {
			$response["success"] = true;
		}

		echo json_encode($response);
	}
}

if (!function_exists("popover_post_follow_json")) {
	/**
	 * poll_vote_ajax()
	 */
	function popover_post_follow_json()
	{

		$response = ["success" => false];
		$process = true;
		$current_user = get_current_user_info();
		if (!$current_user) {
			$response["login_modal"] = true;
		}

		$post_id = $_POST["post_id"] ?? "";

		if (empty($poll) || empty($vote)) {
			$response["msg"] = _t("حدث خطأ المرجو إعادة المحاولة");
			$process = false;
		}

		if ($process) {

			$html = '
				<span class="dropdown-item post-bk" data-post="'. esc_html($post_id) .'">
					'. bookmark_opt($post_id) .'
					<span class="ml-2">حفظ المنشور</span>
				</span>
			';
			$response["success"] = true;
			$response["html"] = $html;
		}

		echo json_encode($response);
	}
}
