<?php
$post_id = $_GET["post_id"] ?? "";
$args = ["post_id" => $post_id, "post_lang" => false, 'post_in' => ''];
$query = new Query_post($args);
$get_post = $query->get_post();
$current_user = get_current_user_info();
if ($get_post) {
	$post = $get_post;
	if ($post["post_status"] != "publish" && $current_user->id != $post["post_author"] && admin_authority()->posts != "on") {
		exit(0);
	}
}
/** Get post rates */
if (is_login_in()) {
	$get_rates = get_rates($post_id, $current_user->id);
} else {
	$get_rates = get_rates($post_id);
}
$get_user_rate = $get_rates["user_rate"] ?? 0;

$post_type = $post["post_type"];
insert_analytics("post_views", $post_id);
$embera = new \Embera\Embera();

?>
<!-- Modal -->
<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<!-- share buttons -->
			<div class="btn-actions d-inline-block mb-3">
				<div class="d-flex">
					<div class="px-1">
						<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=facebook" class="btn btn-facebook-login border share-btn rounded-circle text-white" title="<?php echo _t("شارك على فايسبوك"); ?>"><i class="fab fa-facebook-f"></i></a>
					</div>
					<div class="px-1">
						<a href="#" data-toggle="tooltip" data-url="<?php echo siteurl() . "/share.php?url=" . get_post_link($post); ?>&post_id=<?php esc_html($post_id); ?>&plat=twitter" class="btn btn-twitter-login border share-btn rounded-circle text-white" title="<?php echo _t("شارك على تويتر"); ?>"><i class="fab fa-twitter"></i></a>
					</div>
				</div>
			</div>
			<!-- / share buttons -->
			<div class="post-quick-details">
				<div class="d-flex">
					<div class="ml-auto">
						<ul class="list-unstyled p-2 mb-0">
							<li class="mr-3"><i class="fas fa-clock"></i>&nbsp;<?php echo get_timeago(strtotime($post["post_date_gmt"])); ?></li>
							<li class="mr-3"><i class="fas fa-share"></i>&nbsp;<?php esc_html($post["post_share"]); ?></li>
							<li class="mr-3"><i class="fas fa-comments"></i>&nbsp;<?php esc_html($post["comments_count"]); ?></li>
							<li><i class="fas fa-eye"></i>&nbsp;<?php esc_html($post["post_views"]); ?></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="my-2"></div>
			<?php
			switch ($post_type):
				case "research":
					include '../parts/single-post/research.php';
					break;
				case "book":
					include '../parts/single-post/book.php';
					break;
				default:
					if (in_array($post["post_type"], ["video", "audio"])):
			?>
						<div class="post-embed">
							<!-- 16:9 aspect ratio-->
							<div class="embed-responsive embed-responsive-16by9">
								<?php echo $embera->autoEmbed(get_post_meta($post_id, "video_url")); ?>
							</div>
						</div>
					<?php elseif (!empty($post["post_thumbnail"])): ?>
						<div class="post-thumb">
							<img src="<?php esc_html(get_thumb($post["post_thumbnail"], null)); ?>" class="img-fluid w-100" />
						</div>
						<div class="my-2"></div>
					<?php endif; ?>
			<?php endswitch; ?>
			<div class="post-content">
				<h1 class="h3 py-2"><?php esc_html($post["post_title"]); ?></h1>
				<div class="border-top border-warning"></div>
				<div class="my-2"></div>
				<div class="single-post post-details quick-post-details" style="display:block;">
					<?php echo validate_html($post["post_content"]); ?>
				</div>
			</div>

			<div class="my-4"></div>

			<!-- slices -->
			<div class="post-slices">
				<?php
				$get_post_slices = $query->get_post_slices();
				if ($get_post_slices):
					render_slices_single_post($get_post_slices);
				endif;
				$get_post_reaction = get_post_reaction($post_id);
				?>
			</div><!-- slices -->

			<div class="d-flex border-bottom border-warning pb-2">
				<!-- post reaction -->
				<div class="reaction-area position-relative w-100">
					<button class="btn btn-transparent rounded-circle reaction-btn reaction-user animated" data-reacted="<?php esc_html($get_post_reaction["react"]); ?>" data-post="<?php esc_html($post_id); ?>"></button>
					<?php if (is_array($get_post_reaction["reactions"])): ?>
						<ul class="list-unstyled d-flex flex-row mb-0 mt-2">
							<?php
							foreach ($get_post_reaction["reactions"] as $reaction => $index):
							?>
								<li class="reaction-user reactions-count" data-toggle="tooltip" title="<?php esc_html($reaction); ?>(<?php esc_html($index); ?>)" data-reacted="<?php esc_html($reaction); ?>"></li>
							<?php
							endforeach;
							?>
						</ul>
					<?php endif; ?>
					<div class="block-react-eem position-absolute shadow-sm p-1 bg-white animated">
						<div class="emoji  emoji--like react" data-reaction="like">
							<div class="emoji__hand">
								<div class="emoji__thumb"></div>
							</div>
						</div>
						<div class="emoji  emoji--love react" data-reaction="love">
							<div class="emoji__heart"></div>
						</div>
						<div class="emoji  emoji--haha react" data-reaction="haha">
							<div class="emoji__face">
								<div class="emoji__eyes"></div>
								<div class="emoji__mouth">
									<div class="emoji__tongue"></div>
								</div>
							</div>
						</div>
						<div class="emoji  emoji--wow react" data-reaction="wow">
							<div class="emoji__face">
								<div class="emoji__eyebrows"></div>
								<div class="emoji__eyes"></div>
								<div class="emoji__mouth"></div>
							</div>
						</div>
						<div class="emoji  emoji--sad react" data-reaction="sad">
							<div class="emoji__face">
								<div class="emoji__eyebrows"></div>
								<div class="emoji__eyes"></div>
								<div class="emoji__mouth"></div>
							</div>
						</div>
						<div class="emoji  emoji--angry react" data-reaction="angry">
							<div class="emoji__face">
								<div class="emoji__eyebrows"></div>
								<div class="emoji__eyes"></div>
								<div class="emoji__mouth"></div>
							</div>
						</div>
					</div>
				</div>
				<!-- / post reaction -->
				<div class="ml-auto mt-auto">
					<div class="btn-group dropright">
						<button type="button" class="btn btn-warning py-2 rounded" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-bars"></i></button>
						<div class="dropdown-menu dropdown-menu--post mr-2 rounded">
							<?php echo popover_post($post_id); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="mt-3"></div>
			<!-- Rating posts -->
			<div class="box-rating">
				<div class="rating">
					<input type="radio" id="star5" name="rating" value="5" <?php checked_val($get_user_rate, 5); ?> />
					<label class="rate-content full" for="star5" title="Awesome - 5 stars" data-value="5" data-post="<?php esc_html($post_id); ?>"></label>

					<input type="radio" id="star4" name="rating" value="4" <?php checked_val($get_user_rate, 4); ?> />
					<label class="rate-content full" for="star4" title="Pretty good - 4 stars" data-value="4" data-post="<?php esc_html($post_id); ?>"></label>

					<input type="radio" id="star3" name="rating" value="3" <?php checked_val($get_user_rate, 3); ?> />
					<label class="rate-content full" for="star3" title="Meh - 3 stars" data-value="3" data-post="<?php esc_html($post_id); ?>"></label>

					<input type="radio" id="star2" name="rating" value="2" <?php checked_val($get_user_rate, 2); ?> />
					<label class="rate-content full" for="star2" title="Kinda bad - 2 stars" data-value="2" data-post="<?php esc_html($post_id); ?>"></label>

					<input type="radio" id="star1" name="rating" value="1" <?php checked_val($get_user_rate, 1); ?> />
					<label class="rate-content full" for="star1" title="Sucks big time - 1 star" data-value="1" data-post="<?php esc_html($post_id); ?>"></label>
				</div>
				<a class="d-block text-center collapse-rating collapsed" data-toggle="collapse" href="#collapseRating" role="button" aria-expanded="false" aria-controls="collapseRating"></a>
				<div class="rating-progress px-4 collapse" id="collapseRating">
					<?php

					if (!$get_rates["stars"]) {
						$get_rates["stars"] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
					}
					foreach ($get_rates["stars"] as $rate_k => $rate_v):
						if (is_array($rate_v)) {
							$star_percent = $rate_v["percent"];
						} else {
							$star_percent = 0;
						}
					?>
						<div class="d-flex align-items-center">
							<div class="rating-star-count mr-3"><i class="fas fa-star text-warning mr-2"></i><span><?php esc_html($rate_k); ?></span></div>
							<div class="progress flex-grow-1">
								<div class="progress-rate-percent-<?php esc_html($rate_k); ?> progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="<?php echo $star_percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $star_percent; ?>%"></div>
							</div>
						</div>
					<?php
					endforeach;
					?>
				</div>
			</div><!-- Rating posts -->
			<div class="mt-3"></div>
			<!-- comments -->
			<?php
			if (get_post_meta($post_id, "disable_comments") == "off"):
				if (display_fbComments()):
			?>
					<!-- Facebook comments -->
					<div class="fb-comments" data-href="<?php echo get_post_link($post_id); ?>" data-numposts="5" data-width="100%"></div>
					<!-- / Facebook comments -->
				<?php endif; ?>
				<div class="my-4"></div>
			<?php
				echo get_comments_form($post_id, false);
				echo '<div class="my-3"></div>';
				get_post_comments($post_id);
			endif;
			?>
			<!-- / comments -->

		</div>
	</div>
</div><!-- / Modal -->
<?php
if (display_fbComments()):
?>
	<div id="fb-root"></div>
	<script>
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s);
			js.id = id;
			js.src = 'https://connect.facebook.net/<?php echo current_lang(); ?>/sdk.js#xfbml=1&autoLogAppEvents=1&version=v3.1&appId=811664755689133';
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	</script>
<?php
endif;
?>
<link href="../assets/css/emoji.css" rel="stylesheet" type="text/css" />
<script>
	$(function() {
		$('[data-toggle="popover"]').popover();
		$("#emojoComment1").emojioneArea({
			filters: {
				recent: false // disable recent
			},
			attributes: {
				dir: "rtl"
			},
		});
	});
</script>