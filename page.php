<?php
require_once 'init.php';
$page_id = $_GET["id"] ?? "";
$page_title = $_GET["title"] ?? "";
if($page_id && $page_title) {
	$get_pages = get_pages($page_id);
}else{
    exit(header("HTTP/1.1 404 Not found"));
}
?>
<!DOCTYPE html>
<html dir="rtl">
	<head>
	<?php
	get_head();
	?>
	</head>
	<body>
		<?php
		get_header();
		?>
		<div class="my-5"></div>
		<div class="container">
			<nav aria-label="breadcrumb">
			  <ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?php echo siteurl(); ?>"><i class="fas fa-home"></i></a></li>
				<li class="breadcrumb-item active" aria-current="page"><?php esc_html($get_pages["page_title"]); ?></li>
			  </ol>
			</nav>
			<div class="bg-white shadow-sm p-3">
				<?php echo $get_pages["page_content"]; ?>
			</div>
		</div>
		<div class="my-5"></div>
		<?php user_end_scripts(); ?>
		<?php get_footer(); ?>
	</body>
</html>