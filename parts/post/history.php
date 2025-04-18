<?php
$events = [
	"deaths" => _t("الوفيات"),
	"today" => _t("أحداث اليوم"),
	"occasions" => _t("مناسبات")
];

$history_calendars = [
	"hijri" => _t("هجري"),
	"gregorian" => _t("ميلادي"),
	"kurdish" => _t("كردي")
];

$history_date = @explode("-",get_post_meta($post_id,"history_date"));
$history_year = $history_date[0];
$history_month = $history_date[1];
$history_day = $history_date[2];
$history_calendar = get_post_meta($post_id,"history_calendar");
$history_event = get_post_meta($post_id,"history_event");
?>
<div class="form-group">
    <label for="history_event" class="font-weight-bold"><?php echo _t("تصنيف"); ?><sup class="text-danger">*</sup></label>
    <select name="post_meta[history_event]" id="history_event" class="form-control custom-select rounded-0">
        <option value=""><?php echo _t("إختر تصنيف"); ?></option>
		<?php foreach($events as $event_type=>$event): ?>
			<option value="<?php esc_html($event_type); ?>" <?php selected_val($event_type,$history_event); ?> ><?php esc_html($event); ?></option>
		<?php endforeach; ?>
	</select>
    <div id="history_event_error_txt" class="invalid-feedback"></div>
</div>
<div class="form-group">
    <label for="history_calendar" class="font-weight-bold"><?php echo _t("نوع التاريخ"); ?><sup class="text-danger">*</sup></label>
    <select name="post_meta[history_calendar]" id="history_calendar" class="form-control custom-select rounded-0">
        <option value="" selected="true" disabled="true"><?php echo _t("كردي, هجري, ميلادي"); ?></option>
		<?php foreach($history_calendars as $type_date=>$date_title): ?>
			<option value="<?php esc_html($type_date); ?>" <?php selected_val($type_date,$history_calendar); ?> ><?php esc_html($date_title); ?></option>
		<?php endforeach; ?>
	</select>
    <div id="history_calendar_error_txt" class="invalid-feedback"></div>
</div>
<div class="form-group">
    <label class="font-weight-bold"><?php echo _t("إختيار التاريخ"); ?><sup class="text-danger">*</sup></label>
	<div class="form-row">
		<div class="col-lg-4">
			<input type="text" name="post_meta[history_date][year]" id="history_year" value="<?php esc_html($history_year); ?>" class="form-control rounded-0" placeholder="<?php echo _t("أكتب السنة"); ?>"/>
			<div id="history_year_error_txt" class="invalid-feedback"></div>
		</div>		
		<div class="col-lg-4">
			<select name="post_meta[history_date][month]" id="history_month" class="form-control custom-select rounded-0" placeholder="<?php echo _t("إختر الشهر"); ?>">
				<?php 
				if($action == "edit"): 
				foreach(months_names(null,$history_calendar) as $month_num=>$month_name):
				?>
					<option value="<?php esc_html($month_num); ?>" <?php selected_val($month_num,$history_month); ?> ><?php esc_html($month_name); ?></option>
				<?php 
				endforeach;
				endif; 
				?>
			</select>
			<div id="history_month_error_txt" class="invalid-feedback"></div>
		</div>		
		<div class="col-lg-4">
			<input type="text" name="post_meta[history_date][day]" id="history_day" value="<?php esc_html($history_day); ?>" class="form-control rounded-0" placeholder="<?php echo _t("أكتب اليوم"); ?>"/>
			<div id="history_day_error_txt" class="invalid-feedback"></div>
		</div>
	</div>
</div>
<div class="form-group" id="post_content">
	<label for="post_content"><?php echo _t("الأحداث"); ?><sup class="text-danger">*</sup></label>
    <textarea class="tinymce-area" name="post_content"><?php echo validate_html($post_content_editor); ?></textarea>
	<div id="post_content_error_txt" class="invalid-feedback"></div>
</div>