<?php
$path = $_GET["path"] ?? '';
if ($path == "dash-statistics") {
    $section = $_GET["section"] ?? "";
    $duration = $_GET["duration"] ?? "today";
    if ($section == "statistics") {
        $get_posts_analytics = get_analytics("post_views", "posts_analytics", null, $duration);
        $get_posts_views = get_analytics("post_views", "posts_views", null, $duration);
        $get_dash_visits_analytics = get_analytics("post_views", "site_visitors", null, $duration);
        $shares_analytics = get_analytics("post_share", "posts_shares", null, $duration);
?>
        <div class="site_all_stat line-elm-flex">
            <div class="site-stat-elm site-stat-info-1 r25-width line-elm-flex">
                <div class="stat-elm-icon"><img src="<?php echo siteurl(); ?>/assets/images/icons/hex2.png" /></div>
                <div class="stat-elm-info">
                    <h2><?php esc_html($get_dash_visits_analytics["visits"] ?? 0); ?></h2>
                    <h3>زيارات للموقع</h3>
                </div>
            </div>
            <div class="site-stat-elm site-stat-info-2 r25-width line-elm-flex">
                <div class="stat-elm-icon"><img src="<?php echo siteurl(); ?>/assets/images/icons/hex3.png" /></div>
                <div class="stat-elm-info">
                    <ul>
                        <li class="trusted_post_area_icon"><?php esc_html($get_posts_views["trusted_views"] ?? 0); ?></li>
                        <li class="untrusted_post_area_icon"><?php esc_html($get_posts_views["untrusted_views"] ?? 0); ?></li>
                    </ul>
                    <h3>مشاهدة المواضيع</h3>
                </div>
            </div>
            <div class="site-stat-elm site-stat-info-3 r25-width line-elm-flex">
                <div class="stat-elm-icon"><img src="<?php echo siteurl(); ?>/assets/images/icons/hex4.png" /></div>
                <div class="stat-elm-info">
                    <h2>458</h2>
                    <h3>نقرات على المواضيع</h3>
                </div>
            </div>
            <div class="site-stat-elm site-stat-info-4 r25-width line-elm-flex">
                <div class="stat-elm-icon"><img src="<?php echo siteurl(); ?>/assets/images/icons/hex1.png" /></div>
                <div class="stat-elm-info">
                    <ul>
                        <li><?php esc_html($shares_analytics["shares"] ?? 0); ?></li>
                    </ul>
                    <h3>كل المشاركات</h3>
                </div>
            </div>
        </div>
        <?php
    }
    if ($section == "visitors") {
        $get_views_by_country = get_analytics("post_views", "views_by_country", "", $duration);
        if ($get_views_by_country) {
        ?>
            <div class="table-responsive">
                <table>
                    <tr>
                        <th>
                            <span>الدولة</span>
                        </th>
                        <th>
                            <span>عدد الزوار</span>
                        </th>
                        <th>
                            <span>مواضيع موتوقة <i class="psts_brvd fa fa-check"> </i></span>
                        </th>
                        <th>
                            <span>مواضيع غير موتوقة <i class="psts_brvd psts_nt_brvd fa fa-check"> </i></span>
                        </th>
                    </tr>
                    <?php
                    foreach ($get_views_by_country as $view_country_k => $view_country_v) {
                        $country_flag = false;
                        $country_info = get_countries($view_country_k);
                        if ($country_info) {
                            $country_flag = get_thumb($country_info["country_flag"]);
                        }
                        $country_name = $country_info["country_name"] ?? "n/a";
                    ?>
                        <tr class="cntry_dsply_dt">
                            <td>
                                <span>

                                    <?php
                                    if ($country_flag) {
                                        echo '<img src="' . $country_flag . '" class="img-fluid" width="18" height="18"/>';
                                    }
                                    esc_html(json_decode($country_name)->{current_lang()} ?? "n/a");
                                    ?>

                                </span>
                            </td>
                            <td>
                                <span><?php esc_html($view_country_v["all_views"] ?? 0); ?></span>
                            </td>
                            <td>
                                <span><?php esc_html($view_country_v["trusted_views"] ?? 0); ?></span>
                            </td>
                            <td>
                                <span><?php esc_html($view_country_v["untrusted_views"] ?? 0); ?></span>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        <?php
        } else {
        ?>
            <p class="no-posts">لاتوجد أي إحصائيات حاليا</p>
            <?php
        }
    }
    if ($section == "postsData") {
        $filter_by = $_GET["filter_by"] ?? "";
        $text_s = "مشاهدة";

        $args = [
            "post_lang" => false,
            "order" => ['posts.post_views', 'desc']
        ];
        $col = 'post_views';
        if ($filter_by == "top_shared") {
            $args['order'] = ['posts.post_share', 'desc'];
            $text_s = "مشاركة";
            $col = 'post_share';
        } elseif ($filter_by == "top_liked") {
            $text_s = "إعجاب";
            $get_posts = $dsql->dsql()->table('posts');
            $get_posts->join('user_meta.meta_key', $get_posts->expr('user_meta.meta_key = CONCAT("post_reaction__",posts.id)'))->where('posts.post_status', 'not in', ['auto-draft', 'draft'])->where('user_meta.meta_key', '!=', null)->field('count(*)', 'reactions')->field('posts.*')->order('reactions', 'desc')->group('user_meta.meta_key')->limit(12);
            $get_posts = $get_posts->get();
            $col = 'reactions';
        } elseif ($filter_by == "top_voted") {
            $text_s = "تصويت";
            $get_posts = $dsql->dsql()->table('posts');
            $get_posts->join('rating_sys.post_id', 'posts.id')->where('posts.post_status', 'not in', ['auto-draft', 'draft'])->where('rating_sys.post_id', '!=', null)->field('count(*)', 'ratings')->field('posts.*')->order('ratings', 'desc')->group('rating_sys.post_id')->limit(12);
            $get_posts = $get_posts->get();
            $col = 'ratings';
        }

        if (!isset($get_posts)) {
            $query_post = new Query_post($args);
            $get_posts = $query_post->get_posts();
        }
        if ($get_posts) {
            foreach ($get_posts as $get_posts_allK => $get_posts_allV) {
                $text_val = "";
                $post_id = $get_posts_allV["id"];
                $post_title = substr_str($get_posts_allV["post_title"], 20);
                $post_author_name = get_user_field($get_posts_allV["post_author"], "user_name");
                $post_type = $get_posts_allV["post_type"];
                $post_thumb = get_thumb($get_posts_allV["post_thumbnail"], "md", true, $post_id);
            ?>
                <div class="dsply_psts_lytcs">
                    <div class=" dsply_psts_img pull-right">
                        <a href="<?php echo get_post_link($get_posts_allV); ?>"><img src="<?php echo $post_thumb; ?>" /></a>
                    </div>
                    <div class="dsply_psts_inf pull-right">
                        <div class="dsply_psts_inf_ttl">
                            <a href="<?php echo get_post_link($get_posts_allV); ?>"><?php esc_html($post_title); ?></a>
                            <i class="psts_brvd fa fa-check"></i>
                        </div>
                        <div>
                            <span><?php echo $text_s; ?></span>
                            <span><?php esc_html($get_posts_allV[$col]); ?></span>
                        </div>
                    </div>
                </div>
            <?php
            }
        } else {
            ?>
            <p class="no-posts">لاتوجد أي إحصائيات حاليا</p>
        <?php
        }
    } elseif ($section == "newPosts") {
        $filter_by = $_GET["filter_by"] ?? false;

        $query_all_posts = new Query_post([
            "post_lang" => false,
            'post_in' => $filter_by,
            'limit' => 5,
            "order" => ['id', 'desc']
        ]);

        $get_posts_all = $query_all_posts->get_posts();

        if ($get_posts_all) {
        ?>
            <div class="table-responsive">
                <table class="table_parent">
                    <tr>
                        <th></th>
                        <th>موضوع</th>
                        <th>إسم الكاتب</th>
                        <th>القسم</th>
                        <th>الإجراءات</th>
                    </tr>
                    <?php
                    foreach ($get_posts_all as $get_posts_allK => $get_posts_allV) {
                        $post_id = $get_posts_allV["id"];
                        $post_title = $get_posts_allV["post_title"];
                        $post_author = $get_posts_allV["post_author"];
                        $post_author_name = get_user_field($post_author, "user_name");
                        $post_type = $get_posts_allV["post_type"];
                        $post_views = $get_posts_allV["post_views"];
                        $post_thumb = get_thumb($get_posts_allV["post_thumbnail"], "sm", true, $post_id);
                        $post_status  = $get_posts_allV["post_status"];
                        $post_in  = $get_posts_allV["post_in"];
                        if ($post_status == "publish") {
                            $lock_btn_class = "fa-lock";
                            $lock_action_tooltip = "حظر";
                        } else {
                            $lock_btn_class = "fa-lock-open";
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

                    ?>
                        <tr>
                            <td><img src="<?php echo $post_thumb; ?>" /></td>
                            <td><a href="<?php echo get_post_link($post_id); ?>"><?php esc_html($post_title); ?></a></td>
                            <td><a href="<?php echo siteurl() . "/user/" . $post_author; ?>"><?php esc_html($post_author_name); ?></a></td>
                            <td><?php esc_html(get_taxonomy_title($post_type)); ?></td>
                            <td>
                                <table class="table_child">
                                    <tr>
                                        <td><button class="action_stg edit-st-btn open-url" data-url="<?php echo siteurl() . "/post.php?post_type=" . $post_type . "&post_in=" . $post_in . "&action=edit&post_id=" . $post_id; ?>" title="تعديل" data-url="" id=""><i class="fas fa-cog"></i></button></td>
                                        <td><button class="action_stg un-trusted-btn updateData" title="<?php echo $un_trusted_tooltip; ?>" data-id="<?php esc_html($post_id); ?>" data-method="merge_to_un_trusted"><i class="<?php echo $un_trusted_btn_class; ?>"></i></button></td>
                                        <td><button class="action_stg lock-btn updateData" title="<?php echo $lock_action_tooltip; ?>" data-id="<?php esc_html($post_id); ?>" data-method="un_lock_post_ajax"><i class="fas <?php echo $lock_btn_class; ?>"></i></button></td>
                                        <td><button class="action_stg delete-btn open-url" data-url="dashboard/delete?type=posts&id=<?php echo $post_id; ?>" title="حدف"><i class="fa fa-trash"></i></button></td>
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
        } else {
        ?>
            <p class="no-posts">لاتوجد أي مواضيع حاليا</p>
        <?php
        }
    } elseif ($section == "users_statistics") {
        $filter_by = $_GET["filter_by"] ?? "top_points";
        $get_users = [];
        if ($filter_by == "top_points") {
            $text_s = "نقاط";
            $get_users = get_users("points", 'desc', true);
        } elseif ($filter_by == "active_today") {
            $get_users = get_users('active_today', 'desc', true);
        } elseif ($filter_by == "active") {
            $get_users = get_users('active', 'desc', true);
        } elseif ($filter_by == "top_posts") {
            $get_users = get_users('posts', 'desc', true);
            $text_s = "مواضيع";
        }
        
        $get_users = $get_users->limit(10)->get();
        
        if ($get_users) {
            ?>
            <div id="user-sct">
                <?php
                foreach ($get_users as $u_i) {
                    $text_val = "";
                    $user_pic = get_thumb(get_user_field($u_i["id"], 'user_picture'), 'sm');

                    if ($filter_by == "top_posts") {
                        $text_val = count_user_posts($u_i["id"]);
                    } else {
                        $text_val = get_user_meta($u_i["id"], "points_remaining");
                    }
                ?>
                    <div class="user-sct">
                        <div class="user-title"><span><a href="<?php echo siteurl() . "/user/" . $u_i["id"]; ?>"><?php echo substr_str(get_user_field($u_i["id"], "user_name"), 10, ""); ?></a></span></div>
                        <div class="user-img"><a href="<?php echo siteurl() . "/user/" . $u_i["id"]; ?>"><img src="<?php echo $user_pic; ?>" /></a></div>
                        <?php if (!empty($text_s)): ?>
                            <div class="user-details">
                                <span><?php echo $text_s; ?> : <?php echo $text_val; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php
                }
                ?>
            </div>
        <?php
        }
    }
} elseif ($path == "menus_elms") {
    $section = $_GET["section"] ?? "";
    if ($section == "categories") {
        $get_categories = get_categories();
        foreach ($get_categories as $category) {
            $category_id = $category["id"];
            $category_title = $category["cat_title"];
        ?>
            <li class="droppable-links" data-id="<?php esc_html($category_id); ?>" data-type="category">
                <span><?php echo $category_title; ?></span>
            </li>
            <?php
        }
    } elseif ($section == "external_links") {
        $get_external_links = get_external_links();
        if (is_array($get_external_links)) {
            foreach ($get_external_links as $link) {
                $link_title = $link["link_title"];
            ?>
                <li class="droppable-links" data-id="<?php esc_html($link["id"]); ?>" data-type="link">
                    <span><?php echo $link_title; ?></span>
                </li>
            <?php
            }
        }
    } elseif ($section == "pages") {
        $get_pages = get_pages(null, true, false);
        if (is_array($get_pages)) {
            foreach ($get_pages as $page) {
            ?>
                <li class="droppable-links" data-id="<?php esc_html($page["id"]); ?>" data-type="page">
                    <span><?php esc_html($page["page_title"]); ?></span>
                </li>
<?php
            }
        }
    }
} elseif ($path == "bloc_menu") {
    $bloc_area = $_GET["bloc_area"] ?? "";
    $lang = $_GET["lang"] ?? "";
    if (empty($bloc_area) || empty($lang)) {
        exit(0);
    }
    $bloc = extract(switch_blocs($bloc_area));
    $menu = get_the_menu(${"bloc_{$bloc_area}_menu_" . $lang . "_{$bloc_area}_bloc"});
    foreach ($menu as $link) {
        echo '<li class="droppable-links" data-id="' . $link["id"] . '" data-type="' . $link["type"] . '">
        		' . $link["title"] . '&nbsp;<i class="fas fa-times remove-parent" data-id="' . $link["id"] . '" data-type="' . $link["type"] . '"></i>
        	</li>';
    }
} elseif ($path == "cat_taxonomies") {
    $taxo_type = $_GET["taxo_type"] ?? "";
    foreach (get_categories($taxo_type) as $cat) {
        echo '<option value="' . $cat["id"] . '">' . $cat["cat_title"] . '</option>';
    }
}

?>