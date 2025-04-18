<?php
/**
 * notifications.php
 * User dashboard page
 */
 
 $notifs = @json_decode(get_user_meta(get_current_user_info()->id,"notifs_settings"));
?>
<div class="user-dashboard-notifs">
	<div class="my-5"></div>
	<form action="" method="POST">
	
		<!-- row -->
		<div class="form-row form-group">
			<!-- ON/OFF -->
			<div class="col-md-6">
				<label for="" class="d-block font-weight-bold"><?php echo _t("إشعارات التعليقات"); ?></label>		
				<input type="checkbox" class="js-switch" name="notifs[comment]" <?php checked_val($notifs->comment,"on"); ?>/>
			</div>
			<!-- ON/OFF -->
			
			<!-- ON/OFF -->
			<div class="col-md-6">
				<label for="" class="d-block font-weight-bold"><?php echo _t("إشعارات التقييم"); ?></label>		
				<input type="checkbox" class="js-switch" name="notifs[rate]" <?php checked_val($notifs->rate,"on"); ?>/>
			</div>
			<!-- ON/OFF -->
		</div><!-- row -->

		<!-- row -->
		<div class="form-row form-group">
			<!-- ON/OFF -->
			<div class="col-md-6">
				<label for="" class="d-block font-weight-bold"><?php echo _t("إشعارات الإشترك"); ?></label>		
				<input type="checkbox" class="js-switch" name="notifs[subscribe]" <?php checked_val($notifs->subscribe,"on"); ?>/>
			</div>
			<!-- ON/OFF -->
			<!-- ON/OFF -->
			<div class="col-md-6">
				<label for="" class="d-block font-weight-bold"><?php echo _t("إشعارات رد فعل"); ?></label>		
				<input type="checkbox" class="js-switch" name="notifs[reaction]" <?php checked_val($notifs->reaction,"on"); ?>/>
			</div>
			<!-- ON/OFF -->
		</div><!-- row -->
		<div class="my-5"></div>
		<div class="form-group">
			<button class="btn btn-danger btn-lg update-notif-settings"><?php echo _t("حفظ"); ?></button>
		</div>
		<input type="hidden" name="method" value="user_ajax"/>
		<input type="hidden" name="request" value="update_notifs_settings"/>
	</form>
</div>