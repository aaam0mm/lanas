<?php
	$letter = $_GET["letter"] ?? "";
	$gender = $_GET["gender"] ?? "";
	
	$filter = ["letter" => $letter,"gender" => $gender];
	
	$args = [
	    "post_type" => $post_type,
	    "post_category" => $category_id,
	    "order" => "posts.post_title ASC"
	];
	
	if(!empty($letter)) {
	    $args["post_title_like"] = $letter.'%';
	}
	
	if($post_type == 'name' ) {
	   if(!empty($gender)) {
	    $args["post_meta"][] = ["name_gender","=",$gender];
	   }
	}

	$query = new Query_post($args);
    $query->do_query_count = true;
    $get_posts = $query->get_posts();
	?>
    <div id="posts-feed">
    <div class="names-tabs">
        <?php
		if($get_posts):
		foreach($get_posts as $post): 
		?>
        <!-- name -->
        <div class="accordion name-accordion" id="accordionName-<?php esc_html($post["id"]); ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <button class="btn btn-link font-weight-bold text-dark p-0" style="white-space: inherit; text-align: right;" type="button" data-toggle="collapse" data-target="#collapse-name-<?php esc_html($post["id"]); ?>" aria-expanded="false" aria-controls="collapse-name-<?php esc_html($post["id"]); ?>">
							<?php esc_html($post["post_title"]); ?>
						</button>
                    </h5>
                </div>

                <div id="collapse-name-<?php esc_html($post["id"]); ?>" class="collapse collapse-name accor-post" data-parent="#accordionName-<?php esc_html($post["id"]); ?>">
                    <div class="card-body p-0 m-2">
                        <p><?php echo validate_html($post["post_content"]); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- name -->
        <?php 
		endforeach;
		else:
		no_content();
		endif;
		?>
    </div>
    </div>
    <!-- Names tabs -->