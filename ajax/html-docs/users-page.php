<?php
	$q = $_GET["q"] ?? "";
	$sort = $_GET["sort"] ?? "";
	$order_by = $_GET["order_by"] ?? "";
	$get_users = get_users($sort,$order_by);
	if($get_users) :
		foreach( $get_users["results"] as $user ):
		$user_role_icon = get_thumb(get_roles($user["user_role"],"role_icon"));
		?>
		<div class="col-lg-3 text-center user-profile px-2 mb-3">
			<div class="shadow-sm p-3 bg-white">
				<a href="<?php echo get_author_in_post($user["id"])->link ?>">
					<img src="<?php esc_html(get_thumb($user["user_picture"],"sm")); ?>" class="user-pic rounded-circle img-fluid"/>
				</a>
				<div class="my-3"></div>
				<a href="<?php echo get_author_in_post($user["id"])->link ?>" class="h6 font-weight-bold">
					<?php 
					esc_html($user["user_name"]); 
					if($user_role_icon): 
					?>
					&nbsp;<img src="<?php echo $user_role_icon; ?>" width="18" height="18" data-toggle="tooltip" title="<?php esc_html(get_role_name($user["user_role"])); ?>"/>
					<?php endif; ?>
					</a>
				<div class="my-3"></div>
				<span class="user-bio font-weight-bold"><?php esc_html( get_user_meta($user["id"],"bio") ); ?></span>
				<div class="my-3"></div>
				<div class="row">
					<div class="col">
						<span class="h5"><?php echo formatWithSuffix( get_user_meta($user["id"],"points_remaining") ); ?></span><br/>
						<span class="h6"><?php echo _t("النقاط"); ?></span>
					</div>
					<div class="col">
						<span class="h5"><?php echo formatWithSuffix( count_user_posts($user["id"]) ); ?></span><br/>
						<span class="h6"><?php echo _t("المواضيع"); ?></span>
					</div>
					<div class="col">
						<span class="h5"><?php echo formatWithSuffix( count_user_followers($user["id"]) ); ?></span><br/>
						<span class="h6"><?php echo _t("المتابعين"); ?></span>
					</div>
				</div>
				<div class="my-3">
					<div class="row">
						<div class="col pr-1"><button class="btn btn-primary rounded-0 w-100 follow-btn not-followed" data-user="<?php esc_html($user["id"]); ?>"><?php echo _t("متابعة"); ?></button></div>
						<div class="col pl-1"><button class="btn btn-warning rounded-0 w-100 send-message-modal" data-user="<?php esc_html($user["id"]); ?>"><i class="fas fa-envelope mr-2"></i><?php echo _t("أرسل رسالة"); ?></button></div>
					</div>
				</div>
			</div>
		</div>
		<?php 
		endforeach; 
	endif;
	?>