<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * core_fn.php
 * this file contain all function that not used to communcation with database
 */
require_once 'get_head.php';

function pre_print($value)
{
	return '<pre>' . print_r($value) . '</pre>';
}

if (!function_exists('count_last_query')) {
	/**
	 * Return total rows matched from last run query
	 * if you want count results from some query call this function directly after it
	 */
	function count_last_query()
	{
		global $dsql;
		return count_rows($dsql->dsql()->expr("SELECT FOUND_ROWS() AS count")->getRow(), 'count');
	}
}

if (!function_exists("may_object")) {
	/**
	 * Check wheather string is json encoded string
	 */
	function may_object($string)
	{

		$string = json_decode($string);
		return is_object($string);
	}
}
if (!function_exists("absint")) {

	function absint($maybeint): int
	{
		return abs(intval($maybeint));
	}
}

if (!function_exists("may_array")) {
	/**
	 *
	 */
	function may_array($string) {}
}

if (!function_exists("count_rows")) {
	/**
	 * @param array $array
	 * @param string $column
	 *
	 * @return int
	 */
	function count_rows(array $array, $column = "count(*)")
	{
		return (int) $array[$column];
	}
}

if (!function_exists("get_metadata")) {
	/**
	 * get_meta_value() function : used to quick get meta_value column from some meta_{table}
	 *
	 * @param stirng $table
	 * @param string $meta_key	 
	 * @param string $meta_special
	 * @param string $meta_special_val
	 * @param int $meta_id
	 */
	function get_metadata($meta_type, $object_id, $meta_key, $single = true, $return_meta_id = false)
	{

		$get_cache = get_cache($object_id, $meta_type . '_meta');
		if ($get_cache && isset($get_cache[$meta_key])) {
			return $get_cache[$meta_key];
		}

		global $dsql;

		$column = $meta_type . '_id';
		$query = $dsql->dsql()->table($meta_type . '_meta')->where("meta_key", $meta_key)->where($column, $object_id);
		$query->field("meta_value");

		if ($return_meta_id) {
			$query->field('id');
		}

		$results = $query->get();

		if ($results) {

			if ($single && $return_meta_id === false) {
				$results = $results[0]['meta_value'];
			} elseif ($single && $return_meta_id) {
				$results = $results[0];
			} elseif ($single === false && $return_meta_id === false) {
				$results = array_column($results, 'meta_value');
			}
			add_cache($object_id, $meta_type . '_meta', $results);
			return $results;
		}
	}
}

if (!function_exists("db")) {
	/**
	 * db() 
	 * connect to database
	 *
	 * @return object|boolean
	 */
	function db()
	{
		$dsn = "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST . ";port=". DB_PORT .";charset=utf8mb4";
		return atk4\dsql\Connection::connect($dsn, DB_USER, DB_PASSWORD);
	}
}

if (!function_exists("getPdoConnection")) {
	/**
	 * getPdoConnection() 
	 * Connect to the database using PDO
	 *
	 * @return PDO|false
	 */
	// Function to create a PDO connection
	function getPdoConnection()
	{
		try {
			// Define DSN and credentials
			$dsn = "mysql:dbname=u794773365_nas;host=localhost;charset=utf8mb4";
			$user = 'u794773365_nas';
			$password = 'ZT@y6?th@Pgy';

			// Create a new PDO instance
			$pdo = new PDO($dsn, $user, $password);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			return $pdo;
		} catch (PDOException $e) {
			// Log and handle connection error
			error_log('PDO connection failed: ' . $e->getMessage());
			echo 'PDO connection failed: ' . $e->getMessage() . '<br>';
			return false;
		}
	}
}

if (!function_exists("write_ini_file")) {
	/**
	 * write_ini_file()
	 *
	 * @param array assoc_arr
	 * @param string assoc_arr
	 */
	function write_ini_file($assoc_arr, $path)
	{
		$content = "";

		foreach ($assoc_arr as $key => $elem) {
			if (is_array($elem)) {
				for ($i = 0; $i < count($elem); $i++) {
					$content .= $key . "[] = " . $elem[$i] . "\n";
				}
			} else if ($elem == "") $content .= $key . " = \n";
			else $content .= $key . " = " . $elem . "\n";
		}
		if (!$handle = fopen($path, 'w')) {
			return false;
		}

		$success = fwrite($handle, $content);
		fclose($handle);

		return $success;
	}
}

if (!function_exists("unlinkr")) {
	/**
	 * unlinkr()
	 *
	 * @param string $dir
	 * @param string $pattern
	 */
	function unlinkr($dir, $pattern = "*")
	{
		// find all files and folders matching pattern
		$files = glob($dir . "/$pattern");
		//interate thorugh the files and folders
		foreach ($files as $file) {
			//if it is a directory then re-call unlinkr function to delete files inside this directory     
			if (is_dir($file) and !in_array($file, array('..', '.'))) {
				unlinkr($file, $pattern);
				//remove the directory itself
				rmdir($file);
			} else if (is_file($file) and ($file != __FILE__)) {
				// make sure you don't delete the current script
				unlink($file);
			}
		}
		rmdir($dir);
	}
}

if (!function_exists("current_lang")) {
	/**
	 * current_lang()
	 * get cookie lang value
	 *
	 */
	function current_lang()
	{
		if (empty($_SESSION) || empty($_COOKIE)) {
			//session_start();
		}
		// return $_COOKIE["lang"] ?? M_L;
		return $_COOKIE["lang"] ?? 'ar';
	}
}

if (!function_exists('is_lang_set')) {
	function is_lang_set()
	{
		return isset($_COOKIE["lang"]) && !empty($_COOKIE["lang"]);
	}
}

if (!function_exists("current_content_lang")) {
	/**
	 * current_content_lang()
	 * get cookie lang value
	 *
	 */
	function current_content_lang()
	{
		if (empty($_SESSION) || empty($_COOKIE)) {
			//session_start();
		}
		if (is_login_in()) {
			return get_current_user_info()->user_lang;
		}
		// return $_COOKIE["content_lang"] ?? M_L;
		return $_COOKIE["content_lang"] ?? 'ar';
	}
}

if (!function_exists("set_lang")) {
	/**
	 * set_lang()
	 * set lang cookie
	 *
	 */
	function set_lang($site_lang = "", $content_lang = "")
	{
		//if(empty($_SESSION) && empty($_COOKIE)) {
		session_start();
		//}

		if (is_login_in()) {
			$content_lang = null;
		}
		if (!empty($site_lang)) {
			setcookie("lang", $site_lang, strtotime("+30 days"), "/", "", is_ssl(), true); // This cookie store in user device for 30 days
			if (!is_null($content_lang)) {
				setcookie("content_lang", $content_lang, strtotime("+30 days"), "/", "", is_ssl(), true); // This cookie store in user device for 30 days
			}
		}
	}
}

if (!function_exists("is_ltr")) {
	/**
	 * is_ltr()
	 * check if language set by user direction
	 * 
	 * @return boolean
	 */
	function is_ltr()
	{
		if (function_exists("get_langs")) {
			$get_lang = get_langs(current_lang());
			if ($get_lang["lang_dir"] == "ltr") {
				return true;
			}
		}
		return false;
	}
}

if (!function_exists("is_rtl")) {
	/**
	 * is_rtl()
	 * 
	 * 
	 * @return boolean
	 */
	function is_rtl()
	{
		if (is_ltr()) {
			return "false";
		}
		return "true";
	}
}

if (!function_exists("get_header")) {
	/**
	 * get_header()
	 * Get headers sections depending on page
	 * All files should located in main site direcorty
	 *
	 * @param string $name
	 */
	function get_header($name = null)
	{
		$header_file = "header.php";
		if (!empty($name)) {
			$header_file = "header-" . $name . ".php";
		}
		if (file_exists($header_file)) {
			include_once $header_file;
		}
	}
}

if (!function_exists("get_footer")) {
	/**
	 * get_footer()
	 * Get footers section depending on page
	 *
	 * @param string $name
	 */
	function get_footer($name = null)
	{
		if (!empty($name)) {
			$footer_file = "footer-" . $name . ".php";
		}
		$footer_file = "footer.php";
		if (file_exists($footer_file)) {
			include_once $footer_file;
		}
	}
}

/**
 *
 * selected_val__() 
 * Check if two var are equal
 * @return string
 *
 * Function : selected_val()
 * echo attribute [selected="true"] if selected_val__ return true
 * @echo string
 *
 * @param mixed $val
 * @param mixed $compare
 */

if (!function_exists("selected_val__")) {

	function selected_val__($val, $compare)
	{

		if (is_array($compare)) {
			if (in_array($val, $compare)) {
				return 'selected="true"';
			}
		}

		if ($val == $compare) {
			return 'selected="true"';
		}
	}
}

if (!function_exists("selected_val")) {

	function selected_val($val, $compare)
	{
		echo selected_val__($val, $compare);
	}
}

if (!function_exists("checked_val__")) {

	/**
	 * @param mixed $val
	 * @param mixed $compare
	 * @return string
	 */
	function checked_val__($val, $compare)
	{
		if ($val == $compare) {
			return 'checked="true"';
		}
	}
}

if (!function_exists("checked_val")) {
	/**
	 * @param mixed $val
	 * @param mixed $compare
	 * @return string
	 */
	function checked_val($val, $compare)
	{
		echo checked_val__($val, $compare);
	}
}

if (!function_exists("esc_html__")) {

	function esc_html__($string, $e_v = "")
	{
		$string = @(string) $string;
		$string = @htmlspecialchars($string, ENT_QUOTES, "UTF-8");
		$string = trim($string);
		if (strlen($string) == 0) {
			return $e_v;
		}
		return $string;
	}
}

if (!function_exists("esc_html")) {
	function esc_html($string, $e_v = "")
	{
		echo esc_html__($string, $e_v);
	}
}

if (!function_exists("set_http_header")) {
	function set_http_header()
	{
		header('X-XSS-Protection: 1; mode=block');
		header('x-frame-options: SAMEORIGIN');
		header('X-Content-Type-Options:nosniff');
		/** */
		header("Content-Security-Policy: script-src 'self' 'unsafe-inline' *.googleapis.com *.sharethis.com;img-src 'self' data: *.gstatic.com *.googleapis.com");
	}
}

if (!function_exists("_csrf")) {
	/**
	 * _csrf()
	 * generate unique token for every session
	 */
	function _csrf()
	{
		@session_start();
		if (empty($_SESSION["csrf"])) {
			$_SESSION["csrf"] = generateRandomString(8);
		}
		return $_SESSION["csrf"];
	}
}

if (!function_exists("send_mail")) {
	/**
	 * send_mail()
	 * 
	 * @param string $email
	 * @param string (HTML Markup) $message
	 * @param string $subject
	 * @return boolean
	 */
	function send_mail($email, $message, $subject)
	{
		$mail = new PHPMailer(true);

		try {
			//Server settings
			$mail->SMTPDebug = 0;                                       // Enable verbose debug output
			$mail->isSMTP();                                            // Set mailer to use SMTP
			$mail->Host       = 'localhost';  // Specify main and backup SMTP servers
			$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
			$mail->Username   = '_mainaccount@laanas.net';                     // SMTP username
			$mail->Password   = '4vKF@8<u26';                               // SMTP password
			$mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
			$mail->Port       = 587;                                    // TCP port to connect to
			$mail->CharSet = 'UTF-8';
			$mail->Encoding     = "base64";
			$mail->SMTPSecure = false;
			$mail->SMTPAutoTLS = false;
			//Recipients

			$mail->setFrom('_mainaccount@laanas.net', 'laanas');
			$mail->addAddress($email);     // Add a recipient
			$mail->addCC('cc@example.com');
			$mail->addBCC('bcc@example.com');

			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $message;

			$mail->send();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}

if (!function_exists("is_ssl")) {
	/**
	 * is_ssl()
	 *
	 * @return bool
	 */
	function is_ssl()
	{
		if (isset($_SERVER['HTTPS'])) {
			if ('on' == strtolower($_SERVER['HTTPS'])) {
				return true;
			}

			if ('1' == $_SERVER['HTTPS']) {
				return true;
			}
		} elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
			return true;
		}
		return false;
	}
}

if (!function_exists("generateRandomString")) {

	/**
	 * generateRandomString()
	 *
	 * @param int $length
	 * @return string
	 */
	function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}

if (!function_exists("generate_nums")) {
	/**
	 * generate_nums()
	 * This function can generate serial numbers Ascending/Descending
	 *
	 * @param int $int_start
	 * @param int $int_end
	 * @param string $monotony
	 * @return array
	 */
	function generate_nums($int_start, $int_end, $monotony = "asc")
	{

		if ($int_start > $int_end) {
			return false;
		}
		$nums = [];
		if ($monotony == "asc") {
			for ($i = $int_start; $i <= $int_end; $i++) {
				$nums[] = $i;
			}
		} elseif ($monotony == "desc") {
			for ($i = $int_end; $i >= $int_start; $i--) {
				$nums[] = $i;
			}
		}
		return $nums;
	}
}

if (!function_exists("kurdish_year")) {
	/**
	 * kurdish_year()
	 *1+ (Actual Gregorian Year + 611) = Kurdish Year
	 * 
	 * @param string $georgian_year
	 * @return string
	 */
	function kurdish_year($georgian_year)
	{
		if (empty($georgian_year)) {
			$georgian_year = date("Y");
		}
		return 1 + ($georgian_year + 611);
	}
}

if (!function_exists("months_names")) {
	/**
	 * months_names()
	 *
	 * @param int $month (range(1,12))
	 * @param string $calendar
	 * @retun string
	 */
	function months_names($month = null, $calendar = "gregorian")
	{

		$months = [
			"gregorian" => [
				1 => _t("يناير"),
				2 => _t("فبراير"),
				3 => _t("مارس"),
				4 => _t("أبريل"),
				5 => _t("مايو"),
				6 => _t("يونيو"),
				7 => _t("يوليو"),
				8 => _t("أُغسطس"),
				9 => _t("سبتمبر"),
				10 => _t("أكتوبر"),
				11 => _t("نوفمبر"),
				12 => _t("ديسمبر")
			],
			"hijri" => [
				1 => _t("محرم"),
				2 => _t("صفر"),
				3 => _t("ربيع الأول"),
				4 => _t("ربيع الثاني"),
				5 => _t("جمادي الأول"),
				6 => _t("جمادي الثاني"),
				7 => _t("رجب"),
				8 => _t("شعبان"),
				9 => _t("رمضان"),
				10 => _t("شوال"),
				11 => _t("ذو القعدة"),
				12 => _t("ذو الحجة")
			],
			"kurdish" => [
				1 => _t("خاکەلێو(نەورۆز)"),
				2 => _t("گوڵان (بانەمەڕ)"),
				3 => _t("جۆزەردان"),
				4 => _t("پووشپەڕ"),
				5 => _t("گەلاوێژ"),
				6 => _t("خەرمانان"),
				7 => _t("ڕەزبەر"),
				8 => _t("خەزەڵوەر(گەڵارێزان)"),
				9 => _t("سەرماوەز"),
				10 => _t("بەفرانبار"),
				11 => _t("ڕێبەندان"),
				12 => _t("ڕەشەمێ")
			]

		];

		if (!empty($month) && ($month <= 12 || $month >= 1)) {
			return $months[$calendar][$month] ?? null;
		}
		return $months[$calendar];
	}
}

if (!function_exists("get_timeago")) {
	/**
	 * get_timeago()
	 * calculate time ago 
	 *
	 * @param string(time) $ptime
	 * @return string
	 */
	function get_timeago($ptime)
	{

		if ($ptime < 0) {
			return 'n/a';
		}

		$estimate_time = time() - $ptime;

		if ($estimate_time < 1) {
			return _t('أقل من 1 ثانية');
		}

		$condition = array(
			12 * 30 * 24 * 60 * 60  =>  _t('سنة'),
			30 * 24 * 60 * 60       =>  _t('شهر'),
			24 * 60 * 60            =>  _t('يوم'),
			60 * 60                 =>  _t('ساعة'),
			60                      =>  _t('دقيقة'),
			1                       =>  _t('ثانية')
		);

		foreach ($condition as $secs => $str) {
			$d = $estimate_time / $secs;

			if ($d >= 1) {
				$r = round($d);
				return _t('منذ ') . $r . ' ' . $str . ($r > 1 ? '' : '');
			}
		}
	}
}

if (!function_exists("convert_size")) {
	/**
	 * convert_size()
	 *
	 * @param float $size
	 * @param string $unit
	 * @return float
	 */
	function convert_size($size, $unit = "mb")
	{
		return round($size / 1024 / 1024, 4);
	}
}

if (!function_exists("get_last_inserted_id")) {
	/**
	 * Get last inserted id
	 */
	function get_last_inserted_id()
	{
		global $dsql;
		$get_id = $dsql->dsql()->field("LAST_INSERT_ID()", "insert_id")->getRow();
		return $get_id["insert_id"];
	}
}
