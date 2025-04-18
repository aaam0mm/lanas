<?php
require_once 'init.php';
$author_id = $_GET["author_id"] ?? "";

$post_type = 'book';
if (empty($post_type)) {
	$post_type = false;
}
if (empty($author_id)) {
	exit(http_response_code(404));
}

$get_author_info = get_author_info($author_id);
$user_id = $get_author_info->user_id ?? 0;
$get_user_info = get_user_info($user_id) ?? null;

$post_meta['book_author'] = $get_author_info->name;
$post_meta['book_author_id'] = $author_id;

$query_my_posts = new Query_post([
	"post_in" => '',
	"post_author" => $author_id,
	"post_type__not" => ['name', 'wisdom', 'dictionary', 'quote', 'history'],
	"post_type" => $post_type,
	"post_in" => false,
	"post_lang" => false,
	"post_meta" => $post_meta,
	"order" => ['posts.id', 'desc']
]);

$query_my_posts->do_query_count = true;

$get_my_posts = $query_my_posts->get_posts();
$get_my_posts_rows = $query_my_posts->count_results();

$get_fav_posts = [];
$get_fav_posts_rows = 0;

$query_news_feed = new Query_post([
	"post_author" => $author_id,
	"post_lang" => false,
	"order" => ['posts.id', 'desc'],
	"post_meta" => $post_meta,
	"post_in" => false
]);
$query_news_feed->do_query_count = true;
$get_news_feed = $query_news_feed->get_posts();
$get_news_feed_rows = $query_news_feed->count_results();

?>
<!DOCTYPE html>
<html>

<head>
	<?php get_head(); ?>
	<link href="<?php echo siteurl(); ?>/assets/css/emoji.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/css/bookblock.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/css/custom.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/css/jquery.jscrollpane.custom.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo siteurl(); ?>/assets/lib/FullscreenBookBlock/js/modernizr.custom.79639.js"></script>
	<title><?php esc_html($get_author_info->name); ?></title>
	<meta name="description" content="<?php echo strip_tags(substr($meta_desc_html, $meta_desc_start, $meta_desc_end - $meta_desc_start + 4)); ?>" />
	<!-- <meta name="keywords" content="<?php esc_html($post["post_keywords"]); ?>" />
	<meta property="og:image" content="<?php esc_html(get_thumb($post["post_thumbnail"], null, false, $post_id)); ?>">
	<meta property="og:url" content="<?php echo get_post_link($post_id); ?>"> -->
	<meta name="author" content="" />
</head>

<body style="background-color: #f4f4f4 !important;">
	<?php user_end_scripts(); ?>
	<?php
	$header_name = null;
	get_header($header_name);
	$posts = $dsql->dsql()->table('post_meta')->where("meta_key", "book_author_id")->where('meta_value', $author_id)->get();
	?>
	<nav aria-label="breadcrumb" class="breadcrumb-post-page">
		<ol class="breadcrumb container rounded-0">
			<li class="breadcrumb-item"><a href="#" class="color-link"><i class="fas fa-home"></i>&nbsp;<?php echo _t("الرئيسية"); ?></a></li>
			<li class="breadcrumb-item active" aria-current="page" class="color-link"><?php esc_html('المؤلف'); ?></li>
		</ol>
	</nav>
	<div class="my-2"></div>
	<div class="container">
		<div class="d-sm-flex justify-content-center flex-wrap">
			<div class="single-right-col bg-white" style="border: 2px solid #f0ae027a;border-top: 5px solid #f0ae02;border-radius: 14px;overflow: hidden">
				<div class="row justify-content-center align-items-center">
					<div class="col-lg-12">
						<div class="profil-image mt-4 mx-auto">
							<?php
								if(isset($get_user_info->user_picture)) {
									echo '<img src="'. get_thumb($get_user_info->user_picture) . '" class="rounded-circle w-100 h-100" />';
								} else {
									echo '<img src="'. siteurl() . "/assets/images/icons/book/author.svg" .'" class="rounded-circle w-100 h-100" />';
								}
							?>
						</div>
					</div>
					<div class="my-2 col-lg-12"></div>
					<div class="col-lg-12 text-center">
						<h3>
							<?php
							if($get_author_info->user_id > 0) {
								echo '<a style="color: #FF595B;" href="'. siteurl() . '/user/' . $get_author_info->user_id .'">'. $get_author_info->name .'</a>';
							} else {
								echo '<span>'.  $get_author_info->name .'</span>';
							}
							?>
							<?php
							if($get_author_info->author_stat == 1) {
								echo '<img data-toggle="tooltip" title="المؤلف مثبت على لاناس" src="'. siteurl() .'/assets/images/icons/book/verify.svg"';
							}
							?>
						</h3>
					</div>
					<div class="col-lg-12 text-center">
						<p style="font-weight: bold;" class="text-dark">
							<?php
								$author_numPosts = count_author_posts($get_author_info->name);
							?>
							لديه[ا] (<?= $author_numPosts ;?>) كتاب في لاناس
						</p>
					</div>
					<!-- <div class="my-4 col-lg-12"></div> -->
					<!-- <div class="col-lg-12 text-center">
						<p style="font-weight: bold;" class="text-dark">
							استخدامات لكتبه على لاناس
						</p>
					</div> -->
					<?php
						if(count($posts) > 0) {
							$posts_ids = [];
							foreach($posts as $post) {
								$post_id = $post['post_id'];
								$posts_ids[] = $post_id;
							}
							$posts_ids_str = implode(',', $posts_ids);
							$downloads = $dsql->dsql()
                ->expr("SELECT SUM(meta_value) AS downloads FROM post_meta WHERE meta_key = 'book_downloads' AND post_id IN($posts_ids_str);")
                ->getOne();

							$previews = $dsql->dsql()
								->expr("SELECT SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta_value, '$.preview')) AS UNSIGNED)) AS previews FROM post_meta WHERE meta_key = 'book_preview' AND post_id IN($posts_ids_str);")
								->getOne();

							$listens = $dsql->dsql()
								->expr("SELECT SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta_value, '$.listen')) AS UNSIGNED)) AS listens FROM post_meta WHERE meta_key = 'book_listen' AND post_id IN($posts_ids_str);")
								->getOne();

							$shares = $dsql->dsql()
								->expr("SELECT SUM(post_share) AS shares FROM posts WHERE id IN($posts_ids_str);")
								->getOne();

							$comments = $dsql->dsql()
								->expr("SELECT COUNT(*) AS comments FROM comments WHERE post_id IN($posts_ids_str);")
								->getOne();
						}
					?>
					<div class="border-top border-warning col-lg-11"></div>
					<div class="col-lg-12 text-center row flex-wrap justify-content-center align-items-center">
						<div class="col-3 m-3 text-center">
							<button class="btn btn-warning text-white">
								<i class="fas fa-book-open"></i>
							</button>
							<small class="d-block">قراءة (<?= $previews ?? 0 ;?>)</small>
						</div>
						<div class="col-3 m-3 text-center">
							<button class="btn btn-warning text-white">
								<i class="fas fa-headset"></i>
							</button>
							<small class="d-block">استماع (<?= $listens ?? 0 ;?>)</small>
						</div>
						<div class="col-3 m-3 text-center">
							<button class="btn btn-warning text-white">
								<i class="fas fa-upload"></i>
							</button>
							<small class="d-block">تحميل (<?= $downloads ?? 0 ;?>)</small>
						</div>
						<div class="col-3 m-3 text-center">
							<button class="btn btn-warning text-white">
								<i class="fas fa-share-alt"></i>
							</button>
							<small class="d-block">مشاركة (<?= $shares ?? 0 ;?>)</small>
						</div>
						<div class="col-3 m-3 text-center">
						<?php
                // Fetch the average rating and count of ratings for the specified post_id
                $result = $dsql->dsql()
								->table('rating_sys')
								->where($dsql->expr("post_id IN($posts_ids_str)")) // Filter by multiple post IDs
								->field('AVG(rate_stars) AS avg_rating') // Calculate the average of all ratings
								->field('SUM(rate_stars) AS total_rating_sum') // Sum up all rating values
								->field('COUNT(rate_stars) AS total_ratings') // Count all ratings
								->getRow();
                $avg = $result['avg_rating'] ?? 0;
                $average_rating = round($avg, 1);
                $total_ratings = (int) $result['total_ratings']; // Ensure it's an integer
            ?>
							<button class="btn btn-warning text-white">
								<i class="fas fa-comments"></i>
							</button>
							<small class="d-block">تعليقات (<?= $comments ?? 0 ;?>)</small>
						</div>
						<div class="col-3 m-3 text-center">
							<button class="btn btn-warning text-white">
								<i class="fas fa-star-half-alt"></i>
							</button>
							<small class="d-block">تقييمات (<?= $average_rating ;?>)</small>
						</div>
					</div>
					<div class="col-lg-12 text-center mb-1">
						
							<?php
								if($get_author_info->user_id > 0) {
									echo '<a style="color: #FF595B;" href="'. siteurl() . '/user/' . $get_author_info->user_id .'">';
										echo 'لديه[ا] حساب في لاناس';
										echo '<i class="fas fa-thumbs-up ml-2"></i>';
									echo '</a>';
								} else {
									echo '<p style="font-weight: bold;" class="text-dark">';
										echo 'ليس لديه[ا] حساب في لاناس';
										echo '<i class="fas fa-thumbs-down ml-2"></i>';
									echo '</p>';
								}
							?>
					</div>
				</div>
			</div>
			<div class="my-1 w-100"></div>
			<div class="single-right-col" style="background-color: #8c8f98; height: 100px">
				
			</div>
			<div class="my-1 w-100"></div>
			<div class="single-right-col bg-white rounded">
				<div class="row justify-content-center p-2 w-100 m-auto">
					<?php
					if(count($posts) > 0) {
						foreach($posts as $post_meta) {
							$post_id = $post_meta['post_id'];
							$post_thumb = get_post_field($post_id, 'post_thumbnail');
							$post_title = get_post_field($post_id, 'post_title');
							$image = $post_thumb ? get_thumb($post_thumb, ["w" => 320, "h" => 450]) : siteurl() . "/assets/images/no-image.svg";
							?>
							<div class="col-lg-4 p-1">
								<a class="position-absolute top-0 left-O w-100 h-100" href="<?php echo get_post_link($post_id); ?>"></a>
								<img src="<?php echo $image; ?>" class="img-fluid w-100" alt="" />
								<p class="text-center my-2 bg-warning text-dark rounded p-1"><?= $post_title ;?></p>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>
			<div class="my-5 w-100"></div>
		</div>
	</div>
</body>

</html>