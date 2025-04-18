<?php
class save_post
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
		@$this->post_id = $query["post_id"] ?? "";
		@$this->post_title = $query["post_title"] ?? "";
		@$this->post_content = $query["post_content"] ?? "";
		@$this->post_status = $query["post_status"] ?? "";
		@$this->post_in = $query["post_in"] ?? "";
		@$this->reviewed = empty($query["reviewed"]) ? (isset($_POST['reviewed']) ? $_POST['reviewed'] : "off") : "off";
		@$this->share_authority = empty($query["share_authority"]) ? (isset($_POST['share_authority']) ? $_POST['share_authority'] : "off") : "off";
		@$this->in_slide = empty($query["in_slide"]) ? "off" : "on";
		@$this->in_special = empty($query["in_special"]) ? "off" : "on";
		@$this->per_pag = $query["per_pag"] ?? RESULT_PER_PAGE;
		@$this->post_type = $query["post_type"] ?? null;
		@$this->post_author = $query["post_author"] ?? null;
		@$this->post_lang = $query["post_lang"] ?? "";
		@$this->post_meta = $query["post_meta"] ?? [];
		@$this->post_thumbnail = $query["post_thumbnail"] ?? "";
		@$this->post_category = $query["post_category"] ?? "";
		@$this->post_keywords = $query["post_keywords"] ?? "";
		@$this->save_as = $query["save_as"] ?? "";
		@$this->errors = $errors;
	}


	/**
	 * @return mixed (array on success | boolean on failure)
	 */
	public function save_post()
	{
		if ($this->can_edit_post() !== true || $this->verify_required_input() === false || (get_langs(current_content_lang(), "on", true) === false)) {
			return false;
		}
		/** check if no errors inserted by any of class Methods */
		if (is_null($this->errors) === false) {
			return false;
		}
		/** Post allowed meta */
		$allowed_meta = ["book_author", "book_translator", "book_published_year", "book_pages", "post_attachment", "history_event", "history_calendar", "history_year", "history_month", "history_day", "name_lang", "name_gender"];
		$post_url_title =  preg_replace('/[^\w\/s+]+/u', '-', $this->post_title);
		$post_url_title = str_replace("/", "", $post_url_title);
		$in_special = get_post_field($this->post_id, "in_special");
		// $reviewed = get_post_field($this->post_id, "reviewed");
		// $share_authority = get_post_field($this->post_id, "share_authority");
		$post_status = get_post_field($this->post_id, "post_status");
		$post_author = get_post_field($this->post_id, "post_author");
		$post_lang = isset($_POST["post_lang"]) ? $_POST["post_lang"] : $this->post_lang ?? current_content_lang();
		$data = [
			"post_title" => $this->post_title,
			"post_url_title" => $post_url_title,
			"post_status" => $this->post_status(),
			"post_content" => $this->post_content,
			"post_thumbnail" => $this->post_thumbnail,
			"post_keywords" => $this->post_keywords,
			"post_lang" => $post_lang,
			"reviewed" => $this->reviewed,
			"share_authority" => $this->share_authority,
			"in_slide" => $this->in_slide,
			"in_special" => $this->in_special,
		];

		if ($post_status == 'draft' || $post_status == 'auto-draft') {
			$data["post_date_gmt"] = gmdate("Y-m-d H:i:s");
		}

		if ($this->post_status() == "publish") {
			remove_post_meta($this->post_id, "post_content_edit");
		} else {
			if ($this->post_status() == "approval") {
				$this->post_meta["post_content_edit"] = $this->post_content;
				$data["post_content"] = get_post_field($this->post_id, "post_content");
			}
		}
		global $dsql;
		$this->manage_points();
		$save_post = $dsql->dsql()->table('posts')->set($data)->where('id', $this->post_id)->update();
		
		if ($save_post) {

			if ($this->post_status() == 'publish' && $post_status != 'publish') {
				insert_notif(0, $post_author, $this->post_id, "publish_post", 1);
			}

			// Insert post categories
			if (is_array($this->post_categories())) {
				foreach ($this->post_categories() as $cat) {
					$dsql->dsql()->table('post_category')->set(["post_id" => $this->post_id, "post_category" => $cat])->insert();
				}
			}

			if (is_array($this->post_meta)) {

				if (admin_authority()->posts == "on") {
					$this->post_meta["notice"] = $this->post_meta["notice"] ?? "";
				}

				// Insert post meta

				/** below post meta required to be in post meta array */
				$this->post_meta["disable_comments"] = $this->post_meta["disable_comments"] ?? "off";
				$this->post_meta["disable_copy"] = $this->post_meta["disable_copy"] ?? "off";
				$this->post_meta["source"] = @json_encode($this->post_meta["source"]);

				if (isset($this->post_meta["history_date"]) && is_array($this->post_meta["history_date"])) {
					$this->post_meta["history_date"] = [$this->post_meta["history_date"]["year"], $this->post_meta["history_date"]["month"], $this->post_meta["history_date"]["day"]];
					$this->post_meta["history_date"] = implode("-", $this->post_meta["history_date"]);
				}

				foreach ($this->post_meta as $meta_key => $meta_value) {
					update_post_meta($this->post_id, $meta_key, $meta_value);
				}
			}
			// insert notification to post author
			if ($in_special == "off" && $this->in_special == "on") {
				insert_notif(0, get_post_field($this->post_id, "post_author"), $this->post_id, "in_feautred_posts", 1);
			}
			$this->save_slices();
			return ["post_id" => $this->post_id, "post_title" => $this->post_title, "post_link" => get_post_link($this->post_id)];
		} else {
			$this->errors[] = ["error" => _t("حدث خطأ المرجو إعادة المحاولة")];
		}
		return false;
	}

	/**
	 * Insert post as auto-draft status
	 *
	 * @return mixed (int on success | boolean on failure)
	 */
	public function reserve_post()
	{
		if ($this->can_add_content() === false) {
			return false;
		}
		$get_current_user_info = get_current_user_info();
		if ($get_current_user_info === false || !in_array($this->post_in, ["trusted", "untrusted"]) || !in_array($this->post_type, get_taxonomies())) {
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
	 * @return array on failure (user can't add) | boolean on success (user can add)
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
	 * @param int $post_id
	 * @return boolean
	 */
	public function can_edit_post($post_id = null)
	{
		$current_user = get_current_user_info();
		if ($current_user === false) {
			return false;
		}

		if (empty($post_id) && !empty($this->post_id)) {
			$post_id = $this->post_id;
		}

		if (empty($post_id)) {
			return false;
		}
		$query_post = new Query_post(
			[
				"post_id" => $post_id,
				"post_in" => false,
				"post_status" => false,
				"post_lang" => false
			]
		);

		$get_post = $query_post->get_post();

		if (!$get_post) {
			return false;
		}

		$post = $get_post;
		$post_in = $post["post_in"];
		if ((admin_authority()->posts == "on" || ($post["post_author"] == $current_user->id)) === false) {
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
	 * @return mixed(string on success | boolean on failure)
	 */
	public function post_status()
	{

		$post_old_status = get_post_field($this->post_id, "post_status");

		if ($this->save_as == "edit" && admin_authority()->posts == "on") {
			return $post_old_status;
		}

		if ($this->save_as == "draft" && $post_old_status == 'auto-draft') {
			return "draft";
		}

		$post_status = "publish";

		$post_in = get_post_field($this->post_id, "post_in");

		if ((admin_authority()->posts != "on" && user_authority()->auto_approve != "yes") && $post_in == "trusted") {

			if ($post_old_status == "auto-draft" || $post_old_status == 'draft') {
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
		$post_old_status = get_post_field($this->post_id, "post_status");
		if (in_array($post_old_status, array('auto-draft', 'pending')) && $this->post_status() == "publish") {
			$post_in = get_post_field($this->post_id, "post_in");
			$post_author = get_post_field($this->post_id, "post_author");
			$post_type = get_post_field($this->post_id, "post_type");
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

		$query_post = new Query_post(["post_id" => $this->post_id]);

		$get_post_categories = $query_post->get_post_categories();
		if ($get_post_categories) {
			global $dsql;
			foreach ($get_post_categories as $cat_id) {
				$delete = $dsql->dsql()->table('post_category')->where('post_category', $cat_id)->where('post_id', $this->post_id)->delete();
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
			$dsql->dsql()->table('post_slices')->where('post_id', $this->post_id)->delete();
			return null;
		}

		foreach ($slices as $slice_type => $slice) {
			foreach ($slices[$slice_type] as $slice_content) {
				$data = ["post_id" => $this->post_id, "slice_type" => $slice_type, "slice_content" => json_encode($slice_content)];
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

	private function extractFirstPageThumbnail($pdfPath, $thumbnailPath) {
    $imagick = new \Imagick();
    
    // Set the resolution for better quality
    $imagick->setResolution(150, 150);
    
    try {
        // Read the first page of the PDF
        $imagick->readImage($pdfPath . '[0]'); // First page
        
        // Set the output format (jpg, png, etc.)
        $imagick->setImageFormat('jpg');
        
        // Save the thumbnail image to the desired path
        $imagick->writeImage($thumbnailPath);
        
        // Clear resources
        $imagick->clear();
        $imagick->destroy();
        
        return true;
    } catch (Exception $e) {
        error_log('Error creating thumbnail: ' . $e->getMessage());
        return false;
    }
	}

	private function saveThumbnailToDB($thumbnailPath, $originalFileUrl, $postAuthor = 1) {
    global $dsql;

    // Extract the file extension
    $fileName = basename($thumbnailPath);
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

    // Get file size
    $fileSize = filesize($thumbnailPath);
    
    // Generate a random folder and file key for the thumbnail
    $folderName = generateRandomString();
    $fileKey = generateRandomString(32);
    
    // Move the file to the final directory (after ensuring the folder exists)
    $uploadDir = UPLOAD_DIR . $folderName;
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $uploadFilePath = $uploadDir . '/' . $fileName;
    rename($thumbnailPath, $uploadFilePath);  // Move the thumbnail to the new location

    // Prepare data for insertion into the 'files' table
    $datas = [
        "file_name" => $fileName,
        "file_original_name" => $originalFileUrl,
        "file_dir" => $folderName,
        "file_key" => $fileKey,
        "mime_type" => 'image/jpeg',
        "file_upload_date" => gmdate("Y-m-d H:i:s"),
        "file_uploader" => $postAuthor,
        "file_type" => 'user_attachment',
        "file_category" => 1,
    ];

    // Insert the thumbnail record into the database
    $insert = $dsql->dsql()->table('files')->set($datas)->insert();

    if ($insert) {
        // Retrieve the last inserted id (file_id)
        return get_last_inserted_id();
    }

    return false;
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
		global $dsql;
		$check_category = $check_thumb = $check_title = true;

		if (is_array($this->post_keywords)) {
			$this->post_keywords = implode(",", $this->post_keywords);
		}
		if ($this->post_type == "book") {

			$is_for_read = isset($this->post_meta['is_for_read']) && $this->post_meta['is_for_read'] == "on" ? 'on' : 'off';
			if($is_for_read == 'on') {
				if(empty($this->post_meta['book_pages']) || $this->post_meta['book_pages'] == 0) {
					$this->errors[] = ["selector" => "#book_pages", "error" => _t("تحديد عدد الصفحات اجباري في حالة ما كان الكتاب للمراجعة فقط")];
				}
				if(empty($this->post_meta['book_published_year'])) {
					$this->errors[] = ["selector" => "#book_published_year", "error" => _t("تحديد سنة النشر اجباري في حالة ما كان الكتاب للمراجعة فقط")];
				}
				if(empty($this->post_meta['book_published_name'])) {
					$this->errors[] = ["selector" => "#book_published_name", "error" => _t("تحديد دار النشر اجباري في حالة ما كان الكتاب للمراجعة فقط")];
				}
				if(empty($this->post_thumbnail)) {
					$this->errors[] = ["selector" => "#post_thumbnail", "error" => _t("تحديد صورة غلاف الكتاب اجباري في حالة ما كان الكتاب للمراجعة فقط")];
				}
				if(empty($this->post_content)) {
					$this->errors[] = ["selector" => "#post_content", "error" => _t("اضافة وصف للكتاب اجباري في حالة ما كان الكتاب للمراجعة فقط")];
				}
			} else {
				$check_thumb = false;
			}

			$books_ids = $this->post_meta["books_ids"] ?? "";
			$book_links = null;
			if (user_authority()->upload_links == 'on') {
				$books_links = $this->post_meta["books_links"] ?? "";
			}

			if (empty($books_ids) && empty($books_links) && $is_for_read == 'off') {
				$this->errors[] = ["selector" => "#books_ids", "error" => _t("المرجو رفع الكتاب")];
			} else {
				if(!empty($books_ids)) {
					$this->post_meta["books_ids"] = @serialize($books_ids);
					if($this->post_meta["upload_thumbnail_from_book"] > 0) {
						$this->post_thumbnail = $this->post_meta["upload_thumbnail_from_book"];
					}
				}
				if(!empty($books_links)) {
					$this->post_meta['books_links'] = @serialize(@explode(PHP_EOL, $books_links));
				}
			}
	

			if (empty($this->post_meta['is_book_author']) || $this->post_meta['is_book_author'] == 0 || $this->post_meta["book_author"] == 0) {
				$this->errors[] = ["selector" => "#is_book_author", "error" => _t("يرجى تحديد من هو مؤلف الكتاب")];
			} else {
				if (empty(trim($this->post_meta["book_author"]))) {
					$this->errors[] = ["selector" => "#book_author", "error" => _t("المرجو إدخال إسم مؤلف الكتاب")];
				} elseif(intval($this->post_meta["book_author"]) > 0) {
					$author = $dsql->dsql()->table('authors')->field('name')->where('id', $this->post_meta["book_author"])->getOne();
					$this->post_meta['book_author_id'] = $this->post_meta["book_author"];
					$this->post_meta['book_author'] = $author;
				} else {
					$insert = $dsql->dsql()->table('authors')->set(['name' => $this->post_meta['book_author']])->insert();
					if ($insert) {
						$this->post_meta['book_author_id'] = $dsql->lastInsertId();
					}
				}
			}

			if($this->post_meta['is_book_translator'] == "no" || $this->post_meta['is_book_translator'] == 0 || $this->post_meta["book_translator"] == 0) {
				$this->post_meta['book_translator'] = "";
			} else {
				if(intval($this->post_meta["book_translator"]) > 0) {
					$translator = $dsql->dsql()->table('translators')->field('name')->where('id', $this->post_meta["book_translator"])->getOne();
					$this->post_meta['book_translator_id'] = $this->post_meta["book_translator"];
					$this->post_meta['book_translator'] = $translator;
				} else {
					$insert = $dsql->dsql()->table('translators')->set(['name' => $this->post_meta['book_translator']])->insert();
					if ($insert) {
						$this->post_meta['book_translator_id'] = $dsql->lastInsertId();
					}
				}
			}

			// audio
			$audios_ids = $this->post_meta["audios_ids"] ?? "";
			if(!empty($audios_ids)) {
				$this->post_meta["audios_ids"] = @serialize($audios_ids);
			}

		} elseif ($this->post_type == "name") {
			if (empty(trim($this->post_meta["name_gender"]))) {
				$this->errors[] = ["selector" => "#name_gender", "error" => _t("المرجو إختيار جنس الإسم")];
			}

			$check_thumb = false;
		} elseif ($this->post_type == "quote") {
			$check_thumb = false;
		} elseif ($this->post_type == "dictionary") {
			$check_thumb = false;
		} elseif ($this->post_type == "author_article") {
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
		if ($check_thumb) {
			if (empty($this->post_thumbnail) && empty($this->post_meta["video_thumbnail"])) {
				$this->errors[] = ["selector" => "#post-add-media-library", "error" => _t("المرجو إختيار صورة")];
			} else {
				/** verify if user sent a valid thumb */
				if (verify_thumb($this->post_thumbnail) === false) {
					// this is user Invalid activity
					return false;
				}
			}
		}
		if ($check_title) {
			if (empty($this->post_title)) {
				$this->errors[] = ["selector" => "#post_title", "error" => _t("المرجو إدخال عنوان")];
			}
		}
		if ($check_category) {
			if (empty($this->post_category)) {
				$this->errors[] = ["selector" => "#post-categoy-select2", "error" => _t("المرجو إختيار قسم")];
			}
		}
	}
}
