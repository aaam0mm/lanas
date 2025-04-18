<?php
$action = $_GET["action"] ?? "";
?>
<!-- modals -->
<div class="modal fade" id="commentsModal" tabindex="-1" role="dialog" aria-labelledby="commentsModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                        <h5 class="modal-title" id="commentsModalLabel">التعليقات</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span>&times;</span>
                        </button>
                </div>
                <div class="modal-body text-center">
                    <div class="container my-4">
                        <h4>عرض التعليقات</h4>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>لغة التعليق</th>
                                    <th>نص التعليق</th>
                                    <th>تاريخ الانشاء</th>
                                    <th>الاجراءات</th>
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
<!-- users family modal -->
<div class="modal fade" id="bootFamilyModal" tabindex="-1" role="dialog" aria-labelledby="bootFamilyModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title" id="bootFamilyModalLabel">عائلة البوت</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                    </button>
            </div>
            <div class="modal-body" id="userList">
                
            </div>
        </div>
    </div>
</div>
<!-- boot analytics modal -->
<div class="modal fade" id="bootAnalyticModal" tabindex="-1" role="dialog" aria-labelledby="bootAnalyticModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bootAnalyticModalLabel">احصائيات البوت</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="statList">
                
            </div>
        </div>
    </div>
</div>


<div class="dash-part-form">
    <div class="full-width">
        <?php if (!$action) {
            $per_page = 50;
            $boot_id = $_GET["boot_id"] ?? "";
            $filter_name = $_GET["filter_name"] ?? null;
            if ($action == "edit" && !$boot_id) {
                exit();
            }
            $current_user = get_current_user_info();
            if (!$action || $action == "edit") {
                $q = $_GET["q"] ?? "";
                $per_page = $_GET["per_page"] ?? 50;
                $show = $_GET["show"] ?? "";
                $where_args = [];
                $order_by = "";
                $join = '';
                $filter_lang = $_GET["filter_lang"] ?? "";
            
                $get_boots_all = $dsql->dsql()->table('boots');
                if ($q) {
                    $get_boots_all->where($get_boots_all->expr("boots.name LIKE '%$q%'"));
                }
                if($filter_lang) {
                    $get_boots_all->where('boots.lang', $filter_lang);
                }
                $get_boots_all->field('boots.*');
            
                $get_boots_all = $get_boots_all->get();
            
                $count_members_rows = count_last_query();
            
                $boots_with_book_count = [];
            }
            ?>
            <!-- Send boot message box -->
            <form action="" method="get" id="form_filter">
                <div class="page-action">
                    <div class="pull-right">
                        <a href="<?= siteurl() . '/admin/dashboard/';?>boots?action=add" class="btn btn-success">اضافة بوت</a>
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
                        <div class="full-width">
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
                        <div class="r7-width">
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
                                </select>
                            </div>
                            <div class="r3-width">
                                <input type="submit" value="تنفيذ" class="btn_action submit-action" />
                            </div>
                        </div>
                        <input type="hidden" name="target" value="boots" />
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
                            <th>الاسم</th>
                            <th>عدد العائلة</th>
                            <th>اللغة</th>
                            <th>تاريخ الانشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                        <?php
                        if(count($get_boots_all) > 0) {
                            foreach ($get_boots_all as $get_boot_k => $get_boot_v) {
                                $boot_id = $get_boot_v["id"];
                                $boot_name = $get_boot_v["name"];
                                $count_users_family = !empty($get_boot_v["users_family"]) ? substr_count($get_boot_v["users_family"], ',') + 1 : 0;
                                $boot_lang = get_langs($get_boot_v["lang"])['lang_name'];
                                $created_at = $get_boot_v["created_at"];
                                $boot_stat = $get_boot_v["stat"];
                                $analytics = get_boot_meta($boot_id, 'boots_analytics');
                                // setup buttons
                            ?>
                                <tr>
                                    <td><input type="checkbox" class="select-checkbox check-box-action" data-stat="<?= $boot_stat > 0 ? 'dis' : 'en' ;?>" data-id="<?php echo $boot_id; ?>" /></td>
                                    <td><?php esc_html($boot_name); ?></td>
                                    <td><a data-id="<?php echo $boot_id;?>" href="#" data-toggle="modal" data-target="#bootFamilyModal"><?php esc_html($count_users_family); ?></a></td>
                                    <td><?php esc_html($boot_lang); ?></td>
                                    <td><?php echo get_timeago(strtotime($created_at)); ?></td>
                                    <td>
                                        <table class="table_child">
                                            <tr>
                                                <td>
                                                    <button class="boot-change-stat action_stg <?= $boot_stat > 0 ? 'btn-danger' : 'btn-success';?>" data-toggle="tooltip" data-id="<?= $boot_id ;?>" data-stat="<?= $boot_stat ;?>" title="<?= $boot_stat > 0 ? 'موقوف عن العمل' : 'تشغيل البوت' ;?>"><i class="fa fa-<?= $boot_stat > 0 ? 'stop-circle' : 'play-circle';?>"></i></button>
                                                </td>
                                                <td>
                                                    <button class="action_stg edit-st-btn open-url" data-url="dashboard/boots?action=edit&boot_id=<?php esc_html($boot_id); ?>" title="تعديل"><i class="fa fa-cog"></i></button>
                                                </td>
                                                <?php
                                                if(!empty($analytics)) {
                                                    ?>
                                                    <td>
                                                        <button class="analytics action_stg btn-primary"><i class="fa fa-chart-line"></i></button>
                                                    </td>
                                                    <script>
                                                        $(document).on('click', '.analytics', function(e) {
                                                            let btn = $(this);
                                                            const analytics = <?php echo $analytics; ?>;
                                                            console.log(analytics);
                                                            if (analytics) {
                                                                const modal = $('#bootAnalyticModal');
                                                                
                                                                modal.find('.modal-body').html(``);
                                                    
                                                                if (analytics.follow_accounts) {
                                                                    modal.find('.modal-body').append(
                                                                        `
                                                                            <table class="table table-bordered follow_accounts">
                                                                                <thead></thead>
                                                                                <tbody></tbody>
                                                                            </table>
                                                                        `
                                                                    );
                                                                    const follow_accounts = analytics.follow_accounts;
                                                                    const familiesSuccess = JSON.parse(follow_accounts.families_success);
                                                                    const usersSuccess = JSON.parse(follow_accounts.users_success);
                                                                    modal.find('.modal-body .follow_accounts thead').html(`
                                                                    <tr>
                                                                        <th>عدد الحسابات المتفاعلة</th>
                                                                        <th>عدد المتابعات</th>
                                                                        <th>نسبة اتمام العملية</th>
                                                                        <th>اخر تاريخ للتفاعل</th>
                                                                    </tr>
                                                                    `);
                                                    
                                                                    modal.find('.modal-body .follow_accounts tbody').html(`
                                                                    <tr>
                                                                        <td>${familiesSuccess.length}</td>
                                                                        <td>${Object.keys(usersSuccess).length}</td>
                                                                        <td>${follow_accounts.progress}</td>
                                                                        <td>${follow_accounts.updated_at}</td>
                                                                    </tr>
                                                                    `);
                                                                }

                                                                if (analytics.add_comments) {
                                                                    modal.find('.modal-body').append(
                                                                        `
                                                                            <table class="table table-bordered add_comments">
                                                                                <thead></thead>
                                                                                <tbody></tbody>
                                                                            </table>
                                                                        `
                                                                    );
                                                                    const analytics_comments = analytics.add_comments;
                                                                    const terminated_posts = JSON.parse(analytics_comments.terminated_posts);
                                                                    const terminated_commentators = JSON.parse(analytics_comments.terminated_commentators);
                                                                    
                                                                    modal.find('.modal-body .add_comments thead').html(`
                                                                    <tr>
                                                                        <th>عدد الحسابات المتفاعلة</th>
                                                                        <th>عدد المواضيع</th>
                                                                        <th>نسبة اتمام العملية</th>
                                                                        <th>عدد التعليقات</th>
                                                                    </tr>
                                                                    `);
                                                                    modal.find('.modal-body .add_comments tbody').html(`
                                                                    <tr>
                                                                        <td>${Math.round(analytics_comments.commentProgressNumber / (terminated_posts.length * 2))}</td>
                                                                        <td>${terminated_posts.length * 2}</td>
                                                                        <td>${analytics_comments.commentProgress}</td>
                                                                        <td>${analytics_comments.commentProgressNumber}</td>
                                                                    </tr>
                                                                    `);
                                                                }

                                                                if (analytics.add_reviews) {
                                                                    modal.find('.modal-body').append(`
                                                                    <table class="table table-bordered add_reviews">
                                                                        <thead></thead>
                                                                        <tbody></tbody>
                                                                    </table>
                                                                    `);
                                                                    const analytics_reviews = analytics.add_reviews;
                                                                    const terminated_posts_reviews = JSON.parse(analytics_reviews.terminated_posts);
                                                                    const terminated_reviewers = JSON.parse(analytics_reviews.terminated_reviewers);
                                                                    
                                                                    modal.find('.modal-body .add_reviews thead').html(`
                                                                    <tr>
                                                                        <th>عدد الحسابات المتفاعلة</th>
                                                                        <th>عدد المواضيع</th>
                                                                        <th>نسبة اتمام العملية</th>
                                                                        <th>عدد التقييمات</th>
                                                                    </tr>
                                                                    `);
                                                                    modal.find('.modal-body .add_reviews tbody').html(`
                                                                    <tr>
                                                                        <td>${Math.round(analytics_reviews.reviewProgressNumber / (terminated_posts_reviews.length))}</td>
                                                                        <td>${terminated_posts_reviews.length * 2}</td>
                                                                        <td>${analytics_reviews.reviewProgress}</td>
                                                                        <td>${analytics_reviews.reviewProgressNumber}</td>
                                                                    </tr>
                                                                    `);
                                                                }
                                                                // 
                                                                if (analytics.add_previews) {
                                                                    modal.find('.modal-body').append(`
                                                                    <table class="table table-bordered add_previews">
                                                                        <thead></thead>
                                                                        <tbody></tbody>
                                                                    </table>
                                                                    `);
                                                                    const analytics_previews = analytics.add_previews;
                                                                    const terminated_posts_previews = JSON.parse(analytics_previews.terminated_posts_previews);
                                                                    const terminated_previewers = JSON.parse(analytics_previews.terminated_previewers);
                                                                    
                                                                    modal.find('.modal-body .add_previews thead').html(`
                                                                    <tr>
                                                                        <th>عدد الحسابات المتفاعلة</th>
                                                                        <th>عدد المواضيع</th>
                                                                        <th>نسبة اتمام العملية</th>
                                                                        <th>عدد المشاهدات</th>
                                                                    </tr>
                                                                    `);
                                                                    modal.find('.modal-body .add_previews tbody').html(`
                                                                    <tr>
                                                                        <td>${Math.round(analytics_previews.previewProgressNumber / (terminated_posts_previews.length))}</td>
                                                                        <td>${terminated_posts_previews.length * 2}</td>
                                                                        <td>${analytics_previews.previewProgress}</td>
                                                                        <td>${analytics_previews.previewProgressNumber}</td>
                                                                    </tr>
                                                                    `);
                                                                }
                                                                // 
                                                                if (analytics.books_and_subject_tools) {
                                                                    modal.find('.modal-body').append(`
                                                                    <table class="table table-bordered books_and_subject_tools">
                                                                        <thead></thead>
                                                                        <tbody></tbody>
                                                                    </table>
                                                                    `);
                                                                    const analytics_books = analytics.books_and_subject_tools;
                                                                    const terminated_books_manager = JSON.parse(analytics_books.terminated_books_manager);
                                                                    const listen = JSON.parse(analytics_books.listen);
                                                                    const preview = JSON.parse(analytics_books.preview);
                                                                    const download = JSON.parse(analytics_books.download);

                                                                    modal.find('.modal-body .books_and_subject_tools thead').html(`
                                                                    <tr>
                                                                        <th>عدد الحسابات المتفاعلة</th>
                                                                        <th>عدد المواضيع</th>
                                                                        <th>المسموعات</th>
                                                                        <th>المعاينات</th>
                                                                        <th>التحميلات</th>
                                                                        <th>نسبة اتمام العملية</th>
                                                                        <th>عدد العمليات</th>
                                                                    </tr>
                                                                    `);
                                                                    modal.find('.modal-body .books_and_subject_tools tbody').html(`
                                                                    <tr>
                                                                        <td>${Math.round(analytics_books.bookProgressNumber / (terminated_books_manager.length))}</td>
                                                                        <td>${terminated_books_manager.length * 2}</td>
                                                                        <td>
                                                                            <span class="d-block mb-2">
                                                                                <i class="mr-2 fas fa-check-circle text-success"></i>${listen.success}
                                                                            </span>
                                                                            <span class="d-block mb-2">
                                                                                <i class="mr-2 fas fa-times-circle text-danger"></i>${listen.fails}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <span class="d-block mb-2">
                                                                                <i class="mr-2 fas fa-check-circle text-success"></i>${preview.success}
                                                                            </span>
                                                                            <span class="d-block mb-2">
                                                                                <i class="mr-2 fas fa-times-circle text-danger"></i>${preview.fails}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <span class="d-block mb-2">
                                                                                <i class="mr-2 fas fa-check-circle text-success"></i>${download.success}
                                                                            </span>
                                                                            <span class="d-block mb-2">
                                                                                <i class="mr-2 fas fa-times-circle text-danger"></i>${download.fails}
                                                                            </span>
                                                                        </td>
                                                                        <td>${analytics_books.bookProgress}</td>
                                                                        <td>${analytics_books.bookProgressNumber}</td>
                                                                    </tr>
                                                                    `);
                                                                }
                                                    
                                                                modal.modal('show');
                                                            }
                                                        });
                                                    </script>
                                                    <?php
                                                }
                                                ?>
                                                <td>
                                                    <button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=boots&id=<?php esc_html($boot_id); ?>"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php
        } elseif($action == "comments") {
            $comment_id = $_GET["comment_id"] ?? "";
            $order_meta = $_GET["order_meta"] ?? "";
            $current_user = get_current_user_info();
            $q = $_GET["q"] ?? "";
            $per_page = $_GET["per_page"] ?? 50;
            $show = $_GET["show"] ?? "";
            $where_args = [];
            $order_by = "";
            $join = '';

            $get_comments_all = $dsql->dsql()->expr("SELECT `comment_name`, 
                                COUNT(*) AS comment_count, 
                                MAX(`created_at`) AS latest_comment_date
                                FROM `boot_comments` 
                                GROUP BY `comment_name`;");

            // $get_comments_all = $dsql->dsql()->table('boot_comments');
            if ($q) {
                $get_comments_all->where($get_comments_all->expr("boot_comments.name LIKE '%$q%'"));
            }
            // $get_comments_all->field('boot_comments.*');

            $get_comments_all = $get_comments_all->get();

            $count_members_rows = count_last_query();
            // ?>
            <!-- Send boot message box -->
            <form action="<?= siteurl() . '/admin/dashboard/';?>boots?action=comments" method="GET" id="form_filter2">
                <div class="page-action">
                    <div class="pull-right">
                        <a href="<?= siteurl() . '/admin/dashboard/';?>boots?action=add_comment" class="btn btn-success">اضافة تعليق</a>
                    </div>
                    <div class="pull-left">
                        <div class="line-elm-flex">
                            <div class="7r-width">
                                <input type="text" name="q" placeholder="إبحث عن بالنص" value="<?php echo $q; ?>" />
                            </div>
                            <div class="r3-width">
                                <button form="form_filter2" id="search_btn2"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="panel_filter">
                    <div class="pull-left line-elm-flex">
                        <div class="r9-width">
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
                                </select>
                            </div>
                            <div class="r3-width">
                                <input type="submit" value="تنفيذ" class="btn_action submit-action" />
                            </div>
                        </div>
                        <input type="hidden" name="target" value="boot_comments" />
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
                            <th>عائلة التعليقات</th>
                            <th>عدد التعليقات</th>
                            <th>تاريخ اخر تعليق</th>
                            <th>الإجراءات</th>
                        </tr>
                        <?php
                        if(count($get_comments_all) > 0) {
                            foreach ($get_comments_all as $get_comment_k => $get_comment_v) {
                                // $comment_id = $get_comment_v["id"];
                                $comment_name = $get_comment_v["comment_name"];
                                // $comment_lang = !empty($get_comment_v["comment_lang"]) ? get_langs($get_comment_v["comment_lang"])['lang_name'] : 'غير محدد';
                                $comment_count = $get_comment_v["comment_count"];
                                $latest_comment_date = $get_comment_v["latest_comment_date"];
                                // setup buttons
                            ?>
                                <tr>
                                    <td><input type="checkbox" class="select-checkbox check-box-action" data-id="<?php echo $comment_name; ?>" /></td>
                                    <td><?php echo $comment_name; ?></td>
                                    <td><a data-id="<?php echo $comment_name; ?>" href="#" data-toggle="modal" data-target="#commentsModal"><?php echo $comment_count; ?></a></td>
                                    <!-- <td><p style="max-width: 255px;" class="text-truncate"><?php //esc_html($comment); ?></p></td> -->
                                    <td><?php echo get_timeago(strtotime($latest_comment_date)); ?></td>
                                    <td>
                                        <table class="table_child">
                                            <tr>
                                                <!-- <td><button class="action_stg edit-st-btn open-url" data-url="dashboard/boots?action=edit_comment&comment_id=<?php //esc_html($comment_id); ?>" title="تعديل"><i class="fa fa-cog"></i></button></td> -->
                                                <td>
                                                    <button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=boot_comments&id=<?php esc_html($comment_name); ?>"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <script>
                $('#commentsModal').on('shown.bs.modal', function (event) {
                    let btn = event.relatedTarget;
                    let comment_name = $(btn).data('id');
                    $.ajax({
                    url: 'admin-ajax.php',
                    type: 'POST',
                    data: {action: 'getfetchcomments', comment_name: comment_name},
                    success: function(response) {
                        $(`#commentsModal .modal-body .table tbody`).html(``);
                        let datas = response ? JSON.parse(response) : [];
                        if(datas.length > 0) {
                        for(let row of datas) {
                            $(`#commentsModal .modal-body .table tbody`).append(`
                            <tr>
                                <td>${row.comment_lang}</td>
                                <td>${row.comment}</td>
                                <td>${row.created_at}</td>
                                <td>
                                    <button class="action_stg edit-st-btn open-url" data-url="dashboard/boots?action=edit_comment&comment_id=${row.id}" title="تعديل"><i class="fa fa-cog"></i></button>
                                    <button class="action_stg delete-btn open-url" title="حدف" data-url="dashboard/delete?type=boot_comments&id=${row.id}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            `);
                        };
                        } else {
                        $(`#commentsModal .modal-body .table tbody`).append(`
                            <tr>
                                <td colspan="4" class="text-center">لا توجد بيانات</td>
                            </tr>
                        `);
                        }
                    }
                    });
                });
            </script>
        <?php
        } elseif ($action == "add" || $action == "edit") {
            $boot = [];
            $boot_id = null;
            $boot_lang = null;
            if(isset($_GET['boot_id'])) {
                $boot_id = $_GET['boot_id'];
                global $dsql;
                $boot = $dsql->dsql()->table('boots')->where('id', $boot_id)->limit(1)->getRow();
            }
        ?>
            <h2>أضف بوت جديد</h2>
            <form method="post" id="form_data">
                <div class="full-width input-label-noline">
                    <label for="name">اسم عائلة البوت</label>
                    <input type="text" name="name" placeholder="اسم عائلة البوت" class="sign_inpt_elm" value="<?php esc_html($boot['name'] ?? ""); ?>" />
                    <span id="name_error" class="error-inp-txt"></span>
                </div>

                <div class="full-width">
                    <label for="lang">اختيار اللغة</label>
                    <select name="lang" id="lang">
                        <option value="0" selected="true" disabled="true">إختر اللغة</option>
                        <?php
                        foreach (get_langs() as $lang_k => $lang_v) {
                            $lang_code = $lang_v["lang_code"];
                            $lang_name = $lang_v["lang_name"];
                            $selected_language = $lang_code == $boot['lang'] ? 'selected="true"' : '';
                        ?>
                            <option <?php echo $selected_language ?> value="<?php esc_html($lang_code); ?>"><?php echo $lang_name; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <span id="lang_error" class="error-inp-txt"></span>
                </div>

                <div class="full-width">
                    <label for="type">إختر الصنف</label>
                    <select name="type" id="type">
                        <option selected="true" disabled="true"><?php echo _t("إختر الصنف"); ?></option>
                        <option value=""><?php echo _t("الكل"); ?></option>
                        <?php
                        $get_taxonomies = get_all_taxonomies();
                        if ($get_taxonomies):
                            foreach ($get_taxonomies as $taxo):
                                $taxo_type = $taxo["taxo_type"];
                                echo '<option value="' . $taxo_type . '">' . get_taxonomy_title($taxo) . '</option>';
                            endforeach;
                        endif;
                        ?>
                    </select>
                    <span id="type_error" class="error-inp-txt"></span>
                </div>

                <div class="full-width">
                    <div id="post-categoy-select2" class="form-group position-relative">
                        <label for="boot_category" class="font-weight-bold"><?php echo _t("الأقسام"); ?></label>
                        <select name="boot_category[]" id="boot_category" class="form-control" multiple>

                        </select>
                        <div id="boot_category_error_txt" class="invalid-feedback d-block"></div>
                    </div>
                </div>
                <?php
                if(isset($boot['cats'])) {
                    ?>
                    <script>
                        $(document).ready(function() {
                            let type = "<?php echo $boot['type'] ?>";
                            let categories = <?php echo $boot['cats']; ?>;
                            $(`#type`).val(type).trigger('change');

                            $(document).on('categories-loaded', function() {
                                // Set the value of the select to the categories array
                                $("#boot_category").val(categories);

                                // Trigger the change event for Select2 to reflect the selection
                                $("#boot_category").trigger('change');
                            });

                        });
                    </script>
                    <?php
                }
                ?>
                

                <?php
                $users = get_users(null,"desc", ['limit' => "all"]);
                usort($users['results'], function($a, $b) {
                    return strcmp($a['user_name'], $b['user_name']);
                });
                ?>
                <div class="full-width">
                    <div id="boot-account-select2" class="form-group position-relative">
                        <label for="users_family" class="font-weight-bold"><?php echo _t("اختيار حسابات لعائلة البوت"); ?></label>
                        <select name="users_family[]" id="users_family" class="form-control" multiple>
                            <?php foreach ($users['results'] as $user_k => $user_v):
                                $user_code = $user_v["id"];
                                $user_name = $user_v["user_name"];
                            ?>
                                <option value="<?php esc_html($user_code); ?>">
                                    <?php esc_html($user_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="users_family_error_txt" class="invalid-feedback d-block"></div>
                    </div>
                </div>

                <?php
                    if(isset($boot['users_family'])) {
                        ?>
                        <script>
                            $(document).ready(function() {
                                let users = <?php echo $boot['users_family']; ?>;
                                // Set the value of the select to the categories array
                                $("#users_family").val(users);
                                // Trigger the change event for Select2 to reflect the selection
                                $("#users_family").trigger('change');

                            });
                        </script>
                        <?php
                    }
                ?>

                <div class="full-width">
                    <div id="boot-account-user-select2" class="form-group position-relative">
                        <label for="users" class="font-weight-bold"><?php echo _t("اختيار حسابات للتفاعل عليها اذا لم بتم الاختيار يعمل البوت على جميع الحسابات"); ?></label>
                        <select name="users[]" id="users" class="form-control" multiple>
                            <?php foreach ($users['results'] as $user_k => $user_v):
                                $user_code = $user_v["id"];
                                $user_name = $user_v["user_name"];
                            ?>
                                <option value="<?php esc_html($user_code); ?>">
                                    <?php esc_html($user_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="users_error_txt" class="invalid-feedback d-block"></div>
                    </div>
                </div>

                <?php
                    $boot_user = get_boot_meta($boot_id, 'users') ?? "";
                    if(!empty($boot_user)) {
                        ?>
                        <script>
                            $(document).ready(function() {
                                let users = <?php echo $boot_user;?>;
                                // Set the value of the select to the categories array
                                $("#users").val(users);
                                // Trigger the change event for Select2 to reflect the selection
                                $("#users").trigger('change');

                            });
                        </script>
                        <?php
                    }
                ?>

                <?php
                    $comments = $dsql->dsql()->expr("SELECT `comment_name`, 
                        COUNT(*) AS comment_count
                        FROM `boot_comments` 
                        GROUP BY `comment_name`;")->get();
                ?>

                <div class="full-width">
                    <div id="boot-comments-select2" class="form-group position-relative">
                        <label for="comments" class="font-weight-bold"><?php echo _t("حدد عائلات التعليقات المستعملة في البوت"); ?></label>
                        <select name="comments[]" id="comments" class="form-control" multiple>
                            <?php foreach ($comments as $comment):
                                $comment_name = $comment["comment_name"];
                                $comment_count = $comment["comment_count"];
                            ?>
                                <option value="<?php esc_html($comment_name); ?>">
                                    <?php esc_html($comment_name); ?>(<?php esc_html($comment_count) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="comments_error_txt" class="invalid-feedback d-block"></div>
                    </div>
                </div>

                <?php
                    $boot_comments = get_boot_meta($boot_id, 'comments') ?? "";
                    if(!empty($boot_comments)) {
                        ?>
                        <script>
                            $(document).ready(function() {
                                let comments = <?php echo $boot_comments;?>;
                                // Set the value of the select to the categories array
                                $("#comments").val(comments);
                                // Trigger the change event for Select2 to reflect the selection
                                $("#comments").trigger('change');

                            });
                        </script>
                        <?php
                    }
                ?>

                <div id="checks-container" class="full-width input-label-noline">
                    <label for="name">صلاحيات البوت</label>
                    <?php
                        $boot_permissions = $dsql->dsql()->table('boot_permissions')->get();
                        if(count($boot_permissions) > 0) {
                            foreach($boot_permissions as $data) {
                                ?>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="boot_permissions[]" id="<?php esc_html($data['permission_key']) ?>">
                                    <label class="custom-control-label" for="<?php esc_html($data['permission_key']) ?>"><?php esc_html($data['permission_value_ar']) ?></label>
                                </div>
                                <?php
                            }
                        }
                    ?>
                </div>

                <?php
                    if(isset($boot['permissions'])) {
                        ?>
                        <script>
                            $(document).ready(function() {
                                let permissions = <?php echo $boot['permissions'];?>;
                                let permissionsSelectors = permissions.map(item => {
                                    $(`#${item}`).val(item).trigger('click');
                                    setTimeout(function() {
                                        if($(`#count_of_comments`).length === 1) {
                                            let count_of_comments = "<?php echo get_boot_meta($boot_id, 'count_of_comments');?>";
                                            $(`#count_of_comments`).val(count_of_comments);
                                        }
                                    });
                                });
                            });
                        </script>
                        <?php
                    }
                ?>

                <?php
                if ($action == "edit") {
                ?>
                    <input type="hidden" name="boot_id" value="<?php esc_html($boot_id); ?>" />
                <?php } ?>
                <input type="hidden" name="method" value="boot_save" />
                <input type="hidden" name="req" value="admin" />
                <button id="submit_form" class="saveData">أضف</button>
            </form>
        <?php
        } elseif ($action == "add_comment" || $action == "edit_comment") {
            $comment = [];
            $comment_lang = null;
            if(isset($_GET['comment_id'])) {
                $comment_id = $_GET['comment_id'];
                global $dsql;
                $comment = $dsql->dsql()->table('boot_comments')->where('id', $comment_id)->limit(1)->getRow();
                $comment_lang = $comment['comment_lang'];
            }
        ?>
            <h2>أضف تعليق جديد</h2>
            <form method="post" id="form_data">
                <div class="full-width input-label-noline">
                    <label for="comment_name">اسم التعليق</label>
                    <input type="text" name="comment_name" placeholder="اسم التعليق" class="sign_inpt_elm" value="<?php esc_html($comment['comment_name'] ?? ""); ?>" />
                    <span id="comment_name_error" class="error-inp-txt"></span>
                </div>
                <div class="full-width input-label-noline">
                    <label for="comment">نص التعليق</label>
                    <textarea style="min-height: 181px !important;padding:7px;" name="comment" id="comment" placeholder="نص التعليق" cols="30" rows="10"><?php esc_html($comment['comment'] ?? ""); ?></textarea>
                    <span id="comment_error" class="error-inp-txt"></span>
                </div>
                <div class="full-width">
                    <label for="comment_lang">اختيار اللغة</label>
                    <select name="comment_lang" id="comment_lang">
                        <option value="0" selected="true" disabled="true">إختر اللغة</option>
                        <?php
                        foreach (get_langs() as $lang_k => $lang_v) {
                            $lang_code = $lang_v["lang_code"];
                            $lang_name = $lang_v["lang_name"];
                            $selected_language = $lang_code == $comment_lang ? 'selected="true"' : '';
                        ?>
                            <option <?= $selected_language ;?> value="<?php esc_html($lang_code); ?>"><?php echo $lang_name; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <?php
                if ($action == "edit_comment") {
                ?>
                    <input type="hidden" name="comment_id" value="<?php esc_html($comment_id); ?>" />
                <?php } ?>
                <input type="hidden" name="method" value="comment_save" />
                <input type="hidden" name="req" value="admin" />
                <button id="submit_form" class="saveData">أضف</button>
            </form>
        <?php
        }
        ?>
    </div>
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
                // selector.html(`<option value="0" selected="true">اختر قسما</option>`);
                selector.html(``);
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

    $(`#form_data [name="lang"], #form_data [name="type"]`).unbind().on('change', function() {
        let keys = [
        {
            'language': $(`#form_data [name="lang"]`).val()
        },
        {
            'texo': $(`#form_data [name="type"]`).val()
        }
        ]
        getCats(keys, $(`#form_data #boot_category`), true);
    });

    // checks
    $(`#checks-container input[type="checkbox"]`).unbind().on('change', function() {
        let value = $(this).is(':checked') ? $(this).attr('id') : "off";
        $(this).val(value);
        if($(this).is(`#add_comments`)) {
            if(value != 'off') {
                if($(`#add_comments`).siblings('#count_of_comments').length === 0) {
                    $(`[for="add_comments"]`).addClass('d-inline').after(`
                        <input type="number" id="count_of_comments" placeholder="عدد" name="count_of_comments" style="width:7%;" class="form-control form-control-sm d-inline ml-5"/>
                    `);
                    $('#count_of_comments').focus();
                } else {
                    $('#count_of_comments').focus();
                }
            } else {
                $(`[for="add_comments"]`).removeClass('d-inline');
                $('#count_of_comments').remove();
            }
        }
    });
</script>