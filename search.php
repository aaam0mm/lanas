<?php
/**
 * search.php
 */
 require_once 'init.php';
 $q = $_GET["s"] ?? "";
 $post_type = !empty($_GET["post_type"]) ? $_GET["post_type"] : false;
 $current_user = get_current_user_info();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<title></title>
	</head>
	<body>
	<?php get_header(); ?>
	<?php if(!empty($q)): ?>
	<div class="container py-5">
	    <h1 class="mb-5"><?php echo sprintf(_t("نتائج البحث عن %s"),$q); ?></h1>
	    <div class="search-results">
	<?php
    $search_query = new Query_post([
        "post_title" => $q,
        "post_type" => $post_type
    ]);
    $search_query->do_query_count = true;
    $get_results = $search_query->get_posts();
            
	if($get_results) {
		foreach($get_results as $s) {
		?>
		<!-- instant result -->
		<div class="media mb-2 border-bottom pb-2">
			<div class="media-left mr-3">
				<a href="<?php echo get_post_link($s); ?>"><img src="<?php echo get_thumb($s["post_thumbnail"]); ?>" class="media-object" style="width:60px"></a>
			</div>					
			<div class="media-body overflow-hidden">
				<a href="<?php echo get_post_link($s); ?>" class="media-heading"><?php esc_html($s["post_title"]); ?><span class="badge badge-primary badge-pill ml-2"><?php esc_html( get_taxonomy_title($s["post_type"]) ); ?></span></a>
				<?php if($s["post_type"] != "research"): ?>
				<p class="text-muted small text-ellipsis"><?php echo strip_tags($s["post_content"]); ?></p>
				<?php endif; ?>
			</div>
		</div><!-- instant result -->
		<?php
		}		
	}else{
		no_content();
	}
	?>
	</div>
	<?php echo load_more_btn($search_query->count_results(),".search-results",["request" => "instant-search","s" => $q,"post_type" => $post_type]); ?>
	</div>
    <div class="my-5"></div>
	<?php else: no_content(); endif; ?>
    <?php user_end_scripts(); ?>
	<?php get_footer(); ?>
    </body>
</html>