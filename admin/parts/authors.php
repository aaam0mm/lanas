<?php
$per_page = 50;
$action = $_GET["action"] ?? "";
$author_id = $_GET["author_id"] ?? "";
$order_meta = $_GET["order_meta"] ?? "";
$filter_name = $_GET["filter_name"] ?? null;
if ($section == "edit" && !$author_id) {
    exit();
}
$current_user = get_current_user_info();
if (!$section || $section == "edit") {
    $q = $_GET["q"] ?? "";
    $per_page = $_GET["per_page"] ?? 50;
    $show = $_GET["show"] ?? "";
    $where_args = [];
    $order_by = "";
    $join = '';

    if (!is_null($filter_name) && !empty($filter_name)) {
        if ($filter_name == "similar") {
            $get_authors_all = $dsql->dsql()->expr("SELECT DISTINCT a1.* FROM authors a1 JOIN authors a2 ON a1.id != a2.id WHERE a1.name LIKE CONCAT('%', a2.name, '%') OR a2.name LIKE CONCAT('%', a1.name, '%') ORDER BY a1.name;");
        }
    } else {
        $get_authors_all = $dsql->dsql()->table('authors');
        if ($q) {
            $get_authors_all->where('authors.name', 'LIKE', $q);
        }
        $get_authors_all->field('authors.*');
    }

    $get_authors_all = $get_authors_all->get();

    $count_members_rows = count_last_query();

    $authors_with_book_count = [];

    if ($order_meta) {
        foreach ($get_authors_all as $author) {
            $author_name = $author['name'];
            $author['num_posts'] = count_author_posts($author_name);
            // Query to count reactions for all books by the author
            $query_reactions = "
                SELECT COUNT(rating_sys.id) AS total_reactions
                FROM posts
                INNER JOIN post_meta ON post_meta.post_id = posts.id
                INNER JOIN rating_sys ON rating_sys.post_id = posts.id
                WHERE posts.post_type = 'book'
                AND post_meta.meta_key = 'book_author'
                AND post_meta.meta_value = '$author_name'
            ";
            $reaction_count_query = $dsql->dsql()->expr($query_reactions)->getRow();

            // Query to calculate total shares for all books by the author
            $shares_query = $dsql->dsql()->expr("
                SELECT SUM(posts.post_share) AS total_shares
                FROM posts
                INNER JOIN post_meta ON post_meta.post_id = posts.id
                WHERE posts.post_type = 'book'
                AND post_meta.meta_key = 'book_author'
                AND post_meta.meta_value = '$author_name'
            ")->getRow();

            $author['total_shares'] = $shares_query['total_shares'] ?? 0;

            $author['total_reactions'] = $reaction_count_query['total_reactions'] ?? 0;
            $authors_with_book_count[] = $author;
        }
        // Sort data based on `order_meta`
        if ($order_meta === 'more_books') {
            usort($authors_with_book_count, function ($a, $b) {
                return $b['num_posts'] <=> $a['num_posts'];
            });
        } elseif ($order_meta === 'alphabitic') {
            // Sort alphabetically by name
            usort($authors_with_book_count, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        } elseif ($order_meta === 'more_reactions') {
            // Sort by total reactions (descending)
            usort($authors_with_book_count, function ($a, $b) {
                return $b['total_reactions'] <=> $a['total_reactions'];
            });
        } elseif ($order_meta === 'more_share') {
            // Sort by total shares (descending)
            usort($authors_with_book_count, function ($a, $b) {
                return $b['total_shares'] <=> $a['total_shares'];
            });
        }
        $get_authors_all = $authors_with_book_count;
    }
}
?>
<!-- start modals -->
<!-- modal download pdf -->
<div class="modal fade" id="unionAuthors" tabindex="-1" aria-labelledby="unionAuthorsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border: 1px solid #f0ae027a;border-top: 5px solid #f0ae02;border-radius: 9px;overflow: hidden">
            <div class="modal-body">
                <form action="" id="unionNames">
                    <div id="book-author-select2" class="form-group">
                        <label for="book_author" class="font-weight-bold"><?php echo _t("الاسم الجديد"); ?></label>
                        <select name="book_author" id="book_author" class="form-control">
                            <?php
                            $get_authors = get_authors();
                            usort($get_authors, function($a, $b) {
                                return strcmp($a['name'], $b['name']);
                            });
                            $authorSelected = '';
                            ?>
                            <?php foreach ($get_authors as $author): ?>
                                <?php
                                echo get_post_meta($post_id, 'book_author') == $author["name"];
                                $authorSelected = $author["name"] == get_current_user_info()->user_name ? 'data-selected="true"' : (get_post_meta($post_id, 'book_author') == $author["name"] ? 'selected="true"' : '');
                                ?>
                                <option <?= $authorSelected; ?> value="<?php esc_html($author["id"]); ?>">
                                    <?php esc_html($author["name"]); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php
                            if (empty($authorSelected)) {
                            ?>
                                <option value="<?= get_current_user_info()->user_name; ?>" data-uname="<?= get_current_user_info()->user_name; ?>"><?= get_current_user_info()->user_name; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <div id="book-author-select2_error_txt" class="invalid-feedback d-block"></div>
                    </div>
                </form>
                <div class="my-2"></div>
                <button form="unionNames" type="submit" class="btn btn-success btn-sm"><?php echo _t('موافق') ?></button>
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal"><?php echo _t('اغلاق') ?></button>
            </div>
        </div>
    </div>
</div>
<!-- end of modal download pdf -->
<!-- end modals -->

<div class="dash-part-form">
    <div class="full-width">
        <?php if (!$section) { ?>
            <!-- Send author message box -->
            <form action="" method="get" id="form_filter">
                <div class="page-action">
                    <div class="pull-right">
                        <button type="button" data-toggle="modal" data-target="#unionAuthors" class="btn btn_action">دمج الاسماء</button>
                        <a href="<?= siteurl() . '/admin/dashboard/';?>authors?section=add" class="btn btn-success">اضافة كاتب</a>
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
                            <select name="filter_name" class="on_change_submit">
                                <option value="">عرض الجميع</option>
                                <option value="similar" <?php if ($filter_name == 'similar') {
                                                            echo 'selected="true"';
                                                        } ?>>الاسماء المتشابهة</option>
                            </select>
                        </div>
                        <div class="7r-width">
                            <select name="order_meta" class="on_change_submit">
                                <option value="">عرض الجميع</option>
                                <option value="alphabitic" <?php if ($order_meta == 'alphabitic') {
                                                                echo 'selected="true"';
                                                            } ?>>أبجديا</option>
                                <option value="more_books" <?php if ($order_meta == 'more_books') {
                                                                echo 'selected="true"';
                                                            } ?>>الاكثر كتبا</option>
                                <option value="more_reactions" <?php if ($order_meta == 'more_reactions') {
                                                                    echo 'selected="true"';
                                                                } ?>>الاكثر تفاعلا مع كتبه</option>
                                <option value="more_share" <?php if ($order_meta == 'more_share') {
                                                                echo 'selected="true"';
                                                            } ?>>الاكثر مشاركة لكتبه</option>
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
                                    <option value="set">تثبيت/الغاء التثبيت</option>
                                </select>
                            </div>
                            <div class="r3-width">
                                <input type="submit" value="تنفيذ" class="btn_action submit-action" />
                            </div>
                        </div>
                        <input type="hidden" name="target" value="authors" />
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
                            <th>إسم المؤلف</th>
                            <th>حسابه في لاناس</th>
                            <th>عدد الكتب</th>
                            <th>تاريخ الانشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                        <?php
                        foreach ($get_authors_all as $get_author_k => $get_author_v) {
                            $author_id = $get_author_v["id"];
                            $author_name = $get_author_v["name"];
                            $author_numPosts = count_author_posts($author_name);
                            $created_at = $get_author_v["created_at"];
                            $author_stat = $get_author_v["author_stat"];
                            $user_id = $get_author_v["user_id"] ?? 0;
                            // setup buttons
                        ?>
                            <tr>
                                <td><input type="checkbox" class="select-checkbox check-box-action" data-stat="<?= $author_stat > 0 ? 'dis' : 'en' ;?>" data-id="<?php echo $author_id; ?>" /></td>
                                <td><a href="<?php echo siteurl() . "/m/" . $author_id; ?>"><?php esc_html($author_name); ?></a></td>
                                <td>
                                    <?= $user_id > 0 ? '<a href="'. siteurl() . '/user/' . $user_id .'">'. $author_name .'</a>' : 'لا يوجد';?>
                                </td>
                                <td><?php esc_html($author_numPosts); ?></td>
                                <td><?php echo get_timeago(strtotime($created_at)); ?></td>
                                <td>
                                    <table class="table_child">
                                        <tr>
                                            <td><button class="author-change-stat action_stg <?= $author_stat > 0 ? 'btn-danger' : 'btn-success';?>" data-id="<?= $author_id ;?>" data-stat="<?= $author_stat > 0 ? 'dis' : 'en' ;?>" title="<?= $author_stat > 0 ? 'الغاء تثبيت الاسم' : 'تثبيت الاسم' ;?>"><i class="fa fa-<?= $author_stat > 0 ? 'times' : 'check';?>"></i></button></td>
                                            <td><button class="action_stg edit-st-btn open-url" data-url="dashboard/authors?section=edit&author_id=<?php esc_html($author_id); ?>" title="تعديل"><i class="fa fa-cog"></i></button></td>
                                            <td>
                                                <button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=authors&id=<?php esc_html($author_id); ?>"><i class="fa fa-trash"></i></button>
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
            $author = [];
            if(isset($_GET['author_id'])) {
                $author = get_authors($_GET['author_id']);
            }
        ?>
            <div class="r7-width">
                <div class="r7-width">
                    <h2>أضف كاتب جديد</h2>
                    <form method="post" id="form_data">
                        <div class="full-width input-label-noline">
                            <label for="name">الإسم</label>
                            <input type="text" name="name" placeholder="إسم" class="sign_inpt_elm" value="<?php esc_html($author['name'] ?? ""); ?>" />
                            <span id="name_error" class="error-inp-txt"></span>
                        </div>
                        <?php
                        $users = get_users(null,"desc", ['limit' => "all"]);
                        usort($users['results'], function($a, $b) {
                            return strcmp($a['user_name'], $b['user_name']);
                        });
                        ?>
                        <div id="author-account-select2" class="form-group m-2" data-select2-id="author-account-select2">
                            <label for="author-account">ربط المؤلف بحساب مستخدم</label>
                            <select name="user_id" id="author-account" class="form-control">
                                <option value="" selected="true">اختر الحساب المناسب</option>
                                <?php foreach ($users['results'] as $user_k => $user_v):
                                    $user_code = $user_v["id"];
                                    $user_name = $user_v["user_name"];
                                ?>
                                <option <?= isset($author['user_id']) && $author['user_id'] == $user_code ? 'selected="true"' : '';?> value="<?php esc_html($user_code); ?>">
                                    <?php esc_html($user_name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="author-account-select2_error_txt" class="invalid-feedback d-block"></div>
                        </div>
                        <div class="full-width input-label-noline">
                            <label for="description">نبذة عن المؤلف</label>
                            <textarea style="min-height: 181px !important;" name="description" id="description" placeholder="نبذة عن المؤلف" cols="30" rows="10"><?php esc_html($author['description'] ?? ""); ?></textarea>
                            <span id="description_error" class="error-inp-txt"></span>
                        </div>
                        <?php
                        if ($section == "edit") {
                        ?>
                            <input type="hidden" name="author_id" value="<?php esc_html($author_id); ?>" />
                        <?php } ?>
                        <input type="hidden" name="method" value="author_save" />
                        <input type="hidden" name="req" value="admin" />
                        <button id="submit_form" class="saveData">أضف</button>
                    </form>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<script>
    // union authors
    if ($('#unionAuthors').length > 0) {
        $('#unionAuthors').on('shown.bs.modal', function(event) {

            $('#book_author').select2({
                tags: true,
                dir: "rtl",
                width: '100%',
                language: 'ar',
                allowClear: true,
                dropdownParent: $('#unionAuthors') // Fix for Select2 in modals
            });

            $(`#unionNames input.action-selected-inps`).remove();
            $(`#action-form input.action-selected-inps`).each(function() {
                $(`#unionNames`).append($(this));
            });
            $(`#unionNames`).unbind().on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let formdata = new FormData(form.get(0));
                formdata.append('action', 'unionauthornames');
                $.ajax({
                    url: `/user-ajax.php`,
                    type: "POST",
                    data: formdata,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.match(/ok/)) {
                            swal({
                                text: "تمت دمج المؤلفين بنجاح",
                                icon: "success",
                                buttons: {
                                    حسنا: true,
                                },
                            }).then((clicked) => {
                                if (clicked) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            swal({
                                text: "حدث خطأ",
                                icon: "error",
                                buttons: {
                                    حسنا: true,
                                },
                            });
                        }
                    }
                });

            });

        });
    }
</script>