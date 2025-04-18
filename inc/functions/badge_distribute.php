<?php
if(!function_exists("badges_distribute")) {
	/** badges_distribute()
	 *
	 * @param int $user_id
	 * @param int $user_points
	 * @param int $user_seniority
	 * @param int $user_trusted_posts
	 * @param int $user_role
	 */
	function badges_distribute($user_id) {
		$lang = current_lang();
		$get_badges = get_badges();
		$user_points = get_user_meta($user_id,"points_remaining");
		$user_role = get_user_field($user_id,"user_role");
		$user_trusted_posts = count_user_posts($user_id);
		$user_joindate = get_user_field($user_id, 'user_joindate');
		
		$user_joindate = new DateTime($user_joindate);
        $today = new DateTime("now");
        $user_seniority = $user_joindate->diff($today)->y;
		$trusted_posts_badge = $seniority_badge = $points_badge = $roles_badge = $manual_badges = $badges_returns = array();
		if($get_badges) {
			foreach($get_badges as $badge_option_k=>$badge_option_v) {
			   $badge_id = $badge_option_v["id"];
			   $badge_icon = $badge_option_v["badge_icon"];
			   $badge_desc = json_decode($badge_option_v["badge_desc"])->$lang;
			   $badge_name = json_decode($badge_option_v["badge_name"])->$lang;
			   $badge_option = unserialize($badge_option_v["badge_options"]);
			   $badge_points_remainig = $badge_option["points_remaining"] ?? 0;
			   $badge_condition = $badge_option["condition"];
			   if($badge_option["condition"] != "manual") {
					$badge_requirement = $badge_option[$badge_condition];
			   }
			   if($badge_condition == "points" && $badge_requirement <= $user_points) {
				   $points_badge[$badge_requirement] = array( "badge_id" => $badge_id, "badge_name" => $badge_name , "badge_icon" => $badge_icon , "badge_desc" => $badge_desc );
				}
			   if($badge_condition == "seniority" && $badge_requirement <= $user_seniority) {
					$seniority_badge[$badge_requirement] = array( "badge_id" => $badge_id, "badge_name" => $badge_name , "badge_icon" => $badge_icon , "badge_desc" => $badge_desc );
				}
				if($badge_condition == "trusted_posts" && $badge_requirement <= $user_trusted_posts) {
					$trusted_posts_badge[$badge_requirement] = array( "badge_id" => $badge_id, "badge_name" => $badge_name , "badge_icon" => $badge_icon , "badge_desc" => $badge_desc );
				}
				if($badge_condition == "role" && $badge_requirement == $user_role) {
					$roles_badge[0] = array( "badge_id" => $badge_id, "badge_name" => $badge_name , "badge_icon" => $badge_icon , "badge_desc" => $badge_desc );
				}
			}
		}
		
		$get_meta_manual_badges = @unserialize(get_user_meta($user_id,"manual_badges"));
		if(is_array($get_meta_manual_badges)) {
			foreach($get_meta_manual_badges as $manual_badge_id) {
				$get_badge_info = get_badges($manual_badge_id);
				if($get_badge_info) {
					$badge_name = json_decode($get_badge_info["badge_name"])->$lang;
					$badge_desc = json_decode($get_badge_info["badge_desc"])->$lang;
					$badge_icon = $get_badge_info["badge_icon"];
					$badge_id = $get_badge_info["id"];
					$badges_returns[] = array( "badge_id" => $badge_id, "badge_name" => $badge_name, "badge_icon" => $badge_icon, "badge_desc" => $badge_desc );
				}
			}
		}
		
		if(count(array_keys($points_badge)) > 0) {
			$max_points_badge = max(array_keys($points_badge));
			if($max_points_badge <= $user_points) {
				$badges_returns[] = $points_badge[$max_points_badge];
			}
		}
		if(count(array_keys($seniority_badge)) > 0) {
			$max_seniority_badge = max(array_keys($seniority_badge));
			if($max_seniority_badge <= $user_seniority) {
				$badges_returns[] = $seniority_badge[$max_seniority_badge];
			}
		}
		if(count(array_keys($trusted_posts_badge)) > 0) {
			$max_trusted_posts_badge = max(array_keys($trusted_posts_badge));
			if($max_trusted_posts_badge <= $user_trusted_posts) {
				$badges_returns[] = $trusted_posts_badge[$max_trusted_posts_badge];
			}
		}
		
		if($roles_badge) {
			$badges_returns[] = $roles_badge[0];
		}
		
		return $badges_returns;
	}
}