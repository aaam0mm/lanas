	<div class="dash-part-form">
		<div class="full-width">
			<?php 
			if(!$section) {  
			$get_badges = get_badges(null,null);
			?>
			<div class="page-action">
				<div class="pull-right">
					<a href="dashboard/badges?section=add" id="btn_link">إضافة</a>    
				</div>
				<div class="pull-left">
					<!--
					<div class="line-elm-flex">
						<div class="7r-width">
						<input name="q" placeholder="إبحث عن رتبة" type="text">
						</div>
						<div class="r3-width">
						<button form="form_filter" id="search_btn"><i class="fa fa-search"></i></button>
						</div>
					</div>
					-->
				</div>
			</div>
			<div class="clear"></div>
			<div class="full-width">
				<?php
				if($get_badges) {
					?>
				    <div class="table-responsive">
					<table class="table_parent">
						<tr>
						    <th>#</th>
							<th>الوسام</th>    
							<th>الأيقونة</th>   
							<th>وسام ل</th>   
							<th>الإجراءات</th>    
						</tr> 
						<?php
						foreach($get_badges as $get_badges_allK=>$get_badges_allV) {
							$badge_id = $get_badges_allV["id"];
							$badge_icon = $get_badges_allV["badge_icon"];
							$badge_name = json_decode($get_badges_allV["badge_name"]);
							$badge_desc = json_decode($get_badges_allV["badge_desc"]);
							$badge_options = unserialize($get_badges_allV["badge_options"]);
							// case 1 active role
							// case 2 unactive role
							$badge_case  = $get_badges_allV["badge_case"];
							if($badge_case == 1) {
								$lock_btn_class = "fa-lock-open";
								$lock_action = "lock";
								$lock_action_tooltip = "إغلاق";
							}elseif($badge_case == 2){
								$lock_btn_class = "fa-lock";
								$lock_action = "unlock";
								$lock_action_tooltip = "فتح";
							}
							?>
							<tr>
							<td><?php esc_html($badge_id); ?></td>
							<td><?php echo $badge_name->ar; ?></td>
							<td><img src="<?php echo get_thumb($badge_icon,"sm"); ?>" class="meduim_icon"/></td>
							<td><?php esc_html($badge_options["condition"]); ?></td>
							<td>
							<table class="table_child">
							<tr>  
							<td><button class="action_stg edit-st-btn open-url" title="تعديل" data-url="dashboard/badges?section=edit&badge_id=<?php esc_html($badge_id); ?>" id=""><i class="fa fa-cog"></i></button></td>
							<td><button class="action_stg lock-btn updateData" title="<?php esc_html($lock_action_tooltip); ?>" data-request="<?php esc_html($lock_action); ?>" data-method="un_lock_badge_ajax" data-id="<?php esc_html($badge_id); ?>"><i class="fa <?php echo $lock_btn_class; ?>"></i></button></td>      
							<td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=badges&id=<?php esc_html($badge_id); ?>"><i class="fa fa-trash"></i></button></td> 
							</tr>
							</table>    
							</td>
							</tr>
							<?php
						}
						?>
					</table>
                </div>
					<?php 
				} 
				?>
			</div>
			<?php 
			}elseif($section == "add" or $section == "edit") { 
			$badge_id = $_GET["badge_id"] ?? "";
			$get_badge = get_badges($badge_id);
			$badge_options = @unserialize($get_badge["badge_options"]);
			$badge_icon = $get_badge["badge_icon"] ?? "";
			$badge_name = $get_badge["badge_name"] ?? "";
			$badge_desc = $get_badge["badge_desc"] ?? "";
			?>
				<div class="r7-width">
					<div class="">
						<div class="add_new_badge">
							<form method="post" id="form_data" enctype="multipart/form-data">
								<div class="full-width input-label-noline">
								 <label for="badge_name">إسم الوسام</label>
									<?php multi_input_languages("badge_name","text",$badge_name); ?>
								</div>
								 <div class="full-width input-label-noline">
								 <label for="badge_desc">الوصف</label>
									<?php multi_input_languages("badge_desc","textarea",$badge_desc); ?>
								</div>
								<div class="full-width input-label-noline">
								<label for="conditon">شرط الحصول على الوسام</label>
								<div class="notices">
									   <p>لا يمكن لأي عضو الحصول على وسامين من نفس الفئة. هذا الشرط لا يشمل الأوسمة اليدوية</p>
								</div>
								<div class="clear"></div>
								<select name="badge[condition]" id="badge_conditions">
								   <option selected="true" disabled="true">إختر شرط الحصول على الوسام</option>
									<option value="seniority" <?php if($badge_options["condition"] == "seniority") { ?> selected="" <?php } ?> >الأقدمية</option>
									<option value="points" <?php if($badge_options["condition"] == "points") { ?> selected="" <?php } ?> >النقاط</option>
									<option value="trusted_posts" <?php if($badge_options["condition"] == "trusted_posts") { ?> selected="" <?php } ?> >عدد المواضيع الموثوقة</option>
									<option value="role" <?php if($badge_options["condition"] == "role") { ?> selected="" <?php } ?> >الرتبة</option>
									<option value="manual" <?php if($badge_options["condition"] == "manual") { ?> selected="" <?php } ?> style="background-color:#f00; color:#fff;">يدويا</option>
								</select>
								</div>
								<div class="clear"></div>
								<div class="badge_condition_val">
									<?php 
									if(is_array($badge_options)) { 
										if($badge_options["condition"] == "seniority") {
											?>
											<p>أدخل عدد السنوات بالرقم</p>
											<input type="text" name="badge[seniority]" value="<?php esc_html($badge_options["seniority"]); ?>"/>
											<?php
										}elseif($badge_options["condition"] == "points") {
											?>
											<p>أدخل عدد النقاط اللازمة لظهور الوسام في ملف العضو</p>
											<input type="text" name="badge[points]" value="<?php esc_html($badge_options["points"]); ?>"/>
											<?php
										}elseif($badge_options["condition"] == "trusted_posts") {
											?>
											<p>أدخل عدد المواضيع الموثوقة اللازمة لظهور الوسام في ملف العضو</p>
											<input type="text" name="badge[trusted_posts]" value="<?php esc_html($badge_options["trusted_posts"]); ?>"/>
											<?php
										}										
									?>
										
									<?php } ?>
								</div>
								<div class="clear"></div>
								<div class="full-width input-label-noline">
								<div class="up-upload-input">
									<input type="hidden" id="badge_icon" name="badge_icon" value="<?php esc_html($badge_icon); ?>"/>
									<button class="upload-btn" data-input="#badge_icon"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
									<div class="clear"></div>
										<div class="img-preview">
											<img src="<?php esc_html(get_thumb($badge_icon)); ?>" id="badge_icon_prv"/>
									</div>							
								</div>
								</div>
								<div class="full-width input-label-noline">
								 <label for="badge_desc">نقاط</label>
								    <input type="text" name="badge[points_remaining]" value="<?php esc_html($badge_options["points_remaining"]); ?>"
								</div>

								<input type="hidden" name="method" value="badges"/>
								<input type="hidden" name="action" value="<?php esc_html($section); ?>"/>
								<?php if($section == "edit") { ?>
								<input type="hidden" name="badge_id" value="<?php esc_html($badge_id); ?>"/>
								<?php } ?>
								<button id="submit_form" class="saveData">أضف</button>
							</form>
							
						</div>
					</div>
				</div>
				<script>
					$(document).ready(function() {
					   $(document).on("change","#badge_conditions",function() {
						  var badge_type = $(this).val();
						   var condition_vals = {
							   'seniority' : {
									'html' : '<input type="text" name="badge[seniority]"/>',
									'text' : 'أدخل عدد السنوات بالرقم'
							   },
							   
							   'points' : {
									'html' : '<input type="text" name="badge[points]"/>',
									'text' : 'أدخل عدد النقاط اللازمة لظهور الوسام في ملف العضو'
								},
							   'role' : {
								   'html' : '<select name="badge[role]">'
								   
								   <?php
								   foreach(get_roles() as $rolek=>$rolev) {
									?>
										+ '<option value="<?php echo $rolev["id"]; ?>"><?php echo json_decode($rolev["role_title"])->ar; ?></option>'    
									<?php
								   }
								   ?>
								   
								   + '</select>'
								   ,
								   'text' : 'إختر الرتبة اللازمة لظهور الوسام في ملف العضو'
							   },							   
							   'trusted_posts' : {
								   'html' : '<input type="text" name="badge[trusted_posts]"/>',
								   'text' : 'أدخل عدد المواضيع الموثوقة اللازمة لظهور الوسام في ملف العضو'
							   },
							   'manual' : {
								   'html' : null
							   }
						   };
						   $(".badge_condition_val").html("<p>"+condition_vals[badge_type].text+"</p>"+condition_vals[badge_type].html);
					   });
					});
				</script>
			<?php
			}
			?>
		</div>
	</div>