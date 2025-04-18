	<?php
    $points = get_option("points");
    $points = @unserialize($points);
    $t = [
        "article" => "مقال",
        "book" => "كتاب",
        "image" => "صورة",
        "video" => "فيديو",
        "research" => "بحث",
        "audio" => "صوتية",
        "history" => "تاريخ",
        "dictionary" => "قاموس",
        "daily_login" => "دخول يومي",
        "posts_views" => "مشاهدات",
        "posts_comments" => "تعليقات",
        "share" => "مشاركة",
        "like" => "إعجاب",
        "rate5s" => "تقييم 5 نجوم",
        "openaccount" => "فتح حساب جديد",
        "name" => "إسم",
        "quote" => "حكمة",
        "author_article" => "مقال كاتب"
    ];
    ?>
	<div class="dash-part-form">
	    <div class="full-width">
	        <form method="post" id="form_data">
	            <div class="notices">
	                <p>عدد المحتوى : يتم أخد بعين إعتبار خانة عدد المحتوى لعدد المحتوى اللازم من أجل الحصول على النقاط. إذ لم يكن المحتوى مربوط بعدد المحتوى المرجو ترك خانة عدد المحتوى فارغ</p>
	            </div>
	            <div class="clear"></div>
	            <div class="table-responsive">
	                <table class="table_parent">
	                    <tbody>

	                        <tr>
	                            <th>نوع المحتوى</th>
	                            <th>نقاط المكافئة</th>
	                            <th>نقاط الخصم</th>
	                            <th>عدد المحتوى</th>
	                        </tr>
	                        <?php
                            foreach ($points["add"] as $points_k => $points_v) {
                                $points_add_val = $points["add"][$points_k];
                                $points_substract_val = $points["substract"][$points_k];
                                $points_condition_val = $points["condition"][$points_k];
                            ?>
	                            <tr>
	                                <td><?php echo $t[$points_k]; ?></td>
	                                <td><input type="text" name="points_add[<?php esc_html($points_k); ?>]" value="<?php esc_html($points_add_val); ?>" class="r3-width" /></td>
	                                <td><input type="text" name="points_substract[<?php esc_html($points_k); ?>]" value="<?php esc_html($points_substract_val); ?>" class="r3-width" /></td>
	                                <td><input type="text" name="apply_add[<?php esc_html($points_k); ?>]" value="<?php esc_html($points_condition_val); ?>" class="r3-width" /></td>
	                            </tr>
	                        <?php
                            }
                            ?>

	                    </tbody>
	                </table>
	            </div>
	            <input type="hidden" name="method" value="points" />
	            <button id="submit_form" class="saveData">تحديث</button>
	        </form>
	    </div>
	</div>