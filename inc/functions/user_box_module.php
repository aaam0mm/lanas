<?php

function user_box_module( $user ) {
	
	$current_user = get_current_user_info();
	
	ob_start();
	?>
<!-- user box -->
<div class="shadow-sm p-3 bg-white">
    <img src="<?php echo get_thumb( $user["user_picture"] ); ?>" class="user-pic rounded-circle img-fluid"/>
    <div class="my-3"></div>
    <a href="<?php echo siteurl().'/user/'.$user["id"]; ?>" class="h6 font-weight-bold">
        <?php esc_html($user["user_name"]); ?>
        <?php if($user["user_verified"] == "yes"): ?>
		<i class="fas fa-check-circle text-primary"></i>
        <?php endif; ?>
    </a>
    <div class="my-3"></div>
    <span class="user-bio font-weight-bold">
        <?php esc_html( get_user_meta($user["id"],"bio") ); ?></span>
    <div class="my-3"></div>
    <div class="row">
        <div class="col-4">
            <span class="h6">
                <?php echo formatWithSuffix( get_user_meta($user["id"],"points_remaining") ); ?></span><br />
            <span class="h6">
                <?php echo _t("النقاط"); ?>
			</span>
        </div>
        <div class="col-4">
            <span class="h6">
                <?php echo formatWithSuffix( count_user_posts($user["id"], "trusted") ); ?></span><br />
            <span class="h6">
                <?php echo _t("المواضيع"); ?>
			</span>
        </div>
        <div class="col-4">
            <span class="h6">
                <?php echo formatWithSuffix( count_user_followers($user["id"]) ); ?></span><br />
            <span class="h6">
                <?php echo _t("المتابعين"); ?>
			</span>
        </div>
    </div>
	<?php if($current_user && $current_user->id != $user["id"]): ?>
    <div class="my-3">
        <div class="row">
            <div class="col-12 col-sm-6 mb-2 mb-sm-0"><button class="btn btn-primary rounded-0 w-100 follow-btn <?php is_follower_h_c($user["id"]); ?>" data-user="
                <?php esc_html($user["id"]); ?>">
                <?php echo _t("متابعة"); ?></button>
			</div>
            <div class="col-12 col-sm-6"><button class="btn btn-warning rounded-0 w-100 send-message-modal" data-user="<?php esc_html($user["id"]); ?>"><i class="fas fa-envelope mr-2"></i>
                <?php echo _t("أرسل رسالة"); ?></button>
			</div>
        </div>
    </div>
	<?php endif; ?>
</div><!-- user box -->
	<?php
	return ob_get_clean();
}
