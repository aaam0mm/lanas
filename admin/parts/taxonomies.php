<?php
$action = $_GET["action"] ?? "";
if (!$action):
?>
    <div class="dash-part seo-part">
        <div class="dash-part-form">
            <div class="table-responsive">
                <table class="parent_table">
                    <tr>
                        <th></th>
                        <th>إسم التصنيف</th>
                        <th>الإجراءات</th>
                    </tr>
                    <?php
                    foreach (get_all_taxonomies() as $taxo_key => $taxo_val) {
                        $taxo_id = $taxo_val["id"];
                        $taxo_title = json_decode($taxo_val["taxo_title"]);
                        $taxo_title = $taxo_title->{M_L};
                    ?>
                        <tr>
                            <td></td>
                            <td><?php echo esc_html($taxo_title); ?></td>
                            <td>
                                <table class="table_child">
                                    <tr>
                                        <td><button class="action_stg edit-st-btn open-url" title="تعديل" data-url="dashboard/taxonomies?action=edit&taxo_id=<?php esc_html($taxo_id); ?>" id=""><i class="fa fa-cog"></i></button></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($action == "edit"):
    $taxonomy_id = $_GET["taxo_id"];
    $get_taxonomy = get_taxonomy($taxonomy_id);
    $image_resulotion = @unserialize(get_settings("min_resolution_" . $get_taxonomy["taxo_type"] . "_image"));
    $minWidth_image_resulotion = $image_resulotion["width"];
    $minHeight_image_resulotion = $image_resulotion["height"];
    $taxo_terms = @json_decode($get_taxonomy["taxo_terms"]);
    $taxo_settings = @unserialize($get_taxonomy["taxo_settings"] ?? []);
    if (is_array($taxo_settings)) {
        extract($taxo_settings);
    }

    // taxonomy settings
    $copy_s = $copy ?? "yes";
    $comment_s = $comment ?? "all";
    // taxonomy terms
    $content_page = $taxo_terms->content_page ?? "";
    $alert = $taxo_terms->alert ?? "";
    $terms = $taxo_terms->terms ?? "";
    $authorized_sources = $taxo_terms->authorized_sources ?? "";
    $text_file_local_download = $taxo_terms->text_file_local_download ?? "";
?>
    <div class="dash-part-form">
        <div class="full-width">
            <div class="r7-width">
                <form method="post" id="form_data">
                    <div class="full-width">
                        <label for="taxo_icon">أيقونة</label>
                        <div class="notices">
                            <p>جلب الأيقونة من <a href="http://fontawsome.com/v4.0.7/">fontawesome 5</a></p>
                            <p>كتابة إسم الأيقونة على الشكل fa-{iconname} (بدون {})</p>
                        </div>
                        <div class="clear"></div>
                        <input type="text" name="taxo_icon" placeholder="fa-{iconname}" value="<?php echo $get_taxonomy["taxo_icon"]; ?>" />
                    </div>
                    <div class="full-width">
                        <label for="">نص البحث عن التلخيص chatgpt</label>
						<div class="notices not-before my-3">
							<p>الاستعمال الصحيح للمعلومات المتغيرة في النص:</p>
							<p>- لأضافة عنوان الكتاب اضف العلامة {t}</p>
							<p>- لأضافة مؤلف الكتاب اضف العلامة {a}</p>
							<p>- لأضافة لغة الكتاب اضف العلامة {l}</p>
						</div>
						<textarea cols="30" rows="10" name="taxo_setting[chat_gpt_text]" class="form-control" style="min-height: 131px;"><?php echo $chat_gpt_text ?? ""; ?></textarea>
					</div>
                    <div class="full-width">
                        <label for="title">إسم الصنف</label>
                        <?php multi_input_languages("taxo_title", "text", $get_taxonomy["taxo_title"]); ?>
                    </div>
                    <div class="full-width">
                        <label for="max_image_up_size">الأبعاد دنوية لرفع صورة في موضيع الصنف</label>
                        <div class="notices">
                            <p>الأبعاد تكون ب pixel</p>
                        </div>
                        <div class="clear"></div>
                        <div class="line-elm-flex">
                            <div class="r25-width">
                                <input type="number" name="meta[min_resolution_<?php echo $get_taxonomy["taxo_type"]; ?>_image][width]" placeholder="العرض" value="<?php echo $minWidth_image_resulotion; ?>" />
                            </div>
                            <div class="r25-width">
                                <input type="number" name="meta[min_resolution_<?php echo $get_taxonomy["taxo_type"]; ?>_image][height]" placeholder="الطول" value="<?php echo $minHeight_image_resulotion; ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="full-width">
                        <label for="title">إسم الصنف (الإضافة)</label>
                        <div class="notices">
                            <p>النص الذي يظهر عند ظهور صندوق إضافة المحتوى (مثل أضف مقال)</p>
                        </div>
                        <div class="clear"></div>
                        <?php multi_input_languages("taxo_add_title", "text", $get_taxonomy["taxo_add_title"]); ?>
                    </div>
                    <div class="full-width">
                        <label for="title">تنبيه صفحة المحتوى</label>
                        <?php multi_input_languages("taxo_notice", "text", $get_taxonomy["taxo_notice"]); ?>
                    </div>
                    <div class="full-width">
                        <label for="title">نص إضافة محتوى</label>
                        <div class="notices">
                            <p>النص الذي يظهر عند محاولة إضافة محتوى في الصنف</p>
                        </div>
                        <div class="clear"></div>
                        <?php multi_input_languages("taxo_add_text", "textarea", $get_taxonomy["taxo_add_text"]); ?>
                    </div>
                    <div class="full-width">
                        <label for="term_alert">تنبيه أقسام الصنف</label>
                        <?php multi_input_languages("terms[alert]", "textarea", $alert); ?>
                    </div>
                    <div class="full-width">
                        <label for="term_terms">قوانين الصنف</label>
                        <?php multi_input_languages("terms[terms]", "textarea", $terms); ?>
                    </div>
                    <div class="full-width">
                        <label for="term_authorized_sources">مصادر معتمدة</label>
                        <?php multi_input_languages("terms[authorized_sources]", "textarea", $authorized_sources); ?>
                    </div>
                    <div class="full-width">
                        <label for="term_text_file_local_download">نص اضافي في نافذة تحميل الكتاب</label>
                        <?php multi_input_languages("terms[text_file_local_download]", "textarea", $text_file_local_download); ?>
                    </div>
                    <div class="full-width categories-adv-s">
                        <label for="copy">تعطيل النسخ</label>
                        <div class="full-width line-elm-flex">
                            <div class="col-s-setting">
                                <input name="taxo_setting[copy]" value="on" id="taxo_setting1" class="ios-toggle" type="checkbox" <?php checked_val($copy_s, "on"); ?> />
                                <label for="taxo_setting1" class="checkbox-label"></label>
                            </div>
                        </div>
                        <label for="">التعليق على المواضيع</label>
                        <div class="full-width line-elm-flex">
                            <div class="col-s-setting">
                                <input name="taxo_setting[comment]" value="on" id="taxo_setting2" class="ios-toggle" type="checkbox" <?php checked_val($comment_s, "on"); ?> />
                                <label for="taxo_setting2" class="checkbox-label"></label>
                            </div>
                        </div>
                    </div>
                    
                    <button id="submit_form" class="saveData">أضف</button>
                    <input type="hidden" name="method" value="taxonomies" />
                    <input type="hidden" name="action" value="<?php esc_html($action); ?>" />
                    <input type="hidden" name="taxo_id" value="<?php esc_html($taxonomy_id); ?>" />
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>