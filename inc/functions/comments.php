<?php

function save_post_comment(array $fields)
{

	$post_id = ((int) $fields["post_id"] ?? 0);
	$comment_user = ((int) $fields['comment_user'] ?? 0);
	$comment = $fields["comment"] ?? "";
	$attachment = $fields["comment_attachment"] ?? null;
	$comment_id = ((int) $fields["id"] ?? 0);
	$comment_type = $fields['comment_type'] ?? 'comment';
	$comment_parent = ((int) $fields['comment_parent'] ?? 0);
	$comment_date = $fields['comment_date'] ?? gmdate("Y-m-d H:i:s");
	$comment_status = $fields['comment_status'] ?? 'publish';

	if (absint($post_id) === 0 || absint($comment_user) === 0) {
		return false;
	}

	global $dsql;

	$data = [
		"comment" => $comment,
		"post_id" => $post_id,
		"comment_user" => $comment_user,
		"comment_date" => $comment_date,
		"comment_type" => $comment_type,
		"comment_attachment" => $attachment,
		"comment_status" => $comment_status,
		"comment_parent" => $comment_parent
	];

	$query = $dsql->dsql()->table('comments')->set($data);

	if (absint($comment_id) > 0) {
		$query->where('id', $comment_id)->where('comment_user', $comment_user)->update();
	} else {
		$query->insert();
		$comment_id = absint(get_last_inserted_id());
	}

	$data['id'] = $comment_id;
	if(absint($data['id']) > 0) {
		$general_settings = @unserialize(get_settings("site_general_settings"));
		if($general_settings["boot_learning"] == "on") {
			$post_type = get_post_field($post_id, 'post_type');
			$category = $dsql->dsql()->table('categories')->where("cat_taxonomy", $post_type)->field('cat_title')->limit(1)->getRow();
			$cols = [
				"comment_name" => $category['cat_title'] ?? 'مختلف',
				"comment_lang" => current_lang(),
				"comment" => $comment
			];
			$dsql->dsql()->table('boot_comments')->set($cols)->insert();
		}
	}

	return $data;
}

function get_comments_form($post_id, $media = true, $user_pic = null)
{
	/** @var $class_attr add this class when user not loginin */
	$class_attr = "";
	if (!is_login_in()) {
		$class_attr = "toggle-signin-modal";
	}
	ob_start();
?>
	<!-- Comments -->
	<div class="comments">
		<div class="add-comment-form">
			<form action="" method="POST" id="comment-form" class="<?php echo $class_attr; ?>">
				<div class="form-row">
					<div class="col-12">
						<div class="comment-form">
							<div>
								<div class="d-flex" style="height: 55px;">
									<img src="<?php echo $user_pic; ?>" class="user-pic img-fluid mr-1" />
									<textarea style="z-index: 3;" id="emojoComment1" name="comment" class="form-control rounded h-100"></textarea>

									<button class="btn bg-danger rounded-circle d-flex justify-content-center align-items-center p-1 add-comment">
										<i style="font-size: 11px;" class="far fa-paper-plane text-white"></i>
									</button>

								</div>
							</div>
							<div class="comment-attachment-dsp mt-3">
								<i class="fas fa-times remove-comment-thumb"></i>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" name="method" value="save_post_comment_ajax" />
				<input type="hidden" name="comment_attachment" id="comment_attachment" value="" />
				<input type="hidden" name="post_id" value="<?php esc_html($post_id); ?>" />
			</form>
		</div>
	</div>
	<!-- Comments -->
	<?php
	return ob_get_clean();
}


if (!function_exists("un_lock_comment")) {
	/**
	 * un_lock_comment()
	 *
	 * @param int $comment_id
	 * @return boolean
	 */
	function un_lock_comment($comment_id, $default_status = false)
	{

		if (empty($comment_id)) {
			return false;
		}

		$current_user = get_current_user_info();
		if (!$current_user) {
			return false;
		}

		$post_id = get_comment_field($comment_id, "post_id");

		$post_author = get_post_field($post_id, "post_author");

		if (($post_author == $current_user->id || is_super_admin()) === false) {
			return false;
		}

		$new_status = "";

		if ($default_status) {

			$new_status = $default_status;
		} else {

			$comment_status = get_comment_field($comment_id, "comment_status");
			if ($comment_status == "publish") {
				$new_status = "locked";
			} else {
				$new_status = "publish";
			}
		}

		global $dsql;


		$update = $dsql->dsql()->table('comments')->set(["comment_status" => $new_status])->where('id', $comment_id)->update();
		if ($update) {
			return true;
		}

		return false;
	}
}

function post_comment_html($comments, $no_reply = false)
{
	$current_user = get_current_user_info();
	ob_start();
	foreach ($comments as $comment):
		$user_name = get_user_field($comment["comment_user"], "user_name");
		$user_picture = get_user_field($comment["comment_user"], "user_picture");
		$comment_replies = $comment['replies'] ?? 0;
		$replies = is_array($comment_replies) ? count($comment_replies) : $comment_replies;
	?>
		<!-- comment -->
		<div class="form-row comment-p-id-<?php esc_html($comment["id"]); ?>">
			<div class="col-1"><img src="<?php echo get_thumb($user_picture); ?>" class="img-fluid" /></div>
			<div class="col-11">
				<div class="d-flex align-items-center">
					<div class="w-100 pr-3">
						<a href="<?php echo siteurl() . "/user/" . $comment["comment_user"]; ?>"><?php esc_html($user_name); ?></a>&nbsp;
						<small class="text-muted"><?php get_timeago(strtotime($comment["comment_date"])); ?></small>
						<div class="comment-text-<?php esc_html($comment["id"]); ?>">
							<p class="comment-text"><?php esc_html($comment["comment"]); ?></p>
						</div>
						<?php if ($no_reply === false): ?>
							<div class="comment-replies comment-childs-<?php esc_html($comment["id"]); ?>">
								<a href="#" data-id="<?php esc_html($comment['id']); ?>" class="add-reply"><?php echo _t('أضف رد'); ?></a> |
								<a href="#" class="show-replies" data-id="<?php esc_html($comment['id']); ?>"><?php echo _t('عرض الردود'); ?> (<?php esc_html($replies); ?> )</a>
								<div class="reply-form"></div>
								<div class="users-replies">
								</div>
							</div>
						<?php endif; ?>
						<?php if (!empty($comment["comment_attachment"])): ?>
							<div class="my-2 comment-attachment">
								<img src="<?php echo get_thumb($comment["comment_attachment"], "sm"); ?>" class="img-fluid" />
								<input type="hidden" class="comment-thumb-id-<?php esc_html($comment["id"]); ?>" value="<?php esc_html($comment["comment_attachment"]); ?>" />
							</div>
						<?php endif; ?>
					</div>
					<!-- comment settings -->
					<div class="ml-auto comment-settings">
						<button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
						<div class="dropdown-menu">
							<?php if ($comment["comment_user"] == @$current_user->id): ?>
								<span class="dropdown-item edit-comment" data-id="<?php esc_html($comment["id"]); ?>" href="#"><?php echo _t("تعديل"); ?></span>
							<?php endif; ?>
							<div class="dropdown-divider"></div>
							<?php if ($comment["comment_user"] == @$current_user->id || @admin_authority()->posts == "on" || @admin_authority()->comments == "on"): ?>
								<span class="dropdown-item delete-comment-btn" data-id="<?php esc_html($comment["id"]); ?>" data-remove=".comment-p-id-<?php esc_html($comment["id"]); ?>"><?php echo _t("حدف"); ?></span>
							<?php endif; ?>
							<?php if ($comment["comment_user"] != @$current_user->id): ?>
								<span class="dropdown-item open-complain-form" data-post="<?php esc_html($comment["post_id"]); ?>" data-comment="<?php esc_html($comment["id"]); ?>"><?php echo _t("تبليغ"); ?></span>
							<?php endif; ?>
						</div>
					</div><!-- comment settings -->
				</div>
			</div>
		</div><!-- comment -->
<?php
	endforeach;
	return ob_get_clean();
}
