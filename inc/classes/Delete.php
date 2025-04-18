<?php
class Delete
{

	public $ids;
	private $errors = null;

	function __construct($ids, $errors = null)
	{
		if (!is_array($ids)) {
			$this->ids = [$ids];
		} else {
			$this->ids = $ids;
		}
		$this->errors = $errors;
	}

	function delete_countries()
	{
		global $dsql;
		if (@admin_authority()->countries != "on") {
			return false;
		}
		foreach ($this->ids as $id) {
			if (!$dsql->dsql()->table('countries')->where('id', $id)->delete()) {
				$this->errors[] = ["error" => _t("المعذرة ! لم يتم حدف بعض الدول")];
			}
		}
	}

	function delete_pages()
	{
		global $dsql;
		if (@admin_authority()->pages != "on") {
			return false;
		}
		foreach ($this->ids as $id) {
			if (!$dsql->dsql()->table('pages')->where('id', $id)->delete()) {
				$this->errors[] = ["error" => _t("المعذرة ! لم يتم حدف بعض الصفحات")];
			}
		}
	}

	function delete_posts()
	{
		// ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
		global $dsql;
		foreach ($this->ids as $id) {
			if ($this->can_delete_post($id)) {
				$post_author = get_post_field($id, "post_author");
				$post_thumbnail = get_post_field($id, "post_thumbnail");
				$post = $dsql->dsql()->table('posts')->where('id', $id);
				$post_f = $post->field('info_id')->field('post_content')->field('post_type')->getRow();
				$post_type = $post_f['post_type'];
				$data_fid_array = [];
				if ($post->delete()) {
					// check if image in other post
					if(!empty($post_thumbnail) && $post_thumbnail && !is_null($post_thumbnail)) {
						$this->delete_post_thumbnail($id, $post_thumbnail);
					}
					if($post_type == 'book') {
						$ids = @unserialize(get_post_meta($id, "books_ids"));
						if(is_array($ids) && count($ids) > 0) {
							$data_fid_array = array_merge($data_fid_array, $ids);
						}
						$audios_ids = @unserialize(get_post_meta($id, "audios_ids"));
						if (is_array($audios_ids) && count($audios_ids) > 0) {
							$final_ids = [];
							foreach($audios_ids as $audios) {
								$audio_info = json_decode($audios, true);
								if ($audio_info && isset($audio_info['file_id'])) {
									$final_ids[] = $audio_info['file_id'];
								}
							}
							$data_fid_array = array_merge($data_fid_array, $final_ids);
						}
					}
					if(isset($post_f['info_id']) && $post_f['info_id'] > 0) {
						$post_content = $post_f['post_content'];
						$pattern = '/data-fid=[\'"](\d+)[\'"]/';
						
						if (preg_match_all($pattern, $post_content, $matches)) {
							$data_fid_array = array_merge($data_fid_array, $matches[1]);
						}
						// $query_posts = new Query_post(['info_id' => $post_f['info_id'], 'limit' => 'all']);
						$count_posts = $dsql->dsql()->table('posts')->where('info_id', $post_f['info_id'])->get();
						$count_posts = $count_posts ? count($count_posts) : 0;
						// $get_posts_all = $query_posts->get_posts() ? count($query_posts->get_posts()) : 0;

						$dsql->dsql()->table('post_info')->set(['number_art' => $count_posts])->where('id', $post_f['info_id'])->update();
					}
					if(count($data_fid_array) > 0) {
						foreach($data_fid_array as $file_id) {
							$this->delete_file($file_id);
						}
					}
					insert_notif(0, $post_author, $id, "delete_post", 1);
					$dsql->dsql()->table('post_meta')->where('post_id', $id)->delete();
				}
			} else {
				$this->errors[] = ["error" => _t(sprintf("ليس لديك صلاحيات لحدف '%s'", get_post_field($id, 'post_title')))];
			}
		}
		// if (is_null($this->errors)) {
		// 	return true;
		// }
		// return false;
	}

	function delete_link()
	{
		
		global $dsql;
		foreach ($this->ids as $id) {
			if ($this->can_delete_link($id)) {
				if ($dsql->dsql()->table('post_info')->where('id', $id)->delete()) {
					$posts = $dsql->dsql()->table('posts')->where('info_id', $id);
					if($posts) {
						$update = $posts->set('info_id', NULL)->update();
						if(!$update) {
							$this->errors[] = ["error" => _t('لم يتم التعديل على المنشورات')];
						}
					}
				}
			} else {
				$this->errors[] = ["error" => _t(sprintf("ليس لديك صلاحيات لحدف الرابط ذو المعرف : '%s'", get_post_info_field($id, 'post_key')))];
			}
		}
	}

	private function delete_post_thumbnail($post_id, $post_thumbnail): void
	{
		global $dsql;
		$file_dir = get_file_field($post_thumbnail, "file_dir");
		$check = $dsql->dsql()->table('posts')->where('id', '!=', $post_id)->where('post_thumbnail', $post_thumbnail)->limit(1)->getRow();
		if (!$check) {
			$dsql->dsql()->table('files')->where('id', $post_thumbnail)->where('file_type', '!=', 'site_images')->delete();
			if (!$dsql->dsql()->table('files')->where('id', $post_thumbnail)->getRow()) {
				unlinkr(ROOT . "/uploads/" . $file_dir . "/");
			}
		}
	}

	function delete_users()
	{
		global $dsql;
		foreach ($this->ids as $id) {
			if ($this->can_delete_user($id)) {
				$dsql->dsql()->table('users')->where('id', $id)->delete();
				$dsql->dsql()->table('user_meta')->where('user_id', $id)->delete();
				$this->delete_user_posts($id);
			} else {
				$this->errors[] = ["error" => _t("لا يمكن إكمال هذه العملية")];
			}
		}
		if (is_null($this->errors)) {
			return true;
		}
		return false;
	}

	function delete_comments()
	{
		global $dsql;
		foreach ($this->ids as $id) {
			if ($this->can_delete_comment($id)) {
				$delete = $dsql->dsql()->table('comments')->where('id', $id)->delete();
				$this->delete_files_by_id(get_comment_field($id, "comment_attachment"));
			} else {
				$this->errors[] = ["error" => _t("لا يمكن إكمال هذه العملية")];
			}
		}
		if (is_null($this->errors)) {
			return true;
		}
		return false;
	}

	private function delete_file($file_id)
	{
		global $dsql;
		$current_user = get_current_user_info();
		$file_dir = get_file_field($file_id, "file_dir");
		$file_uploader  = get_file_field($file_id, "file_uploader");
		if (!empty($file_dir)) {
			$delete = $dsql->dsql()->table('files')->where('id', $file_id);
			if (@admin_authority()->files != "on" && $current_user->id != $file_uploader) {
				$this->errors[] = ["error" => _t(sprintf("ليس لديك صلاحيات حدف ملف رقم %d", $file_id))];
				return false;
			}
			$delete = $delete->delete();
			if (!$dsql->dsql()->table('files')->where('id', $file_id)->getRow()) {
				// unlinkr("../uploads/" . $file_dir . "/");
				unlinkr(UPLOAD_DIR . $file_dir . "/");
			} else {
				$this->errors[] = ["error" => _t(sprintf("الملف رقم %d لم يتم حدفه", $file_id))];
				return false;
			}
			return true;
		}
		return false;
	}

	function delete_profile_picture()
	{
		foreach ($this->ids as $id) {
			$this->delete_file($id);
		}
	}

	function delete_lang()
	{
		global $dsql;
		foreach ($this->ids as $id) {
			$delete = $dsql->dsql()->table('languages')->where('id', $id)->delete();
			if (!$delete) {
				$this->errors[] = ["error" => _t(printf("اللغة رقم %d لم يتم حدفه", $id))];
			} else {
				$delete_posts = $dsql->dsql()->table('posts')->where('post_lang', $id)->delete();
			}
		}
	}

	function delete_contact_form()
	{
		global $dsql;
		foreach ($this->ids as $id) {
			$dsql->dsql()->table('contact_form')->where('id', $id)->delete();
		}
	}

	function delete_complain()
	{
		global $dsql;
		foreach ($this->ids as $id) {
			$dsql->dsql()->table('post_meta')->where('id', $id)->where('meta_key', 'complain')->delete();
		}
	}

	function delete_files()
	{

		foreach ($this->ids as $id) {
			$this->delete_file($id);
		}
		if (is_null($this->errors)) {
			return true;
		}
		return false;
	}

	function delete_ads()
	{
		global $dsql;
		if (@admin_authority()->ads != "on") {
			return false;
		}
		foreach ($this->ids as $id) {
			$old_attachment = (int) get_ad_field($id, 'ad_code');
			if (is_numeric($old_attachment)) {
				$this->delete_file($old_attachment);
			}
			$delete = $dsql->dsql()->table('ads')->where('id', $id)->delete();
			if (!$delete) {
				$this->errors[] = ["error" => _t(printf("الإعلان رقم %d لم يتم حدفه", $id))];
			}
		}
	}

	function delete_badges()
	{
		global $dsql;
		if (@admin_authority()->badges != "on") {
			return false;
		}

		foreach ($this->ids as $id) {
			$badge_icon = get_badge_field($id, 'badge_icon');
			$delete = $dsql->dsql()->table('badges')->where('id', $id)->delete();
			if (!$delete) {
				$this->errors[] = ["error" => _t(printf("لم يتم حدف الوسام رقم %d", $id))];
			} else {
				$this->delete_file($badge_icon);
			}
		}
	}

	function delete_roles($new_role)
	{
		if (!is_super_admin()) {
			return false;
		}

		if (empty($new_role)) {
			$this->errors[] = ["error" => _t("المرجو إختيار رتبة ")];
			return false;
		}

		global $dsql;
		foreach ($this->ids as $id) {
			$update = $dsql->dsql()->table('users')->set(["user_role" => $new_role])->where('user_role', $id)->update();
			$delete = $dsql->dsql()->table('roles_permissions')->where('id', $id)->delete();
			if (!$delete) {
				$this->errors[] = ["error" => _t(printf("لم يتم حدف الرتبة رقم %d", $id))];
			}
		}
	}

	function delete_file_category($new_category)
	{
		if (@admin_authority()->files != "on" || empty($new_category)) {
			return false;
		}
		global $dsql;
		foreach ($this->ids as $id) {
			if ($id == 1) {
				$this->errors[] = ["error" => _t("المعدرة لا يمكن حدف القسم الإفتراضي")];
				return false;
			}
			$update = $dsql->dsql()->table('files')->set(["file_category" => $new_category])->where('file_category', $id)->update();
			if ($update) {
				$delete = $dsql->dsql()->table('files_categories')->where('id', $id)->delete();
				if (!$delete) {
					$this->errors[] = ["error" => _t(printf("القسم رقم %d لم يتم حدفه", $id))];
				}
			} else {
				$this->errors[] = ["error" => _t("حدث خطأ المرجو إعادة المحاولة")];
			}
		}
	}

	function delete_categories($move_posts_to)
	{
		if (@admin_authority()->categories != "on") {
			return false;
		}
		global $dsql;
		foreach ($this->ids as $id) {
			$dsql->dsql()->table('post_category')->set(["post_category" => $move_posts_to])->where('post_category', $id)->update();
			$dsql->dsql()->table('categories')->set(["cat_parent" => get_category_field($id, 'cat_parent')])->where('cat_parent', $id)->update();
			$dsql->dsql()->table('post_category')->where('post_category', $id)->delete();
			$delete = $dsql->dsql()->table('categories')->where('id', $id)->delete();
			if (!$delete) {
				$this->errors[] = ["error" => _t(printf("القسم رقم %d لم يتم حدفه", $id))];
			}
		}
	}

	function delete_external_links()
	{
		if (@admin_authority()->external_links != "on") {
			return false;
		}
		global $dsql;
		foreach ($this->ids as $id) {
			$delete = $dsql->dsql()->table('site_links')->where('id', $id)->delete();
			if (!$delete) {
				$this->errors[] = ["error" => _t(printf("رابط رقم %d لم يتم حدفه", $id))];
			}
		}
	}
	function delete_conversation()
	{
		global $dsql;
		foreach ($this->ids as $id) {
			if (get_message_field($id, "msg_from") == get_current_user_info()->id) {
				$delete = $dsql->dsql()->table('messages_sys')->where('id', $id)->delete();
				$dsql->dsql()->table('conversations_sys')->where("msg_id", $id)->delete();
				continue;
			} else {
				$this->errors = ["error" => _t("حدث خطأ المرجو إعادة المحاولة")];
			}
		}
		if (is_null($this->errors)) {
			return true;
		}
		return false;
	}

	function errors()
	{
		return $this->errors;
	}

	private function delete_user_posts($user_id)
	{

		global $dsql;

		$dsql->dsql()->table('posts')->set(["post_author" => 1])->where('post_author', $user_id)->where('post_in', 'trusted')->update();

		$get_user_posts = $dsql->dsql()->table('posts')->where('post_author', $user_id)->where('post_in', 'untrusted')->get();

		if ($get_user_posts) {
			foreach ($get_user_posts as $post) {
				$this->delete_post_thumbnail($post["id"], $post["post_thumbnail"]);
				$dsql->dsql()->table('posts')->where('id', $post["id"])->delete();
			}
		}
	}

	private function can_delete_user($user_id)
	{
		// only user with super admin role can delete user
		if (is_super_admin() || get_current_user_info()->id == $user_id) {
			return true;
		}
		return false;
	}

	private function can_delete_post($post_id)
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		$post_in = get_post_field($post_id, "post_in");
		$post_author = get_post_field($post_id, "post_author");
		if ($post_in == "untrusted" && ($current_user->id == $post_author || admin_authority()->posts == "on")) {
			return true;
		} elseif ($post_in == "trusted" && admin_authority()->posts == "on" &&  admin_authority()->delete === true) {
			return true;
		}
		return false;
	}

	private function can_delete_link($post_id)
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}
		$post_in = get_post_info_field($post_id, "post_in");
		$post_author = get_post_info_field($post_id, "post_author");

		if ($post_in == "untrusted" && ($current_user->id == $post_author || admin_authority()->posts == "on")) {
			return true;
		} elseif ($post_in == "trusted" && admin_authority()->posts == "on" &&  (admin_authority()->delete === true or admin_authority()->posts == "on")) {
			return true;
		}
		return false;
	}

	private function can_delete_comment($comment_id)
	{
		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		$comment_user = get_comment_field($comment_id, "comment_user");
		$post_id = get_comment_field($comment_id, "post_id");
		$post_author = get_post_field($post_id, "post_author");

		if (($current_user->id == $post_author || $current_user->id == $post_author || admin_authority()->posts == "on" || is_super_admin()) === false) {
			return false;
		}
		return true;
	}

	private function delete_files_by_post($post_id)
	{
		if (empty($post_id)) {
			return false;
		}
		$post_thumb = get_post_field($post_id, "post_thumbnail");
		if ($post_thumb) {
			if ($this->delete_file($post_thumb)) {
				return true;
			}
		}
		return false;
	}

	private function delete_files_by_id($id)
	{
		if (empty($id)) {
			return false;
		}
		if ($this->delete_file($id)) {
			return true;
		}

		return false;
	}

	// author

	function delete_authors()
	{
		global $dsql;
		if (@admin_authority()->authors != "on" && !is_super_admin()) {
			return false;
		}
		foreach ($this->ids as $id) {
			if (!$dsql->dsql()->table('authors')->where('id', $id)->delete()) {
				$this->errors[] = ["error" => _t("المعذرة ! لم يتم حدف بعض المؤلفين")];
			}
		}
	}

	// boot comment

	function delete_boot_comments()
	{
		global $dsql;
		if (@admin_authority()->boots != "on" && !is_super_admin()) {
			return false;
		}
		$init_query = $dsql->dsql()->table('boot_comments');
		foreach ($this->ids as $id) {
			if(intval($id) > 0) {
				$init_query = $init_query->where('id', $id);
			} else {
				$init_query = $init_query->where('comment_name', $id);
			}
			if (!$init_query->delete()) {
				$this->errors[] = ["error" => _t("المعذرة ! لم يتم حدف بعض التعليقات")];
			}
		}
	}

	// boot

	function delete_boots()
	{
		global $dsql;
		if (@admin_authority()->boots != "on" && !is_super_admin()) {
			return false;
		}
		foreach ($this->ids as $id) {
			if (!$dsql->dsql()->table('boots')->where('id', $id)->delete()) {
				$this->errors[] = ["error" => _t("المعذرة ! لم يتم حدف بعض البوتات")];
			} else {
				if(!$dsql->dsql()->table('boot_meta')->where('boot_id', $id)->delete()) {
					$this->errors[] = ["error" => _t("المعذرة ! لم يتم حدف بعض المعلومات التابعة للبوتات")];
				}
			}
		}
	}

}
