<?php
$per_page = 50;
$action = $_GET["action"] ?? "";
$user_id = $_GET["user_id"] ?? "";
if ($section == "edit" && !$user_id) {
	exit();
}
$current_user = get_current_user_info();
if (!$section || $section == "edit") {
	$q = $_GET["q"] ?? "";
	$per_page = $_GET["per_page"] ?? 50;
	$filter_role = $_GET["filter_role"] ?? "";
	$cv = $_GET["cv"] ?? "";
	$show = $_GET["show"] ?? "";
	$where_args = [];
	$order_by = "";
	$join = '';

	$get_members_all = $dsql->dsql()->table('users');

	if ($show == "newset") {
		$get_members_all->order('users.id', 'desc');
	} elseif ($show == "verified") {
		$get_members_all->where('users.user_verified', 'yes');
	} elseif ($show == "blocked") {
		$get_members_all->where('users.user_status', 'blocked');
	}
	if ($filter_role) {
		$get_members_all->where('users.user_role', $filter_role);
	}
	if ($user_id) {
		$get_members_all->where('users.id', $user_id);
	}
	if ($q) {
		$get_members_all->where('users.user_name', 'LIKE', $q);
	}
	if ($cv) {
		$get_members_all->join('user_meta.user_id')->where('user_meta.meta_key', 'cv_badge_request')->where('user_meta.meta_value', $cv);
	}

	$get_members_all->field('users.*');

	$get_members_all = $get_members_all->get();
	$count_members_rows = count_last_query();
}
$query_roles = get_roles();
$query_badges = get_badges();
?>
<div class="model-main model-change-role">
	<div class="model">
		<div class="model-content">
			<div class="model-ver-a">
				<div class="model-top-title">
					<h3>تغيير رتبة العضو</h3>
				</div>
				<div class="model-content-e">
					<p>إختر الرتبة الجديدة التي تريد منحها للعضو</p>
					<div class="notices">
						<p>المرجو الإنتباه إلى صلاحيات الرتبة جيدا قبل إعطائها لأي عضو</p>
					</div>
					<div class="clear"></div>
					<form method="POST" id="change_role_form">
						<select name="role" class="new_role">
							<option selected="" disabled="">إختر الرتبة التي تريد منحها للعضو</option>
							<?php
							foreach ($query_roles as $query_rolek => $query_rolev) {
								$q_role_name = json_decode($query_rolev["role_title"]);
							?>
								<option value="<?php esc_html($query_rolev["id"]); ?>"><?php esc_html($q_role_name->ar); ?></option>
							<?php
							}
							?>
						</select>
						<input type="hidden" name="method" value="update_user_role" />
						<input type="hidden" name="user_id" class="user_role_id" value="" />
					</form>
				</div>
				<div class="model-settings-btns">
					<div class="model-btns">
						<button class="confirm-model change_role">تطبيق</button>
						<button class="cancel-model">إلغاء</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="dash-part-form">
	<div class="full-width">
		<?php if (!$section) { ?>
			<!-- Send user message box -->
			<div class="model-main model-send-msg">
				<div class="model">
					<div class="model-content">
						<div class="model-ver-a">
							<div class="model-top-title">
								<h3>أرسل رسالة</h3>
							</div>
							<div class="model-content-e">
								<div class="notices">
									<p>سيتم إرسال محتوى هذه الرسالة إلى المستخدم على شكل تنبيه</p>
								</div>
								<div class="clear"></div>
								<form action="" method="post" id="send_message_form">
									<textarea name="msg"></textarea>
									<input type="hidden" name="method" value="send_alert" />
									<input type="hidden" name="user_id" class="user_msg_id" value="" />
								</form>
								<div class="model-settings-btns">
									<div class="model-btns">
										<button class="confirm-model send-alert">تطبيق</button>
										<button class="cancel-model">إلغاء</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div><!-- Send user message box -->
			<form action="" method="get" id="form_filter">
				<div class="page-action">
					<div class="pull-right">
						<a href="dashboard/users?section=add" id="btn_link">إضافة</a>
					</div>
					<div class="pull-left">
						<div class="line-elm-flex">
							<div class="7r-width">
								<input type="text" name="q" placeholder="إبحث عن بالإسم" value="<?php echo $q; ?>" />
							</div>
							<div class="r3-width">
								<button form="form_filter" id="search_btn"><i class="fa fa-search"></i></button>
							</div>
						</div>
					</div>
				</div>
				<div class="clear"></div>
				<div class="panel_filter">
					<div class="pull-left line-elm-flex">
						<div class="7r-width">
							<select name="cv" class="on_change_submit">
								<option disabled="">وسام السيرة الذاتية</option>
								<option value="" <?php if ($cv == "" or (!$show)) {
																		echo 'selected="true"';
																	} ?>>عرض الجميع</option>
								<option value="pending" <?php if ($cv == "pending") {
																					echo 'selected="true"';
																				} ?>>بإنتظار الموافقة</option>
								<option value="refuse" <?php if ($cv == "refuse") {
																					echo 'selected="true"';
																				} ?>>مرفوضة</option>
								<option value="accept" <?php if ($cv == "accept") {
																					echo 'selected="true"';
																				} ?>>موافقة</option>
							</select>
						</div>
						<div class="7r-width">
							<select name="show" class="on_change_submit">
								<option value="" <?php if ($show == "" or (!$show)) {
																		echo 'selected="true"';
																	} ?>>عرض الجميع</option>
								<option value="active" <?php if ($show == "active") {
																					echo 'selected="true"';
																				} ?>>المفعلين</option>
								<option value="verified" <?php if ($show == "verified") {
																						echo 'selected="true"';
																					} ?>>موثقون</option>
								<option value="blocked" <?php if ($show == "blocked") {
																					echo 'selected="true"';
																				} ?>>محضورين</option>
								<option value="newset" <?php if ($show == "newset") {
																					echo 'selected="true"';
																				} ?>>جدد</option>
							</select>
						</div>
						<div class="r3-width">
							<select name="per_page" class="on_change_submit">
								<option value="50" <?php if ($per_page == 50) {
																			echo 'selected="true"';
																		} ?>>50</option>
								<option value="100" <?php if ($per_page == 100) {
																			echo 'selected="true"';
																		} ?>>100</option>
								<option value="250" <?php if ($per_page == 250) {
																			echo 'selected="true"';
																		} ?>>250</option>
							</select>
						</div>
					</div>
				</div>
			</form>
			<div class="clear"></div>
			<div class="panel_filter">
				<form method="get" action="dashboard/delete" id="action-form">
					<div class="pull-right r3-width">
						<div class="line-elm-flex">
							<div class="r3-width">
								<select name="action" class="">
									<option value="delete">حدف</option>
									<option value="verify">تحقق</option>
									<option value="unverify">إلغاء التحقق</option>
									<option value="lock">حظر</option>
									<option value="publish">نشر</option>
								</select>
							</div>
							<div class="r3-width">
								<input type="submit" value="تنفيذ" class="btn_action submit-action" />
							</div>
						</div>
						<input type="hidden" name="target" value="users" />
						<input name="method" value="multi_action" type="hidden">
					</div>
				</form>

			</div>
			<div class="clear"></div>
			<div class="table-responsive">
				<table class="table_parent">
					<tbody>
						<tr>
							<th><input type="checkbox" class="select-checkbox-all check-all-multi" /></th>
							<th></th>
							<th>إسم المستخدم</th>
							<th>رتبة المستخدم</th>
							<th>البريد أو الجوال</th>
							<th>المشاركات</th>
							<th>إنضم في</th>
							<th>آخر دخول</th>
							<th>الإجراءات</th>
						</tr>
						<?php
						foreach ($get_members_all as $get_member_k => $get_member_v) {
							$user_id = $get_member_v["id"];
							$user_name = $get_member_v["user_name"];
							$user_email = $get_member_v["user_email"];
							$user_numPosts = count_user_posts($user_id);
							$user_picture = get_thumb($get_member_v["user_picture"]);
							$user_joindate = $get_member_v["user_joindate"];
							$user_lastseen = $get_member_v["user_lastseen"];
							$user_status = $get_member_v["user_status"];
							$user_verified = $get_member_v["user_verified"];
							$user_role = $get_member_v["user_role"];
							$cv_badge_request = get_user_meta($user_id, "cv_badge_request");
							$user_identify = get_user_meta($user_id, "user_identify");
							// setup buttons

							if ($user_status == "active") {
								$lock_btn_class = "fa-lock-open";
								$lock_action_tooltip = "حظر";
							} else {
								$lock_btn_class = "fa-lock";
								$lock_action_tooltip = "إلغاء حظر";
							}
							if ($user_verified == "yes") {
								$verifie_btn_class = "fas fa-star";
								$verify_action = "unverify";
								$verfiy_action_tooltip = "إلغاء التحقق";
							} else {
								$verifie_btn_class = "far fa-star";
								$verify_action = "verify";
								$verfiy_action_tooltip = "تحقق";
							}
						?>
							<tr>
								<td><input type="checkbox" class="select-checkbox check-box-action" data-id="<?php echo $user_id; ?>" /></td>
								<td><img src="<?php echo $user_picture; ?>" width="28" height="28" /></td>
								<td><a href="<?php echo siteurl() . "/user/" . $user_id; ?>"><?php esc_html($user_name); ?></a></td>
								<td><?php esc_html(get_role_name($user_role, "ar")); ?></td>
								<td><?php esc_html($user_email); ?></td>
								<td><?php esc_html($user_numPosts); ?></td>
								<td><?php esc_html($user_joindate); ?></td>
								<td><?php echo get_timeago(strtotime($user_lastseen)); ?></td>
								<td>
									<table class="table_child">
										<tr>
											<td>
												<button class="action_stg send_msg_btn" <?php if ($current_user->id == $user_id) {
																																	echo 'disabled="true"';
																																} ?> title="أرسل رسالة" data-user="<?php esc_html($user_id); ?>"><i class="fas fa-envelope"></i></button>
											</td>
											<td>
												<?php
												if ($cv_badge_request):
													if (get_file($user_identify)) {
														echo '<a href="' . get_file($user_identify) . '">ملف</a>';
													}

													if ($cv_badge_request == "pending") {
														$cv_badge_text = "إنتظار الموافقة";
													} elseif ($cv_badge_request == "refuse") {
														$cv_badge_text = "ملغية";
													} elseif ($cv_badge_request == "accept") {
														$cv_badge_text = "موافقة";
													}
												?>
													<select class="cv-badge-request" data-user="<?php esc_html($user_id); ?>" data-method="cv_badge_request">
														<option selected="" disabled="">وسام السيرة الذاتية (<?php esc_html($cv_badge_text); ?>)</option>
														<option value="normal">الحالة الطبيعة</option>
														<?php if ($cv_badge_request == "pending"): ?>
															<option value="accept">موافقة</option>
															<option value="refuse">رفض</option>
														<?php endif; ?>
													</select>
												<?php endif; ?>
											</td>
											<td>
												<button class="action_stg verify_btn updateData" data-id="<?php esc_html($user_id); ?>" data-method="un_verfiy_users" title="<?php echo $verfiy_action_tooltip; ?>"><i class="<?php echo $verifie_btn_class; ?>"></i></button>
											</td>
											<td><button class="action_stg refresh-btn" data-user="<?php esc_html($user_id); ?>"><i class="fas fa-sync"></i></button></td>
											<td><button class="action_stg lock-btn updateData" data-id="<?php esc_html($user_id); ?>" data-method="un_lock_user_ajax" title="<?php echo $lock_action_tooltip; ?>"><i class="fas <?php echo $lock_btn_class; ?>"></i></button></td>
											<td><button class="action_stg edit-st-btn open-url" data-url="dashboard/users?section=edit&user_id=<?php esc_html($user_id); ?>" title="تعديل"><i class="fa fa-cog"></i></button></td>
											<td>
												<button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=users&id=<?php esc_html($user_id); ?>"><i class="fa fa-trash"></i></button>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						<?php
						}
						?>
					</tbody>
				</table>
			</div>
		<?php
		} elseif ($section == "add" || $section == "edit") {
			$get_member_v = $get_members_all[0];
			$user_id = $get_member_v["id"];
			$user_name = $get_member_v["user_name"];
			$username = $get_member_v["username"];
			$user_email = $get_member_v["user_email"];
			$user_country = $get_member_v["user_country"];
			$user_birthdate = strtotime($get_member_v["birth_date"]);
			$user_birthDay = date("d", $user_birthdate);
			$user_birthMonth = date("m", $user_birthdate);
			$user_birthYear = date("Y", $user_birthdate);
			$user_gender = $get_member_v["user_gender"];
			$user_status = $get_member_v["user_status"];
			$get_meta_permissions = get_user_meta($user_id, "user_permissions");
			$get_meta_block_reason = get_user_meta($user_id, "block_reason");
			$get_meta_manual_bagdes = get_user_meta($user_id, "manual_badges");
			$get_meta_manual_bagdes = @unserialize($get_meta_manual_bagdes) ?? [];
			$get_meta_permissions = json_decode($get_meta_permissions);
			$perm_access_trusted = $get_meta_permissions->access_trusted ?? "";
			$perm_direct_publish = $get_meta_permissions->direct_publish ?? "";
			$perm_publish_in = $get_meta_permissions->publish_in ?? "";
			$perm_admin_panel = $get_meta_permissions->admin_panel ?? "";
			$perm_statistics = $get_meta_permissions->statistics ?? "";
			$perm_members = $get_meta_permissions->users ?? "";
			$perm_external_links = $get_meta_permissions->external_links ?? "";
			$perm_taxonomies = $get_meta_permissions->taxonomies ?? "";
			$perm_points = $get_meta_permissions->points ?? "";
			$perm_files = $get_meta_permissions->files ?? "";
			$perm_countries = $get_meta_permissions->countries;
			$perm_complains = $get_meta_permissions->complains;
			$perm_posts = $get_meta_permissions->posts ?? "";
			$perm_ads = $get_meta_permissions->ads ?? "";
			$perm_categories = $get_meta_permissions->categories ?? "";
			$perm_pages = $get_meta_permissions->pages ?? "";
			$perm_contact = $get_meta_permissions->contact ?? "";
			$perm_comments = $get_meta_permissions->comments ?? "";
			$perm_social_accounts = $get_meta_permissions->social_accounts ?? "";
			$perm_badges = $get_meta_permissions->badges ?? "";
			$perm_general_settings = $get_meta_permissions->general_settings ?? "";
			$perm_adv_settings = $get_meta_permissions->advanced_settings ?? "";
			$perm_delete = $get_meta_permissions->delete ?? "";
			$perm_post_per_day = $get_meta_permissions->post_per_day ?? "";
			$perm_languages_control = $get_meta_permissions->languages_control ?? "";
		?>
			<div class="r7-width">
				<div class="r7-width">
					<h2>أضف مستخدم جديد</h2>
					<form method="post" id="form_data">
						<div class="full-width input-label-noline">
							<label for="name">الإسم</label>
							<input type="text" name="user_name" placeholder="إسم" class="sign_inpt_elm" value="<?php esc_html($user_name); ?>" />
							<span id="user_name_error" class="error-inp-txt"></span>
						</div>
						<div class="full-width input-label-noline">
							<label for="username">إسم المستخدم</label>
							<input type="text" name="username" class="sign_inpt_elm" value="<?php esc_html($username); ?>" />
							<span id="username_error" class="error-inp-txt"></span>
						</div>
						<div class="full-width input-label-noline">
							<label for="email">البريد الإلكتروني</label>
							<input type="email" name="user_email" class="sign_inpt_elm" value="<?php esc_html($user_email); ?>" />
							<span id="user_email_error" class="error-inp-txt"></span>
						</div>
						<div class="line-elm-flex">
							<div class="r3-width">
								<label for="gender">الجنس</label>
								<select name="user_gender" class="sign_inpt_elm">
									<option selected="" disabled="">إختر جنس</option>
									<option value="male" <?php selected_val($user_gender, "male"); ?>>ذكر</option>
									<option value="female" <?php selected_val($user_gender, "female"); ?>>أنثى</option>
								</select>
								<span id="user_gender_error" class="error-inp-txt"></span>
							</div>
							<div class="r7-width">
								<label for="birth_date">تاريخ الإزدياد</label>
								<div class="line-elm-flex">
									<div class="r3-width">
										<select name="user_birth_year" class="sign_inpt_elm">
											<?php foreach (generate_nums(1930, date("Y"), "desc") as $year) : ?>
												<option value="<?php esc_html($year); ?>" <?php selected_val($year, $user_birthYear); ?>><?php esc_html($year); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="r3-width">
										<select name="user_birth_month" class="sign_inpt_elm">
											<?php foreach (generate_nums(1, 12) as $month) : ?>
												<option value="<?php esc_html($month); ?>" <?php selected_val($month, $user_birthMonth); ?>><?php esc_html(months_names($month)); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="r3-width">
										<select name="user_birth_day" class="sign_inpt_elm">
											<?php foreach (generate_nums(1, 31) as $day) : ?>
												<option value="<?php esc_html($day); ?>" <?php selected_val($day, $user_birthDay); ?>><?php esc_html($day); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="full-width input-label-noline">
							<label for="country">الدولة</label>
							<select name="user_country" class="sign_inpt_elm">
								<?php
								foreach (sort_json(get_countries(), "country_name", "asc", M_L) as $country_val) {
									$country_name = $country_val["country_name"];
									$country_code = $country_val["country_code"];
								?>
									<option value="<?php esc_html($country_code); ?>" <?php selected_val($country_code, $user_country); ?>><?php esc_html($country_name); ?></option>
								<?php
								}
								?>
							</select>
							<span id="country_error" class="error-inp-txt"></span>
						</div>
						<div class="full-width input-label-noline">
							<label for="password">كلمة المرور</label>
							<input type="password" class="sign_inpt_elm" name="user_pwd" placeholder="كلمة المرور" />
							<span id="user_pwd_error" class="error-inp-txt"></span>
						</div>
						<span class="error-inp-txt password_error"></span>
						<div class="full-width input-label-noline">
							<label for="password">تأكيد كلمة المرور</label>
							<input type="password" class="sign_inpt_elm" name="user_re_pwd" placeholder="تأكيد كلمة المرور" />
							<span id="user_re_pwd_error" class="error-inp-txt"></span>
						</div>
						<div class="full-width">
							<label for="points_remaining">أضف نقاط للعضو</label>
							<input type="number" class="sign_inpt_elm" min="1" name="points_remaining" value="" />
							<span class="error-inp-txt points_remaining_error"></span>
						</div>
						<?php
						if ($section == "edit") {
						?>
							<div class="full-width">
								<label for="manual_bagdes">أوسمة يدوية</label>
								<select name="manual_badges[]" style="min-height:200px;" multiple>
									<?php
									foreach (get_badges() as $badge_v) {
										$badge_opt = unserialize($badge_v["badge_options"]);
										if ($badge_opt["condition"] == "manual") {
											$selected_value = '';
											if (in_array($badge_v["id"], $get_meta_manual_bagdes)) {
												$selected_value = 'selected="true"';
											}
									?>
											<option value="<?php esc_html($badge_v["id"]); ?>" <?php echo $selected_value; ?>><?php esc_html(json_decode($badge_v["badge_name"])->ar); ?></option>
									<?php
										}
									}
									?>
								</select>
								<span class="error-inp-txt points_remaining_error"></span>
							</div>
							<?php
							if ($user_status == "blocked"):
							?>
								<div class="clear"></div>
								<div class="full-width">
									<label for="block_reason">سبب الحظر</label>
									<textarea name="block_reason"><?php esc_html($get_meta_block_reason); ?></textarea>
								</div>
							<?php endif; ?>
							<div class="full-width users_permissions">
								<label>صلاحيات الأدمن</label>
								<div class="table-responsive">
									<table>
										<tr>
											<td>إحصائيات</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[statistics]" value="on" id="checkbox3" class="ios-toggle" type="checkbox" <?php if ($perm_statistics == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox3" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تحكم بالاعضاء</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[users]" value="on" id="checkbox4" class="ios-toggle" type="checkbox" <?php if ($perm_members == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox4" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>روابط خارجية</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[external_links]" value="on" id="checkbox5" class="ios-toggle" type="checkbox" <?php if ($perm_external_links == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox5" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تصنيفات</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[taxonomies]" value="on" id="checkbox6" class="ios-toggle" type="checkbox" <?php if ($perm_taxonomies == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox6" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>الوصول لإعدادات حصص النشر</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[points]" value="on" id="checkbox7" class="ios-toggle" type="checkbox" <?php if ($perm_points == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox7" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>مركز الرفع</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[files]" value="on" id="checkbox8" class="ios-toggle" type="checkbox" <?php if ($perm_files == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox8" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تحكم بالمشاركات</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[posts]" value="on" id="checkbox9" class="ios-toggle" type="checkbox" <?php if ($perm_posts == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox9" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تحكم بالإعلانات</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[ads]" value="on" id="checkbox10" class="ios-toggle" type="checkbox" <?php if ($perm_ads == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox10" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تحكم بالاقسام</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[categories]" value="on" id="checkbox11" class="ios-toggle" type="checkbox" <?php if ($perm_categories == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox11" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تحكم بالصفحات</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[pages]" value="on" id="checkbox12" class="ios-toggle" type="checkbox" <?php if ($perm_pages == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox12" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>إتصل بنا</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[contact]" value="on" id="checkbox20" class="ios-toggle" type="checkbox" <?php if ($perm_contact == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox20" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>الدول</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[countries]" value="on" id="checkbox21" class="ios-toggle" type="checkbox" <?php if ($perm_countries == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox21" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>الشكاوي</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[complains]" value="on" id="checkbox22" class="ios-toggle" type="checkbox" <?php if ($perm_complains == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox22" class="checkbox-label"></label>
												</div>
											</td>
										</tr>

										<tr>
											<td>تحكم بمواقع التواصل الإجتماعي</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[social_accounts]" value="on" id="checkbox13" class="ios-toggle" type="checkbox" <?php if ($perm_social_accounts == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox13" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تحكم بالأوسمة</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[badges]" value="on" id="checkbox14" class="ios-toggle" type="checkbox" <?php if ($perm_badges == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox14" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>تحكم بالتعليقات</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[comments]" value="on" id="checkbox15" class="ios-toggle" type="checkbox" <?php if ($perm_comments == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox15" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>الوصول إلى الإعدادات العامة</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[general_settings]" value="on" id="checkbox16" class="ios-toggle" type="checkbox" <?php if ($perm_general_settings == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox16" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>الوصول إلى الإعدادات المتقدمة</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[advanced_settings]" value="on" id="checkbox17" class="ios-toggle" type="checkbox" <?php if ($perm_adv_settings == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox17" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>حدف المحتوى (السماح للأدمن بحدف المحتوى الذي له صلاحية في الوصول إليه)</td>
											<td>
												<div class="col-s-setting">
													<input name="user_permissions[delete]" value="on" id="checkbox19" class="ios-toggle" type="checkbox" <?php if ($perm_delete == "on") { ?> checked="true" <?php } ?> />
													<label for="checkbox19" class="checkbox-label"></label>
												</div>
											</td>
										</tr>
										<tr>
											<td>لغة المحتوى</td>
											<td>
												<div class="col-s-setting">
													<input name="languages_control" value="on" id="checkbox18" class="ios-toggle" type="checkbox" <?php if ($perm_languages_control == "all") { ?> checked="true" <?php } ?> />
													<label for="checkbox18" class="checkbox-label"></label>
													<select name="user_permissions[languages_control][]" multiple style="min-height:150px;">
														<?php
														foreach (get_langs() as $lang_k => $lang_v) {
															$selected_attr = '';
															if (is_array($perm_languages_control)) {
																if (in_array($lang_v["lang_code"], $perm_languages_control)) {
																	$selected_attr = ' selected="true" ';
																}
															}

														?>
															<option value="<?php echo $lang_v["lang_code"]; ?>" <?php echo $selected_attr; ?>><?php echo $lang_v["lang_name"]; ?></option>
														<?php
														}
														?>
													</select>
												</div>
											</td>
										</tr>
									</table>
								</div>
							</div>
							<input type="hidden" name="user_id" value="<?php esc_html($user_id); ?>" />
						<?php } ?>
						<input type="hidden" name="method" value="user_<?php esc_html($section); ?>" />
						<input type="hidden" name="req" value="admin" />
						<button id="submit_form" class="saveData">أضف</button>
					</form>

				</div>
			</div>
		<?php
		} elseif ($section == "group-alert") {
			include 'parts/group-alert.php';
		} elseif ($section == "settings") {
			$members_settings_default_male_image = get_option("default_male_picture");
			$members_settings_default_female_image = get_option("default_female_picture");
			$members_settings_new_members_role = get_option("new_members_role");
			$site_register = get_option("site_register");
			$cv_badge_id = get_option("cv_badge_id");
			$query_roles = get_roles();
		?>
			<div class="dash-part-form">
				<div class="full-width">
					<div class="half-width">
						<div class="add_new_role">
							<form id="form_data" method="post" enctype="multipart/form-data">
								<div class="full-width">
									<label for="">الصورة الافتراضية</label>
									<p>ذكر</p>
									<button class="upload-btn" data-input="#default_male_picture"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
									<input type="hidden" id="default_male_picture" name="default_male_picture" value="<?php esc_html($members_settings_default_male_image); ?>" />
									<div class="clear"></div>
									<div class="img-preview">
										<img src="<?php echo get_thumb($members_settings_default_male_image); ?>" id="default_male_picture_prv" />
									</div>
									<div class="clear"></div>
									<p>أنثى</p>
									<button class="upload-btn" data-input="#default_female_picture"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
									<input type="hidden" id="default_female_picture" name="default_female_picture" value="<?php esc_html($members_settings_default_female_image); ?>" />
									<div class="clear"></div>
									<div class="img-preview">
										<img src="<?php echo get_thumb($members_settings_default_female_image); ?>" id="default_female_picture_prv" />
									</div>
								</div>
								<div class="full-width">
									<label for="">رتبة مستخدم جديد</label>
									<select name="users_settings[new_members_role]">
										<?php
										foreach ($query_roles as $rolek => $rolev) {
										?>
											<option value="<?php echo $rolev["id"]; ?>" <?php if ($members_settings_new_members_role == $rolev["id"]) {
																																		echo 'selected="true"';
																																	} ?>><?php echo json_decode($rolev["role_title"])->ar; ?></option>
										<?php
										}
										?>
									</select>
								</div>
								<div class="full-width">
									<label for="">تسجيل أعضاء جدد</label>
									<div class="col-s-setting">
										<input name="users_settings[site_register]" value="on" id="checkbox1" class="ios-toggle" type="checkbox" <?php checked_val($site_register, "on"); ?> />
										<label for="checkbox1" class="checkbox-label"></label>
									</div>
								</div>
								<div class="full-width">
									<label>رقم رتبة الوسام</label>
									<input name="users_settings[cv_badge_id]" class="form-control" value="<?php esc_html($cv_badge_id); ?>" />
								</div>
								<input type="hidden" name="method" value="users_settings" />
								<button class="saveData" id="submit_form">تعديل</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		<?php
		}
		?>
	</div>
</div>