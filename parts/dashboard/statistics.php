<?php
/**
 * statistics.php
 * User dashboard page
 */
 $current_user = get_current_user_info();
 $user_trusted_posts = count_user_posts($current_user->id,"trusted");
 $user_untrusted_posts = count_user_posts($current_user->id,"untrusted");
 $user_all_posts = $user_trusted_posts + $user_untrusted_posts;
 $views_analytics = get_analytics("post_views","posts_views",$current_user->id,"today");
 $shares_analytics = get_analytics("post_share","posts_shares",$current_user->id,"today");
 $countries_analytics = get_analytics("post_views","views_by_country",$current_user->id,"today");

 $query_posts = new Query_post([
	"post_author" => $current_user->id,
	"post_lang" => false,
	"order" => ['posts.post_views','DESC']
 ]);
 
 $get_posts = $query_posts->get_posts();
 
 ?>
	 <div class="card-deck user-dashboard-statistics-1">
		<!-- Card Info -->
		<div class="card text-white bg-primary rounded-0">
		  <div class="card-body">
			<div class="d-flex">
				<div class="card-icon bg-white rounded-circle text-center border">
					<i class="fas fa-gift fa-3x text-dark"></i>
				</div>
				<ul class="card-details list-unstyled m-0 ml-2">
					<li><?php echo _t("نقاط الحالية"); ?> <span class="text-dark"><?php esc_html($current_user->points_remaining); ?></span></li>
					<li><?php echo _t("نقاط المستهلكة"); ?>  <span class="text-dark"><?php esc_html($current_user->points_consumed); ?></span></li>
					<li><?php echo _t("كل نقاطك"); ?> <span class="text-dark"><?php esc_html($current_user->points_remaining + $current_user->points_consumed); ?></span></li>
				</ul>
			</div>
		  </div>
		</div><!-- Card Info -->
		<!-- Card Info -->
		<div class="card text-white bg-aqua rounded-0">
		  <div class="card-body">
			<div class="d-flex">
				<div class="card-icon bg-white rounded-circle text-center border">
					<i class="fas fa-gift fa-3x text-dark"></i>
				</div>
				<ul class="card-details list-unstyled m-0 ml-2">
					<li><?php echo _t("مواضيع"); ?> <i class="fas fa-check-circle text-success"></i> <span class="text-dark"><?php esc_html($user_trusted_posts); ?></span></li>
					<li><?php echo _t("مواضيع"); ?> <i class="fas fa-check-circle text-secondary"></i> <span class="text-dark"><?php esc_html($user_untrusted_posts); ?></span></li>
					<li><?php echo _t("كل مواضيع"); ?> <span class="text-dark"><?php esc_html($user_all_posts); ?></span></li>
				</ul>
			</div>
		  </div>
		</div><!-- Card Info -->
		<!-- Card Info -->
		<div class="card text-white bg-pink rounded-0">
		  <div class="card-body">
			<div class="d-flex">
				<div class="card-icon bg-white rounded-circle text-center border">
					<i class="fas fa-gift fa-3x text-dark"></i>
				</div>
				<ul class="card-details list-unstyled m-0 ml-2">
					<li><span class="h3"><?php esc_html( get_role_name($current_user->user_role) ); ?></span></li>
					<li><span class="text-dark small"><?php echo _t("رتبتك في لاناس"); ?></span></li>
				</ul>
			</div>
		  </div>
		</div><!-- Card Info -->
	 </div>
	 <div class="my-5"></div>
	 <div class="statistics-box border border-top-0">
		 <!-- head -->
		 <div class="d-flex statistics-box-head bg-light px-2 py-3 border-bottom align-items-center">
			<div class="title h4 m-0"><i class="fas fa-chart-area"></i>&nbsp;<?php echo _t("احصائيات"); ?></div>
			<div class="filter-input ml-auto">
				<select class="custom-select statistics-analytics-select">
					<option value="today"><?php echo _t("اليوم"); ?></option>
					<option value="month"><?php echo _t("هذا الشهر"); ?></option>
					<option value="year"><?php echo _t("هذه السنة"); ?></option>
				</select>
			</div>
		 </div><!-- / head -->
		 <div class="card-deck p-2">
			<!-- Card Info -->
			<div class="card rounded-0">
			  <div class="card-body p-2">
				<div class="d-flex">
					<div class="card-icon">
						<img src="<?php echo siteurl(); ?>/assets/images/icons/hex2.png" alt=""/>
					</div>
					<ul class="card-details list-unstyled m-0 ml-3">
						<li class="h3 all-views-analytics-num"><?php esc_html($views_analytics["all_views"] ?? 0); ?></li>
						<li class="font-weight-bold"><?php echo _t("زيارات لموقعك"); ?></li>
					</ul>
				</div>
			  </div>
			</div><!-- Card Info -->
			<!-- Card Info -->
			<div class="card rounded-0">
			  <div class="card-body p-2">
				<div class="d-flex">
					<div class="card-icon">
						<img src="<?php echo siteurl(); ?>/assets/images/icons/hex3.png" alt=""/>
					</div>
					<ul class="card-details list-unstyled m-0 ml-3">
						<li class="mb-2">
							<i class="fas fa-check-circle fa-lg text-success" data-toggle="tooltip" title="<?php echo _t("مواضيع موثقة"); ?>"></i> 
							<span class="h3 trusted-posts-views-num"><?php esc_html($views_analytics["trusted_views"] ?? 0); ?></span> 
							<i class="fas fa-check-circle fa-lg"  data-toggle="tooltip" title="<?php echo _t("مواضيع حرة"); ?>"></i> 
							<span class="h3 untrusted-posts-views-num"><?php esc_html($views_analytics["untrusted_views"] ?? 0); ?></span> 
						</li>
						<li class="font-weight-bold"><?php echo _t("مشاهدة المواضيع"); ?></li>
					</ul>
				</div>
			  </div>
			</div><!-- Card Info -->
			<!-- Card Info -->
			<div class="card rounded-0">
			  <div class="card-body p-2">
				<div class="d-flex">
					<div class="card-icon">
						<img src="<?php echo siteurl(); ?>/assets/images/icons/hex4.png" alt=""/>
					</div>
					<ul class="card-details list-unstyled m-0 ml-3">
						<li class="h3 share-posts-num"><?php esc_html($shares_analytics["shares"] ?? 0); ?></li>
						<li class="font-weight-bold"><?php echo _t("قامو بنشر مواضيعك"); ?></li>
					</ul>
				</div>
			  </div>
			</div><!-- Card Info -->
		 </div>		
	 </div>
	 <div class="my-5"></div>
	 <div class="statistics-box border border-top-0">
		 <!-- head -->
		 <div class="d-flex statistics-box-head bg-light px-2 py-3 border-bottom align-items-center">
			<div class="title h4 m-0"><i class="fas fa-chart-area"></i>&nbsp;<?php echo _t("زوار حسب الدولة"); ?> </div>
			<div class="filter-input ml-auto">
				<select class="custom-select countries-analytics-select">
					<option value="today"><?php echo _t("اليوم"); ?></option>
					<option value="month"><?php echo _t("هذا الشهر"); ?></option>
					<option value="year"><?php echo _t("هذه السنة"); ?></option>
				</select>
			</div>
		 </div><!-- / head -->
		 <div class="statistics-by-country p-2">
			<table class="table table-responsive-sm" style="overflow-x:auto;">
				<thead>
					<th><?php echo _t("الدولة"); ?></th>
					<th><?php echo _t("عدد الزوار"); ?></th>
					<th><?php echo _t("مواضيع موتوقة"); ?></th>
					<th><?php echo _t("مواضيع غير موتوقة"); ?></th>
				</thead>
				<tbody class="font-weight-bold">
				    <?php 
				    foreach($countries_analytics as $country_code=>$visits_country):
				        $country_flag = false;
				        $country_info = get_countries($country_code);
				        if($country_info) {
				            $country_flag = get_thumb($country_info["country_flag"]);
				        }
				        $country_name = $country_info["country_name"] ?? "n/a";
				    ?>
					<tr>
						<td>
						    <?php 
						        if($country_flag) {
						            echo '<img src="'.$country_flag.'" class="img-fluid mr-2" width="18" height="18"/>';
						        }
						        esc_html( json_decode($country_name)->{current_lang()} ?? "n/a"); 
						    ?>
						</td>
						<td><?php esc_html($visits_country["all_views"]); ?></td>
						<td><?php esc_html($visits_country["trusted_views"] ?? 0); ?></td>
						<td><?php esc_html($visits_country["untrusted_views"] ?? 0); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		 </div>
	</div>
	<div class="my-5"></div>
	<div class="statistics-box border border-top-0">
		<!-- head -->
		<div class="d-flex statistics-box-head bg-light px-2 py-3 border-bottom align-items-center">
			<div class="title h4 m-0"><i class="fas fa-chart-area"></i>&nbsp;<?php echo _t("إحصائيات المواضيع"); ?></div>
			<div class="filter-input ml-auto">
				<select class="custom-select posts-analytics-select">
					<option value="most_viewed"><?php echo _t("أكثر مشاهدة"); ?></option>
					<option value="most_rated"><?php echo _t("أكثر تقييما"); ?></option>
					<option value="most_reacted"><?php echo _t("أكثر إعجابا"); ?></option>
					<option value="most_shared"><?php echo _t("أكثر مشاركة"); ?></option>
				</select>
			</div>
		</div><!-- / head -->
		<div class="statistics-by-posts p-2">
			<div class="row">
            <?php
            if($get_posts) {
                foreach($get_posts as $post) {
                    ?>
    				<div class="col-md-6 col-sm-12 mb-3">
    					<div class="row">
    					    <?php if(!empty($post["post_thumbnail"])): ?>
    						<div class="col-md-3 col-sm-12 mb-3 mb-md-0">
    							<a href="<?php echo get_post_link($post); ?>"><img src="<?php echo get_thumb($post["post_thumbnail"]); ?>" class="img-fluid" alt=""/></a>
    						</div>
    						<?php endif; ?>
    						<div class="col-md-9 col-sm-12">
    							<a href="<?php echo get_post_link($post); ?>" class="h6 color-link"><?php esc_html($post["post_title"]); ?>&nbsp;<?php echo get_post_in_html($post["post_in"]); ?></a> <br />
    							<span class="text-danger mt-3 d-block"><?php esc_html($post['post_views'].' '._t('مشاهدة')); ?></span>
    						</div>
    					</div>
    				</div>
                    <?php
                }
            }
            ?>
			</div>
		</div>
	</div>