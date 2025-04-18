<?php

class Author
{

	private $errors = null;
	public $author_id  = null;
	function __construct($errors = null, $author_id = null)
	{
		$this->errors = $errors;
		$this->author_id = $author_id;
	}

	/**
	 * update_profile_info()
	 * 
	 */
	public function update_profile_info()
	{
		if (admin_authority()->authors != "on" && !is_super_admin()) {
			$this->errors[] = ["selector" => "#global-msg", "error" => "ليست لديك الصلاحية لاتمام العملية"];
			return false;
		}

		$name = @filter_var($_POST["name"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$description = @filter_var($_POST["description"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "";
		$user_id = @filter_var($_POST["user_id"], FILTER_SANITIZE_NUMBER_INT) ?? null;
		if(isset($_POST["author_id"])) {
			$this->author_id = @filter_var($_POST["author_id"], FILTER_SANITIZE_NUMBER_INT);
		}

		if (!$name) {
			$this->errors[] = ["selector" => "#name", "error" => "ادخال الاسم اجباري"];
		}

		if (is_null($this->errors) === false) {
			return false;
		}

		global $dsql;

		$cols = ["name" => $name, "description" => $description];
		if(!is_null($user_id) && !empty($user_id) && $user_id != 0) {
			$cols['user_id'] = $user_id;
		}

		$author = $dsql->dsql()->table('authors')->set($cols);
		if(!is_null($this->author_id)) {
			$save = $author->where('id', $this->author_id)->update();
		} else {
			$save = $author->insert();
		}
		/**
		 * $update == 0 if user not changed any information it return 0 so however we show success message
		 */
		if ($save) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * return @propery $errors value
	 * @return object
	 */
	public function get_errors()
	{
		return $this->errors;
	}

}
