<?php

/**
 * account.php
 * User dashboard page
 */
$get_user_info = get_current_user_info();
$user_name = $get_user_info->user_name;
$username = $get_user_info->username;
$user_email = $get_user_info->user_email;
$user_gender = $get_user_info->user_gender;
$user_picture = $get_user_info->user_picture;
$user_country = $get_user_info->user_country;
$user_birth_date = $get_user_info->birth_date;
$user_pwd = $get_user_info->user_pwd;

$user_birth_date = strtotime($get_user_info->birth_date);
$user_birthDay = date("d",$user_birth_date);
$user_birthMonth = date("m",$user_birth_date);
$user_birthYear = date("Y",$user_birth_date);

$use_facebook = get_user_meta($get_user_info->id, "use_Facebook");
$use_twitter = get_user_meta($get_user_info->id, "use_Twitter");
?>
<input type="file" class="position-absolute top-0" id="upload_user_picture" accept="image/*" />
<div class="user-dashboard-account position-relative">
    <div class="my-5"></div>
    <div class="shadow-sm px-5 py-2">
        <form action="" method="POST">

            <!-- User row -->
            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4">
                    <!-- -->
                </div>
                <div class="col-lg-8">
                    <button class="btn rounded-circle p-0 position-relative btn-user-picture">
                        <img src="<?php echo get_thumb($get_user_info->user_picture); ?>" class="w-100 h-100 rounded-circle" />
                        <div class="position-absolute rounded-circle top-0 right-0 w-100 h-100 upload-user-picture-btn text-white">
                            <span>
                                <i class="fas fa-image"></i><br />
                                <small><?php echo _t("إرفع صورة"); ?></small>
                            </span>
                        </div>
                    </button>
                    <div class="progress progress-user-picture mt-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <!-- User row -->

            <!-- User row -->
            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4">
                    <label for="user_name" class="font-weight-bold"><?php echo _t("إسمك مع (إسم الاب أو اللقب)"); ?></label>
                </div>
                <div class="col-lg-8">
                    <input type="text" class="form-control w-100" name="user_name" maxlength="25" id="user_name" value="<?php esc_html($user_name); ?>" />
                    <div id="user_name_error_txt" class="invalid-feedback"></div>
                </div>
            </div>
            <!-- User row -->

            <!-- User row -->
            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4">
                    <label for="username" class="font-weight-bold"><?php echo _t("إسم المستخدم بحروف انجليزية"); ?></label>
                </div>
                <div class="col-lg-8">
                    <input type="text" class="form-control w-100" name="username" id="username" value="<?php esc_html($username); ?>" />
                    <div id="username_error_txt" class="invalid-feedback"></div>
                </div>
            </div>
            <!-- User row -->

            <!-- User row -->
            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4">
                    <label for="user_email" class="font-weight-bold"><?php echo _t("البريد الإلكتروني"); ?></label>
                </div>
                <div class="col-lg-8">
                    <input type="email" class="form-control w-100" name="user_email" id="user_email" value="<?php esc_html($user_email); ?>" />
                    <div id="user_email_error_txt" class="invalid-feedback"></div>
                </div>
            </div>
            <!-- /User row -->

            <!-- User row -->
            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4">
                    <label for="user_country" class="font-weight-bold"><?php echo _t("الدولة"); ?></label>
                </div>
                <div class="col-lg-8">
                    <select id="user_country" name="user_country" class="form-control w-100 custom-select">
                        <?php foreach (sort_json(get_countries(), "country_name", "asc") as $country) : ?>
                            <option value="<?php esc_html($country["country_code"]); ?>" <?php selected_val($user_country, $country["country_code"]); ?>><?php esc_html($country["country_name"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="user_country_error_txt" class="invalid-feedback"></div>
                </div>
            </div>
            <!-- /User row -->

            <!-- User row -->
            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4">
                    <label for="user_gender" class="font-weight-bold"><?php echo _t("الجنس"); ?></label>
                </div>
                <div class="col-lg-8">
                    <select id="user_gender" name="user_gender" class="form-control w-100 custom-select">
                        <option value="male" <?php selected_val($user_gender, "male"); ?>><?php echo _t("ذكر"); ?></option>
                        <option value="female" <?php selected_val($user_gender, "female"); ?>><?php echo _t("أنثى"); ?></option>
                    </select>
                    <div id="user_gender_error_txt" class="invalid-feedback"></div>
                </div>
            </div>
            <!-- /User row -->

            <!-- User row -->
            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4">
                    <label class="font-weight-bold"><?php echo _t("تاريخ الميلاد"); ?></label>
                </div>
                <div class="col-lg-8">
                    <div class="input-group">
                        <select id="user_birth_day" name="user_birth_day" class="form-control rounded-0 custom-select">
                            <option selected="true" disabled="true"><?php echo _t("اليوم"); ?></option>
                            <?php foreach (generate_nums(1, 31) as $day) : ?>
                                <option value="<?php esc_html($day); ?>" <?php selected_val($day,$user_birthDay); ?> ><?php esc_html($day); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="user_birth_month" name="user_birth_month" class="form-control rounded-0 custom-select">
                            <option selected="true" disabled="true"><?php echo _t("الشهر"); ?></option>
                            <?php foreach (months_names() as $month_num => $month_name) : ?>
                                <option value="<?php esc_html($month_num); ?>" <?php selected_val($month_num,$user_birthMonth); ?> ><?php esc_html($month_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="user_birth_year" name="user_birth_year" class="form-control rounded-0 custom-select">
                            <option selected="true" disabled="true"><?php echo _t("السنة"); ?></option>
                            <?php foreach (generate_nums(1940, date("Y"), "desc") as $year) : ?>
                                <option value="<?php esc_html($year); ?>" <?php selected_val($year,$user_birthYear); ?>><?php esc_html($year); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <!-- /User row -->

            <div class="form-row form-inline form-group w-100">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <p data-toggle="collapse" href="#areaChange_userpwd" role="button" aria-expanded="false" aria-controls="areaChange_userpwd"><?php echo _t("تغيير كلمة المرور"); ?></p>
                </div>
            </div>
            <div class="collapse" id="areaChange_userpwd">
                <?php if(!empty($user_pwd)): ?>
                <!-- User row -->
                <div class="form-row form-inline form-group w-100">
                    <div class="col-lg-4">
                        <label for="current_user_pwd" class="font-weight-bold"><?php echo _t("كلمة السر الحالية"); ?></label>
                    </div>
                    <div class="col-lg-8">
                        <input type="password" id="current_user_pwd" name="current_user_pwd" class="form-control w-100" />
                        <div id="current_user_pwd_error_txt" class="invalid-feedback"></div>
                    </div>
                </div>
                <!-- /User row -->
                <?php endif; ?>
                <!-- User row -->
                <div class="form-row form-inline form-group w-100">
                    <div class="col-lg-4">
                        <label for="user_pwd" class="font-weight-bold"><?php echo _t("كلمة السر الجديدة"); ?></label>
                    </div>
                    <div class="col-lg-8">
                        <input type="password" id="user_pwd" name="user_pwd" class="form-control w-100" />
                        <div id="user_pwd_error_txt" class="invalid-feedback"></div>
                    </div>
                </div>
                <!-- /User row -->

                <!-- User row -->
                <div class="form-row form-inline form-group w-100">
                    <div class="col-lg-4">
                        <label for="user_re_pwd" class="font-weight-bold"><?php echo _t("تأكيد كلمة السر"); ?></label>
                    </div>
                    <div class="col-lg-8">
                        <input type="password" id="user_re_pwd" name="user_re_pwd" class="form-control w-100" />
                        <div id="user_re_pwd_error_txt" class="invalid-feedback"></div>
                    </div>
                </div>
                <!-- /User row -->
            </div>
            <div class="form-row form-group w-100">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <button class="btn btn-lg btn-danger save-info"><?php echo _t("حفظ"); ?></button>
                </div>
            </div>
            <input type="hidden" name="method" value="user_ajax" />
            <input type="hidden" name="request" value="update_account_info" />
            <input type="hidden" name="user_picture" id="user_picture" value="<?php esc_html($user_picture); ?>" />
        </form>
    </div>
    <div class="my-5"></div>
    <div class="connected-social-account row">
        <div class="col-9 mx-auto row">
            <div class="col-lg-6">
                <div class="border text-center py-2 px-4">
                    <h6 class="h6 font-weight-bold"><?php echo _t("شبكات الإجتماعية المتاحة"); ?></h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link fb p-3 mb-3 smooth-transition" href="#"><i class="fab fa-facebook-f mr-3"></i><?php echo _t("فايسبوك"); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link tw p-3 mb-3 smooth-transition" href="#"><i class="fab fa-twitter mr-3"></i><?php echo _t("تويتر"); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="border text-center py-2 px-4">
                    <h6 class="h6  font-weight-bold"><?php echo _t("شبكات الإجنماعية على إتصال"); ?></h6>
                    <ul class="nav flex-column">

                        <?php if(!empty($use_facebook)): ?>
                        <li class="nav-item">
                            <a class="nav-link fb p-3 mb-3 smooth-transition" href="#"><i class="fab fa-facebook-f mr-3"></i><?php echo _t("فايسبوك"); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if(!empty($use_twitter)): ?>
                        <li class="nav-item">
                            <a class="nav-link tw p-3 mb-3 smooth-transition" href="#"><i class="fab fa-twitter mr-3"></i><?php echo _t("تويتر"); ?></a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6"></div>
    </div>
</div>