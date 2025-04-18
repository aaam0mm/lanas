	<?php
	$term = $_GET["term"] ?? "";
	$taxonomy = $_GET["taxonomy"] ?? "";
	$terms = get_taxonomy_terms($taxonomy);
	
	$title = [
		"alert" => _t("تنبيه"),
		"terms" => _t("قوانين القسم"),
		"authorized_sources" => _t("مصادر معتمدة"),
	];
	?>
	<!-- Modal -->
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="loadModalLabel"><?php echo $title[$term]; ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php echo validate_html($terms->$term->{current_content_lang()}); ?>
			</div>
		</div>
	</div>