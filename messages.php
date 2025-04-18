<?php 
require_once 'init.php';
$get_box_messages = get_box_messages();
$msg_id = $_GET["msg_id"] ?? "";
$current_user = get_current_user_info();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
	</head>
	<body>
		<?php get_header("top"); ?>
		<div class="my-5"></div>
		<div id="messages-area" class="container">
			<div class="d-sm-flex h-100">
				<div class="conversations">
					<?php if($get_box_messages): ?>
					<div class="list-group">
					  <?php 
					  foreach($get_box_messages as $msg_box): 
					  $u_d = switch_message_display_user( $msg_box["msg_from"], $msg_box["msg_to"] );
					  ?>
					  <a href="<?php echo siteurl(); ?>/message/<?php esc_html($msg_box["msg_id"]); ?>" class="list-group-item list-group-item-action flex-column align-items-start <?php if($msg_box["msg_id"] == $msg_id) { echo 'active'; } ?>">
						<div class="row m-0">
						  <div class="msg-user-pic col-2 px-0"><img src="<?php echo get_thumb( $u_d["user_picture"] ); ?>" class="rounded-circle w-100 h-100"/></div>
						  <div class="msg-user-quick col-9 pr-0">
						  <div class="d-flex">
							  <span><?php esc_html( $u_d["user_name"] ); ?></span>
							  <small class="ml-auto"><?php echo get_timeago(strtotime($msg_box["msg_date"])); ?></small>
						  </div>
						  <span class="msg-cnv-last-msg">
							<span class="text-muted"><?php esc_html($msg_box["msg"]); ?></span>
						  </span>
						  </div>
						</div>
					  </a>
					  <?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<div class="conversation border ml-sm-3 w-100 rounded mt-3 mt-sm-0">
					<?php 
					if($msg_id): 
					$get_cnvs = get_cnvs($msg_id, $current_user->id);
					?>
					<div class="d-flex flex-column h-100">
						<div class="cnv h-75 bg-light p-4">
							<?php foreach( $get_cnvs["cnv"] as $cnv ): ?>
							<div class="msg-cnv w-75 <?php if($cnv["display_place"] == "left") { echo "ml-auto"; } ?>">
								<span class="text-white bg-info p-2 rounded shadow-sm d-block"><?php esc_html($cnv["msg"]); ?></span>
								<small class="text-right text-muted d-block"><?php esc_html($cnv["msg_date"]); ?></small>
							</div>
							<div class="my-3"></div>
							<?php endforeach; ?>
						</div>
						<div class="send-msg p-4">
							<form action="" method="POST" id="send-message-form">
								<div class="form-group">
									<textarea name="msg" class="form-control" placeholder="<?php echo _t('أكتب راسالتك هنا'); ?>"></textarea>
								</div>
								<div class="d-flex flex-row">
									<button  class="btn btn-warning ml-auto send-msg-btn send-form"><?php echo _t("أرسل"); ?></button><br />
								</div>
            					<?php if($current_user->id == get_message_field($msg_id,"msg_from")): ?>
            					<div class="text-right mt-3">
            					    <a href="#" class="text-danger delete-conversation-js" data-id="<?php esc_html($msg_id); ?>"><?php echo _t("حدف المحادثة"); ?></a>
            					</div>
            					<?php endif; ?>
								
								<input type="hidden" name="method" value="send_message"/>
								<input type="hidden" name="user_id" value="<?php esc_html($get_cnvs["to"]); ?>"/>
							</form>
						</div>
					</div>
					<?php else: ?>
					<div class="d-flex align-items-center justify-content-center h-100">
						<i class="fas fa-envelope fa-10x"></i>
						<p><?php  ?></p>
					</div>
					<?php endif; ?>

				</div>
			</div>
		</div>
		<?php user_end_scripts(); ?>
	</body>
</html>