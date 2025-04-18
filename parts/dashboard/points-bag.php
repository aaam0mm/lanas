<?php
/**
 * points-bag.php
 * User dashboard page
 */
 
 $current_user = get_current_user_info();
 $points_remaining = $current_user->points_remaining;
 $points_consumed = $current_user->points_consumed;
 $all_points = $points_remaining + $points_consumed;
 $points_bag = @json_decode(get_option("points-bag"));
?>
<div class="user-dashboard-points-bag position-relative">
	<div class="my-5"></div>
	<div class="row">
	
		<div class="col-lg-4 col-md-12 mb-3 mb-lg-0">
			<div class="text-center border py-3 font-weight-bold">
			<?php echo _t("نقاط الحالية"); ?> <span class="text-danger"><?php esc_html($points_remaining); ?></span>
			</div>
		</div>	
		
		<div class="col-lg-4 col-md-12 mb-3 mb-lg-0">
			<div class="text-center border py-3 font-weight-bold">
			<?php echo _t("نقاط المستهلكة"); ?> <span class="text-secondary"><?php esc_html($points_consumed); ?></span>
			</div>
		</div>	
		
		<div class="col-lg-4 col-md-12 mb-3 mb-lg-0">
			<div class="text-center border py-3 font-weight-bold">
			<?php echo _t("كل النقاط المحصل عليها"); ?> <span class="text-success"><?php esc_html( $all_points ); ?></span>
			</div>
		</div>
		
	</div>
	<div class="my-5"></div>
	<?php echo $points_bag->{current_lang()}; ?>
</div>