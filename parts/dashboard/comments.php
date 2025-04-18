<?php
/**
 * posts.php
 * User dashboard page
 */
 
 /** @var $btn_primary can take two values btn-primary or btn-secondary depending on @var $comment_type */
 $btn_primary = "btn-primary";
 $btn_secondary = "btn-secondary";
 
 $current_user = get_current_user_info();
 $comment_type = $_GET["comment_type"] ?? "posts_comments";
 $order_by = $_GET["order_by"] ?? "desc";
 if($comment_type == "posts_comments") {

	$get_comments = get_posts_comments(true,$current_user->id,$order_by);
	
 }elseif($comment_type == "my_comments") {
	$get_comments = get_posts_comments(null,$current_user->id,$order_by);
	$btn_primary = "btn-secondary";
	$btn_secondary = "btn-primary";
 }
$comment_rows_count = count_last_query();
?>
<div class="user-dashboard-posts">
	<div class="my-5"></div>
	<div class="d-flex">
		<?php multi_action_form("comments"); ?>
		<div class="ml-auto">
			<div class="form-group">
				<a href="#" class="btn <?php echo $btn_primary; ?> filter-comment-type" data-value="posts_comments"><?php echo _t("تعليقات المشاركات"); ?></a>
				<a href="#" class="btn <?php echo $btn_secondary; ?> filter-comment-type" data-value="my_comments"><?php echo _t("تعليقاتي"); ?></a>
			</div>	
		</div>
	</div>
	<div class="my-3"></div>
	<!-- filter form -->
	<div class="filter-form">
		<form action="" method="GET" id="filter-form">
			<div class="d-sm-flex">
				<div class="mr-auto">
				</div>
				<div class="ml-auto mt-3 mt-sm-0">
					<?php echo order_by_btn($order_by); ?>
				</div>				
			</div>
			<input type="hidden" name="comment_type" id="comment_type" value="<?php esc_html($comment_type); ?>"/>
			<input type="hidden" name="order_by" value="<?php esc_html($order_by); ?>"/>
		</form>
	</div><!-- / filter-form -->
	<div class="my-5"></div>
	<?php if($get_comments): ?>
	<div class="table-comments">
		<table class="table table-responsive-sm" style="overflow-x:auto;">
			<thead>
				<th>
					<div class="custom-control custom-checkbox">
					  <input type="checkbox" class="custom-control-input check-all-multi" id="select-all-checkbox">
					  <label class="custom-control-label" for="select-all-checkbox"></label>
					</div>				
				</th>
				<th><?php echo _t("التعليق"); ?></th>
				<th><?php echo _t("رقم المحتوى"); ?></th>
				<th><?php echo _t("إسم المستخدم"); ?></th>
				<th><?php echo _t("فئة"); ?></th>
				<th><?php echo _t("الإجراءات"); ?></th>
			</thead>
			<tbody>
				<?php 
				foreach($get_comments as $comment): 
				$un_lock_btn_class = "btn-success";
				if($comment["comment_status"] != "publish") {
					$un_lock_btn_class = "btn-warning post-locked";
				}
				?>
				<tr class="tr-comment-<?php esc_html($comment["id"]); ?>">
					<td>
						<div class="custom-control custom-checkbox">
						  <input type="checkbox" class="custom-control-input check-box-action" data-id="<?php esc_html($comment["id"]); ?>" id="select-all-checkbox-<?php esc_html($comment["id"]); ?>">
						  <label class="custom-control-label" for="select-all-checkbox-<?php esc_html($comment["id"]); ?>"></label>
						</div>					
					</td>
					<td>
						<a href="<?php echo get_post_link($comment["post_id"]); ?>?comment_id=<?php esc_html($comment["id"]); ?>" class="color-link">
							<?php esc_html(substr_str($comment["comment"],20)); ?>
						</a>
					</td>
					<td><a href="<?php echo get_post_link($comment["post_id"]); ?>" class="color-link"><?php esc_html($comment["post_id"]); ?></a></td>
					<td><a href="user/<?php esc_html($comment["comment_user"]); ?>" class="color-link"><?php esc_html( get_user_field($comment["comment_user"],"user_name") ); ?></a></td>
					<td><?php esc_html( get_taxonomy_title(get_post_field($comment["post_id"],"post_type")) ); ?></td>
					<td>
						<!-- Actions buttons -->
						<div class="btn-actions row">
							<div class="col-md-12 col-lg-4 mb-2 mb-lg-0 px-2">
								<button class="btn btn-danger rounded-circle delete-comment-btn" data-id="<?php esc_html($comment["id"]); ?>" data-remove=".tr-comment-<?php esc_html($comment["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('حدف'); ?>"><i class="fas fa-trash"></i></button>
							</div>
							<?php if($comment_type == "posts_comments" && $current_user->id != $comment["comment_user"]): ?>
							<div class="col-md-12 col-lg-4 mb-2 mb-lg-0 px-2">
								<button class="btn <?php echo $un_lock_btn_class; ?> rounded-circle un_lock un_lock_comment" data-id="<?php esc_html($comment["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('قفل'); ?>"></button>
							</div>
							<?php endif; ?>
						</div><!-- Actions buttons -->
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php
		echo load_more_btn(
			$comment_rows_count,
			".table-comments tbody",
			[
				"request" => "dashboard-ajax",
				"data" => "load-comments",
				"comment_type" => esc_html__($comment_type),
				"order_by" => esc_html__($order_by)
			]		
		); 
?>	
	<?php 
	else:
	no_content();
	endif;
	?>	
</div>