<?php

function cv_badge_request()
{

	$response = ['success' => false];

	$user_id = $_POST['user_id'] ?? null;
	$request = $_POST['request'] ?? null;
	if (empty($user_id) || empty($request)) {
		return false;
	}

	$notif_content = '';
	$user_lang = get_user_meta($user_id, 'user_current_lang');


	if ($request == 'normal') {
		$notif_content = _t('تم إرجاع وسام السيرة الذاتية للحالة الطبيعة', $user_lang);
	} elseif ($request == 'accept') {
		$notif_content = _t('مبروك حصلت على وسام السيرة الذاتية', $user_lang);
	} elseif ($request == 'refuse') {
		$notif_content = _t('للأسف تم رفض طلب الحصول على وسام السيرة الذاتية', $user_lang);
	}

	$cv_badge_id = get_option('cv_badge_id');
	$manual_badges = @unserialize(get_user_meta($user_id, 'manual_badges'));
	if (!is_array($manual_badges)) {
		$manual_badges = [];
	}

	if ($request == 'normal') {
		global $dsql;
		$old_user_identify = get_user_meta($user_id, "user_identify");
		if (!empty($old_user_identify)) {
			$delete = new Delete([$old_user_identify]);
			$delete->delete_files();
		}
		$delete = $dsql->dsql()->table('user_meta')->where('user_id', $user_id)->where('meta_key', ['cv_badge_request', 'user_identify'])->delete();
		if ($delete) {
			if (($key = array_search($cv_badge_id, $manual_badges)) !== false) {
				unset($manual_badges[$key]);
			}
			$response['success'] = true;
		}
	} else {


		if ($request == 'accept') {
			$manual_badges[] = $cv_badge_id;
		}

		if (update_user_meta($user_id, 'cv_badge_request', $request)) {
			$response['success'] = true;
		}
	}
	if (!empty($notif_content)) {
		if (update_user_meta($user_id, "manual_badges", @serialize($manual_badges))) {
			insert_notif(0, $user_id, $notif_content, 'cv_badge_notif');
		}
	}

	echo json_encode($response);
}

if (!function_exists("group_alert")) {
	/**
	 * group_alert()
	 */
	function group_alert()
	{
		global $dsql;
		$response = ["success" => false];
		$lang = $_POST["lang"] ?? "";
		$gender = $_POST["gender"] ?? "";
		$alert = $_POST["alert"] ?? "";

		$query = $dsql->dsql()->table('users')->join('user_meta.user_id')->where('users.user_status', 'active')->field('users.id', 'id')->group('users.id');

		if (!empty($gender)) {
			$query->where('users.user_gender', $gender);
		}
		if (!empty($lang)) {
			$query->where('user_meta.meta_key', 'user_current_lang')->where('user_meta.meta_value', $lang);
		}

		$query = $query->get();
		if ($query) {
			foreach ($query as $user) {
				insert_notif(0, $user["id"], $alert, "group_alert");
			}
			$response["msg"] = "تم إرسال التنبيه بنجاح";
			$response["success"] = true;
		} else {
			$response["msg"] = "لم يتم العثور على أي مستخدم";
		}
		echo json_encode($response);
	}
}

if (!function_exists("seo_settings")) {
	/**
	 * seo_settings()
	 */
	function seo_settings()
	{
		$process = true;
		$response = ["success" => false];
		$seo_settings = $_POST["seo_settings"];

		if (is_array($seo_settings)) {
			foreach ($seo_settings as $meta_key => $meta_value) {
				if ($meta_key == "site_favicon") {
					$meta_value = (int) $meta_value;
				}
				$meta_value = @json_encode($meta_value);
				update_meta_settings($meta_key, $meta_value);
			}
		} else {
			$process = false;
		}

		if ($process) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير الإعدادات بنجاح.";
		} else {
			$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
		}
		echo json_encode($response);
	}
}

if (!function_exists("information_box")) {
	/**
	 * information_box()
	 */
	function information_box()
	{
		$process = true;
		$response = ["success" => false];
		$points_bag = @json_encode($_POST["points-bag"]);
		$instructions = @json_encode($_POST["instructions"]);
		if (!update_meta_options("points-bag", $points_bag)) {
			$process = false;
		}
		if (!update_meta_options("instructions", $instructions)) {
			$process = false;
		}
		if ($process) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير الإعدادات بنجاح.";
		} else {
			$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
		}
		echo json_encode($response);
	}
}

if (!function_exists("watermark")) {
	function watermark()
	{
		$response = ["success" => false];
		$watermark = $_POST["watermark"] ?? [];
		$watermark = @serialize($watermark);
		if (update_meta_options("watermark", $watermark)) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير الإعدادات بنجاح.";
		} else {
			$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
		}
		echo json_encode($response);
	}
}

if (!function_exists("category_visibility")) {
	/**
	 * category_visibility()
	 */
	function category_visibility()
	{
		$response = ["success" => false];
		$cat_id = $_POST["id"] ?? "";
		if (empty($cat_id)) {
			return false;
		}

		$get_cat = get_categories(null, $cat_id);
		if (!$get_cat) {
			return false;
		}

		$cat_settings = @unserialize($get_cat["cat_settings"] ?? []);
		$visible = $cat_settings["visible"] ?? "yes";
		if ($visible == "yes") {
			$cat_settings["visible"] = "hidden";
		} else {
			$cat_settings["visible"] = "yes";
		}

		$cat_settings = @serialize($cat_settings);
		global $dsql;
		$query = $dsql->dsql()->table('categories')->set(["cat_settings" => $cat_settings])->where('id', $cat_id)->update();
		if ($query) {
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("add_edit_category")) {
	/**
	 * add_edit_category()
	 */
	function add_edit_category()
	{
		$process = true;
		$response = ["success" => false];

		$category_name = $_POST["category_name"] ?? "";
		$category_lang = $_POST["category_lang"] ?? "";
		$category_keywords = $_POST["keywords"] ?? "";
		$category_taxonomy = $_POST["cat_taxonomy"] ?? "";
		$parent = $_POST["parent"] ?? "";
		$category_setting = $_POST["category_setting"];
		$category_setting['visible'] = $category_setting['visible'] ?? "yes";
		$category_setting = @serialize($category_setting);
		if (empty($category_name)) {
			$response["msg"] = "المرجو إدخال إسم القسم";
			$process = false;
		}
		if (empty($category_lang)) {
			$response["msg"] = "المرجو إختيار لغة للقسم";
			$process = false;
		}
		if (empty($category_taxonomy)) {
			$response["msg"] = "المرجو إختيار صنف للقسم";
			$process = false;
		}

		if ($process) {
			global $dsql;
			$action = $_POST["action"] ?? "";
			$cols = ["cat_title" => $category_name, "cat_lang" => $category_lang, "cat_settings" => $category_setting, "cat_parent" => $parent, "cat_taxonomy" => $category_taxonomy, "cat_keywords" => $category_keywords];
			if ($action == "add") {
				$query = $dsql->dsql()->table('categories')->set($cols)->insert();
			} elseif ($action == "edit") {
				$cat_id = $_POST["cat_id"] ?? "";
				if (empty($cat_id)) {
					return false;
				}
				$query = $dsql->dsql()->table('categories')->set($cols)->where('id', $cat_id)->update();
			} else {
				return false;
			}
			if ($query) {
				$response["success"] = true;
				$response["msg"] = "تم تغيير الإعدادات بنجاح.";
			} else {
				$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("add_edit_files_category")) {
	/**
	 * add_edit_files_category()
	 */
	function add_edit_files_category()
	{
		$files_cats = $_POST["files_cats"] ?? "";
		$response = ["success" => false];
		$process = true;
		if (empty(trim($files_cats[M_L]))) {
			$response["msg"] = "حقل الإسم باللغة الرئيسية للموقع إجباري.";
			$process = false;
		}
		if ($process) {
			global $dsql;
			$action = $_POST["action"] ?? "";
			$files_cats = json_encode($files_cats);
			$cols = ["category_title" => $files_cats];
			$cols_format = ["%s"];
			if ($action == "add") {
				$query = $dsql->dsql()->table('files_categories')->set($cols)->insert();
			} elseif ($action == "edit") {
				$cat_id = $_POST["cat_id"] ?? "";
				if (empty($cat_id)) {
					return false;
				}
				$query = $dsql->dsql()->table('files_categories')->set($cols)->where('id', $cat_id)->update();
			} else {
				return false;
			}
			if ($query) {
				$response["success"] = true;
				$response["msg"] = "تم تغيير الإعدادات بنجاح.";
			} else {
				$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
			}
		}
		echo json_encode($response);
	}
}
if (!function_exists("add_edit_lang")) {
	/**
	 * add_edit_lang()
	 */
	function add_edit_lang()
	{

		$response = ["success" => false];
		$process = true;
		global $dsql;

		$lang_name = trim($_POST["lang_name"] ?? "");
		$lang_code = $_POST["lang_code"] ?? "";
		$lang_dir = $_POST["dir"] ?? "";
		$lang_letters = $_POST['letters'] ?? '';
		$lang_visibility = $_POST["lang_visibility"] ?? "off";
		$lang_icon = $_POST["lang_icon"] ?? "";
		$lang_content = $_POST["lang_content"] ?? "";
		$lang_id = $_POST["lang_id"] ?? 0;

		if (empty($lang_name)) {
			$response["msg"] = "المرجو إدخال إسم اللغة";
			$process = false;
		}
		if (mb_strlen($lang_code) < 2) {
			$response["msg"] = "كود اللغة يجب أن يكون حرفين على الأقل";
			$process = false;
		}
		if (!in_array($lang_dir, ["rtl", "ltr"])) {
			$response["msg"] = "المرجو إختيار إتاجه اللغة.";
			$process = false;
		}

		if ($dsql->dsql()->table('languages')->where('lang_code', $lang_code)->where('id', '!=', $lang_id)->limit(1)->getRow()) {
			$response["msg"] = "المعذرة رمز اللغة مستعمل مسبقا.";
			$process = false;
		}
		/*
		if(isset($_FILES["translate_file"])) {
    		if(!upload_files($_FILES,$file_type,["mo"],__dir__ .DIRECTORY_SEPARATOR . "locale".DIRECTORY_SEPARATOR,null,false,$lang_code)) {
    		    $response["msg"] = "لم يتم رفع ملف الترجمة";
    		    $process = false;	    
    		}		
		}
		*/
		if ($process) {

			$action = $_POST["action"] ?? "";
			$cols = ["lang_name" => $lang_name, "lang_code" => $lang_code, "lang_dir" => $lang_dir, "lang_icon" => $lang_icon, "lang_visibility" => $lang_visibility, "content_lang" => $lang_content, "lang_letters" => $lang_letters];
			if ($action == "add") {
				$query = $dsql->dsql()->table('languages')->set($cols)->insert();
			} elseif ($action == "edit") {

				if (empty($lang_id)) {
					return false;
				}
				$query = $dsql->dsql()->table('languages')->set($cols)->where('id', $lang_id)->update();
			} else {
				return false;
			}
			if ($query) {
				$response["success"] = true;
				$response["msg"] = "تم تغيير الإعدادات بنجاح.";
			} else {
				$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
			}
		}
		echo json_encode($response);
	}
}
if (!function_exists("blocs_settings")) {
	/**
	 * blocs_settings()
	 *
	 */
	function blocs_settings()
	{
		$response = ["success" => false];
		$process = true;
		$settings = $_POST["meta_settings"];

		$site_logo = @unserialize(get_settings('bloc_settings_[header_bloc]'))['site_logo'];


		foreach ($settings as $setting_k => $setting_v) {
			if (!isset($setting_v["display"])) {
				$setting_v["display"] = "off";
			}

			if (isset($setting_v['site_logo']) && $setting_v['site_logo'] != $site_logo) {
				$delete = new Delete([$site_logo]);
				$delete->delete_files();
			}

			$setting_v = serialize($setting_v);
			$update_meta_settings = update_meta_settings("bloc_settings_[" . $setting_k . "]", $setting_v);
			if (!$update_meta_settings) {
				$process = false;
			}
		}
		if ($process) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير الإعدادات بنجاح.";
		} else {
			$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
		}
		echo json_encode($response);
	}
}

if (!function_exists("user_add")) {
	/**
	 * user_add()
	 */
	function user_add()
	{
		$response = ["success" => false];

		$data = [];

		$user = new User;

		if ($user->signup($data)) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير الإعدادات بنجاح.";
		} else {
			$response["input_errors"] = $user->get_errors();
		}
		echo json_encode($response);
	}
}

if (!function_exists("user_edit")) {
	/**
	 * user_edit()
	 */
	function user_edit()
	{
		$response = ["success" => false];
		$user = new User();
		if ($user->update_account_info()) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير الإعدادات بنجاح.";
		} else {
			$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
			$response["input_errors"] = $user->get_errors();
		}
		echo json_encode($response);
	}
}

if (!function_exists("users_settings")) {
	/**
	 * users_settings()
	 */
	function users_settings()
	{

		$process = true;
		$resposne = ["success" => false];

		$default_male_picture = $_POST["default_male_picture"] ?? "";
		$default_female_picture =  $_POST["default_female_picture"] ?? "";
		$users_settings = $_POST["users_settings"] ?? [];
		$users_settings_site_register = $_POST["users_settings"]["site_register"] ?? "off";

		/** push all input in @var array $users_settings */
		$users_settings["site_register"] = $users_settings_site_register;
		$users_settings["default_male_picture"] = $default_male_picture;
		$users_settings["default_female_picture"] = $default_female_picture;

		foreach ($users_settings as $option => $value) {
			$update_settings = update_meta_options($option, $value);
			if (!$update_settings) {
				$process = false;
				break;
			}
		}
		if ($process) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير الإعدادات بنجاح.";
		} else {
			$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
		}
		echo json_encode($response);
	}
}

if (!function_exists("change_role")) {
	/**
	 * change_role()
	 */
	function change_role()
	{
		$user_id = $_POST["user_id"] ?? "";
		$current_user = get_current_user_info();
		if (admin_authority()->users != "on" || is_super_admin($user_id)) {
			return false;
		}
		$response = ["success" => false];
		$role = $_POST["role"] ?? "";
		if (empty($role)) {
			return false;
		}
		global $dsql;
		$update_role = $dsql->dsql()->table('users')->set(["user_role" => $role])->where('id', $user_id)->update();
		if ($update_role) {
			$response["success"] = true;
			$response["msg"] = "تم تغيير رتبة المستخدم بنجاح.";
			insert_notif(0, $user_id, null, "add_new_role", 1);
		} else {
			$response["msg"] = "المعذرة ! المرجو إعادة المحاولة.";
		}
		echo json_encode($response);
	}
}

if (!function_exists("send_alert")) {
	/**
	 * send_alert()
	 */
	function send_alert()
	{
		if (admin_authority()->users != "on") {
			return false;
		}
		$user_id = $_POST["user_id"] ?? "";
		$msg = $_POST["msg"] ?? "";
		$response = ["success" => false];
		if (empty($user_id) || $user_id == 1) {
			return false;
		}
		$process = true;
		if (mb_strlen(strip_tags($msg)) < 8) {
			$response["msg"]  = "المعذرة الرسالة جد قصيرة";
			$process = false;
		}
		if ($process) {
			if (insert_notif(0, $user_id, $msg, "site_management")) {
				$response["success"] = true;
				$response["msg"] = "تم إرسال الرسالة بنحاح.";
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_lock_user_ajax")) {
	function un_lock_user_ajax()
	{
		$response = ["success" => false];
		$user_id = $_POST["id"] ?? "";
		if (un_lock_user($user_id)) {
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_lock_badge_ajax")) {
	function un_lock_badge_ajax()
	{
		if (admin_authority()->badges != "on") {
			return false;
		}

		$badge_id = $_POST["id"] ?? "";
		if (empty($badge_id)) {
			return false;
		}
		$response = ["success" => false];
		global $dsql;
		$user_status = $dsql->dsql()->table('badges')->where('id', $badge_id)->field('badge_case')->limit(1)->getRow()["badge_case"];
		$status = "";
		if ($user_status == 1) {
			$status = 2;
		} else {
			$status = 1;
		}
		$update = $dsql->dsql()->table('badges')->set(["badge_case" => $status])->where('id', $badge_id)->update();
		if ($update) {
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_verfiy_users")) {
	/**
	 * un_verfiy_users()
	 */
	function un_verfiy_users()
	{
		if (admin_authority()->users != "on") {
			return false;
		}
		$user_id = $_POST["id"] ?? "";
		if (empty($user_id)) {
			return false;
		}

		$response = ["success" => false];
		if (un_verify_user($user_id)) {
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_lock_ad_ajax")) {
	/**
	 * un_lock_ad_ajax
	 *
	 * @see functions.php 
	 */
	function un_lock_ad_ajax()
	{
		$ad_id = $_POST["id"] ?? "";
		if (empty($ad_id)) {
			return false;
		}
		$response = ["success" => false];
		if (un_lock_ad($ad_id)) {
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("un_lock_page_ajax")) {
	/**
	 * un_lock_page_ajax
	 *
	 */
	function un_lock_page_ajax()
	{
		$page_id = $_POST["id"] ?? "";
		if (empty($page_id)) {
			return false;
		}
		$response = ["success" => false];
		$page_case = get_page_field($page_id, "page_case");
		if (!$page_case) {
			return false;
		}
		global $dsql;
		$case = 'on';
		if ($page_case == 'on') {
			$case = 'off';
		}

		$update = $dsql->dsql()->table('pages')->set(["page_case" => $case])->where('id', $page_id)->update();

		if ($update) {
			$response["success"] = true;
		}
		echo json_encode($response);
	}
}

if (!function_exists("external_links")) {
	/**
	 * external_links()
	 */
	function external_links()
	{
		$response = ["response" => false];
		$process = true;
		$action = $_POST["action"] ?? "";
		$link_title = $_POST["link_title"] ?? "";
		$link_url = filter_var($_POST["link_url"] ?? "", FILTER_VALIDATE_URL);
		$link_target = $_POST["link_target"] ?? "";
		if (empty($link_url)) {
			$response["msg"] = "المرجو إدخال الرابط";
			$process = false;
		}

		if ($process === true) {
			global $dsql;
			$cols = ["link_title" => $link_title, "link_target" => $link_target, "link_url" => $link_url];
			if ($action == "add") {
				$insert = $dsql->dsql()->table('site_links')->set($cols)->insert();
				if ($insert) {
					$response["success"] = true;
					$response["msg"] = "تم إدخال الرابط بنجاح.";
				} else {
					$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($action == "edit") {
				$link_id = $_POST["link_id"] ?? "";
				if (empty($link_id)) {
					return false;
				}
				$update = $dsql->dsql()->table('site_links')->set($cols)->where('id', $link_id)->update();
				if ($update) {
					$response["success"] = true;
					$response["msg"] = "تم تحديث الرابط بنجاح.";
				} else {
					$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} else {
				return false;
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("taxonomies")) {
	/**
	 * taxonomies()
	 */
	function taxonomies()
	{

		$response = ["success" => false];
		$process = true;
		$action  = $_POST["action"] ?? "";
		$taxo_icon = $_POST["taxo_icon"] ?? "";
		$taxo_title = $_POST["taxo_title"] ?? "";
		$taxo_add_title = $_POST["taxo_add_title"] ?? "";
		$taxo_add_text = $_POST["taxo_add_text"] ?? "";
		$taxo_notice = $_POST["taxo_notice"] ?? "";
		$taxo_terms = $_POST["terms"] ?? [];
		$taxo_settings = $_POST["taxo_setting"] ?? [];
		$taxo_settings["copy"] = $taxo_settings["copy"] ?? "off";
		$taxo_settings["comment"] = $taxo_settings["comment"] ?? "off";
		$taxo_terms = @json_encode($taxo_terms);
		$taxo_settings = @serialize($taxo_settings);
		if ($process === true) {
			global $dsql;
			$taxo_title = json_encode($taxo_title);
			$taxo_add_title = json_encode($taxo_add_title);
			$taxo_add_text = json_encode($taxo_add_text);
			$taxo_notice = json_encode($taxo_notice);

			$cols =  ["taxo_title" => $taxo_title, "taxo_add_title" => $taxo_add_title, "taxo_add_text" => $taxo_add_text, "taxo_icon" => $taxo_icon, "taxo_notice" => $taxo_notice, "taxo_terms" => $taxo_terms, "taxo_settings" => $taxo_settings];
			if ($action == "edit") {
				$taxo_id = $_POST["taxo_id"] ?? "";
				if (empty($taxo_id)) {
					return false;
				}
				$update = $dsql->dsql()->table('taxonomies')->set($cols)->where('id', $taxo_id)->update();
				if ($update) {
					$meta_taxo_settings = $_POST["meta"] ?? "";
					foreach ($meta_taxo_settings as $meta_taxo_k => $meta_taxo_v) {
						$meta_taxo_v = serialize($meta_taxo_v);
						$update_meta_settings = update_meta_settings($meta_taxo_k, $meta_taxo_v);
					}
					$response["success"] = true;
					$response["msg"] = "تم تحديث بنجاح.";
				} else {
					$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} else {
				//
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("points")) {
	/**
	 * points()
	 */
	function points()
	{
		$response = ["success" => false];
		$points_add = $_POST["points_add"] ?? "";
		$points_substract = $_POST["points_substract"] ?? "";
		$apply_add = $_POST["apply_add"] ?? "";

		$points_options = array(

			"add" => $points_add,
			"substract" => $points_substract,
			"condition" => $apply_add

		);

		$points_options_serialize = serialize($points_options);

		if (update_meta_options("points", $points_options_serialize)) {
			$response["success"] = true;
			$response["msg"] = "تم تعديل بنجاح.";
		} else {
			$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
		}
		echo json_encode($response);
	}
}

if (!function_exists("countries")) {
	function countries()
	{
		$action = $_POST["action"] ?? "";
		if (!in_array($action, ["add", "edit"])) {
			return false;
		}
		$country_name = $_POST["country_name"] ?? "";
		$country_code = $_POST["country_code"] ?? "";
		$country_flag = $_POST["country_flag"] ?? "";

		$response = ["success" => false];
		$process = true;

		if (empty($country_name)) {
			$response["msg"] = "المرجو إدخال إسم الدولة";
			$process = false;
		}

		if (empty($country_code)) {
			$response["msg"] = "المرجو إدخال رمز الدولة";
			$process = false;
		}

		global $dsql;
		if ($action == "add") {
			if ($dsql->dsql()->table('countries')->where('country_code', $country_code)->limit(1)->getRow()) {
				$response["msg"] = "المعدرة رمز الدولة مستعمل مسبقا";
				$process = false;
			}
		} else {
			$country_id = $_POST["country_id"] ?? "";
			if (empty($country_id)) {
				return false;
			}
			if ($dsql->dsql()->table('countries')->where('country_code', $country_code)->where('id', '!=', $country_id)->limit(1)->getRow()) {
				$response["msg"] = "المعدرة رمز الدولة مستعمل مسبقا";
				$process = false;
			}
		}

		if ($process) {
			$data = ["country_name" => @json_encode($country_name), "country_code" => $country_code, "country_flag" => $country_flag];
			if ($action == "add") {
				$query = $dsql->dsql()->table('countries')->set($data)->insert();
			} else {
				$query = $dsql->dsql()->table('countries')->set($data)->where('id', $country_id)->update();
			}
			if ($query) {
				$response["success"] = true;
				$response["msg"] = "تم حفظ بنجاح.";
			} else {
				$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("social_accounts")) {
	function social_accounts()
	{
		$response = ["success" => false];
		$process = true;
		$plat = $_POST["plat"] ?? "";
		if (empty($plat)) {
			$response["msg"] = "المرجو إدخال إسم المنصة";
			$process = false;
		}

		if ($process) {
			$get_socialaccounts_settings = @unserialize(get_settings("s_c_settings"));
			if (!is_array($get_socialaccounts_settings)) {
				$get_socialaccounts_settings = [];
			}
			$s_c_settings = $_POST["socialaccounts_settings"];
			if ($s_c_settings["icon"]) {
				$s_c_settings["icon"] = $s_c_settings["icon"];
			} else {
				$s_c_settings["icon"] = $_POST["plat_icon"] ?? "";
			}
			$get_socialaccounts_settings[$plat] = $s_c_settings;
			$s_c_arr = @serialize($get_socialaccounts_settings);
			if (update_meta_settings("s_c_settings", $s_c_arr)) {
				$response["success"] = true;
				$response["msg"] = "تم حفظ بنجاح.";
			} else {
				$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("badges")) {
	/**
	 * badges()
	 */
	function badges()
	{

		$process = true;
		$response = ["success" => false];

		$badge_name = $_POST["badge_name"] ?? "";
		$badge_desc = $_POST["badge_desc"] ?? "";
		$badge_options =  $_POST["badge"] ?? "";
		$badge_icon = $_POST["badge_icon"] ?? "";

		if (empty($badge_name[M_L])) {
			$process = false;
			$response["msg"] = "المرجو إدخال إسم الوسام.";
		}

		$allowed_badge_types = array("seniority", "points", "trusted_posts", "manual", "role");
		if (!in_array($badge_options["condition"] ?? "", $allowed_badge_types)) {
			$process = false;
			$response["msg"] = "المرجو إختيار شرط الحصول على الوسام.";
		}

		if ($process) {
			global $dsql;
			$action = $_POST["action"] ?? "";
			$badge_name = json_encode($badge_name);
			$badge_desc = json_encode($badge_desc);
			$serialize_badge_options = serialize($badge_options);
			$cols = ["badge_name" => $badge_name, "badge_desc" => $badge_desc, "badge_options" => $serialize_badge_options, "badge_case" => 1, "badge_icon" => $badge_icon];
			if ($action == "add") {
				$query = $dsql->dsql()->table('badges')->set($cols)->insert();
			} elseif ($action == "edit") {
				$badge_id = $_POST["badge_id"] ?? "";
				if (empty($badge_id)) {
					return false;
				}

				$current_badge_icon = get_badge_field($badge_id, "badge_icon");
				if ($badge_icon != $current_badge_icon) {
					$delete = new Delete($current_badge_icon);
					$delete->delete_files();
				}
				$query = $dsql->dsql()->table('badges')->set($cols)->where('id', $badge_id)->update();
			} else {
				return false;
			}
			if ($query) {
				$response["success"] = true;
				$response["msg"] = "تم إضافة الوسام بنجاح.";
			} else {
				$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("roles")) {
	/**
	 * badges()
	 */
	function roles()
	{
		$process = true;
		$response = ["success" => false];
		$action = $_POST["action"] ?? "";
		$role_name = $_POST["role_name"] ?? "";
		$post_per_day = $_POST["post_per_day"] ?? "";
		$post_per_day_nolimit = $_POST["post_per_day_nolimit"] ?? "";
		$auto_approve = $_POST["auto_approve"] ?? "";
		$publish_in = $_POST["publish_in"] ?? "";
		$access_adminpanel = $_POST["access_adminpanel"] ?? "";
		$publish_in_all = $_POST["publish_in_all"] ?? "";
		$role_icon = $_POST["role_icon"] ?? "";
		$read_sources = $_POST["read_sources"] ?? "";
		$move_multi_posts = $_POST["move_multi_posts"] ?? "";
		$upload = $_POST["upload"] ?? '';
		$upload_links = $_POST["upload_links"] ?? '';
		$cv_badge = $_POST["cv_badge"] ?? '';
		$delete_content = $_POST['delete'] ?? '';
		if ($post_per_day_nolimit != "on" && empty($post_per_day)) {
			$process = false;
			$response["msg"] = "المرجو تحديد المقالات في اليوم الواحد.";
		}

		if (empty($role_name[M_L])) {
			$process = false;
			$response["msg"] = "المرجو إدخال إسم الرتبة.";
		}

		if ($post_per_day_nolimit == "on") {
			$post_per_day = "unlimited";
		}
		if ($auto_approve == "on") {
			$auto_approve = true;
		} else {
			$auto_approve = false;
		}

		if ($access_adminpanel == "on") {
			$access_adminpanel = true;
		} else {
			$access_adminpanel = false;
		}
		if ($publish_in_all == "on") {
			$publish_in = "all";
		}

		if ($read_sources == "on") {
			$read_sources = true;
		}

		if ($move_multi_posts == "on") {
			$move_multi_posts = true;
		}

		if ($delete_content == 'on') {
			$delete_content = true;
		}

		if ($process) {
			global $dsql;
			$permissions = ["delete" => $delete_content, "post_per_day" => $post_per_day, "auto_approve" => $auto_approve, "access_adminpanel" => $access_adminpanel, "publish_in" => $publish_in, "read_sources" => $read_sources, "move_multi_posts" => $move_multi_posts, "upload" => $upload, "upload_links" => $upload_links, "cv_badge" => $cv_badge];
			$role_name = json_encode($role_name);
			$permissions = json_encode($permissions);
			$cols = ["role_title" => $role_name, "role_permissions" => $permissions, "role_icon" => $role_icon, "role_case" => 1];
			if ($action == "add") {
				$insert = $dsql->dsql()->table('roles_permissions')->set($cols)->insert();
				if ($insert) {
					$response["success"] = true;
					$response["msg"] = "تم إضافة الرتبة بنجاح.";
				} else {
					$response["msg"] = "حدث خطأ المرجو إعادة المحاولة مرة أخرى.";
				}
			} elseif ($action == "edit") {
				$role_id = $_POST["role_id"] ?? "";
				if (empty($role_id)) {
					return false;
				}
				$update = $dsql->dsql()->table('roles_permissions')->set($cols)->where('id', $role_id)->update();
				if ($update) {
					$response["success"] = true;
					$response["msg"] = "تم تعديل بنجاح";
				} else {
				}
			} else {
				return false;
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("general_settings")) {
	/**
	 * general_settings()
	 *
	 */
	function general_settings()
	{
		$general_settings = $_POST["general_settings"] ?? "";
		$site_allowed_ext = explode(",", $general_settings["site_allowed_ext"]);
		$general_settings["site_allowed_ext"] = $site_allowed_ext;
		$general_settings = serialize($general_settings);
		$update_meta_settings = update_meta_settings("site_general_settings", $general_settings);
		if ($update_meta_settings) {
			$response["success"] = true;
			$response["msg"] = "تم تحديث بنجاح.";
		} else {
			$response["msg"] = "المعذرة ! حدث خطأ المرجو إعادة المحاولة";
		}
		echo json_encode($response);
	}
}

if (!function_exists("pages")) {
	/**
	 * pages()
	 *
	 */
	function pages()
	{
		$response = ["success" => false];
		$process = true;
		$action = $_POST["action"] ?? "";
		$page_title = $_POST["page_title"] ?? "";
		$page_translate = $_POST["page_translate"] ?? "";
		$page_content = $_POST["page_content"] ?? "";
		$page_lang = $_POST["page_lang"] ?? "";

		if (trim(empty($page_title))) {
			$response["msg"] = "المرجو إدخال عنوان الصفحة.";
			$process = false;
		}

		if (empty($page_lang)) {
			$response["msg"] = "المرجو إدخال لغة الصفحة.";
			$process = false;
		}
		if ($process) {
			global $dsql;
			$cols = ["page_title" => $page_title, "page_content" => $page_content, "page_lang" => $page_lang, "page_translate" => $page_translate, "page_case" => "on"];
			if ($action == "add") {
				$insert = $dsql->dsql()->table('pages')->set($cols)->insert();
				if ($insert) {
					$response["success"] = true;
					$response["msg"] = "تم إضافة الصفحة بنجاح.";
				} else {
					$response["msg"] = "المعذرة ! حدث خطأ المرجو إعادة المحاولة";
				}
			} elseif ($action == "edit") {
				$page_id = $_POST["page_id"] ?? "";
				if (empty($page_id)) {
					return false;
				}
				$update = $dsql->dsql()->table('pages')->set($cols)->where('id', $page_id)->update();
				if ($update) {
					$response["success"] = true;
					$response["msg"] = "تم تعديل الصفحة بنجاح.";
				} else {
					$response["msg"] = "المعذرة ! حدث خطأ المرجو إعادة المحاولة";
				}
			}
		}
		echo json_encode($response);
	}
}

if (!function_exists("ads")) {
	/**
	 * ads()
	 */
	function ads()
	{
		$process = true;
		$response = ["success" => false];
		$action = $_POST["action"] ?? "";
		$ad_title = $_POST["ad_title"] ?? "";
		$ad_link = $_POST["ad_link"] ?? "";
		$ad_area = $_POST["ad_area"] ?? "";
		$ad_lang = $_POST["ad_lang"] ?? "";
		$ad_code = $_POST["ad_code"] ?? "";
		$ad_image = $_POST["ad_image"] ?? "";

		if (empty($ad_title)) {
			$response["msg"] = "المغذرة ! المرجو التأكد من إسم الإعلان";
			$process = false;
		}

		if (!empty($ad_image)) {
			$ad_code = $ad_image;
		}

		if (empty($ad_code)) {
			$response["msg"] = "المغذرة ! المرجو التأكد من كود الإعلان";
			$process = false;
		}
		if (empty($ad_area)) {
			$response["msg"] = "المغذرة ! المرجو التأكد من مكان الإعلان";
			$process = false;
		}
		if ($process) {
			global $dsql;
			$ad_views = $ad_clicks = 0;
			$ad_case = 1;
			$ad_addDate = gmdate("Y-m-d H:i:s");
			$ad_key = bin2hex(random_bytes(32));
			$cols = ["ad_link" => $ad_link, "ad_title" => $ad_title, "ad_code" => $ad_code, "ad_area" => $ad_area, "ad_lang" => $ad_lang, "ad_clicks" => $ad_clicks, "ad_views" => $ad_views, "ad_case" => $ad_case, "ad_key" => $ad_key, "ad_date" => $ad_addDate];
			if ($action == "add") {
				$insert = $dsql->dsql()->table('ads')->set($cols)->insert();
				if ($insert) {
					$response["success"] = true;
					$response["msg"] = "تم إضافة الإعلان بنجاح.";
				} else {
					$response["msg"] = "المعذرة ! حدث خطأ المرجو إعادة المحاولة";
				}
			} elseif ($action == "edit") {
				$ad_id = $_POST["ad_id"] ?? "";
				if (empty($ad_id)) {
					return false;
				}
				$update = $dsql->dsql()->table('ads')->set($cols)->where('id', $ad_id)->update();
				if ($update) {
					$response["success"] = true;
					$response["msg"] = "تم تعديل الإعلان بنجاح.";
				} else {
					$response["msg"] = "المعذرة ! حدث خطأ المرجو إعادة المحاولة";
				}
			}
		}
		echo json_encode($response);
	}
}

// authors part
if (!function_exists("author_save")) {
	/**
	 * author_save()
	 */
	function author_save()
	{
		$response = ["success" => false];

		$data = [];

		$author = new Author();
		if ($author->update_profile_info()) {
			$response["success"] = true;
			$response["msg"] = "تمت العملية بنجاح.";
		} else {
			$response["input_errors"] = $author->get_errors();
		}
		echo json_encode($response);
	}
}

// boot part
	// comment
	if (!function_exists("comment_save")) {
    /**
     * comment_save()
     */
    function comment_save()
    {
        $response = ["success" => false];
        $errors = [];

        function update_comment_info($comments)
        {
            global $errors, $dsql;
            // Check for admin authority
            if (admin_authority()->comments != "on" && !is_super_admin()) {
                $errors[] = ["selector" => "#global-msg", "error" => "ليست لديك الصلاحية لاتمام العملية"];
                return false;
            }

            foreach ($comments as $comment) {
                // Prepare columns for insert/update
                $cols = [
                    "comment_name" => $comment['comment_name'],
                    'comment_lang' => $comment['comment_lang'] ?? "",
                    "comment" => $comment['comment'],
                ];

                // Check if it's an update or insert
                if (isset($comment['id']) && $comment['id'] !== null) {
                    $save = $dsql->dsql()->table('boot_comments')
                        ->set($cols)
                        ->where('id', $comment['id'])
                        ->update();
                } else {
                    $save = $dsql->dsql()->table('boot_comments')
                        ->set($cols)
                        ->insert();
                }

                if (!$save) {
                    $errors[] = ["selector" => "#global-msg", "error" => "حدث خطأ أثناء حفظ تعليق: " . $comment['comment']];
                    return false;
                }
            }

            return true;
        }

        // Sanitize and validate inputs
        $comment_name = isset($_POST["comment_name"]) 
            ? @filter_var($_POST["comment_name"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) 
            : "";
        $raw_comment = @filter_var($_POST["comment"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "";
        $comment_lang = isset($_POST["comment_lang"]) && !empty($_POST["comment_lang"])
            ? @filter_var($_POST["comment_lang"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) 
            : "";
        $comment_id = isset($_POST["comment_id"]) 
            ? @filter_var($_POST["comment_id"], FILTER_SANITIZE_NUMBER_INT) 
            : null;

        // Validate required fields
        if (empty($comment_name)) {
            $errors[] = ["selector" => "#comment_name", "error" => "ادخال الاسم اجباري"];
        }
        if (empty($raw_comment)) {
            $errors[] = ["selector" => "#comment", "error" => "ادخال النص اجباري"];
        }

        if (!empty($errors)) {
            $response["input_errors"] = $errors;
            echo json_encode($response);
            return;
        }

        // Split comments based on lines and punctuation
        $comments = array_filter(array_map('trim', preg_split('/\n|\r\n?/', $raw_comment)), function ($line) {
					return !empty($line); // Consider any non-empty line as a valid comment
				});
			

        // Prepare comment data for each line
        $comment_data = array_map(function ($text) use ($comment_lang, $comment_id, $comment_name) {
            return [
                'comment_name' => $comment_name,
                'comment' => $text,
                'comment_lang' => $comment_lang ?? NULL,
                'id' => $comment_id,
            ];
        }, $comments);

        // Update or insert comments
        if (update_comment_info($comment_data)) {
            $response["success"] = true;
            $response["msg"] = "تمت العملية بنجاح.";
        } else {
            $response["input_errors"] = $errors;
        }

        echo json_encode($response);
    }
	}


	// boot
	if (!function_exists("boot_save")) {
    /**
     * boot_save()
     */
    function boot_save()
		{
				$response = ["success" => false];
				$errors = [];

				function update_boot_info()
				{
						global $errors, $dsql;

						// Check for admin permissions
						if (admin_authority()->boots != "on" && !is_super_admin()) {
								$errors[] = ["selector" => "#global-msg", "error" => "ليست لديك الصلاحية لاتمام العملية"];
								return false;
						}

						// Fetch and sanitize input
						$name = $_POST['name'] ?? "";
						$lang = $_POST['lang'] ?? current_lang();
						$type = $_POST['type'] ?? "";
						$users_family = isset($_POST['users_family']) ? json_encode($_POST['users_family']) : "";
						$users = isset($_POST['users']) ? json_encode($_POST['users']) : "";
						$permissions = isset($_POST['boot_permissions']) ? json_encode($_POST['boot_permissions']) : "";
						$comments = isset($_POST['comments']) ? json_encode($_POST['comments']) : json_encode([]);
						$count_of_comments = $_POST['count_of_comments'] ?? 1;
						$boot_id = $_POST['boot_id'] ?? null;
						// Validation
						if (empty($name)) {
								$errors[] = ["selector" => "#name", "error" => "ادخال الاسم اجباري"];
						}

						if (isset($_POST['boot_category']) && count($_POST['boot_category']) > 0) {
								$cats = json_encode($_POST['boot_category']);
						} else {
								$cats_query = $dsql->dsql()->table('categories');
								if(!empty($type)) {
									$cats_query = $cats_query->where('cat_taxonomy', $type);
								}
								$cats_query = $cats_query->field('id')->get();
								$cats = array_map(function ($cat) {
										return $cat['id'];
								}, $cats_query);
								$cats = json_encode($cats);
						}

						if (empty($cats)) {
								$errors[] = ["selector" => "#boot_category", "error" => "تحديد الاقسام اجباري"];
						}

						if (empty($users_family)) {
								$errors[] = ["selector" => "#users_family", "error" => "تحديد اعضاء العائلة اجباري"];
						}

						if (empty($permissions)) {
								$errors[] = ["selector" => "#boot_permissions", "error" => "تحديد الصلاحيات اجباري"];
						}

						// Stop execution if there are validation errors
						if (!empty($errors)) {
								return false;
						}

						// Prepare data for saving
						$cols = [
								"name" => $name,
								'lang' => $lang,
								"type" => $type,
								"cats" => $cats,
								"users_family" => $users_family,
								"permissions" => $permissions,
								"stat" => 0,
						];

						if (!is_null($boot_id)) {
								$save = $dsql->dsql()->table('boots')
										->where('id', $boot_id)
										->set($cols)
										->update();
						} else {
								$save = $dsql->dsql()->table('boots')
										->set($cols)
										->insert();
						}

						if (!$save) {
								$errors[] = ["selector" => "#global-msg", "error" => "حدث خطأ أثناء حفظ البوت"];
								return false;
						}

						// Process additional meta information
						$boot_id = $boot_id ?? $dsql->lastInsertId() ?? 0;
						$cols_meta = [];

						$users = empty($users) ? "all" : $users;
						array_push($cols_meta, ['meta_key' => 'users', 'meta_value' => $users, 'boot_id' => $boot_id]);

						if (in_array("add_comments", $_POST['boot_permissions'])) {
								if (empty($comments) && !in_array("comment_execept", $_POST['boot_permissions']) && !in_array("comment_events", $_POST['boot_permissions'])) {
									$errors[] = ["selector" => "#comments", "error" => "تحديد عائلات التعليقات اجباري"];
									return false;
								}
								array_push($cols_meta, ['meta_key' => 'comments', 'meta_value' => $comments, 'boot_id' => $boot_id]);
								array_push($cols_meta, ['meta_key' => 'count_of_comments', 'meta_value' => $count_of_comments, 'boot_id' => $boot_id]);
						}

						if (count($cols_meta) > 0 && $boot_id > 0) {
								$success_counter = 0;

								// Delete existing metadata
								$dsql->dsql()->table('boot_meta')
										->where('boot_id', $boot_id)
										->where($dsql->expr('meta_key IN("comments","count_of_comments")'))
										->delete();

								// Insert new metadata
								foreach ($cols_meta as $cols) {
										$save_meta = $dsql->dsql()->table('boot_meta')
												->set($cols)
												->insert();
										if ($save_meta) {
												$success_counter++;
										}
								}

								if ($success_counter != count($cols_meta)) {
										$errors[] = ["selector" => "#global-msg", "error" => "حدث خطأ أثناء حفظ المعلومات الاضافية للبوت"];
										return false;
								}
						}

						return true;
				}

				// Run the update function and handle the response
				if (update_boot_info()) {
						$response["success"] = true;
						$response["msg"] = "تمت العملية بنجاح.";
				} else {
						$response["input_errors"] = $errors;
				}

				echo json_encode($response);
		}
	}

