<?php
require_once 'init.php';

$q = $_GET["q"] ?? "";
$sort = $_GET["sort"] ?? "";
$order_by = $_GET["order_by"] ?? "desc";
$get_users = get_users($sort,$order_by, ['q' => $q]);
?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<title></title>
	</head>
	<body>
		<?php get_header();  ?>
		<div class="my-5"></div>
		<div id="users-page">
			<div class="container">
				<?php if($get_users): ?>
				<div class="form-filter">
					<form method="GET" id="filter-users">
						<div class="d-md-flex">			
							<div>
							<input type="text" name="q" class="form-control rounded-0" placeholder="<?php echo _t('إبحث عن عضو'); ?>"/>
							</div>
							<div class="ml-auto mt-3 mt-md-0">
							<select class="form-control filter-users custom-select rounded-0" name="sort">
								<option selected="true" disabled="true"><?php echo _t("ترتيب حسب"); ?></option>
								<option value=""><?php echo _t("الكل"); ?></option>
								<option value="posts" <?php selected_val($sort,"posts"); ?> ><?php echo _t("أكثر مواضيع"); ?></option>
								<option value="points" <?php selected_val($sort,"points"); ?> ><?php echo _t("أكثر نقاط"); ?></option>
								<option value="most_followed"<?php selected_val($sort,"most_followed"); ?> ><?php echo _t("أكثر متابعين"); ?></option>
								<option value="post_views" <?php selected_val($sort,"post_views"); ?> ><?php echo _t("أكثر قراء للمواضيع"); ?></option>
								<option value="post_share" <?php selected_val($sort,"post_share"); ?> ><?php echo _t("أكثر مواضيع مشاركة"); ?></option>
							</select>
							</div>
						</div>
					</form>
				</div>
				<div class="my-5"></div>
				<div class="row load-users">
					<?php 
					foreach($get_users["results"] as $user): 
						$user_role_icon = get_thumb(get_roles($user["user_role"],"role_icon"));
					?>
					<div class="col-lg-3 text-center user-profile px-2 mb-3">
						<div class="shadow-sm p-3 bg-white">
							<a href="<?php echo get_author_in_post($user["id"])->link ?>">
								<img src="<?php esc_html(get_thumb($user["user_picture"],"sm")); ?>" class="user-pic rounded-circle img-fluid"/>
							</a>
							<div class="my-3"></div>
							<a href="<?php echo get_author_in_post($user["id"])->link ?>" class="h5 font-weight-bold"><?php esc_html($user["user_name"]); ?>
								<?php if($user_role_icon): ?>
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
									<div class="col pr-1"><button class="btn btn-primary rounded-0 w-100 follow-btn <?php is_follower_h_c($user["id"]); ?>" data-user="<?php esc_html($user["id"]); ?>"><?php echo _t("متابعة"); ?></button></div>
									<div class="col pl-1"><button class="btn btn-warning rounded-0 w-100 send-message-modal" data-user="<?php esc_html($user["id"]); ?>"><i class="fas fa-envelope mr-2"></i><?php echo _t("أرسل رسالة"); ?></button></div>
								</div>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php 
				else:
				no_content();
				endif;
				?>
				<?php user_end_scripts(); ?>

				<?php 
				echo load_more_btn($get_users["rows"],'.load-users',["q" => $q, "sort" => $sort, "order_by" => $order_by,"request" => "users-ajax"]);
				?>
			</div>
		</div>
		<div class="my-5"></div>
		<?php get_footer(); ?>
		<script>
		$(function() {
			$(".filter-users").on("change",function() {
				$("#filter-users").submit();
			});
		});
		</script>
	</body>
</html>