<?php
require_once 'init.php';
if(!is_login_in()) {
	exit;
}
$page = $_GET["page"] ?? "statistics";
$dashboard_menu_items = [
	"statistics" => [
		"title" => _t("احصائيات"),
		"icon" => "fas fa-chart-area",
		"sub_title" => _t("هده الإحصائيات خاصة بموقعك فقط و ليست بالموقع كامل"),
	],	
	"posts" => [
		"title" => _t("مقالاتي و مشاركاتي"),
		"icon" => "fas fa-file",
	],	
	"comments" => [
		"title" => _t("اعدادات التعليقات"),
		"icon" => "fas fa-comments",
	],	
	"notifications" => [
		"title" => _t("اعدادات الإشعارات"),
		"icon" => "fas fa-bullhorn",
	],	
	"profile" => [
		"title" => _t("الملف الشخصي(CV)"),
		"icon" => "fas fa-user",
	],	
	"account" => [
		"title" => _t("اعدادات الحساب"),
		"icon" => "fas fa-cog",
	],	
	"social-networks" => [
		"title" => _t("الشبكات الإجتماعية"),
		"icon" => "fas fa-share-square",
	],	
	"points-bag" => [
		"title" => _t("حقيبة النقاط"),
		"icon" => "fas fa-briefcase",
	],	
	"instructions" => [
		"title" => _t("تعليمات هامة"),
		"icon" => "fas fa-allergies",
	]
];

if(!is_super_admin()) {
    $dashboard_menu_items["delete-account"] = [
		"title" => _t("حدف الحساب"),
		"icon" => "fas fa-user-times"
	];
}

$get_group_alerts = $dsql->dsql()->table('notifications_sys')->where('notif_type','group_alert')->where('notif_case','!=',4)->where('notif_to',get_current_user_info()->id)->order('notif_date','desc')->get();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<!-- SWITCHERY CSS -->
		<link href="<?php echo siteurl(); ?>/assets/lib/switchery/dist/switchery.min.css" rel="stylesheet" type="text/css"/>
		<title><?php echo _t( 'لوحة التحكم' ).' - '.$dashboard_menu_items[$page]["title"]; ?></title>
	</head>
	<body class="bg-light">
		<?php get_header("top");  ?>
		<!-- Dashboard -->
		<div id="user-dashboard" class="container my-3">
			<div class="row m-0">
			
				<!-- Dashboard nav -->
				<div class="user-dashboard-nav col-lg-3 col-md-12 p-0 bg-white border mb-3 mb-lg-0">
						<div class="d-none d-lg-block">
							<div class="dashboard-nav-title color-primary text-center font-weight-bold py-3"><?php echo _t('لوحة التحكم'); ?></div>
							<ul class="nav flex-column">
								<?php foreach($dashboard_menu_items as $page_name=>$menu): ?>
								<li class="nav-item">
									<a class="nav-link" href="<?php echo siteurl(); ?>/dashboard/<?php esc_html($page_name); ?>"><i class="<?php echo $menu["icon"]; ?>"></i>&nbsp;<?php echo $menu["title"]; ?></a>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="responsive-dashboard-nav d-lg-none d-block">
							<select class="custom-select dashboard-rs-menu">
								<?php foreach($dashboard_menu_items as $page_name=>$menu): ?>
								<option value="<?php esc_html($page_name); ?>" <?php selected_val($page_name,$page); ?> ><?php esc_html($menu["title"]); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
				</div><!-- / Dashboard nav -->
				
				<!-- User dashboard control -->
				<div class="user-dashboard-control col-lg-9 col-md-12 pl-lg-4 p-0">
					<?php if($get_group_alerts): ?>
					<!-- Alerts -->
						<?php foreach($get_group_alerts as $alert): ?>
						<div class="alert alert-danger rounded-0 alert-dismissible fade show" role="alert">
							<?php echo read_notif($alert["id"],$alert["notif_type"],$alert["notif_content"],$alert["notif_from"]); ?>
							<button type="button" class="close close-alert-group" data-id="<?php esc_html($alert["id"]); ?>" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<?php endforeach; ?>
					<!-- / Alerts -->
					<?php endif; ?>
					<div class="bg-white border p-2">
					<div class="user-dashboard-title border-bottom">
						<h1 class="h2"><i class="<?php echo $dashboard_menu_items[$page]["icon"]; ?>"></i>&nbsp;<?php echo $dashboard_menu_items[$page]["title"]; ?></h2>
						<?php if(isset($dashboard_menu_items[$page]["sub_title"])): ?>
						<p class="text-muted small"><?php echo $dashboard_menu_items[$page]["sub_title"]; ?></p>
						<?php endif; ?>
					</div>
					<div class="user-dashboard-body my-5">
						<?php include_once 'parts/dashboard/'.$page.".php"; ?>
					</div>
					</div>
				</div><!-- / User dashboard control -->
				
			</div>
		</div><!-- / Dashboard -->
		<?php user_end_scripts(); ?>
		<script src="<?php echo siteurl(); ?>/assets/lib/jqueryui/jquery-ui.min.js"></script>
		<script src="<?php echo siteurl(); ?>/assets/lib/switchery/dist/switchery.min.js"></script>
		<script>
			$(function() {
			     $(".sortabletable tbody").sortable();
				$(".dashboard-rs-menu").on("change",function() {
					window.location.href = "dashboard/"+$(this).val();
				});
			});
			var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));

			elems.forEach(function(html) {
			  var switchery = new Switchery(html);
			});
		</script>
	</body>
</html>