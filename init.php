<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
$dsql = db();
cache_init();
if(!isset($_SESSION)) {
    session_start();
}
$general_settings = @unserialize( get_settings("site_general_settings") );
$general_settings["lock_site"] = "off";
if((isset($general_settings["lock_site"]) && $general_settings["lock_site"] == "on") && is_admin() === false) {
    $display_errors = [];
	$display_errors[] = ["error" => _t("الموقع مغلق حاليا")];
    echo error_page($display_errors,'<div style="text-align:center;"><img src="'.siteurl().'/assets/images/under-constraction.svg" style="width:50%;"/></div>');
    exit(0);
}

/** set main language in user cookie */
//set_lang(M_L,false);

if(is_login_in()) {
    if(get_user_field(get_current_user_info()->id,"user_status") == "email_verify") {
        if(!in_array(basename($_SERVER["SCRIPT_NAME"]),["confirm_account.php","logout.php","ajax_service.php"])) {
            header("location:".siteurl()."/confirm_account.php");
            exit(0);
        }
    }
}
