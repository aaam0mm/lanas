<?php
require_once "../init.php";
require_once ROOT . "/ajax/functions.php";
$method = $_POST["method"] ?? "";
if(!empty($method)) {

	$allowed_methods = [
		"upload_ajax", "user_ajax", "post_ajax", "remove_meta_user_ajax", "send_message", "un_follow", "un_bookmark", "un_lock_comment_ajax", "un_rate_ajax", "save_post_comment_ajax",
		"lang_settings", "contact_form", "send_complain_ajax", "poll_vote_ajax", "un_reaction", "instant_notifs", "open_notifs", "delete_post", "delete_comment", "un_subscribe_taxonomy",
		"delete_account_request", "get_analytics_nums",  "merge_to_un_trusted", "delete_account",
		"multi_action", "close_notif", "delete_file_ajax", "post_notice_ajax", "send_cv_badge_order", "delete_conversation","group_alert","seo_settings","information_box",
		"watermark","category_visibility","add_edit_category","add_edit_files_category","add_edit_lang","blocs_settings","user_add","user_edit","users_settings","change_role",
		"send_alert","un_lock_user_ajax","un_lock_badge_ajax","un_verfiy_users","un_lock_post_ajax","un_lock_ad_ajax","un_lock_page_ajax","external_links","taxonomies","points",
		"countries","social_accounts","badges","roles","general_settings","pages","ads","cv_badge_request","user_signup_ajax",
		"author_save", "popover_post_bk_json", "popover_post_follow_json", "authorize_share", "comment_save", "boot_save"
	];

	if(in_array($method,$allowed_methods)) {
		if(call_user_func($method) === false) {
				exit(http_response_code("400"));
		};
	}else{
		exit(http_response_code("404"));
	}
}else{
	exit(http_response_code("404"));
}
