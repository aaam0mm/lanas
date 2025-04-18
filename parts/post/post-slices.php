<?php
/**
 * post-slices.php
 * Add/Edit post slices input HTML markup
 *
 */
 
 if(!function_exists("post_slices_html")) {
	/**
     * post_slices_html()
	 *
	 */
	function post_slices_html($post_type, $post_id = null) {
		if(in_array($post_type,NO_SLICE)) {
			return;
		}
		
		$build_tpl = [
			"image" => [
				"title" => true,
				"desc" => true,
				"image" => true,
			],	
			/*
			"video" => [
				"title" => true,
				"desc" => true,
				"image" => false,
			],
			"audio" => [
				"title" => true,
				"desc" => true,
				"image" => false,
			],
			*/
			"text" => [
				"title" => true,
				"desc" => true,
				"image" => false,
			],			
			"embed" => [
				"title" => true,
				"desc" => true,
				"image" => false,
			],			
			"map" => [
				"title" => true,
				"desc" => true,
				"image" => false,
			],			
			"vote" => [
				"title" => true,
				"desc" => true,
				"image" => true,
			],			
			"quizz" => [
				"title" => true,
				"desc" => true,
				"image" => true,
			],
		];
		
		$slices = [
			"image" => [
				"title" => _t("صورة"),
				"icon" => "fas fa-image",
			],
			/*
			"video" => [
				"title" => _t("فيديو"),
				"icon" => "fas fa-video",
			],			
			"audio" => [
				"title" => _t("صوت"),
				"icon" => "fas fa-music",
			],
			*/			
			"text" => [
				"title" => _t("نص"),
				"icon" => "fas fa-font",
			],			
			"embed" => [
				"title" => _t("تضمين"),
				"icon" => "fas fa-share-square",
			],			
			"map" => [
				"title" => _t("خرائط"),
				"icon" => "fas fa-map-marked",
			],			
			"vote" => [
				"title" => _t("تصويت"),
				"icon" => "fas fa-thumbs-up",
			],			
			"quizz" => [
				"title" => _t("مسابقة"),
				"icon" => "fas fa-question",
			]
		];
		
		?>
		<div id="slice-app">
			<!-- add slices -->
			<div class="form-group slices-choose p-2">
				<label class="font-weight-bold"><?php echo _t("من هنا تستطيع إضافة شرائح للمحتوى المنشور"); ?></label>
				<button class="btn btn-green btn-show-slice form-control border-0 mb-3 collapsed" type="button" data-toggle="collapse" data-target="#slicesChoose" aria-expanded="false" aria-controls="collapseExample">
				</button>
				<div id="slicesChoose" class="collapse">
						<div class="d-flex flex-wrap justify-content-center">
							<?php foreach($slices as $slice_type=>$slice): ?>
							<!-- slice -->
							<div class="slice">
								<div class="slice-area text-center add-slice" data-type="<?php echo $slice_type; ?>" data-title="<?php echo $slice["title"]; ?>">
									<div class="slice-icon rounded-circle"><i class="<?php echo $slice["icon"]; ?>"></i></div>
									<div class="slice-title"><span class="h6 font-weight-bold"><?php echo $slice["title"]; ?></span></div>
								</div>
							</div><!-- / slice -->
							<?php endforeach; ?>
						</div>
				</div>
			</div>
			<!-- / add slices -->
			
			<div id="get_slices">
			</div>
			
			<?php foreach($build_tpl as $slice_name=>$tpl): ?>

				<script type="x-tmpl-mustache" id="tmpl-slice-<?php echo $slice_name; ?>">
					{{#tmpl}}
					<div class="slice-type-area slice-type-<?php echo $slice_name; ?> slice-type-<?php echo $slice_name; ?>-{{ slice_count }} p-3 mb-3">
						<div class="slice-top">
							<div class="d-flex align-items-center">
								<h5 class="mb-0">{{ slice_title }}</h5>
								<div class="ml-auto">
									<button class="btn btn-warning rounded-circle btn-sm remove-slice" data-remove=".slice-type-<?php echo $slice_name; ?>-{{ slice_count }}"><i class="fas fa-times fa-sm"></i></button>
								</div>
							</div>
						</div>
						<div class="my-3"></div>
						<?php if($tpl["title"]): ?>
						<div class="form-group">
							<input type="text" name="slice[{{ slice_type }}][{{ slice_count }}][title]" value="{{ title }}" class="form-control rounded-0"/>
						</div>
						<?php 
						endif; 
						if($tpl["desc"]): 
						?>
						<div class="form-group">
							<textarea class="tinymce-area" id="tinymce-{{ slice_type }}-desc-{{ slice_count }}" name="slice[{{ slice_type }}][{{ slice_count }}][desc]">{{ desc }}</textarea>
						</div>
						<?php 
						endif; 
						if($tpl["image"]):
							post_thumbnail_html( 'slice-{{ slice_type }}-{{ slice_count }}', 'slice-{{ slice_type }}-{{ slice_count }}', 'slice[{{ slice_type }}][{{ slice_count }}][image]', '{{ image_thumb }}',_("إضافة صورة"),false, '{{image_id}}' );
						endif;
						?>
						<input type="hidden" name="slice[{{ slice_type }}][{{ slice_count }}][slice_id]" value="{{ slice_id }}"/>
						<?php
						switch($slice_name) {
							case "embed" :
								?>
								<div class="form-group">
									<input type="text" name="slice[{{ slice_type }}][{{ slice_count }}][embed]" value="{{ embed }}" placeholder="<?php echo _t('ألصق رابط المحتوى'); ?>" class="form-control rounded-0"/>
								</div>
								<?php					
							break;
							case "map" :
								?>
								<div class="form-group">
									<textarea name="slice[{{ slice_type }}][{{ slice_count }}][map_url]"  placeholder="<?php echo _t('رابط الخريطة'); ?>" class="form-control rounded-0">
									{{ map_url }}
									</textarea>
								</div>
								<?php					
							break;
							case "vote":
								?>
								<div class="form-group">
									<label class="font-weight-bold"><?php echo _t("خيارات التصويت"); ?></label>
									<div class="form-group">
										<div class="vote-options vote-options-{{ slice_count }} position-relative">

										</div>
									</div>
									<div class="form-group">
										<button class="add-vote btn btn-warning" data-slice="{{ slice_count }}"><i class="fas fa-plus mr-2"></i><?php echo _t("إضافة إستطلاع"); ?></button>
									</div>
								</div>
								<?php
							break;
							case "quizz":
								?>
								<div class="form-group">
									<label for="" class="font-weight-bold"><?php echo _t("الأجوبة المقترحة"); ?></label>
									<div class="form-group">
										<div class="quizz-questions quizz-{{ slice_count }}">
											
										</div>
									</div>
								</div>
								<div class="form-group">
									<button class="btn btn-primary add-question-answer" data-quiz="{{ slice_count }}"><i class="fas fa-plus mr-2"></i><?php echo _t("أضف جواب"); ?></button>
								</div>
								<?php
							break;
						}
						?>
					</div>
					{{/tmpl}}
				</script>
			<?php 
			endforeach;
			?>
			<script type="x-tmpl-mustache" id="tmpl-vote-option">
				{{#tmpl}}
				{{#options}}
				<div class="form-group vote-option position-relative">
					<input type="text" name="slice[{{ slice_type }}][{{ slice_count }}][options][][text]" value="{{ text }}" class="form-control rounded-0"/>
					<button class="btn btn-warning rounded-circle btn-sm remove-vote-option position-absolute" data-remove=""><i class="fas fa-times fa-sm"></i></button>
				</div>
				{{/options}}
				{{/tmpl}}
			</script>
			<script type="x-tmpl-mustache" id="tmpl-quizz-answer">
				{{#tmpl}}
				<div class="quiz-answer form-group position-relative">
					{{#answer}}
					<div class="form-row">
						<div class="col-lg-10">
							<input type="text" class="form-control rounded-0" name="slice[{{ slice_type }}][{{ slice_count }}][answer][{{ answer_count }}][text][value]" value="{{ text.value }}"/>
						</div>
						<div class="col-lg-2">
							<div class="bg-white h-100 d-flex align-items-center justify-content-center">
								<input type="checkbox" {{ attr_checked }} name="slice[{{ slice_type }}][{{ slice_count }}][answer][{{ answer_count }}][text][is_true]" />
								<label for="" class="ml-2"><?php echo _t("صحيح"); ?></label>
								<button class="btn btn-warning rounded-circle btn-sm remove-quizz-answer position-absolute"><i class="fas fa-times fa-sm"></i></button>
							</div>
						</div>
					</div>
					{{/answer}}
				</div>
				{{/tmpl}}
			</script>
		</div>
		<?php
		$post_slices = load_post_slices($post_id);
		if($post_slices):
			$slices_data = $slices_types = $slice_count = [];
			$l = [];
			foreach($build_tpl as $slice_type=>$slice_settings) {
				$l[$slice_type] = 1; 
			}
			foreach($post_slices as $slice):
				$slices_types[] = $slice["type"];
				$slice_content = $slice["content"];
				if(isset($slice_content->image)) {
					$slice_content->image_thumb = get_thumb($slice_content->image,"lg");
					$slice_content->image_id =  $slice_content->image;
				}
				$slice_content->slice_id = $slice["slice_id"];
				$slice_content->slice_title = $slices[$slice["type"]]["title"];
				$slice_content->slice_type = $slice["type"];
				$slice_content->slice_count = $l[$slice["type"]];
				$slices_data[][$slice["type"]]["tmpl"] = $slice_content;
				$l[$slice["type"]]++;
			endforeach;
		?>
			<!-- Render slices -->
			<script>
			var slice_data = <?php echo json_encode($slices_data); ?>;
			var slices = <?php echo json_encode($slices_types); ?>;
			</script>
			<!-- / Render slices -->
			<?php
		endif;
	}
	
 }