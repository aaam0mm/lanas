<?php
class save_post_info
{

	/** @var array $query */
	public $query;
	/** 
	 * @VAR mixed $errors
	 * Collect all errors from all methods
	 */
	private $errors;

	function __construct($query = [], $errors = null)
	{
		@$this->info_id = $query["info_id"] ?? "";
		@$this->post_status = $query["post_status"] ?? "";
		@$this->post_in = $query["post_in"] ?? "";
		@$this->per_pag = $query["per_pag"] ?? RESULT_PER_PAGE;
		@$this->post_type = $query["post_type"] ?? null;
		@$this->post_author = $query["post_author"] ?? null;
		@$this->post_lang = $query["post_lang"] ?? "";
		@$this->post_category = $query["post_category"] ?? "";
		@$this->post_keywords = $query["post_keywords"] ?? "";
		@$this->save_as = $query["save_as"] ?? "";
		@$this->post_key = $query['post_key'] ?? "";
		@$this->number_fetch = $query['number_fetch'] ?? "";
		@$this->number_art = $query['number_art'] ?? "";
		@$this->post_show_pic = $query['post_show_pic'] ?? "off";
		@$this->book_without_pdf = $query['book_without_pdf'] ?? "off";
		@$this->post_source_1 = $query['post_source_1'] ?? "";
		@$this->post_source_2 = $query['post_source_2'] ?? "";
		@$this->post_fetch_url = $query['post_fetch_url'] ?? "";
		@$this->post_in = $query['post_in'] ?? "";
		@$this->errors = $errors;
	}


	/**
	 * @return mixed (array on success 		boolean on failure)
	 */
	public function save_post()
	{
		if ($this->verify_required_input() === false |	(get_langs(current_content_lang(), "on", true) === false)) {
			return false;
		}
		/** check if no errors inserted by any of class Methods */
		if (is_null($this->errors) === false) {
			return false;
		}
		/** Post allowed meta */
		$allowed_meta = ["book_author", "book_translator", "book_published_year", "book_pages", "post_attachment", "history_event", "history_calendar", "history_year", "history_month", "history_day", "name_lang", "name_gender"];
		$post_status = get_post_info_field($this->info_id, "post_status");
		$post_author = intval($this->post_author) > 0 ? $this->post_author : get_user_id_from_user_name($this->post_author);
		$data = [
			'post_category' => $this->post_category,
			'post_type' => $this->post_type,
			'post_status' => $this->post_status,
			'post_in' => 'trusted',
			'post_author' => $post_author,
			'post_lang' => $this->post_lang,
			'post_keywords' => $this->post_keywords ?? null,
			'number_fetch' => $this->number_fetch ?? null,
			'post_show_pic' => $this->post_show_pic,
			'book_without_pdf' => $this->book_without_pdf,
			'post_source_1' => $this->post_source_1,
			'post_source_2' => $this->post_source_2,
			'post_fetch_url' => $this->post_fetch_url,
		];

		if ($post_status == 'draft' |	$post_status == 'auto-draft') {
			$data["post_date_gmt"] = gmdate("Y-m-d H:i:s");
		}

		global $dsql;
		$this->manage_points();
		$save_post = $dsql->dsql()->table('post_info')->set($data)->where('id', $this->info_id)->update();
		
		if ($save_post) {

			// if ($this->post_status() == 'publish' && $post_status != 'publish') {
			// 	insert_notif(0, $post_author, $this->info_id, "publish_post", 1);
			// }
			return ["status" => 'success'];
		} else {
			$this->errors[] = ["error" => _t("حدث خطأ المرجو إعادة المحاولة")];
		}
		return false;
	}

	/**
	 * Insert post as auto-draft status
	 *
	 * @return mixed (int on success 		boolean on failure)
	 */
	public function reserve_post()
	{
		if ($this->can_add_content() === false) {
			return false;
		}
		$get_current_user_info = get_current_user_info();
		if ($get_current_user_info === false |		!in_array($this->post_in, ["trusted", "untrusted"]) |		!in_array($this->post_type, get_taxonomies())) {
			return false;
		}
		global $dsql;
		$cols = [
			"post_title" =>  _t("مسودة تلقائية"),
			"post_content" => " ",
			"reactions_count" => " ",
			"post_status" => "auto-draft",
			"post_author" => $get_current_user_info->id,
			"post_in" => $this->post_in,
			"post_lang" => current_content_lang(),
			"post_type" => $this->post_type,
		];
		$reserve_post = $dsql->dsql()->table('posts')->set($cols)->insert();
		if ($reserve_post) {
			return get_last_inserted_id();
		}
		return false;
	}

	/**
	 * return @propery $errors value
	 * @return array
	 */
	public function get_errors()
	{
		return $this->errors;
	}

	/**
	 * check if current user logged can add content
	 *
	 * @return array on failure (user can't add) 		boolean on success (user can add)
	 */
	public function can_add_content()
	{
		$current_user = get_current_user_info();
		if ($current_user === false) {
			return false;
		}
		$response = null;
		if (user_authority()->publish_in != "all" && !in_array($this->post_type, user_authority()->publish_in)) {
			return false;
		}

		if ((($current_user->points_remaining < points_manage($this->post_type, "substract")) === true) && ($this->post_in == "untrusted")) {
			$this->errors[] = ["error" => _t("المعذرة ! نقاطك لاتسمح لك بإضافة موضوع")];
			return false;
		}

		if (user_authority()->post_per_day !== "unlimited" && user_authority()->post_per_day <= $current_user->post_of_today) {
			$this->errors[] = ["error" => _t("المعذرة ! لقد تجاوزت الحد المسموج به للإضافة اليوم")];
			return false;
		}

		return true;
	}

	/**
	 * check if user can edit post
	 *
	 * @param int $info_id
	 * @return boolean
	 */
	public function can_edit_post_info($info_id = null)
	{
		$current_user = get_current_user_info();
		if ($current_user === false) {
			return false;
		}
		
		if (empty($info_id) && !empty($this->info_id)) {
			$info_id = $this->info_id;
		}


		if (empty($info_id)) {
			return false;
		}
		$query_post_info = new Query_post(false);
		$query_post_info->set_post_info_data([
			"info_id" => $info_id,
			"post_in" => false,
			"post_status" => false,
			"post_lang" => false
		]);

		$get_post = $query_post_info->get_post();
		if (!$get_post) {
			return false;
		}

		$post = $get_post;
		$post_in = $post["post_in"];
		if ((admin_authority()->posts == "on" |		($post["post_author"] == $current_user->id)) === false) {
			return false;
		}

		if ($post["post_status"] == "blocked" && admin_authority()->posts != "on") {
			$this->errors[] = ["error" => _t("تم إيقاف هذا المحتوى من طرف الإدارة")];
			return false;
		}
		return true;
	}
	/**
	 * post_status()
	 * Select post status
	 * 
	 * @return mixed(string on success 		boolean on failure)
	 */
	public function post_status()
	{

		$post_old_status = get_post_info_field($this->info_id, "post_status");

		if ($this->save_as == "edit" && admin_authority()->posts == "on") {
			return $post_old_status;
		}

		if ($this->save_as == "draft" && $post_old_status == 'auto-draft') {
			return "draft";
		}

		$post_status = "publish";

		$post_in = get_post_info_field($this->info_id, "post_in");

		if ((admin_authority()->posts != "on" && user_authority()->auto_approve != "yes") && $post_in == "trusted") {

			if ($post_old_status == "auto-draft" |	$post_old_status == 'draft') {
				$post_status = "pending";
			} elseif ($post_old_status == 'publish') {
				$post_status = 'approval';
			} else {
				$post_status = $post_old_status;
			}
		}

		return $post_status;
	}

	private function manage_points()
	{
		$post_old_status = get_post_info_field($this->info_id, "post_status");
		if (in_array($post_old_status, array('auto-draft', 'pending')) && $this->post_status() == "publish") {
			$post_in = get_post_info_field($this->info_id, "post_in");
			$post_author = get_post_info_field($this->info_id, "post_author");
			$post_type = get_post_info_field($this->info_id, "post_type");
			$user_points = get_user_info($post_author);
			$points_remaining = $user_points->points_remaining;
			$points_consumed = $user_points->points_consumed;
			if ($post_in == "trusted") {
				$new_remaining_points = $points_remaining + distribute_points($post_type, "add");
			} elseif ($post_in == "untrusted") {
				$substract = distribute_points($post_type, "substract");
				$new_remaining_points = $points_remaining - $substract;
				$new_consumed_points = $points_consumed + $substract;
				if ($new_consumed_points > $points_consumed) {
					if (!update_user_meta($post_author, "points_consumed", $new_consumed_points)) {
						return false;
					}
				}
			}

			if (!update_user_meta($post_author, "points_remaining", $new_remaining_points)) {
				return false;
			}
			return true;
		}
		//return $post_old_status;
	}

	/**
	 * post_categories()
	 * get categories parents
	 * 
	 * @return array
	 */
	private function post_categories()
	{

		if (!is_array($this->post_category)) {
			return false;
		}

		$query_post_info = new Query_post(["info_id" => $this->info_id]);

		$get_post_categories = $query_post_info->get_post_categories();
		if ($get_post_categories) {
			global $dsql;
			foreach ($get_post_categories as $cat_id) {
				$delete = $dsql->dsql()->table('post_category')->where('post_category', $cat_id)->where('info_id', $this->info_id)->delete();
			}
		}

		$post_categories = $this->post_category;
		foreach (explode(",", get_category_parents($this->post_category)) as $cat_id) {
			if (!empty($cat_id)) {
				$post_categories[] = $cat_id;
			}
		}

		return $post_categories;
	}

	/**
	 * Get and save slices
	 */
	private function save_slices()
	{

		global $dsql;

		$slices = $_POST["slice"] ?? null;
		if (empty($slices)) {
			$dsql->dsql()->table('post_slices')->where('info_id', $this->info_id)->delete();
			return null;
		}

		foreach ($slices as $slice_type => $slice) {
			foreach ($slices[$slice_type] as $slice_content) {
				$data = ["info_id" => $this->info_id, "slice_type" => $slice_type, "slice_content" => json_encode($slice_content)];
				$query = $dsql->dsql()->table('post_slices')->set($data);
				if (isset($slice_content["slice_id"]) && !empty($slice_content["slice_id"])) {
					$query->where("id", $slice_content["slice_id"])->update();
				} else {
					$query->insert();
				}
			}
		}
		return true;
	}

	/**
	 * Check all required input depending on post type
	 * @return mixed boolean|null
	 *  return boolean by this function is mean that find strong invalid data 
	 */
	private function verify_required_input()
	{
		/** 
		 * @VAR boolean $check_thumb & $check_category
		 * if some post type has optional/no thumbnail & category we turned value to False
		 */
		$check_category = $check_lang = $check_type = $check_author = $check_post_fetch_url = true;

		// if (is_array($this->post_keywords)) {
		// 	$this->post_keywords = implode(",", $this->post_keywords);
		// }
		if ($this->post_type == "book") {
			// if (empty(trim($this->post_meta["book_author"]))) {
			// 	$this->errors[] = ["selector" => "#book_author", "error" => _t("المرجو إدخال إسم مؤلف الكتاب")];
			// }

			// $books_ids = $this->post_meta["books_ids"] ?? "";
			// $book_links = null;
			// if (user_authority()->upload_links == 'on') {
			// 	$books_links = $this->post_meta["books_links"] ?? "";
			// }
			// if (empty($books_ids) && empty($books_links)) {
			// 	$this->errors[] = ["selector" => "#books_ids", "error" => _t("المرجو رفع كتاب")];
			// } else {
			// 	$this->post_meta['books_links'] = @serialize(@explode(PHP_EOL, $books_links));
			// 	$this->post_meta["books_ids"] = @serialize($books_ids);
			// }
		} elseif ($this->post_type == "name") {
			if (empty(trim($this->post_meta["name_gender"]))) {
				$this->errors[] = ["selector" => "#name_gender", "error" => _t("المرجو إختيار جنس الإسم")];
			}

			$check_thumb = false;
		} elseif ($this->post_type == "quote") {
			$check_thumb = false;
		} elseif ($this->post_type == "dictionary") {
			$check_thumb = false;
		} elseif ($this->post_type == "article") {
			//$check_category = false; 
		} elseif ($this->post_type == "history") {
			$check_category = $check_thumb = false;
			if (!in_array($this->post_meta["history_event"] ?? "", ["deaths", "today", "occasions"])) {
				$this->errors[] = ["selector" => "#history_event", "error" => _t("المرجو إختيار تصنيف ")];
			}
			if (!in_array($this->post_meta["history_calendar"] ?? "", ["hijri", "gregorian", "kurdish"])) {
				$this->errors[] = ["selector" => "#history_calendar", "error" => _t("المرجو إختيار التقويم")];
			}
			if (empty($this->post_meta["history_date"]["year"] ?? "")) {
				$this->errors[] = ["selector" => "#history_year", "error" => ""];
			}
			if (empty($this->post_meta["history_date"]["day"] ?? "")) {
				$this->errors[] = ["selector" => "#history_day", "error" => ""];
			} else {
				if (((int) $this->post_meta["history_date"]["day"]) < 10) {
					$this->post_meta["history_date"]["day"] = "0" . (int) $this->post_meta["history_date"]["day"];
				}
			}
			if (@!in_array($this->post_meta["history_date"]["month"], range(1, 12))) {
				$this->errors[] = ["selector" => "#history_month", "error" => _t("المرجو إختيار الشهر")];
			}
			/** post title  = post date (d-m-y) */
			$this->post_title = $this->post_meta["history_date"]["day"] . "-" . $this->post_meta["history_date"]["month"] . "-" . $this->post_meta["history_date"]["year"];
		} elseif ($this->post_type == "research") {
			// we send research details as post_meta and we decode it to json and save as post_content
			$researchs = $_POST["research"] ?? "";
			if (!is_array($researchs)) {
				return false;
			}

			if (empty($this->post_meta["research_author"] ?? "")) {
				$this->errors[] = ["selector" => "#research_author", "error" => _t("المرجو إدخال إسم المؤلف")];
			}

			$this->post_content = json_encode($researchs);
		}
		if ($check_category) {
			if (empty($this->post_category)) {
				$this->errors[] = ["selector" => "#post-categoy-select2", "error" => _t("المرجو إختيار قسم")];
			}
		}
		if ($check_lang) {
			if (empty($this->post_lang)) {
				$this->errors[] = ["selector" => "#post-categoy-select2", "error" => _t("المرجو إختيار اللغة")];
			}
		}
		if ($check_type) {
			if (empty($this->post_type)) {
				$this->errors[] = ["selector" => "#post-categoy-select2", "error" => _t("المرجو إختيار الصنف")];
			}
		}
		if ($check_author) {
			if (empty($this->post_author)) {
				$this->errors[] = ["selector" => "#post-categoy-select2", "error" => _t("المرجو إختيار الحساب")];
			}
		}
		if ($check_post_fetch_url) {
			if (empty($this->post_fetch_url)) {
				$this->errors[] = ["selector" => "#post-categoy-select2", "error" => _t("المرجو إختيار رابط الجلب")];
			}
		}
	}
}
