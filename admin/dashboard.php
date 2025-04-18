<?php

require_once "../init.php";
require_once "functions.php";
$user_authority = user_authority();

if (is_admin() === false) {
	exit(http_response_code(404));
}

$page = $_GET["page"] ?? "statistics";
$section = $_GET["section"] ?? "";
if ($section == 'add_category') {
	$section = 'add_categories';
}
$user_authority = user_authority();

if (admin_authority()->$page == "on" || admin_authority()->$page === true) {
	$page_require = "parts/" . $page . ".php";

} else {
	exit(header("HTTP/1.1 404 Not found"));
}
require_once("inc/dash-menu.php");
?>
<!DOCTYPE html>
<html dir="rtl">

<head>
	<base href="<?php echo siteurl(); ?>/admin/" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<link href="<?php echo siteurl(); ?>/assets/lib/fontawesome/css/fontawesome.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/admin/css/flatpickr.min.css" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/earlyaccess/notonaskharabic.css?ver=4.8.1" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Cairo" rel="stylesheet">
	<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo siteurl(); ?>/assets/lib/bootstrap/css/rtl/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo siteurl(); ?>/assets/css/animate.css" />
	<link href="<?php echo siteurl(); ?>/assets/css/default.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/admin/css/style.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/switchery/dist/switchery.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
	<!-- FONT AWESOME CSS -->
	<link href="<?php echo siteurl(); ?>/assets/lib/fontawesome/css/all.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo siteurl(); ?>/assets/lib/jquery/jquery.min.js?v="></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/lib/bootstrap/js/popper.min.js?v="></script>
	<!-- BOOTSTRAP JS -->
	<script src="<?php echo siteurl(); ?>/assets/lib/bootstrap/js/bootstrap.min.js?v="></script>
	<!-- CORE JS -->
	<script src="<?php echo siteurl(); ?>/assets/js/core.js?v="></script>
	<script src="<?php echo siteurl(); ?>/assets/lib/tinymce/tinymce.min.js?apiKey=cddlpm4517sshn5nibdymuairnrhqi3hx7kyvcomimsfdc4m"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery-ui-multidatespicker@1.6.6/jquery-ui.multidatespicker.js"></script>
	<script>
		$(function() {
			$(".tinymce-area").add_tinymce();
		});
	</script>
	<style>
		.select2-container .select2-selection--single {
			height: 37px !important;
		}
		.select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
			height: 100%;
			line-height: 35px;
		}
	</style>
	<?php global_js(); ?>
	<script src="../assets/lib/sweetalert/sweetalert.min.js?v="></script>
	<title>لوحة التحكم - <?php echo $arr[$page]["title"]; ?></title>
</head>

<body>
	<input type="file" name="file" class="file-inp" />
	<div id="panel_dashboard">
		<div class="cover d-none">
			<div class="loader-nas"></div>
		</div>
		<div id="panel_rightbar">
			<div class="panel_t">
				<h2>لوحة التحكم</h2>
			</div>
			<div class="panel_rightbar">
				<ul class="panel_parent">
					<?php
					echo admin_dash_menu();
					?>
				</ul>
			</div>
		</div>
		<div id="panel_leftbar">
			<div class="top_notif-bar d-flex align-items-center">
				<div class="toggle-sidebar"><i class="fa fa-bars"></i></div>
				<div class="ml-auto pr-5">
					<ul class="list-unstyled d-flex mb-0 align-items-center">

						<?php header_buttons('admin_dash'); ?>
					</ul>
				</div>
			</div>
			<div class="dash-part members-part">
				<div class="dash-part-title">
					<h1>
						<?php
						$dash_section_title = "";
						if (isset($arr[$page]["title"])) {
							$dash_section_title .= $arr[$page]["title"];

							if ($section) {
								@$dash_section_title .= " > " . $arr[$page]["childs"][$section]["title"];
							}
						}
						echo $dash_section_title;
						?>
					</h1>
				</div>
				<?php
				require_once($page_require);
				?>
			</div>
		</div>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="loadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center">
					<i class="fas fa-spinner fa-spin fa-4x"></i>
				</div>
			</div>
		</div>
	</div><!-- / Modal -->

	<?php include_once ROOT . '/parts/media-uploader.php'; ?>
	<script src="<?php echo siteurl(); ?>/admin/js/lib/chartjs/Chart.min.js"></script>
	<script src="<?php echo siteurl(); ?>/admin/js/flatpickr.min.js"></script>
	<script src="<?php echo siteurl(); ?>/admin/js/dash.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/lib/select2/js/select2.min.js"></script>
	<script src="<?php echo siteurl(); ?>/admin/js/custom.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/config.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/utils.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/audioController.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/progressController.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/playlistController.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/controlsController.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/uiController.js"></script>
	<script src="<?php echo siteurl(); ?>/assets/js/audio/main.js"></script>
	<!-- ANIMATE CSS -->
	<!-- HTML 5 shiv -->
	<!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js?v="></script>
        <![endif]-->
	<?php
		echo '<div id="audio-player-container">';
			if (isset($_SESSION['audio_files'])):
				require_once ROOT . "/player.php";
			endif;
		echo '</div>';
	?>
		<script>
				function updateListen(btnTarget) {
						let uid = btnTarget.data("uid"),
								pid = btnTarget.data("pid");
						$.ajax({
							url: `user-ajax.php`,
							type: "POST",
							data: {action: 'updatelisten', user_id: uid, post_id: pid},
							success: function(response) {
								if(parseInt(response) > 0) {
									btnTarget.find('span.count').text(response)
								}
							}
						});
					}
					$(document).ready(function() {
							// Open the player
							$('#book-audio-btn').click(function() {
								if($(this).hasClass("audio-readable")) {
									let btnTarget = $(this);
									$.ajax({
											url: '/player.php', // Endpoint to handle player state
											method: 'POST',
											data: { action: 'open', post_id: $(this).data('pid') },
											success: function(response) {
													// Update the player container with the new player HTML
													$('#audio-player-container').html(response);
													updateListen(btnTarget);
											}
									});
								} else {
										swal({
											text: 'لا يوجد ملف صوتي',
											icon: "error",
											buttons: {
												حسنا: true,
											},
										});
									}
							});

							// Close the player
							$(document).on('click', '#close-player', function() {
									$.ajax({
											url: '/player.php', // Endpoint to handle player state
											method: 'POST',
											data: { action: 'close' },
											success: function(response) {
													// Remove the player from the DOM
													if(!$(`.play-button > .pause-icon`).hasClass("hidden")) {
														$(`.play-button`).click();
													}
													$('#audio-player-container').empty();
											}
									});
							});
					});
			</script>
</body>

</html>