<?php
$q = $_GET["q"] ?? "";
$per_page = $_GET["per_page"] ?? 50;
$filter_type = $_GET["filter_type"] ?? false;
$filter_post_type = $_GET["filter_post_type"] ?? false;
$filter_status = $_GET["filter_status"] ?? "";
$filter_post_author = $_GET["filter_post_author"] ?? "";
$filter_category = $_GET["filter_category"] ?? "";
$filter_post_id = $_GET["filter_post_id"] ?? "";
$filter_lang = $_GET["filter_lang"] ?? false;
$args = [
    "post_status" => false,
    "post_status__not" => ['auto-draft', 'draft'],
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

if ($filter_status == "newset") {
    $args['order'] = ['post_date_gmt', 'desc'];
} elseif ($filter_status == "special") {
    $args['in_special'] = 'on';
} elseif ($filter_status == "pending" || $filter_status == "approval" || $filter_status == "publish") {
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
        <form action="" method="get" id="form_filter">
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
                            <option value="publish" <?php if ($filter_status == "publish") {
                                                        echo 'selected="true"';
                                                    } ?>>مواضيع منشورة</option>
                            <option value="special" <?php if ($filter_status == "special") {
                                                        echo 'selected="true"';
                                                    } ?>>مواضيع مميزة</option>
                            <option value="newset" <?php if ($filter_status == "newset") {
                                                        echo 'selected="true"';
                                                    } ?>>مواضيع جديدة</option>
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
                        <select name="filter_type" class="on_change_submit">
                            <option value="">عرض الجميع</option>
                            <option value="trusted" <?php if ($filter_type == "trusted") {
                                                        echo 'selected="true"';
                                                    } ?>>مشاركات موثوقة</option>
                            <option value="untrusted" <?php if ($filter_type == "untrusted") {
                                                            echo 'selected="true"';
                                                        } ?>>مشاركات غير موثوقة</option>
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
                                <option value="summary">تلخيص</option>
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
                            <th>المشاهدات</th>
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
                            $reviewed = $get_posts_allV["reviewed"];
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
                            $reviewed_icon = '';
                            if($reviewed == 'on') {
                                $reviewed_icon .= '<img class="mx-1" data-toggle="tooltip" title="تمت المراجعة" style="width: 14px;" src="'. siteurl() . "/assets/images/icons/book/reviewed.svg" .'" />';
                            }
                            $ar_translate_posts_status = array("publish" => "مفعل", "approval" => "بإنتظار المراجعة", "pending" => "بإنتظار الموافقة", "canceled" => "ملغي");
                            $post_cats = '';
                            
                            if (isset($posts_categories_title[$post_id]) && is_array($posts_categories_title[$post_id])) {
                                $post_cats = '(' . implode(',', $posts_categories_title[$post_id]) . ')';
                            }

                            $book_summary_indecator = get_post_meta($post_id, 'book_summary') ? "(تم تلخيص الكتاب)" : "";

                            $image = $post_thumb ? get_thumb($post_thumb) : siteurl() . "/assets/images/no-image.svg";
                        ?>
                            <tr>
                                <td><input type='checkbox' class="select-checkbox check-box-action" data-id="<?php esc_html($post_id); ?>" /></td>
                                <td><img src="<?= $image ;?>" /></td>
                                <td><a class="d-inline-flex align-items-center" href="<?php echo get_post_link($post_id); ?>" target="_blank"><?php echo $post_title . " " . $post_icon_tu_title . $reviewed_icon; ?></a><?php echo $post_cats; ?><span class="mx-1 text-danger"><?php echo $book_summary_indecator; ?></span></td>
                                <td><a href="<?php echo siteurl(); ?>/admin/dashboard/users?user_id=<?php echo $post_author_id; ?>"><?php esc_html($post_author_name); ?></a></td>
                                <td><?php esc_html($post_lang); ?></td>
                                <td><?php esc_html(get_taxonomy_title($post_type)); ?></td>
                                <td><?php esc_html($post_views); ?></td>
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