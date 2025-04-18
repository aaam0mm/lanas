<?php
/**
 * notifications.php
 * User dashboard page
 */
	$current_user = get_current_user_info();
	$user_id = $current_user->id;
	$user_identify = get_user_meta($user_id, "user_identify");
	$cv_badge_request = get_user_meta($user_id, "cv_badge_request");
    $get_skills_experiences = get_user_meta($user_id,"skills_experiences", false, true);
    $get_achievements_publications = get_user_meta($user_id,"achievements_publications", false, true);
    $get_activities_courses = get_user_meta($user_id,"activities_courses", false, true);
    $get_skills_degrees = get_user_meta($user_id,"skills_degrees", false, true);
 	
	$arr = ["achievements_publications" => ["inputs" => ["name" => ["label" => _t("الإنجاز أو الإصدار") , "length" => 85, "type" => "text", "default_value" => "", "value" => "", ], "date" => ["label" => _t("تاريخ الإصدار") , "length" => "", "type" => "date", "default_value" => "", "value" => "", ], "url" => ["label" => _t("الرابط") , "length" => "", "type" => "text", "default_value" => "", "value" => "", ], ], "privacy" => true, ], "skills_experiences" => ["inputs" => ["name" => ["label" => _t("المهارة أو الخبرة") , "length" => 85, "type" => "text", "default_value" => "", "value" => "", ], "level" => ["label" => _t("اكتب رقم للمستوى مثل: (80 أو 40 أو..)") , "length" => "", "type" => "text", "default_value" => "", "value" => "", ], ], "privacy" => true], "activities_courses" => ["inputs" => ["name" => ["label" => _t("النشاط أو الدورة") , "length" => 85, "type" => "text", "default_value" => "", "value" => "", ], "joining_date" => ["label" => _t("تاريخ الإلتحاق") , "length" => "", "type" => "text", "default_value" => "", "value" => "", ], "joining_date" => ["label" => _t("تاريخ الإلتحاق") , "length" => "", "type" => "text", "default_value" => "", "value" => "", ], "leaving_date" => ["label" => _t("تاريخ المغادرة") , "length" => "", "type" => "text", "default_value" => "", "value" => "", ], ], "privacy" => true], "skills_degrees" => ["inputs" => ["name" => ["label" => _t("الشهادة التي حصلت عليها") , "length" => 85, "type" => "text", "default_value" => "", "value" => "", ], "graduation_date" => ["label" => _t("تاريخ التخرج") , "length" => "", "type" => "date", "default_value" => "", "value" => "", ], ], "privacy" => true] ];
	$progress_sections = [
		"skills_experiences" => [
			"complete" => "no",
			"title" => _t("المهارات و الخبرات"),
		],
		"achievements_publications" => [
			"complete" => "no",
			"title" => _t("الإنجازات و الإصدارات"),
		],
		"activities_courses" => [
			"complete" => "no",
			"title" => _t(" النشاطات و الدورات"),
		],
		"skills_degrees" => [
			"complete" => "no",
			"title" => _t("الموهلات العلمية و الشهادات"),
		],
	];
	$progress_class_s = [];
	if($get_skills_experiences) {
		$progress_class_s[] = "complete";
		$progress_sections["skills_experiences"]["complete"] = "yes";
	}
	if($get_achievements_publications) {
		$progress_class_s[] = "complete";
		$progress_sections["achievements_publications"]["complete"] = "yes";
	}
	if($get_activities_courses) {
		$progress_class_s[] = "complete";
		$progress_sections["activities_courses"]["complete"] = "yes";
	}
	if($get_skills_degrees) {
		$progress_class_s[] = "complete";
		$progress_sections["skills_degrees"]["complete"] = "yes";
	}
	
	$completed_progress = count($progress_class_s);
	if($completed_progress < 4 && $completed_progress != 0) {
		$progress_class_s[$completed_progress] = "active";
	}	
?>
<input type="file" class="position-absolute top-0" id="upload_user_identify"/>
<div class="user-dashboard-profile position-relative">
	<div class="position-relative bs-wizard-c d-lg-block d-none">
		<div class="row justify-content-center bs-wizard" style="border-bottom:0;">
		
			<div class="col-lg-2 bs-wizard-step <?php esc_html($progress_class_s[0] ?? ""); ?>">
			   <div class="progress"><div class="progress-bar"></div></div>
				<a href="#" class="bs-wizard-dot"></a>
			</div>     
			
			<div class="col-lg-2 bs-wizard-step <?php esc_html($progress_class_s[1] ?? ""); ?>">
			   <div class="progress"><div class="progress-bar"></div></div>
				<a href="#" class="bs-wizard-dot"></a>
			</div>	
			
			<div class="col-lg-2 bs-wizard-step <?php esc_html($progress_class_s[2] ?? ""); ?>">
			   <div class="progress"><div class="progress-bar"></div></div>
				<a href="#" class="bs-wizard-dot"></a>
			</div>	
			
			<div class="col-lg-2 bs-wizard-step <?php esc_html($progress_class_s[3] ?? ""); ?>">
			   <div class="progress"><div class="progress-bar"></div></div>
				<a href="#" class="bs-wizard-dot"></a>
			</div>	
		</div>
		<div class="status-wizard w-100">
			<div class="d-flex justify-content-center">
				<ul class="list-unstyled p-3 bg-white border">
					<?php 
					foreach( $progress_sections as $progress_section ): 
					$icon_class = "fa-check";
					if($progress_section["complete"] == "no") {
						$icon_class = "fa-times";
					}
					?>
					<li><i class="fas <?php echo $icon_class; ?> mr-2"></i><?php echo $progress_section["title"]; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>		
    </div>
	<div class="my-5"></div>
	<!-- Personal infos -->
	<div class="profile-section-info">
		<!-- head -->
		<div class="profile-box-head bg-light p-2 border-bottom align-items-center">
			<div class="title h5 m-0"><i class="fas fa-user"></i>&nbsp;  <?php echo _t("المعلومات الشخصية"); ?></div>
		</div><!-- / head -->
		<form action="" method="POST" class="py-4">
			<?php echo user_personal_info_form(); ?>
			<div class="form-group">
				<button class="btn btn-lg btn-danger update-personal-info"><?php echo _t("حفظ"); ?></button>
			</div>
			<input type="hidden" name="method" value="user_ajax"/>
			<input type="hidden" name="request" value="update_personal_info"/>
		</form>
	</div><!-- Personal infos -->
	
	<div class="my-5"></div>
	
	<!-- Skills & degrees infos -->
	<div class="profile-section-info profile-skills-degrees border border-top-0">
		<!-- head -->
		<div class="profile-box-head bg-light p-2 d-flex border-bottom align-items-center">
			<h5 class="title h5 m-0"><i class="fas fa-graduation-cap"></i>&nbsp;  <?php echo _t("الموهلات العلمية و الشهادات"); ?></h5>
			<div class="ml-auto">
				<button class="btn btn-sm add-new-profile-info font-weight-bold rounded-0" data-form=".skills_degrees" data-title="<?php echo _t("الموهلات العلمية و الشهادات"); ?>"><i class="fas fa-plus mr-2"></i><?php echo _t("أضف مؤهل جديد"); ?></button>
			</div>
		</div><!-- / head -->
		<div class="">
		    <?php if($get_skills_degrees): ?>
			<table class="sortabletable table table-responsive-sm">
				<thead class="text-center">
					<th><?php echo _t("الشهادة"); ?></th>
					<th><?php echo _t("تاريخ التخرج"); ?></th>
					<th></th>
				</thead>
				<tbody>
					<?php 
					foreach($get_skills_degrees as $skill_degree): 
					$skill_degree_meta_value = json_decode($skill_degree["meta_value"]);
					?>
					<tr class="grab-cursor tr-meta-id-<?php esc_html($skill_degree["id"]); ?>">
						<td class="text-center font-weight-bold"><?php esc_html( $skill_degree_meta_value->name ); ?></td>
						<td class="text-center"><?php esc_html( $skill_degree_meta_value->graduation_date ); ?></td>
						<td class="text-center">
						<!-- Actions buttons -->
						<div class="btn-actions row">
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-transparent border rounded-circle edit-cv-info" data-toggle="tooltip" title="<?php echo _t('تعديل'); ?>" data-info="<?php esc_html($skill_degree["meta_value"]); ?>" data-form=".skills_degrees" data-input-prefix=".skills_degrees_" data-id="<?php esc_html($skill_degree["id"]); ?>"><i class="fas fa-pen"></i></button>
							</div>
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-danger rounded-circle delete-cv-info" data-id="<?php esc_html($skill_degree["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('حدف'); ?>"><i class="fas fa-trash"></i></button>
							</div>
						</div><!-- Actions buttons -->
						
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php
            else:
            no_content();
            endif;
            ?>
		</div>
	</div><!-- / Skills & degrees infos -->
	
	<div class="my-5"></div>

	<!-- Activities & courses infos -->
	<div class="profile-section-info profile-activities-courses border border-top-0">
		<!-- head -->
		<div class="profile-box-head bg-light p-2 d-flex border-bottom align-items-center">
			<div class="title h5 m-0"><i class="fas fa-briefcase"></i>&nbsp;  <?php echo _t("النشاطات و الدورات"); ?></div>
			<div class="ml-auto">
				<button class="btn btn-sm add-new-profile-info font-weight-bold rounded-0" data-title="<?php echo _t('النشاطات و الدورات'); ?>"  data-form=".activities_courses"><i class="fas fa-plus mr-2"></i><?php echo _t("أضف مؤهل جديد"); ?></button>
			</div>
		</div><!-- / head -->
		<div class="">
		    <?php if($get_activities_courses): ?>
			<table class="sortabletable table table-responsive-sm">
				<thead class="text-center">
					<th><?php echo _t("نشاط/دورة"); ?></th>
					<th><?php echo _t("تاريخ الإلتحاق"); ?></th>
					<th><?php echo _t("تاريخ المغادرة"); ?></th>
					<th></th>
				</thead>
				<tbody>
					<?php 
					foreach($get_activities_courses as $activite_course): 
					$activite_course_meta_value = json_decode($activite_course["meta_value"]);
					?>
					<tr class="grab-cursor tr-meta-id-<?php esc_html($activite_course["id"]); ?>">
						<td class="text-center font-weight-bold"><?php esc_html( $activite_course_meta_value->name ); ?></td>
						<td class="text-center"><?php esc_html( $activite_course_meta_value->joining_date ); ?></td>
						<td class="text-center"><?php esc_html( $activite_course_meta_value->leaving_date ); ?></td>
						<td class="text-center">
						<!-- Actions buttons -->
						<div class="btn-actions row">
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-transparent border rounded-circle edit-cv-info" data-toggle="tooltip" title="<?php echo _t('تعديل'); ?>" data-info="<?php esc_html($activite_course["meta_value"]); ?>" data-form=".activities_courses" data-input-prefix=".activities_courses_" data-id="<?php esc_html($activite_course["id"]); ?>"><i class="fas fa-pen"></i></button>
							</div>
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-danger rounded-circle delete-cv-info" data-id="<?php esc_html($activite_course["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('حدف'); ?>"><i class="fas fa-trash"></i></button>
							</div>
						</div><!-- Actions buttons -->
						
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php 
			else:
			no_content();
			endif; 
			?>
		</div>
	</div><!-- / Activities & courses infos -->
	
	<div class="my-5"></div>
	
	<!-- Skills & experiences infos -->
	<div class="profile-section-info profile-skills-experiences border border-top-0">
		<!-- head -->
		<div class="profile-box-head bg-light p-2 d-flex border-bottom align-items-center">
			<div class="title h5 m-0"><i class="fas fa-list-ol"></i>&nbsp;  <?php echo _t("لمهارات و الخبرات"); ?></div>
			<div class="ml-auto">
				<button class="btn btn-sm add-new-profile-info font-weight-bold rounded-0" data-title="<?php echo _t('المهارات و الخبرات'); ?>" data-form=".skills_experiences"><i class="fas fa-plus mr-2"></i><?php echo _t("أضف مؤهل جديد"); ?></button>
			</div>
		</div><!-- / head -->
		<div class="">
			<?php if($get_skills_experiences): ?>
			<table class="sortabletable table table-responsive-sm">
				<thead class="text-center">
					<th><?php echo _t("المهارة/الخبرة"); ?></th>
					<th><?php echo _t("اكتب رقم للمستوى مثل: (80 أو 40 أو..)"); ?></th>
					<th></th>
				</thead>
				<tbody>
					<?php 
					foreach($get_skills_experiences as $skill_exper): 
					$skill_meta_value = json_decode($skill_exper["meta_value"]);
					?>
					<tr class="grab-cursor tr-meta-id-<?php esc_html($skill_exper["id"]); ?>">
						<td class="text-center font-weight-bold"><?php esc_html($skill_meta_value->name); ?></td>
						<td class="text-center"><?php esc_html($skill_meta_value->level); ?></td>
						<td class="text-center">
						<!-- Actions buttons -->
						<div class="btn-actions row">
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-transparent border rounded-circle edit-cv-info" data-toggle="tooltip" title="<?php echo _t('تعديل'); ?>" data-info="<?php esc_html($skill_exper["meta_value"]); ?>" data-form=".skills_experiences" data-input-prefix=".skills_experiences_" data-id="<?php esc_html($skill_exper["id"]); ?>"><i class="fas fa-pen"></i></button>
							</div>
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-danger rounded-circle delete-cv-info" data-id="<?php esc_html($skill_exper["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('حدف'); ?>"><i class="fas fa-trash"></i></button>
							</div>
						</div><!-- Actions buttons -->
						
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php 
			else:
			no_content();
			endif; 
			?>
		</div>
	</div><!-- / Skills & experiences infos -->	
	
	<div class="my-5"></div>
	
	<!-- Achievements & Publications infos -->
	<div class="profile-section-info profile-achievements-publications border border-top-0">
		<!-- head -->
		<div class="profile-box-head bg-light p-2 d-flex border-bottom align-items-center">
			<div class="title h5 m-0"><i class="fas fa-ribbon"></i>&nbsp; <?php echo _t("الإنجازات و الإصدارات"); ?></div>
			<div class="ml-auto">
				<button class="btn btn-sm add-new-profile-info font-weight-bold rounded-0" data-title="<?php echo _t('الإنجازات و الإصدارات'); ?>" data-form=".achievements_publications"><i class="fas fa-plus mr-2"></i><?php echo _t("أضف مؤهل جديد"); ?></button>
			</div>
		</div><!-- / head -->
		<div class="">
		    <?php if($get_achievements_publications): ?>
			<table class="sortabletable table table-responsive-sm">
				<thead class="text-center">
					<th><?php echo _t("الإنجاز/الإصدار"); ?></th>
					<th><?php echo _t("التاريخ"); ?></th>
					<th></th>
				</thead>
				<tbody>
					<?php 
					foreach($get_achievements_publications as $achiev_pub): 
					$achiev_pub_meta_value = json_decode($achiev_pub["meta_value"]);
					?>
					<tr class="grab-cursor tr-meta-id-<?php esc_html($achiev_pub["id"]); ?>">
						<td class="text-center font-weight-bold"><?php esc_html( $achiev_pub_meta_value->name ); ?></td>
						<td class="text-center"><?php esc_html( $achiev_pub_meta_value->date ); ?></td>
						<td class="text-center">
						
						<!-- Actions buttons -->
						<div class="btn-actions row">
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-transparent border rounded-circle edit-cv-info" data-toggle="tooltip" title="<?php echo _t('تعديل'); ?>" data-info="<?php esc_html($achiev_pub["meta_value"]); ?>" data-form=".achievements_publications" data-input-prefix=".achievements_publications_" data-id="<?php esc_html($achiev_pub["id"]); ?>"><i class="fas fa-pen"></i></button>
							</div>
							<div class="col-md-12 col-lg-6 mb-2 mb-lg-0 px-1">
								<button class="btn btn-danger rounded-circle delete-cv-info" data-id="<?php esc_html($achiev_pub["id"]); ?>" data-toggle="tooltip" title="<?php echo _t('حدف'); ?>"><i class="fas fa-trash"></i></button>
							</div>
						</div><!-- Actions buttons -->
						
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php 
            else:
            no_content();
            endif;
            ?>
		</div>
	</div><!-- / Achievements & Publications infos -->
	
	<div class="my-5"></div>
	<?php if(user_authority()->cv_badge): ?>
	<div class="cv-badge">
	    <div class="alert alert-info">
	        <?php echo _t("للحصول على وسام السيرة الذاتية يمكنك إرسال طلب بعد إكمال سيرتك الذاتية بمعلومات صحيحة مع رفع نسخة من هوية صادرة من جهة حكومية تحمل صورتك للتحقق من صحة طلبك."); ?><br />
	        <span class="text-danger"><?php echo _t("تنبيه"); ?>  : </span><?php echo _t("إدخال أي معلومات غير صحيحة أو مضللة يعرض حسابك للحظر"); ?>.
		</div>
	    <form action="" method="POST">
    	    <div class="form-row">
				<?php if(!$cv_badge_request): ?>
    	        <div class="col-sm-3">
    	            <div class="form-group">
    	                <button class="btn btn-secondary btn-block upload-user-identify"><?php echo _t("تحميل الهوية"); ?></button>
    	            </div>
    	            <div class="form-group user-identify-field">
    	                <?php if(get_file($user_identify)): ?>
    	                <a href="<?php esc_html(get_file($user_identify)); ?>"><?php echo _t("الملف"); ?></a>
    	                <?php endif; ?>
    	            </div>
				</div>
				<?php endif; ?>
    	        <div class="col-sm-<?php echo $cv_badge_request == "refuse" ? "12" : "3"; ?> ml-auto">
    	            <div class="form-group">
						<?php if(!$cv_badge_request): ?>
    	                <button class="btn btn-danger btn-block js-btn-send-cv-badge-order"><?php echo _t("إرسال الطلب"); ?></button>
						<?php elseif($cv_badge_request == "refuse"): ?>
						<div class="alert alert-danger" disabled="true">
							<?php echo _t("للأسف ! لم تتم الموافقة على منحك وسام السيرة الذاتية. يمكنك مراسلة الإدارة للمزيد من المعلومات"); ?>
						</div>
    	                <?php elseif($cv_badge_request == "pending"): ?>
    	                <button class="btn btn-danger disabled btn-block" disabled="true"><?php echo _t("في طور المراجعة ..."); ?></button>
    	                <?php endif; ?>
    	            </div>
    	       </div>
			</div>
			<?php if(!$cv_badge_request): ?>
    	    <div class="form-row">
    	        <div class="col-md-12">
    	            <div class="progress progress-user-identify mt-3">
					    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
					</div>						
    	        </div>
    	    </div>
    	    <input type="hidden" name="user_identify" id="user_identify" value="<?php esc_html($user_identify); ?>"/>
			<input type="hidden" name="method" value="send_cv_badge_order"/>
			<?php endif; ?>
	    </form>
	</div>
	<?php endif; ?>
	<div class="modal profile-infos-modal fade" id="info-modal">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">

		  <!-- Modal Header -->
		  <div class="modal-header border-bottom">
			<h4 class="modal-title"></h4>
			<button type="button" class="close" data-dismiss="modal">&times;</button>
		  </div>

			<!-- Modal body -->
			<div class="modal-body">
		  		<?php 
				echo user_cv_info_form($arr); 
				?>				
			</div>

		  <!-- Modal footer -->
		  <div class="modal-footer border-top">
			<button type="button" class="btn btn-primary rounded-0 save-cv"><?php echo _t("حفظ"); ?></button>
		  </div>
		</div>
	  </div>
	</div>
	
</div>