<?php
$complain_id = $_GET["complain_id"] ?? "";
?>
<div class="dash-part-form">
	<?php
	if (empty($complain_id)):
		$get_posts_complains = $dsql->dsql()->table('post_meta')->where('meta_key', 'complain')->order('id', 'desc')->get();
		if ($get_posts_complains):
	?>
			<div class="full-width">
				<div class="table-responsive">
					<table class="table_parent">
						<tbody>
							<tr>
								<th>رقم المقال</th>
								<th>إسم المرسل</th>
								<th>رقم الجوال أو البريد الإلكتروني</th>
								<th>نوع المخالفة</th>
								<th>تفاضيل مخالفة</th>
								<th></th>
								<th></th>
							</tr>
							<?php
							foreach ($get_posts_complains as $complain):
								$complain_value = json_decode($complain["meta_value"]);
								$user_id = false;
								if (isset($complain_value->user_id) && !empty($complain_value->user_id)) {
									$user_id = true;
									$user_name =  get_user_info($complain_value->user_id)->user_name;
									$user_email = get_user_info($complain_value->user_id)->user_email;
								} else {
									$user_name = $complain_value->name;
									$user_email = $complain_value->phone_email;
								}

							?>
								<tr>
									<td><a href="<?php echo get_post_link($complain["post_id"]); ?>" target="_blank"><?php esc_html($complain["post_id"]); ?></a></td>
									<td>
										<?php if ($user_id): ?>
											<a href="<?php echo siteurl() . "/user/" . $complain_value->user_id; ?>"><?php esc_html($user_name); ?></a>
										<?php else: ?>
											<span><?php esc_html($user_name); ?></span>
										<?php endif; ?>
									</td>
									<td><?php esc_html($user_email); ?></td>
									<td><?php esc_html($complain_value->type); ?></td>
									<td><a href="dashboard/complains?complain_id=<?php esc_html($complain["id"]); ?>">عرض التفاصيل</a></td>
									<td>
										<?php if (isset($complain_value->comment)): ?>
											<a href="<?php echo get_post_link($complain["post_id"]) . "?comment=" . $complain_value->comment; ?>">تعليق رقم <?php esc_html($complain_value->comment); ?></a>
										<?php endif; ?>
									</td>
									<td>
										<button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=complain&id=<?php esc_html($complain["id"]); ?>"><i class="fas fa-trash"></i></button>
									</td>
								</tr>
						</tbody>
					<?php endforeach; ?>
					</table>
				</div>
			</div>
		<?php
		else:
			no_content();
		endif;
		?>
		<?php else:
		$get_complain = $dsql->dsql()->table('post_meta')->where('meta_key', 'complain')->where('id', $complain_id)->limit(1)->getRow();
		if ($get_complain):
			$complain = $get_complain;
			$complain_value = json_decode($complain["meta_value"]);
			$user_id = false;
			if (isset($complain_value->user_id) && !empty($complain_value->user_id)) {
				$user_id = true;
				$user_name =  get_user_info($complain_value->user_id, "user_name")->user_name;
				$user_email = get_user_info($complain_value->user_id, "user_email")->user_email;
			} else {
				$user_name = $complain_value->name;
				$user_email = $complain_value->phone_email;
			}

		?>
			<div class="table-responsive">
				<table>
					<tbody>
						<tr>
							<td>رقم المقال : </td>
							<td><a href="<?php echo get_post_link($complain["post_id"]); ?>" target="_blank"><?php esc_html($complain["post_id"]); ?></a></td>
						</tr>
						<tr>
							<td>إسم المرسل : </td>
							<td>
								<?php if ($user_id): ?>
									<a href="<?php echo siteurl() . "/user/" . $complain_value->user_id; ?>"><?php esc_html($user_name); ?></a>
								<?php else: ?>
									<span><?php esc_html($user_name); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td>رقم الجوال أو البريد الإلكتروني : </td>
							<td><?php esc_html($user_email); ?></td>
						</tr>
						<tr>
							<td>نوع المخالفة : </td>
							<td><?php esc_html($complain_value->type); ?></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<?php if (isset($complain_value->comment)): ?>
									<a href="<?php echo get_post_link($complain["post_id"]) . "?comment=" . $complain_value->comment; ?>">تعليق رقم <?php esc_html($complain_value->comment); ?></a>
								<?php endif; ?>

							</td>
						</tr>
						<tr>
							<td>تفاضيل مخالفة : </td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
			<p><?php esc_html($complain_value->details); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>