<?php
require_once 'init.php';
$url = $_GET["url"] ?? "";
//$url = filter_var($url,FILTER_VALIDATE_URL);
$post_id = $_GET["post_id"] ?? "";
$plat = $_GET["plat"] ?? "";
if (empty($url) || empty($plat)) {
	exit(0);
}
// insert_analytics("post_share_" . $plat, $post_id);
$share_link = share_link($plat, $url);
if ($share_link) {
	$user_id = get_post_field($post_id, "post_author");
	if ($user_id) {
		$points_remaining = @get_user_info($user_id)->points_remaining;
		$new_remaining_points = $points_remaining + distribute_points("share", "add", $user_id);
		if ($new_remaining_points > $points_remaining) {
			update_user_meta($user_id, "points_remaining", $new_remaining_points);
		}
	}


	header("location:" . $share_link . "");
}
