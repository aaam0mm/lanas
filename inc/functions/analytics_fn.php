<?php

if (!function_exists("get_analytics")) {
	/**
	 * get analytics()
	 *
	 * @param string analysis_key
	 * @param string get_by
	 * @param int $user_id
	 * @param string $duration
	 * @param string $analysis_value
	 * @return (array)
	 */
	function get_analytics($analysis_key, $get_by, $user_id = null, $duration = "", $analysis_value = "")
	{

		global $dsql;
		$today = date("d");
		$this_month = date("m");
		$this_year = date("Y");

		$analytics = $dsql->dsql()->table('analytics')->where('analysis_key', 'like', '%' . $analysis_key . '%')->field('analytics.session_os,analytics.session_browser,analytics.session_countryCode,analytics.analysis_value');

		if ($duration == 'today') {
			$analytics->where($analytics->expr('DATE(session_date) = CURDATE()'));
		} elseif ($duration == 'month') {
			// We set this month and year for the current month and year but we can change it to any other date we want
			$analytics->where($analytics->expr('MONTH(session_date)'), $this_month)->where($analytics->expr('YEAR(session_date)'), $this_year);
		} elseif ($duration == 'year') {
			$analytics->where($analytics->expr('YEAR(session_date)'), $this_year);
		}

		$returns = [];

		if ($get_by == "posts_views") {
			$posts_info = array();
			$analytics->join('posts.id', 'analysis_value');
			if ($user_id) {
				$analytics->where('posts.post_author', $user_id);
			}

			$analytics->field('posts.*');
			$analytics = $analytics->get();
			if (is_array($analytics)) {
				foreach ($analytics as $post_info_k => $post_info_v) {
					$posts_info[$post_info_v["id"]]["post_in"] = $post_info_v["post_in"];
				}
			}
			foreach ($analytics as $post_info) {
				if (isset($returns["all_views"])) {
					$returns["all_views"] += 1;
				} else {
					$returns["all_views"] = 1;
				}

				if (@isset($returns[$posts_info[$post_info["analysis_value"]]["post_in"] . "_views"])) {
					$returns[$posts_info[$post_info["analysis_value"]]["post_in"] . "_views"] += 1;
				} else {
					@$returns[$posts_info[$post_info["analysis_value"]]["post_in"] . "_views"] = 1;
				}
			}
		} elseif ($get_by == "views_by_country") {
			$analytics->join('posts.id', 'analysis_value');
			if ($user_id) {
				$analytics->where('posts.post_author', $user_id);
			}

			$analytics->field('posts.*');
			$analytics = $analytics->get();
			foreach ($analytics as $post_info) {
				$posts_info[$post_info["id"]]["post_views"] = $post_info["post_views"];
				$posts_info[$post_info["id"]]["post_in"] = $post_info["post_in"];
			}
			foreach ($analytics as $analytic) {
				if (isset($returns[$analytic["session_countryCode"]]["all_views"])) {
					$returns[$analytic["session_countryCode"]]["all_views"] += 1;
				} else {
					$returns[$analytic["session_countryCode"]]["all_views"] = 1;
				}

				if (@$posts_info[$analytic["analysis_value"]]["post_in"] == "trusted") {
					if (isset($returns[$analytic["session_countryCode"]]["trusted_views"])) {
						$returns[$analytic["session_countryCode"]]["trusted_views"] += 1;
					} else {
						$returns[$analytic["session_countryCode"]]["trusted_views"] = 1;
					}
				} else {
					if (isset($returns[$analytic["session_countryCode"]]["untrusted_views"])) {
						$returns[$analytic["session_countryCode"]]["untrusted_views"] += 1;
					} else {
						$returns[$analytic["session_countryCode"]]["untrusted_views"] = 1;
					}
				}
			}
		} elseif ($get_by == "browser" || $get_by == "os") {
			$analytics = $analytics->get();
			foreach ($analytics as $analytic) {
				$returns[] = $analytic["session_" . $get_by] ?? '';
			}
			$returns = array_count_values($returns);
		} elseif ($get_by == "site_visitors") {
			$analytics = $analytics->get();
			foreach ($analytics as $analytic) {
				if (isset($returns["visits"])) {
					$returns["visits"] += 1;
				} else {
					$returns["visits"] = 1;
				}
			}
		} elseif ($get_by == 'online_users') {

			$get_users = $dsql->dsql()->table('users');
			$get_users->where($get_users->expr('user_lastseen > NOW() - INTERVAL 2 minute'))->field('count(*)', 'online');
			$get_users = $get_users->getRow();
			$returns['online_users'] = $get_users['online'];
		} elseif ($get_by == "count_users") {

			$get_users = $dsql->dsql()->table('users')->get();
			foreach ($get_users as $u_i) {
				$usr_case = $u_i["user_status"];
				if (isset($returns["all_users"])) {
					$returns["all_users"] += 1;
				} else {
					$returns["all_users"] = 1;
				}
				if (isset($returns[$usr_case])) {
					$returns[$usr_case] += 1;
				} else {
					$returns[$usr_case] = 1;
				}
			}
		} elseif ($get_by == "posts_analytics") {
			$analytics->join('posts.id', 'analysis_value');
			if ($user_id) {
				$analytics->where('posts.post_author', $user_id);
			}

			// posts trusted/untrusted

			// posts status

			$analytics = $dsql->dsql()->table('posts');
			$analytics->field($analytics->expr('post_in,count(post_in) as total_post_in,post_status,count(post_status) as total_post_status'));
			$analytics->group($analytics->expr('post_status,post_in'));
			$analytics = $analytics->where('post_status', 'not in', ['draft', 'auto-draft'])->get();
			foreach ($analytics as $uposts) {

				$returns[$uposts["post_status"] . "_posts"] = $uposts['total_post_status'];
				isset($returns[$uposts["post_in"] . "_posts"]) ? $returns[$uposts["post_in"] . "_posts"] += $uposts['total_post_in'] : $returns[$uposts["post_in"] . "_posts"] = $uposts['total_post_in'];
			}
		} elseif ($get_by == "posts_shares") {
			$analytics->join('posts.id', 'analysis_value');
			if ($user_id) {
				$analytics->where('posts.post_author', $user_id);
			}
			$analytics->field('posts.*');
			$analytics = $analytics->get();
			if (is_array($analytics)) {
				foreach ($analytics as $analytic) {
					isset($returns["shares"]) ? $returns["shares"] += 1 : $returns["shares"] = 1;
				}
			}
		}
		return $returns;
	}
}

if (!function_exists("getip")) {
	/**
	 * getip()
	 * get user ip 
	 *
	 * @return string
	 */
	function getip()
	{
		switch (true) {
			case (!empty($_SERVER['HTTP_X_REAL_IP'])):
				return $_SERVER['HTTP_X_REAL_IP'];
			case (!empty($_SERVER['HTTP_CLIENT_IP'])):
				return $_SERVER['HTTP_CLIENT_IP'];
			case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])):
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			default:
				return $_SERVER['REMOTE_ADDR'];
		}
	}
}

function getOS()
{
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$os_platform = "Unknown OS Platform";
	$os_array = array(
		'/windows nt 10.0/i'     =>  'Windows 10.0',
		'/windows nt 6.3/i'     =>  'Windows 8.1',
		'/windows nt 6.2/i'     =>  'Windows 8',
		'/windows nt 6.1/i'     =>  'Windows 7',
		'/Windows NT 7.0/i'     =>  'Windows 7',
		'/windows nt 6.0/i'     =>  'Windows Vista',
		'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     =>  'Windows XP',
		'/Windows NT5.1/i'     =>  'Windows XP',
		'/windows xp/i'         =>  'Windows XP',
		'/Windows 2000/i'     =>  'Windows 2000',
		'/Windows NT 4.0/i'     =>  'Windows NT',
		'/Windows NT 5.2/i'     =>  'Windows Server 2003',
		'/WinNT4.0/i'     =>  'Windows NT',
		'/windows nt 5.0/i'     =>  'Windows 2000',
		'/windows me/i'         =>  'Windows ME',
		'/Win 9x 4.90/i'         =>  'Windows ME',
		'/Windows CE/i'         =>  'Windows CE',
		'/Windows 98/i'              =>  'Windows 98',
		'/win98/i'              =>  'Windows 98',
		'/win95/i'              =>  'Windows 95',
		'/Windows 95/i'              =>  'Windows 95',
		'/win32/i'              =>  'Windows',
		'/microsoft/i'              =>  'Windows',
		'/teleport/i'              =>  'Windows',
		'/web downloader/i'              =>  'Windows',
		'/flashget/i'              =>  'Windows',
		'/win16/i'              =>  'Windows 3.11',
		'/macintosh|mac os x/i' =>  'Mac OS X',
		'/mac_powerpc/i'        =>  'Mac OS 9',
		'/mac|Macintosh/i'        =>  'Mac OS',
		'/linux/i'              =>  'Linux',
		'/ubuntu/i'             =>  'Ubuntu',
		'/iphone/i'             =>  'iPhone',
		'/ipod/i'               =>  'iPod',
		'/ipad/i'               =>  'iPad',
		'/android/i'            =>  'Android',
		'/blackberry/i'         =>  'BlackBerry',
		'/dos x86/i'         =>  'DOS',
		'/unix/i'         =>  'Unix',
		'/webos/i'              =>  'Mobile'
	);
	foreach ($os_array as $regex => $value) {
		if (preg_match($regex, $user_agent)) {
			$os_platform    =   $value;
		}
	}
	return $os_platform;
}

function getBrowser()
{
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$browser = "Unknown Browser";
	$browser_array  = array(
		'/msie/i'       =>  'Internet Explorer',
		'/firefox/i'    =>  'Firefox',
		'/safari/i'     =>  'Safari',
		'/chrome/i'     =>  'Chrome',
		'/opera/i'      =>  'Opera',
		'/netscape/i'   =>  'Netscape',
		'/maxthon/i'    =>  'Maxthon',
		'/konqueror/i'  =>  'Konqueror',
		'/mobile/i'     =>  'Handheld Browser'
	);
	foreach ($browser_array as $regex => $value) {
		if (preg_match($regex, $user_agent)) {
			$browser    =   $value;
		}
	}
	return $browser;
}


if (!function_exists("insert_analytics")) {
	/**
	 * insert_analytics()
	 *
	 * @param string $analysis_key
	 * @param string $analysis_key
	 * @return boolean
	 */
	function insert_analytics($analysis_key, $analysis_value)
	{
		if (empty($analysis_key) || empty($analysis_value) || is_bot()) {
			return false;
		}
		
		global $dsql;
		$session_ip = getip();
		// $session_ip = '8.8.8.8';
		$csrf = _csrf();
		
		$data_exist = $dsql->dsql()->table('analytics');
		
		$data_exist->where($data_exist->orExpr()->where('session_id', $csrf)->where('session_ip', $session_ip))->where('analysis_key', $analysis_key)->where('analysis_value', $analysis_value)->field('count(*)', 'records');
		
		if (((int) $data_exist->getRow()['records']) > 0) {
			return false;
		}
		
		$session_os = getOS();
		$session_browser = getBrowser();
		$session_date = date("Y-m-d h:i:s");
		$session_id = NULL;
		$current_user = get_current_user_info();
		
		if ($current_user) {
			$session_id = $current_user->id;
		}
		
		$ip_info = file_get_contents("http://ip-api.com/json/" . $session_ip);
		$ip_info = json_decode($ip_info);
		$session_countryCode =  @strtolower($ip_info->countryCode);
		
		$data = [
			"session_ip" => $session_ip,
			"session_id" => $csrf, // Use $csrf for the session identifier
			"session_browser" => $session_browser,
			"session_os"  => $session_os,
			"session_date" => $session_date,
			"session_user" => $session_id, // This should be the user ID, which is 1
			"session_activities" => '', // Make sure to define this
			"analysis_key" => $analysis_key,
			"analysis_value" => $analysis_value,
			"session_countryCode" => $session_countryCode
		];
		$insert_session = $dsql->dsql()->table('analytics')->set($data)->insert();
		if ($insert_session) {
			if ($analysis_key == "post_views") {
				$post_author = get_post_field($analysis_value, 'post_author');
				$update_posts_views = $dsql->dsql()->table('posts');
				$update_posts_views->set($update_posts_views->expr($analysis_key), $update_posts_views->expr("{$analysis_key} + 1"))->where('id', $analysis_value)->update();
				$points_remaining = @get_user_info($post_author)->points_remaining;
				$new_remaining_points = $points_remaining + distribute_points("posts_views", "add", $post_author);
				if ($new_remaining_points > $points_remaining) {
					update_user_meta($post_author, "points_remaining", $new_remaining_points);
				}
			} elseif ((strpos($analysis_key, "post_share") !== false)) {
				$update_post_shares = $dsql->dsql()->table('posts');
				$update_post_shares->set("post_share", $update_post_shares->expr('post_share + 1'))->where('id', $analysis_value)->update();
			}
		}
		return true;
	}
}
