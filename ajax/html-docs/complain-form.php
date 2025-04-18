<?php
$post_id = $_GET["post_id"] ?? ""; 
$comment_id = $_GET["comment_id"] ?? ""; 
$infractions_arr = [
	_t("مُحتوى ذو طابع لا أخلاقي او مخل باداب العامة"),
	_t("مُحتوى يدعوا إلى الحقد و الكراهية و الارهاب"),
	_t("مُحتوى يدعوا إلى شذوذ وفسادالفكري والانحراف"),
	_t("مُحتوى قاذف, فاضح أو مُهين لشخص أو جماعة"),
	_t("مُحتوى فيه مواد محميَّة بحقوق طبع و نشر أو حقوق ملكية الفكرية"),
	_t("مُحتوى ليس صحيحا فيها اخطاء يجب ان يصحح"),
	_t("مصادر المُحتوى ليس صحيحا"),
	_t("محتوى عاطل أو رابط معطل أو فيه فايروس"),
	_t("غير ذالك"),
];

$user_name = $user_email = $atts = "";
if(is_login_in()) {
   $atts = 'readonly="true"';
   $c = get_current_user_info();
   $user_name = $c->user_name;
   $user_email = $c->user_email;
}

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
				<form method="POST">
				
					<div class="form-group">
						<label for="" class="font-weight-bold"><?php echo _t("إسم المرسل"); ?><sup class="text-danger">*</sup></label>
						<input type="text" name="complain[name]" value="<?php esc_html($user_name); ?>" <?php echo $atts; ?> class="form-control"/>
					</div>					
					
					<div class="form-group">
						<label for="" class="font-weight-bold"><?php echo _t("رقم الجوال أو البريد الإلكتروني"); ?></label>
						<input type="text" name="complain[phone_email]" value="<?php esc_html($user_email); ?>" <?php echo $atts; ?> class="form-control"/>
					</div>					
					
					<div class="form-group">
						<label for="" class="font-weight-bold"><?php echo _t("نوع المخالفة"); ?><sup class="text-danger">*</sup></label>
						<?php
						foreach( $infractions_arr as $index=>$infraction ):
						?>
						<div class="custom-control custom-radio form-group">
						  <input type="radio" id="customRadioInline<?php echo $index; ?>" value="<?php echo $infraction; ?>" name="complain[type]" class="custom-control-input">
						  <label class="custom-control-label" for="customRadioInline<?php echo $index; ?>"><?php echo $infraction; ?></label>
						</div>
						<?php endforeach; ?>
					</div>
					
					<div class="form-group">
						<label for="" class="font-weight-bold"><?php echo _t("تفاضيل مخالفة"); ?></label>
						<textarea name="complain[details]" class="form-control"></textarea>
					</div>
					
					<div class="form-group">
						<button class="btn btn-primary send-complain-form"><?php echo _t("أرسل"); ?></button>
					</div>
					
					<input type="hidden" name="method" value="send_complain_ajax"/>
					<input type="hidden" name="post_id" value="<?php esc_html($post_id); ?>"/>
					<?php if(is_login_in()): ?>
					<input type="hidden" name="complain[user_id]" value="<?php esc_html(get_current_user_info()->id); ?>"/>
					<?php endif; ?>
					<?php if($comment_id): ?>
					<input type="hidden" name="complain[comment]" value="<?php esc_html($comment_id); ?>"/>
					<?php endif; ?>
					
				</form>
			</div>
		</div>
	</div><!-- / Modal -->
	<script>
	$(document).on("click",".send-complain-form",function(e) {
		var $t = $(this);
		$t.ajax_req(function(r) {
			
			if(r.success == true) {
                swal({
                    title: r.msg,
                    icon: "success",
                    button:  gbj.ok_text              
                }).then((value) => {
                    $("#loadModal").modal('hide');
                }); 				
			}else{
                swal({
					text: r.msg,
					icon: "error",
					button : gbj.ok_text
				});                
			    
			}
			
		});
		e.preventDefault();
	});
	</script>