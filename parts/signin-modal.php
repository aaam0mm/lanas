<div id="fb-root"></div>
<!-- Modal -->
<div class="modal animated bounceInDown" id="signinModal" tabindex="-1" role="dialog" aria-labelledby="signinModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header shadow-sm d-block text-center py-4">
				<h4><?php echo _t("دخول"); ?></h4>
				<span><?php echo _t("انضم إلى مجتمع مدهش لدينا"); ?></span>
			</div>
			<div class="modal-body py-5">
				<div class="signin-methods text-left mx-auto">
					<a href="<?php echo siteurl(); ?>/signin.php?platform=Facebook" class="text-white small btn btn-signin btn-block btn-facebook-login d-flex align-items-center rounded-0"><i class="fab fa-facebook-f mr-3"></i><?php echo _t("تسجيل الدخول عبر فيسبوك"); ?></a>
					<a href="<?php echo siteurl(); ?>/signin.php?platform=Twitter" class="text-white btn btn-signin btn-block btn-twitter-login d-flex align-items-center rounded-0"><i class="fab fa-twitter mr-3"></i><?php echo _t("تسجيل الدخول عبر تويتر"); ?></a>
					<a href="<?php echo siteurl(); ?>/signin.php" class="text-white btn btn-signin btn-block btn-email-login d-flex align-items-center rounded-0"><i class="fas fa-envelope mr-3"></i><?php echo _t("تسجيل الدخول بالبريد الإلكتروني"); ?></a>
				</div>
			</div>
			<div class="modal-footer border-top d-block text-center">
				<small><?php echo _t("لا يوجد لديك حساب ؟"); ?></small>&nbsp;<a href="signup.php" class="text-danger"><?php echo _t("سجل حسابك"); ?></a>
			</div>
		</div>
	</div>
</div>