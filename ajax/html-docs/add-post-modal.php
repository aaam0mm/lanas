	<?php
	if (user_authority()->publish_in == "all") {
		$get_taxonomies = get_all_taxonomies('taxo_type, taxo_add_title, taxo_icon');
	} else {
		$get_taxonomies = $dsql->dsql()->table('taxonomies')->where('taxo_type', user_authority()->publish_in)->field('taxo_type,taxo_add_title,taxo_icon')->get();
	}
	?>
	<!-- Modal home video -->
	<div class="modal fade" id="addpostModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body mx-3">
					<div class="choose-post-in">
						<div class="row">
							<div class="col-md-6 col-sm-12 pl-0 pr-1">
								<div class="col-12 notice-explaine-choice pt-2 px-0">
									<h5 class="px-2 text-danger"><?php echo _t("إضافة لموقع وملفك"); ?></h5>
									<p class="px-2"><?php echo _t("ترسل هنا فقط مواضيع الذي فيه شروط (مواضيع موثقة) بعد موافقة الإدارة سيظهر الموضوع في الموقع وملفك"); ?></p>
									<div class="add-trusted-post-checkbox bg-green p-2">
										<div class="custom-control custom-radio radio-select-post-type">
											<input type="radio" name="post_type" value="trusted" class="custom-control-input" id="add-trusted-check">
											<label class="h6 text-white custom-control-label ml-1" for="add-trusted-check"><?php echo _t("موثقة"); ?></label>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-12 pr-0 pl-1">
								<div class="col-12 notice-explaine-choice pt-2 px-0">
									<h5 class="px-2 text-danger"><?php echo _t("إضافة لملفك فقط"); ?></h5>
									<p class="px-2"><?php echo _t("تنشر هناأي موضوع تريد لك حريةالنشر ولا تحتاج موافقة للنشر، سيظهر الموضوع في ملفك مباشرة"); ?></p>
									<div class="add-trusted-post-checkbox bg-primary p-2">
										<div class="custom-control custom-radio radio-select-post-type">
											<input type="radio" name="post_type" value="untrusted" class="custom-control-input" id="add-untrusted-check">
											<label class="h6 text-white custom-control-label ml-1" for="add-untrusted-check"><?php echo _t("حرة"); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="choose-post-type">
						<?php
						if ($get_taxonomies):
						?>
							<div class="row">
								<?php
								foreach ($get_taxonomies as $taxo):
									$taxo_add_title = @json_decode($taxo["taxo_add_title"]);
									$taxo_add_title = $taxo_add_title->{current_content_lang()} ?? "";
								?>
									<div class="taxonomy-add-box col-md-4 mb-4" data-taxonomy="<?php esc_html($taxo["taxo_type"]); ?>">
										<div class="col-12 post-type-select p-2 text-center py-5 smooth-transition">
											<div class="post-type-icon rounded-circle mx-auto">
												<i class="<?php esc_html($taxo["taxo_icon"]); ?> fa-2x"></i>
											</div>
											<span class="h4"><?php esc_html($taxo_add_title); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<input type="hidden" id="post-in-selected" value="" />
				</div>
			</div>
		</div>
	</div><!-- Modal home video -->
	<script>
		$(function() {

			$(".taxonomy-add-box").click(function() {
				var post_in = $("#post-in-selected").val();
				var post_type = $(this).data("taxonomy");
				if (typeof(post_in) != "undefined" || typeof(post_type) != "undefined") {
					window.location.href = gbj.siteurl + "/post.php?post_type=" + post_type + "&post_in=" + post_in + "&action=add";
				}
			});

			$("#addpostModal").modal('show');
			$(document).on("click", ".radio-select-post-type", function() {
				$(".choose-post-in").hide();
				$(".choose-post-type").show();
				$("#post-in-selected").val($(this).children(".custom-control-input").val());
			});
		});
	</script>