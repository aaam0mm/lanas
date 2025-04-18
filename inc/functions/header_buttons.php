<?php
if (!function_exists("header_buttons")) {

	function header_buttons($area = 'header')
	{
		$current_user = get_current_user_info();
		?>
		<li class="position-relative header-btn-li">
			<?php if ($area == 'header') : ?>
			<?php elseif ($area == 'admin_dash') : ?>
				<a href="<?php echo siteurl(); ?>" class="btn btn-transparent text-white py-0 px-0 mr-1"><i class="fas fa-home fa-lg"></i></a>
			<?php endif; ?>
		</li>
		<li class="position-relative header-btn-li">
			<button class="position-relative btn btn-transparent text-white notif-open-btn py-0 px-0 mr-1" data-toggle="dropdown">
				<i class="fas fa-bullhorn fa-lg"></i><span class="badge badge-danger position-absolute notif-count"><?php echo count_user_notifs(1); ?></span>
			</button>
			<!-- Dropdown notifs -->
			<div class="dropdown-menu dropdown-user-notifs shadow border-0 animated fadeInUp py-0 overflow-hidden">
				<div class="p-2 h6 m-0"><?php echo _t("تنبيه"); ?></div>
				<div class="dropdown-divider m-0"></div>
				<div class="user-notif-dropdown pt-2">
					<?php
							if (get_user_notifs()) :
								foreach (get_user_notifs() as $notif) :
									?>
							<!-- notif -->
							<div class="notif-id-<?php esc_html($notif["id"]); ?> d-flex px-2">
								<?php if ($notif["notif_from"] != 0) : ?>
									<a href=""><img class="dropdown-img" src="<?php echo get_thumb(get_user_info($notif["notif_from"])->user_picture); ?>" alt="" /></a>
								<?php endif; ?>
								<div class="dropdown-content ml-2">
									<?php echo read_notif($notif["id"], $notif["notif_type"], $notif["notif_content"], $notif["notif_from"]); ?>
									<div class="user-time small text-muted"><i class="fas fa-clock fa-sm mr-1"></i><?php echo get_timeago(strtotime($notif["notif_date"])); ?></div>
								</div>
							</div><!-- notif -->
							<div class="dropdown-divider"></div>
					<?php
								endforeach;
							else :
								no_content();
							endif;
							?>
				</div>
				<a href="#" class="d-block color-link text-center p-2 bg-light open-all-notifs">
					<?php echo _t("عرض الكل"); ?>
				</a>
			</div><!-- / Dropdown notifs -->

		</li>
		<!-- Messages -->
		<li class="position-relative header-btn-li">
			<button class="position-relative btn btn-transparent text-white py-0 px-0 mr-1 msg-open-btn" data-toggle="dropdown">
				<i class="fas fa-envelope fa-lg"></i><span class="badge badge-danger position-absolute msg-count"><?php echo count_user_msgs('sent'); ?></span>
			</button>
			<!-- Dropdown messages -->
			<?php $get_box_messages = get_box_messages(null, 8); ?>
			<div class="dropdown-menu dropdown-user-messages shadow border-0 animated fadeInUp py-0 overflow-hidden">
				<div class="p-2 h6 m-0"><?php echo _t("الرسائل"); ?></div>
				<div class="dropdown-divider m-0"></div>
				<?php if ($get_box_messages) : ?>
					<div class="user-message-dropdown pt-2">
						<?php
							foreach ($get_box_messages as $msg_box) :
										$u_d = switch_message_display_user($msg_box["msg_from"], $msg_box["msg_to"]);
										?>
							<!-- Message -->
							<div class="d-flex px-2">
								<a href="<?php echo siteurl() . "/message/" . $msg_box["msg_id"]; ?>"><img class="dropdown-img" src="<?php echo get_thumb($u_d["user_picture"]); ?>" alt="" /></a>
								<div class="dropdown-content ml-2">
									<a href="<?php echo siteurl() . "/message/" . $msg_box["msg_id"]; ?>" class="color-link"><?php esc_html($msg_box["msg"]); ?></a>
									<div class="user-time small text-muted">
										<i class="fas fa-clock fa-sm mr-1"></i><?php echo get_timeago(strtotime($msg_box["msg_date"])); ?>
									</div>
								</div>
							</div><!-- / Message -->
							<div class="dropdown-divider"></div>
						<?php endforeach; ?>
					</div>
					<a href="<?php echo siteurl() . "/messages.php"; ?>" class="d-block color-link text-center p-2 bg-light">
						<?php echo _t("عرض الكل"); ?>
					</a>
				<?php endif; ?>
			</div><!-- / Dropdown messages -->
		</li><!-- / Messages -->
		<li class="position-relative header-btn-li">
			<button class="btn btn-transparent text-white py-0 px-0 mr-1" data-toggle="dropdown">
				<img class="rounded-circle" src="<?php echo get_thumb($current_user->user_picture); ?>" alt="user image" width="26" height="26" />
			</button>
			<!-- Dropdown user links -->
			<div class="dropdown-menu dropdown-user-menu shadow border-0 animated fadeInUp">
				<a class="dropdown-item py-2" href="<?php echo siteurl() . "/user/" . $current_user->id; ?>"><i class="fas fa-user mr-2"></i><?php echo _t("ملف الشخصي"); ?></a>
				<div class="dropdown-divider m-0"></div>
				<a class="dropdown-item py-2" href="<?php echo siteurl() . "/dashboard/statistics"; ?>"><i class="fas fa-cog mr-2"></i><?php echo _t("إعدادات"); ?></a>
				<div class="dropdown-divider m-0"></div>
				<?php
						if (is_admin() && $area != 'admin_dash') :
							$link_dashboard = (array) @json_decode(get_user_meta($current_user->id, "user_permissions"));
							$page_e = "";
							foreach ($link_dashboard as $page => $stat) {
								if ($stat == "on") {
									$page_e = $page;
									break;
								}
							}
							?>
					<a class="dropdown-item py-2" href="<?php echo siteurl() . "/admin/dashboard/" . $page_e; ?>"><i class="fas fa-user-secret mr-2"></i><?php echo _t("لوحة الأدمن"); ?></a>
					<div class="dropdown-divider m-0"></div>
				<?php endif; ?>
				<?php if ($area != 'admin_dash') : ?>
					<a class="dropdown-item py-2 open-lang-settings" href="#"><i class="fas fa-language mr-2"></i><?php echo _t("اللغة"); ?></a>
					<div class="dropdown-divider m-0"></div>
				<?php endif; ?>
				<?php if ($area != 'admin_dash') : ?>
					<a class="dropdown-item py-2" href="<?php echo siteurl() . "/messages.php"; ?>"><i class="fas fa-envelope mr-2"></i><?php echo _t("الرسائل"); ?></a>
				<?php else : ?>
					<a class="dropdown-item py-2" href="<?php echo siteurl() . "/admin/dashboard/contact"; ?>"><i class="fas fa-envelope mr-2"></i><?php echo _t("الرسائل"); ?></a>
				<?php endif; ?>
				<div class="dropdown-divider m-0"></div>
				<a class="dropdown-item py-2" href="<?php echo siteurl() . "/logout.php"; ?>"><i class="fas fa-power-off mr-2"></i><?php echo _t("الخروج"); ?></a>
			</div><!-- / Dropdown user links -->
		</li>
		<!-- Add post btn -->
		<li>
			<button class="btn header-mini-add-post add-new-post btn-transparent text-white p-0 bg-green rounded-circle">
				<i class="fas fa-plus fa-lg"></i>
			</button>
		</li><!-- / Add post btn -->
<?php
	}
}
