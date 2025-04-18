<?php
$action = $_GET["action"] ?? 'default';
if ($action == "default") {
    $q = $_GET["q"] ?? "";
    $per_page = $_GET["per_page"] ?? 50;
    // $filter_type = $_GET["filter_type"] ?? false;
    $filter_post_type = $_GET["filter_post_type"] ?? false;
    // $filter_status = $_GET["filter_status"] ?? "";
    // $filter_post_author = $_GET["filter_post_author"] ?? "";
    $filter_category = $_GET["filter_category"] ?? "";
    // $filter_post_id = $_GET["filter_post_id"] ?? "";
    $filter_lang = $_GET["filter_lang"] ?? false;
    $args = [
        "post_status" => false,
        'order' => ['post_info.id', 'desc'],
        // 'post_in' => $filter_type,
        'post_type' => $filter_post_type,
        'limit' => $per_page,
    ];

    if ($filter_status == "newset") {
        $args['order'] = ['post_date_gmt', 'desc'];
    } elseif ($filter_status == "pending" || $filter_status == "approval" || $filter_status == "publish") {
        $args['post_status'] = $filter_status;
    } elseif ($filter_status == "closed") {
        $args['post_status'] = ['closed', 'blocked'];
    }
    if ($filter_post_author) {
        $args['post_author'] = $filter_post_author;
    }
    if ($filter_post_id) {
        $args['info_id'] = $filter_post_id;
    }
    if ($filter_lang) {
        $args['post_lang'] = $filter_lang;
    }

    if ($q) {
        $args['post_key'] = $q;
    }

    if ($filter_category) {
        $args['post_category'] = $filter_category;
    }
    $query_posts = new Query_post(false);
    // $query_posts->do_query_count = true;
    $query_posts->set_post_info_data($args);
    $get_posts_all = $query_posts->get_info() ?? [];
    $posts_categories = $get_posts_all ? get_posts_categories_for_info(array_column($get_posts_all, 'id')) : [];
    $posts_categories_title = get_posts_categories_name($posts_categories);
    
?>
    <div class="dash-part-form">
        <!-- Modal -->
        <div class="modal fade" id="addProgramFetchModal" tabindex="-1" role="dialog" aria-labelledby="addProgramFetchModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProgramFetchModalLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="container mt-5">
                            <form action="saveProgram" id="save-program" method="POST">
                                <div class="form-group">
                                    <label>اختر نوع الجدولة:</label>
                                    <div>
                                    <input type="radio" name="schedule_type" id="every_day" value="every" checked>
                                    <label for="every_day">كل يوم</label>
                                    </div>
                                    <div>
                                    <input type="radio" name="schedule_type" id="program_date" value="program">
                                    <label for="program_date">أيام محددة</label>
                                    </div>
                                </div>

                                <!-- Date and Time input for Program Date -->
                                <div class="form-group d-none" id="program_date_section">
                                    <label for="program_date_input">حدد الأيام:</label>
                                    <input type="text" id="program_date_input" class="form-control datepicker">
                                </div>

                                <div class="form-group" id="program_time_section">
                                    <label for="program_time_input">حدد الوقت:</label>
                                    <input type="time" name="program_time" id="program_time_input" class="form-control">
                                </div>

                                <input type="hidden" name="info_id" id="program-info-id">

                                <button type="submit" class="btn btn-primary">إرسال</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- / Modal -->
        <!-- modal of fetch details -->
        <div class="modal fade" id="fetchDetailsModal" tabindex="-1" role="dialog" aria-labelledby="fetchDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fetchDetailsModalLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="container my-4">
                            <h4>عرض التفاصيل</h4>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>عدد النجاحات</th>
                                        <th>عدد الفشل</th>
                                        <th>منشورات تم تجاهلها</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end of modal of fetch details -->
        <div class="full-width">
            <form action="" method="get" id="form_filter">
                <div class="page-action">
                    <div class="pull-right">
                        <a href="dashboard/contents?action=add" id="btn_link">اضافة رابط</a>
                    </div>
                    <div class="pull-left">
                        <div class="line-elm-flex">
                            <div class="7r-width">
                                <input type="text" name="q" placeholder="إبحث عن موضوع" value="<?php echo $q; ?>" />
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
                            <select name="filter_lang" class="on_change_submit">
                                <option value="">كل اللغات</option>
                                <?php
                                foreach (get_langs() as $lang_k => $lang_v) {
                                    $lang_code = $lang_v["lang_code"];
                                    $lang_name = $lang_v["lang_name"];
                                    $selected_attr = "";
                                    if ($lang_code == $filter_lang) {
                                        $selected_attr = 'selected="true"';
                                    }

                                ?>
                                    <option value="<?php esc_html($lang_code); ?>" <?php echo $selected_attr; ?>><?php echo $lang_name; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="7r-width">
                            <select name="filter_post_type" class="on_change_submit">
                                <option value="">عرض الجميع</option>
                                <?php foreach (get_all_taxonomies() as $taxo_val): ?>
                                    <option value="<?php echo $taxo_val["taxo_type"]; ?>" <?php if ($filter_post_type == $taxo_val["taxo_type"]) {
                                        echo 'selected="true"';
                                        } ?>><?php echo get_taxonomy_title($taxo_val["taxo_type"], 'ar'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="7r-width">
                            <select name="filter_category" class="on_change_submit">
                                <option value="">عرض الجميع</option>
                                <?php foreach (get_categories($filter_post_type, null, $filter_lang) as $cat_info_k => $cat_info_v): ?>
                                    <option value="<?php echo $cat_info_v["id"]; ?>" <?php selected_val($filter_category, $cat_info_v['id']); ?>><?php echo $cat_info_v['cat_title']; ?></option>
                                <?php endforeach; ?>
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
                                <select name="action" id="pick_action" class="">
                                    <option value="delete">حدف</option>
                                    <option value="verify">تحقق</option>
                                    <option value="lock">حظر</option>
                                    <option value="publish">نشر</option>
                                    <?php if (!empty($filter_lang) && !empty($filter_post_type)): ?>
                                        <option value="move">نقل</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <?php if (!empty($filter_lang) && !empty($filter_post_type)): ?>
                                <div class="r3-width pick-cat-tomove">
                                    <select name="category" id="move_post_cat">
                                        <option value="" disabled="true" selected="true"></option>
                                        <?php foreach (get_categories($filter_post_type, null, $filter_lang) as $cat_info_k => $cat_info_v): ?>
                                            <option value="<?php echo $cat_info_v["id"]; ?>"><?php echo $cat_info_v['cat_title']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="r3-width">
                                <input type="submit" value="تنفيذ" class="btn_action submit-action" />
                            </div>
                        </div>
                        <input type="hidden" name="target" value="post_infos" />
                        <input name="method" value="multi_action" type="hidden">
                    </div>
                </form>
            </div>
            <div class="clear"></div>
            <div class="full-width">
                <?php if ($get_posts_all) { ?>
                    <div class="table-responsive">
                        <table class="table_parent">
                            <tr>
                                <th><input type='checkbox' class="select-checkbox-all check-all-multi" /></th>
                                <th>المصدر</th>
                                <th>اللغة</th>
                                <th>القسم</th>
                                <th>عدد المواضيع المجلوبة</th>
                                <th>تاريخ الاضافة</th>
                                <th>برنامج</th>
                                <th>الإجراءات</th>
                            </tr>
                            <?php
                            foreach ($get_posts_all as $value) {
                                $post_id = $value["id"];
                                $post_key = $value["post_key"];
                                $post_category = get_category_by_ids([$value["post_category"]])[0]['cat_title'];
                                $post_date = $value["post_date_gmt"];
                                $post_source = $value["post_source_1"];
                                $program = $dsql->dsql()->table('post_info_program')->where('post_info_id', $post_id)->limit(1)->getRow();

                                $program_time = null;
                                if($program) {
                                    $program_details = json_decode($program['program'], true);
                                    if(count($program_details) > 0) {
                                        $program_time = $program_details[0]['time'];
                                    }
                                }
                                if($post_source) {
                                    if (preg_match('/^(.*?):(https?:\/\/.+)$/', $post_source, $matches)) {
                                        $text = $matches[1]; // First part before colon
                                        $post_source = $text;
                                    }
                                }

                                $number_art = $value["number_art"];
                                $post_type = $value["post_type"];
                                $post_lang = $value["post_lang"];
                                $post_author = $value["post_author"];
                                $post_in = $value["post_in"];
                                $post_author_name = get_user_field($post_author, "user_name");
                                // case 1 active role
                                // case 2 unactive role
                                $post_status  = $value["post_status"];
                                if ($post_status == "publish") {
                                    $lock_btn_class = "fa-lock-open";
                                    $lock_action_tooltip = "حظر";
                                } else {
                                    $lock_btn_class = "fa-lock";
                                    $lock_action_tooltip = "إلغاء الحظر";
                                }
                                $ar_translate_posts_status = array("publish" => "مفعل", "approval" => "بإنتظار المراجعة", "pending" => "بإنتظار الموافقة", "canceled" => "ملغي");
                                $post_cats = '';
                                if (isset($posts_categories_title[$post_id]) && is_array($posts_categories_title[$post_id])) {
                                    $post_cats = '(' . implode(',', $posts_categories_title[$post_id]) . ')';
                                }
                                // if($program_time) {
                                //     $date = new DateTime($program_time, new DateTimeZone('UTC')); // Assume time is in UTC
                                //     $timestamp = $date->getTimestamp();
                                // echo $program_time ? 'data-time="'. $timestamp .'"' : '';
                                // }
                            ?>
                                <tr>
                                    <td><input type='checkbox' class="select-checkbox check-box-action" data-id="<?php esc_html($post_id); ?>" /></td>
                                    <td><?php echo esc_html($post_source) ?? ""; ?></td>
                                    <td><?php esc_html($post_lang); ?></td>
                                    <td>
                                        <?php echo esc_html($post_category); ?>
                                    </td>
                                    <td>
                                        <a href="#" data-infoid="<?=$post_id;?>" data-target="#fetchDetailsModal" data-toggle="modal" title="عرض التفاصيل">
                                            <?php echo esc_html($number_art) ?>
                                        </a>
                                    </td>
                                    <td><?php echo get_timeago(strtotime($post_date)); ?></td>
                                    <td><span class="badge badge-danger"><?php echo $program_time ?? '-'; ?></span></td>
                                    <td>
                                        <table class="table_child">
                                            <tr>
                                                <td><button class="action_stg fetch-btn btn-success position-relative" title="ابدأ الجلب" data-id="<?php esc_html($post_id); ?>" data-method="startScraping"><i class="fas fa-magnet"></i></button></td>

                                                <td class="d-none"><button class="action_stg fetch-btn btn-danger position-relative" title="اوقف الجلب" data-id="<?php esc_html($post_id); ?>" data-method="stopScraping"><i class="fas fa-stop"></i></button></td>

                                                <td><button class="action_stg edit-st-btn open-url" data-url="<?php echo siteurl() . "/admin/dashboard/contents?action=edit&info_id=" . $post_id; ?>" title="تعديل"><i class="fas fa-cog"></i></button></td>
                                                <td><button data-infoid="<?=$post_id;?>" data-target="#addProgramFetchModal" data-toggle="modal" class="action_stg time-btn btn-info" title="اضافة برنامج"><i class="fas fa-clock"></i></button></td>

                                                <td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=link&id=<?php echo $post_id; ?>"><i class="fas fa-trash"></i></button></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </div>
                    <?php get_pagination($query_posts->count_results(), $per_page); ?>
                    <!-- <script>
                        function convertUTCToLocalTime(utcTimestamp) {
                            let date = new Date(utcTimestamp * 1000); // Convert to milliseconds
                            return date.toLocaleString(); // Returns time in user's timezone
                        }
                        $(`[data-time]`).each(function() {
                            let span = $(this);
                            let timeStamp = span.data('time');
                            span.text(convertUTCToLocalTime(timeStamp));
                        });
                    </script> -->
                <?php
                } else {
                ?>
                    <div class="no_posts">لاتوجد أي مواضيع حاليا.</div>
                <?php
                }
                ?>
            </div>
            <div class="clear"></div>
            <!-- <div class="form-group mt-3 mb-0">
                <button data-type="get-data"  type="button" class="btn btn-success">جلب</button>
            </div> -->
            <div data-type="form-container" class=""></div>
        </div>
    </div>
<?php
} elseif ($action == "edit" || $action == "add") {
    $visible_to_s = $visible_to ?? "all";
    $sort_s = $sort ?? "new";
    $users = get_users(null,"desc", ['limit' => "all"]);
    usort($users['results'], function($a, $b) {
        return strcmp($a['user_name'], $b['user_name']);
    });
    $info = [];
    if(isset($_GET['info_id'])) {
        $info = $dsql->dsql()->table("post_info")->where('id', $_GET['info_id'])->limit(1)->getRow();
    }
    // dd($info);
?>
    <div class="full-width w-100 p-3">
        <form action="saveLink" method="post" id="save_data_form">
            <div class="full-width">
                <label for="language">اختيار اللغة</label>
                <select name="language" id="language">
                    <option value="0" selected="true">إختر اللغة</option>
                    <?php
                    foreach (get_langs() as $lang_k => $lang_v) {
                        $lang_code = $lang_v["lang_code"];
                        $lang_name = $lang_v["lang_name"];
                        $selected_language = $lang_v["lang_code"];
                        $selected = isset($info['post_lang']) && $info['post_lang'] == $lang_code ? 'selected="true"' : '';
                    ?>
                        <option value="<?php esc_html($lang_code); ?>" <?= $selected ;?>><?php echo $lang_name; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>

            <div class="full-width">
                <label for="type">اختيار الصنف</label>
                <select name="type" id="type">
                    <option selected="true" disabled="true">إختر الصنف</option>
                    <option value="article">مقالات</option>
                    <option value="book">كتب</option>
                </select>
            </div>

            <?php
                if(isset($info['post_type'])) {
                    ?>
                    <script>
                        $(document).ready(function() {
                            let type = <?php echo json_encode($info['post_type']); ?>;
                            // Set the value of the select to the categories array
                            $("#type").val(type).trigger('change');
                        });
                    </script>
                    <?php
                }
            ?>

            <div class="full-width">
                <label for="department">اختيار القسم</label>
                <select name="department" id="department">
                    <option value="0" selected="true" disabled="true">إختر القسم</option>
                </select>
            </div>
            
            <script>
                function getCats(keys, selector, select2 = false) {
                    let data = { action: 'getcatlang' };

                    // Merge keys into data
                    keys.forEach(obj => {
                        Object.keys(obj).forEach(key => {
                            data[key] = obj[key]; // Assign key-value pair to data
                        });
                    });

                    $.ajax({
                        url: 'admin-ajax.php',
                        type: 'POST',
                        data: data,
                        success: function(response) {
                            selector.html(`<option value="0" selected="true">اختر قسما</option>`);
                            let cats = JSON.parse(response);
                            if (cats.length > 0) {
                                for (let cat of cats) {
                                    selector.append(`<option value="${cat.id}">${cat.cat_title}</option>`);
                                }

                                $(document).trigger('categories-loaded');

                                if (select2) {
                                    selector.select2({
                                        dir: "rtl",
                                        width: '100%',
                                        language: 'ar',
                                        placeholder: "<?php echo _t('اختر قسما'); ?>",
                                        dropdownAutoWidth: true,
                                        dropdownParent: $('#post-categoy-select2')
                                    });
                                }
                            }
                        }
                    });
                }

                // select book part or article
                $(`#save_data_form [name="language"], #save_data_form [name="type"]`).unbind().on('change', function() {
                    let keys = [
                    {
                        'language': $(`#save_data_form [name="language"]`).val()
                    },
                    {
                        'texo': $(`#save_data_form [name="type"]`).val()
                    }
                    ]
                    getCats(keys, $(`#save_data_form #department`));
                });

                if(window.location.href.match(/contents\?action=edit/)) {
                    $(`#save_data_form [name="language"]`).trigger("change");
                    $(`#save_data_form [name="type"]`).trigger("change");
                    <?php
                    if(isset($info['post_category'])) {
                        ?>
                        let post_category = <?php echo json_encode($info['post_category']) ?>;
                        setTimeout(function() {
                            $(`#save_data_form #department`).val(post_category).trigger('change');
                        }, 500);
                        <?php
                    }
                    ?>
                }
            </script>
            

            <div id="post-info-author-select2" class="form-group m-2" data-select2-id="post-info-author-select2">
                <label for="account" class="font-weight-bold"><?php echo _t("اختيار حساب"); ?></label>
                <select name="account" id="account" class="form-control">
                    <?php foreach ($users['results'] as $user_k => $user_v):
                        $user_code = $user_v["id"];
                        $user_name = $user_v["user_name"];
                    ?>
                        <option value="<?php esc_html($user_code); ?>">
                            <?php esc_html($user_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="post-info-author-select2_error_txt" class="invalid-feedback d-block"></div>
            </div>

            <?php
                if(isset($info['post_author'])) {
                    ?>
                    <script>
                        $(document).ready(function() {
                            let user = <?php echo json_encode($info['post_author']); ?>;
                            // Set the value of the select to the categories array
                            $("#account").val(user).trigger('change');
                            setTimeout(function() {
                                $("#account").val(user).trigger('change');
                            }, 500);
                        });
                    </script>
                    <?php
                }
            ?>


            <div class="full-width">
                <label for="url">الصق رابط الجلب هنا</label>
                <input type="text" name="url" id="url" placeholder="يدعم روابط الجلب (web scraping)" value="<?php echo $info['post_fetch_url'] ?? '';?>" />
            </div>

            <div class="full-width categories-adv-s">
                <label>نشر تلقائي</label>
                <div class="full-width line-elm-flex">
                    <div class="col-s-setting">
                        <input name="auto_share" value="on" id="auto_share" class="ios-toggle" type="checkbox" <?php checked_val($info['post_status'], "publish"); ?> />
                        <label for="auto_share" class="checkbox-label"></label>
                    </div>
                </div>
                <label data-action="toggle_label" class="<?= isset($info['post_type']) && $info['post_type'] == 'book' ? ' d-none' : ''; ?>"><?php echo _t("بدون الصورة"); ?></label>
                <div data-action="toggle_show_article" class="full-width line-elm-flex <?= isset($info['post_type']) && $info['post_type'] == 'book' ? ' d-none' : ''; ?>">
                    <div class="col-s-setting">
                        <input name="show_pic" value="on" id="show_pic" class="ios-toggle" type="checkbox" <?php checked_val($info['post_show_pic'], "on");?> />
                        <label for="show_pic" class="checkbox-label"></label>
                    </div>
                </div>
                <label data-action="toggle_label" class="<?= isset($info['post_type']) && $info['post_type'] == 'article' ? ' d-none' : ''; ?>"><?php echo _t("كتاب للمراجعة فقط بدون pdf"); ?></label>
                <div data-action="toggle_show_book" class="full-width line-elm-flex <?= isset($info['post_type']) && $info['post_type'] == 'article' ? ' d-none' : ''; ?>">
                    <div class="col-s-setting">
                        <input name="book_without_pdf" value="on" id="book_without_pdf" class="ios-toggle" type="checkbox" <?php checked_val($info['book_without_pdf'], "on"); ?> />
                        <label for="book_without_pdf" class="checkbox-label"></label>
                    </div>
                </div>
            </div>

            <div class="full-width">
                <label for="count">عدد الجلب في اليوم</label>
                <input type="number" name="count" id="count" placeholder="-" value="<?php echo $info['number_fetch'] ?? '';?>" />
            </div>

            <div class="full-width">
                <label for="source1">المصدر الأول</label>
                <input type="text" name="source1" id="source1" placeholder="اكتب المصدر الأول" value="<?php echo $info['post_source_1'] ?? '';?>" />
            </div>

            <div class="full-width">
                <label for="source2">المصدر الثاني</label>
                <input type="text" name="source2" id="source2" placeholder="المصدر الثاني" value="<?php echo $info['post_source_2'] ?? '';?>" />
            </div>

            <?php
            if(isset($info['id'])) {
                echo '<input type="hidden" name="info_id" value="'. $info['id'] .'">';
            }
            ?>

            <div class="full-width my-2">
                <button type="submit" class="btn btn-success">حفظ</button>
            </div>
        </form>
        
    </div>
    <?php } elseif($action == 'fetch') {
        $q = $_GET["q"] ?? "";
        $per_page = $_GET["per_page"] ?? 50;
        $filter_type = $_GET["filter_type"] ?? false;
        $filter_post_type = $_GET["filter_post_type"] ?? false;
        $filter_status = $_GET["filter_status"] ?? 'auto-draft' ?? "draft";
        $filter_post_author = $_GET["filter_post_author"] ?? "";
        $filter_category = $_GET["filter_category"] ?? "";
        $filter_post_id = $_GET["filter_post_id"] ?? "";
        $filter_lang = $_GET["filter_lang"] ?? false;
        $filter_fetch = isset($_GET["filter_fetch"]) && $_GET["filter_fetch"] == 1 ? 'true' : 'false';
        $args = [
            "post_status" => false,
            // "post_status__not" => ['publish'],
            'order' => ['posts.id', 'desc'],
            'post_in' => $filter_type,
            'post_type' => $filter_post_type,
            'limit' => $per_page,
        ];
        
        if (admin_authority()->languages_control != "all") {
            if (is_array(admin_authority()->languages_control)) {
                $args['post_lang'] = admin_authority()->languages_control;
            } else {
                $args['post_lang'] = false;
            }
        } else {
            $args['post_lang'] = false;
        }

        if ($filter_fetch == 'true') {
            $args['fetch'] = 'true';
        }
        
        if ($filter_status == "newset") {
            $args['order'] = ['post_date_gmt', 'desc'];
        } elseif ($filter_status == "special") {
            $args['in_special'] = 'on';
        } elseif ($filter_status == "pending" || $filter_status == "approval" || $filter_status == "publish" || $filter_status == "draft" || "auto-draft") {
            $args['post_status'] = $filter_status;
        } elseif ($filter_status == "closed") {
            $args['post_status'] = ['closed', 'blocked'];
        }
        if ($filter_post_author) {
            $args['post_author'] = $filter_post_author;
        }
        if ($filter_post_id) {
            $args['post_id'] = $filter_post_id;
        }
        if ($filter_lang) {
            if (is_array(admin_authority()->languages_control)) {
                if (in_array($filter_lang, admin_authority()->languages_control)) {
                    $args['post_lang'] = $filter_lang;
                }
            }
        }
        if ($q) {
            $args['post_title'] = $q;
        }
        
        if ($filter_category) {
            $args['post_category'] = $filter_category;
        }

        $query_posts = new Query_post($args);
        $query_posts->do_query_count = true;
        $get_posts_all = $query_posts->get_posts();
        
        $posts_categories = !is_null($get_posts_all) ? get_posts_categories(array_column($get_posts_all, 'id')) : [];
        $posts_categories_title = get_posts_categories_name($posts_categories);
        ?>
        <div class="dash-part-form">
            <div class="full-width">
                <form action="" method="get" id="form_filter" class="fetch">
                    <input type="hidden" name="action" value="fetch">
                    <div class="page-action">
                        <div class="pull-left">
                            <div class="line-elm-flex">
                                <div class="7r-width">
                                    <input type="text" name="q" placeholder="إبحث عن موضوع" value="<?php echo $q; ?>" />
                                </div>
                                <div class="r3-width">
                                    <button form="form_filter" id="search_btn"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="panel_filter">
                        <div class="pull-right">
        
                        </div>
                        <div class="pull-left line-elm-flex">
                            <div class="7r-width">
                                <select name="filter_lang" class="on_change_submit">
                                    <option value="">كل اللغات</option>
                                    <?php
                                    foreach (get_langs() as $lang_k => $lang_v) {
                                        $lang_code = $lang_v["lang_code"];
                                        $lang_name = $lang_v["lang_name"];
                                        $selected_attr = "";
                                        if ($lang_code == $filter_lang) {
                                            $selected_attr = 'selected="true"';
                                        }
        
                                    ?>
                                        <option value="<?php esc_html($lang_code); ?>" <?php echo $selected_attr; ?>><?php echo $lang_name; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="7r-width">
                                <select name="filter_status" class="on_change_submit">
                                    <option value="" <?php if ($filter_status == "all" or (!$filter_status)) {
                                                            echo 'selected="true"';
                                                        }  ?>>عرض الجميع</option>
                                    <option value="pending" <?php if ($filter_status == "pending") {
                                                                echo 'selected="true"';
                                                            } ?>>مواضيع بإنتظار الموافقة</option>
                                    <option value="approval" <?php if ($filter_status == "approval") {
                                                                    echo 'selected="true"';
                                                                } ?>>مواضيع بإنتظار المراجعة</option>
                                    <option value="closed" <?php if ($filter_status == "closed") {
                                                                echo 'selected="true"';
                                                            } ?>>مواضيع ملغية</option>
                                    <option value="special" <?php if ($filter_status == "special") {
                                                                echo 'selected="true"';
                                                            } ?>>مواضيع مميزة</option>
                                    <option value="newset" <?php if ($filter_status == "newset") {
                                                                echo 'selected="true"';
                                                            } ?>>مواضيع جديدة</option>
                                    <option value="auto-draft" <?php if ($filter_status == "auto-draft") {
                                                                echo 'selected="true"';
                                                            } ?>>المسودات</option>
                                </select>
                            </div>
                            <div class="7r-width">
                                <select name="filter_post_type" class="on_change_submit">
                                    <option value="">عرض الجميع</option>
                                    <?php foreach (get_all_taxonomies() as $taxo_val): ?>
                                        <option value="<?php echo $taxo_val["taxo_type"]; ?>" <?php if ($filter_post_type == $taxo_val["taxo_type"]) {
                                                                                                    echo 'selected="true"';
                                                                                                } ?>><?php echo get_taxonomy_title($taxo_val["taxo_type"]); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if (!empty($filter_post_type)): ?>
                                <div class="7r-width">
                                    <select name="filter_category" class="on_change_submit">
                                        <option value="">عرض الجميع</option>
                                        <?php foreach (get_categories($filter_post_type, null, $filter_lang) as $cat_info_k => $cat_info_v): ?>
                                            <option value="<?php echo $cat_info_v["id"]; ?>" <?php selected_val($filter_category, $cat_info_v['id']); ?>><?php echo $cat_info_v['cat_title']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="7r-width">
                                <select name="filter_fetch" class="on_change_submit">
                                    <option value="0">عرض الجميع</option>
                                    <option value="1" <?php if ($filter_fetch == "true") {
                                                                echo 'selected="true"';
                                                            } ?>>مجلوبة تلقائيا</option>
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
                                    <select name="action" id="pick_action" class="">
                                        <option value="delete">حدف</option>
                                        <option value="verify">تحقق</option>
                                        <option value="lock">حظر</option>
                                        <option value="publish">نشر</option>
                                        <?php if (!empty($filter_lang) && !empty($filter_post_type)): ?>
                                            <option value="move">نقل</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php if (!empty($filter_lang) && !empty($filter_post_type)): ?>
                                    <div class="r3-width pick-cat-tomove">
                                        <select name="category" id="move_post_cat">
                                            <option value="" disabled="true" selected="true"></option>
                                            <?php foreach (get_categories($filter_post_type, null, $filter_lang) as $cat_info_k => $cat_info_v): ?>
                                                <option value="<?php echo $cat_info_v["id"]; ?>"><?php echo $cat_info_v['cat_title']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <div class="r3-width">
                                    <input type="submit" value="تنفيذ" class="btn_action submit-action" />
                                </div>
                            </div>
                            <input type="hidden" name="target" value="posts" />
                            <input name="method" value="multi_action" type="hidden">
                        </div>
                    </form>
                </div>
                <div class="clear"></div>
                <div class="full-width">
                    <?php if ($get_posts_all) { ?>
                        <div class="table-responsive">
                            <table class="table_parent">
                                <tr>
                                    <th><input type='checkbox' class="select-checkbox-all check-all-multi" /></th>
                                    <th>الصورة</th>
                                    <th>موضوع</th>
                                    <th>إسم الكاتب</th>
                                    <th>اللغة</th>
                                    <th>الصنف</th>
                                    <th>تاريخ الاضافة</th>
                                    <th>المصدر</th>
                                    <th>الإجراءات</th>
                                </tr>
                                <?php
                                foreach ($get_posts_all as $get_posts_allK => $get_posts_allV) {
                                    $post_id = $get_posts_allV["id"];
                                    $post_title = $get_posts_allV["post_title"];
                                    $post_type = $get_posts_allV["post_type"];
                                    $post_views = $get_posts_allV["post_views"];
                                    $post_lang = $get_posts_allV["post_lang"];
                                    $post_thumb = $get_posts_allV["post_thumbnail"];
                                    $post_author = $get_posts_allV["post_author"];
                                    $post_in = $get_posts_allV["post_in"];
                                    $post_author_name = get_user_field($post_author, "user_name");

                                    $post_date = $get_posts_allV["post_date_gmt"];

                                    $source = get_post_meta($post_id, 'source') ? json_decode(get_post_meta($post_id, 'source'), true) : null;
                                    $post_source = '';
                                    if(is_array($source)) {
                                        $post_source = '<a href="'. $source[0]['url'] .'">'. $source[0]['text'] .'</a>';
                                        // foreach ($source as $src):
                                        //     $post_source = $src['text'];
                                        // endforeach;
                                    }
                                    // if($post_source) {
                                    //     if (preg_match('/^(.*?):(https?:\/\/.+)$/', $post_source, $matches)) {
                                    //         $text = $matches[1]; // First part before colon
                                    //         $post_source = $text;
                                    //     }
                                    // }


                                    // case 1 active role
                                    // case 2 unactive role
                                    $post_status  = $get_posts_allV["post_status"];
                                    
                                    if ($post_status == "publish") {
                                        $lock_btn_class = "fa-lock-open";
                                        $lock_action_tooltip = "حظر";
                                    } else {
                                        $lock_btn_class = "fa-lock";
                                        $lock_action_tooltip = "إلغاء الحظر";
                                    }
                                    
                                    $post_icon_tu_title = "";
                                    if ($post_in == "trusted") {
                                        $post_icon_tu_title = ' <i class="fa fa-check-circle" style="color:green;"></i> ';
                                        $un_trusted_tooltip = "إلغاء التوثيق";
                                        $un_trusted_btn_class = "fas fa-star";
                                    } else {
                                        $post_icon_tu_title = ' <i class="fa fa-check-circle" style="color:grey;"></i> ';
                                        $un_trusted_tooltip = "توثيق";
                                        $un_trusted_btn_class = "far fa-star";
                                    }
                                    $ar_translate_posts_status = array("publish" => "مفعل", "approval" => "بإنتظار المراجعة", "pending" => "بإنتظار الموافقة", "canceled" => "ملغي");
                                    $post_cats = '';
                                    
                                    if (isset($posts_categories_title[$post_id]) && is_array($posts_categories_title[$post_id])) {
                                        $post_cats = '(' . implode(',', $posts_categories_title[$post_id]) . ')';
                                    }
                                    // $headers = @get_headers(get_thumb($post["post_thumbnail"], null));
                                    // $image = isset($headers[0]) && strpos($headers[0], '200') !== false ? get_thumb($post["post_thumbnail"], null) : siteurl() . "/assets/images/no-image.svg";
                                    $image = $post_thumb ? get_thumb($post_thumb) : siteurl() . "/assets/images/no-image.svg";
                                ?>
                                    <tr>
                                        <td><input type='checkbox' class="select-checkbox check-box-action" data-id="<?php esc_html($post_id); ?>" /></td>
                                        <td><img src="<?= $image ;?>" /></td>
                                        <td><a href="<?php echo get_post_link($post_id); ?>" target="_blank"><?php echo $post_title . " " . $post_icon_tu_title; ?></a><?php echo $post_cats; ?></td>
                                        <td><a href="<?php echo siteurl(); ?>/admin/dashboard/users?user_id=<?php echo $post_author_id; ?>"><?php esc_html($post_author_name); ?></a></td>
                                        <td><?php esc_html($post_lang); ?></td>
                                        <td><?php esc_html(get_taxonomy_title($post_type)); ?></td>

                                        <td><?php echo get_timeago(strtotime($post_date)); ?></td>
                                        <td><?php echo $post_source; ?></td>

                                        <td>
                                            <table class="table_child">
                                                <tr>
                                                    <td><button class="action_stg edit-st-btn open-url" data-url="<?php echo siteurl() . "/post.php?post_type=" . $post_type . "&post_in=" . $post_in . "&action=edit&post_id=" . $post_id; ?>" title="تعديل" data-url="" id=""><i class="fas fa-cog"></i></button></td>
                                                    <td><button class="action_stg lock-btn updateData" title="<?php echo $lock_action_tooltip; ?>" data-id="<?php esc_html($post_id); ?>" data-method="un_lock_post_ajax"><i class="fas <?php echo $lock_btn_class; ?>"></i></button></td>
                                                    <td><button class="action_stg un-trusted-btn updateData" title="<?php echo $un_trusted_tooltip; ?>" data-id="<?php esc_html($post_id); ?>" data-method="merge_to_un_trusted"><i class="<?php echo $un_trusted_btn_class; ?>"></i></button></td>
                                                    <td><button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=posts&id=<?php echo $post_id; ?>"><i class="fas fa-trash"></i></button></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </table>
                        </div>
                        <?php get_pagination($query_posts->count_results(), $per_page); ?>
                    <?php
                    } else {
                    ?>
                        <div class="no_posts">لاتوجد أي مواضيع حاليا.</div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php }; ?>