<?php
$msg = $_GET["msg"] ?? "";
?>
<div class="dash-part-form">
	<?php
	    if(empty($msg)):
		$get_contact_form = $dsql->dsql()->table('contact_form')->limit(paged('end'),paged('start'))->get();
		?>
			<div class="full-width">
				<?php if($get_contact_form): ?>
				<div class="table-responsive">
				<table class="table_parent">
					<tbody>
						<tr>
							<th>الإسم الكامل</th>
							<th>البريد الإلكتروني</th>
							<th>سبب المراسلة</th>
							<th>الرسالة</th>
							<th></th>
						</tr>
						<?php foreach( $get_contact_form as $contact_form  ): 
						$user_id = $contact_form["user_id"];
						?>
						<tr>
							<td>
							    <?php 
							    if(is_null($user_id)):
							    esc_html($contact_form["contact_name"]); 
							    else:
							        echo '<a href="'.siteurl()."/user/".$user_id.'">'.esc_html__(get_user_field($user_id,"user_name")).'</a>';
							    endif;
							    ?>
							    </td>
							<td><?php esc_html($contact_form["contact_email"]); ?></td>
							<td><?php esc_html($contact_form["contact_subject"]); ?></td>
							<td><a href="dashboard/contact?msg=<?php esc_html($contact_form["id"]); ?>">عرض الرسالة</a></td>
							<td>
								<button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=contact_form&id=<?php esc_html($contact_form['id']); ?>"><i class="fas fa-trash"></i></button>
							</td>
						</tr>
					</tbody>
					<?php endforeach; ?>
				</table>
                </div>
				<?php 
				else:
					no_content();
				endif;
				?>
			</div>
		<?php else: ?>
		<?php
			$get_msg = $dsql->dsql()->table('contact_form')->where('id',$msg)->limit(1)->getRow();
			if($get_msg): 
			$msg = $get_msg;
			?>
			<div class="table-responsive">
			<table>
				<tbody>
					<tr>
						<td>الإسم الكامل : </td>
						<td><?php esc_html($msg["contact_name"]); ?></td>
					</tr>
					<tr>
						<td>البريد الإلكتروني : </td>
						<td><?php esc_html($msg["contact_email"]); ?></td>
					</tr>
					<tr>
						<td>سبب المراسلة : </td>
						<td><?php esc_html($msg["contact_subject"]); ?></td>
					</tr>
				</tbody>
			</table>
            </div>
			<p><?php esc_html($msg["contact_message"]); ?></p>
			<?php
			else:
				exit(0);
			endif;
		
		?>
		<?php endif; ?>
</div>
