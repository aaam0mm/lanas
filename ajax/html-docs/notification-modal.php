	<!-- Modal -->
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5><?php echo _t("التنبيهات"); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
									<div class="user-notif-dropdown pt-2">
										<?php 
										if(get_user_notifs()):
										foreach(get_user_notifs() as $notif):
										?>
										<!-- notif -->
										<div class="d-flex px-2">
										    <?php if($notif["notif_from"] != 0): ?>
							                <a href=""><img class="dropdown-img" src="<?php echo get_thumb($notif["notif_from"]); ?>" alt=""/></a>
							                <?php endif; ?>

											<div class="dropdown-content ml-2">
												<?php echo read_notif($notif["id"],$notif["notif_type"],$notif["notif_content"],$notif["notif_from"]); ?>
												<div class="user-time small text-muted">
													<i class="fas fa-clock fa-sm mr-1"></i><?php echo get_timeago( strtotime($notif["notif_date"]) ); ?>
												</div>
											</div>
										</div><!-- notif -->
										<div class="dropdown-divider"></div>
										<?php 
										endforeach;
										else:
											no_content();
										endif;
										?>
									</div>
			</div>
		</div>
	</div><!-- / Modal -->