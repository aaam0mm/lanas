<!-- Modal -->
<div class="modal fade" id="send-messageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><?php echo _t("أرسل رسالة إلى"); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body send-message-form">
				<form action="" method="POST" id="send-message-form">
					<div class="form-row">
						<div class="col-2">
							<img src="<?php esc_html( get_thumb(get_current_user_info()->user_picture,["w" => 80, "h" => 80]) ); ?>" class="rounded-circle img-fluid"/>
						</div>
						<div class="col-10">
							<textarea class="form-control mb-4" name="msg"></textarea>
							<div class="d-flex">
								<small><?php echo sprintf(_t("بإرسالك لهذه الرسالة فأنت توافق على %s الموقع"),'<a href="'.get_terms_conditions_page()["link"].'">'.get_terms_conditions_page()['text']).'</a>'; ?></small>
								<div class="ml-auto">
									<button class="btn btn-warning send-msg-btn"><?php echo _t("أرسل"); ?></button>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="method" value="send_message"/>
					<input type="hidden" id="send-msg-id" name="user_id" value="<?php esc_html($user_id); ?>"/>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
$(function() {
	$("#send-messageModal").modal('show');		
	$('#send-messageModal').on('hidden.bs.modal', function (e) {
		$(this).remove();
	});	
});		
</script>