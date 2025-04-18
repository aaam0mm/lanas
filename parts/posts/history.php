<?php
$date_day = !empty($_GET["date_day"]) ? $_GET["date_day"] : date("d");
$date_month = !empty($_GET["date_month"]) ? $_GET["date_month"] : date("n");
$date_type = $_GET["date_type"] ?? "gregorian";
$match_calendar = $_GET["match_calendar"] ?? "gregorian";
$history_posts = get_history_posts($date_type, $date_month, $date_day, $match_calendar);
 $history_calendars = [
	"hijri" => _t("هجري"),
	"gregorian" => _t("ميلادي"),
	"kurdish" => _t("كردي")
]; 
?>
<div class="row m-0">
    <!-- History section -->
    <div class="col-12 border p-0">
				<!-- History section -->
					<div class="history-posts history-posts-page">
					    <div>
						    <select name="match_calendar" class="custom-select" id="history_calendar_select">
						        <option disabled=""><?php echo _t("ظهور أحداث موافق لليوم"); ?></option>
						        <?php foreach($history_calendars as $type_dates=>$date_title): ?>
			                        <option value="<?php esc_html($type_dates); ?>" <?php selected_val($type_dates,$match_calendar); ?> ><?php esc_html($date_title); ?></option>
		                        <?php endforeach; ?>
						    </select>
					    </div>
						<div class="history-posts-head bg-darker py-3">
							<div class="d-flex px-2">
							<span class="mr-auto text-warning navigate-history" data-history="yesterday"><?php echo _t("الأمس"); ?></span>
							<span class="mx-auto text-white navigate-history" data-history="today"><?php echo date("d-m-Y"); ?></span>
							<span class="ml-auto text-warning navigate-history" data-history="tomorrow"><?php echo _t("غدا"); ?></span>

							</div>
						</div>
						<div class="accordion history-posts-load">
						<a href="#" class="btn btn-block btn-warning text-left btn-lg rounded-0 toggle-history-btn" data-tab="#today-history"><?php echo _t('في مثل هذا اليوم'); ?></a>
						<div class="history-tab-shown" id="today-history">
						<?php if($history_posts): ?>
						<ul class="timeline mb-0">
							<?php foreach($history_posts["today"] as $history_post_today): ?>
							<li>
								<a href="<?php echo get_post_link($history_post_today); ?>" class="float-left"><?php esc_html($history_post_today["post_title"]); ?></a><br />
								<p><?php echo substr_str( strip_tags($history_post_today["post_content"]),120 ); ?></p>
							</li>
							<?php endforeach; ?>
						</ul>
						<?php endif; ?>
						</div>
						<a href="#" class="btn btn-block btn-warning text-left btn-lg rounded-0 toggle-history-btn" data-tab="#deaths-history"><?php echo _t('ولادة و وفيات اليوم'); ?></a>
						<div class="history-tab-hidden" id="deaths-history">
						<ul class="timeline mb-0">
							<?php foreach($history_posts["deaths"] as $history_post_deaths): ?>
							<li>
								<a href="<?php echo get_post_link($history_post_deaths); ?>" class="float-left"><?php esc_html($history_post_deaths["post_title"]); ?></a><br />
								<p><?php echo substr_str( strip_tags($history_post_deaths["post_content"]),120 ); ?></p>
							</li>
							<?php endforeach; ?>
						</ul>
						
						</div>
						<a href="#" class="btn btn-block btn-warning text-left btn-lg rounded-0 toggle-history-btn" data-tab="#occasion-history"><?php echo _t('مناسبات اليوم'); ?></a>
						<div class="history-tab-hidden" id="occasion-history">
						<ul class="timeline mb-0">
							<?php foreach($history_posts["occasions"] as $history_post_occasions): ?>
							<li>
								<a href="<?php echo get_post_link($history_post_occasions); ?>" class="float-left"><?php esc_html($history_post_occasions["post_title"]); ?></a><br />
								<p><?php echo substr_str( strip_tags($history_post_occasions["post_content"]),120 ); ?></p>
							</li>
							<?php endforeach; ?>
						</ul>
						
						</div>
						</div>
					</div>
				<!-- History section -->
    </div>
    <!-- History section -->
</div>