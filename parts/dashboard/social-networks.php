<?php
/**
 * social-networks.php
 * User dashboard page
 */
 $user_social_accounts = get_user_meta(get_current_user_info()->id,"user_social_accounts") ? json_decode(get_user_meta(get_current_user_info()->id,"user_social_accounts")) : [];
 $telegram = $user_social_accounts->telegram ?? "";
 $fb = $user_social_accounts->fb ?? "";
 $tw = $user_social_accounts->tw ?? "";
 $gplus = $user_social_accounts->gplus ?? "";
 $yt = $user_social_accounts->yt ?? "";
 $insta = $user_social_accounts->insta ?? "";
?>
<div class="user-dashboard-social-networks position-relative">
	<div class="my-5"></div>
	<div class="px-5 py-2">
		<form action="" method="POST">
		
			<!-- User row -->
			<div class="form-row form-inline form-group w-100">
				<div class="col-lg-4">
					<label for="telegram_url" class="font-weight-bold">تليجرام</label>
				</div>
				<div class="col-lg-8">
					<input type="text" class="form-control w-100" name="s_c[telegram]" id="telegram_url" value="<?php esc_html($telegram); ?>"/>
				</div>
			</div><!-- User row -->
		
			<!-- User row -->
			<div class="form-row form-inline form-group w-100">
				<div class="col-lg-4">
					<label for="fb_url" class="font-weight-bold">الفيس بوك</label>
				</div>
				<div class="col-lg-8">
					<input type="text" class="form-control w-100" name="s_c[fb]" id="fb_url" value="<?php esc_html($fb); ?>"/>
				</div>
			</div><!-- User row -->
		
			<!-- User row -->
			<div class="form-row form-inline form-group w-100">
				<div class="col-lg-4">
					<label for="tw_url" class="font-weight-bold">تويتر</label>
				</div>
				<div class="col-lg-8">
					<input type="text" class="form-control w-100" name="s_c[tw]" id="tw_url" value="<?php esc_html($tw); ?>"/>
				</div>
			</div><!-- User row -->
		
			<!-- User row -->
			<div class="form-row form-inline form-group w-100">
				<div class="col-lg-4">
					<label for="gplus_url" class="font-weight-bold">جوجل</label>
				</div>
				<div class="col-lg-8">
					<input type="text" class="form-control w-100" name="s_c[gplus]" id="gplus_url" value="<?php esc_html($gplus); ?>"/>
				</div>
			</div><!-- User row -->
		
			<!-- User row -->
			<div class="form-row form-inline form-group w-100">
				<div class="col-lg-4">
					<label for="yt_url" class="font-weight-bold">يوتيوب</label>
				</div>
				<div class="col-lg-8">
					<input type="text" class="form-control w-100" name="s_c[yt]" id="yt_url" value="<?php esc_html($yt); ?>"/>
				</div>
			</div><!-- User row -->
		
			<!-- User row -->
			<div class="form-row form-inline form-group w-100">
				<div class="col-lg-4">
					<label for="insta_url" class="font-weight-bold">أنستجرام</label>
				</div>
				<div class="col-lg-8">
					<input type="text" class="form-control w-100" name="s_c[insta]" id="insta_url" value="<?php esc_html($insta); ?>"/>
				</div>
			</div><!-- User row -->
			<div class="form-row form-group w-100">
				<div class="col-lg-4"></div>
				<div class="col-lg-8">
					<button class="btn btn-lg btn-danger save-info"><?php echo _t("حفظ"); ?></button>
				</div>
			</div>	
			<input type="hidden" name="method" value="user_ajax"/>
			<input type="hidden" name="request" value="update_social_accounts"/>
		</form>			
	</div>
</div>