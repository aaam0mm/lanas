<?php
$query = new Query_post(
    ["post_type" => "quote",
    "post_category" => $category_id,
    "order" => $order_by_query
    ]);
$query->do_query_count = true;
$get_quotes = $query->get_posts();
?>
<div id="posts-feed">
<div class="quotes-area">
	<?php foreach($get_quotes as $quote): ?>
	<div class="quote-area border border-warning p-3 mb-3">
		<b class="quote-own p-2 float-left"><?php esc_html($quote["post_title"]); ?></b><span><?php echo $quote["post_content"]; ?></span>
		<div class="mt-3">
			<button type="button" class="btn btn-transparent" data-trigger="focus" title='<?php echo popover_post($quote["id"]); ?>' data-template='<div class="popover post-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>' data-html="true" data-toggle="popover" >
				<i class="fas fa-caret-down fa-lg"></i>
			</button>						
		</div>
	</div>
	<?php endforeach; ?>
</div>
</div>
