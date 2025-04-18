<?php
$info_id = get_post_field($post_id, 'info_id');
$get_taxonomy = get_taxonomy("6");
$taxo_terms = @json_decode($get_taxonomy["taxo_terms"]);
$text_file_local_download = $taxo_terms->text_file_local_download ?? "";
$current_lang = get_current_user_info()->user_lang ?? 'ar';
$book_author = get_post_meta($post_id, "book_author");
$book_author_id = get_post_meta($post_id, "book_author_id");
$init_book_ids = get_post_meta($post_id, "books_ids");
$books_ids = @unserialize($init_book_ids);
$books_ids = !$books_ids ? json_decode($init_book_ids, true) ?? [] : $books_ids;
$book_translator = str_replace("لايوجد", "", get_post_meta($post_id, "book_translator")) ?? 'غير متوفر';
$book_translator = !empty($book_translator) ? $book_translator : 'غير متوفر';
$book_translator = !preg_match("/==/", $book_translator) ? $book_translator : 'غير متوفر';
// $book_categories = implode(", ", array_shift(get_posts_categories_name(get_posts_categories($post_id))));
$post_cats = get_posts_categories_distinct($post_id);
$cats = '<div class="d-inline-flex">';
if(is_array($post_cats) && count($post_cats) > 0) {
    $post_cats = $post_cats[$post_id];
    foreach($post_cats as $index => $cat) {
        $sp = $index < count($post_cats) - 1 ? "," : "";
        $cats .= '<a target="_blanc" href="'. siteurl() .'/posts/book?category='. $cat['id'] .'">'. $cat['cat_title'] .'</a>'.$sp;
    }
}
$cats .= '</div>';
// 
$book_ids_counter = is_array($books_ids) ? count($books_ids) : 1;
$book_lang = get_langs(get_post_field($post_id, 'post_lang'))['lang_name'];
$book_published_year = get_post_meta($post_id, "book_published_year") ?? 'غير متوفر';
$book_published_year = !empty($book_published_year) ? $book_published_year : 'غير متوفر';
$book_published_year = !preg_match("/==/", $book_published_year) ? $book_published_year : 'غير متوفر';
$book_pages = get_post_meta($post_id, "book_pages");

$book_pages = !preg_match("/==/", $book_pages) ? $book_pages : 0;
$book_pages_book_parts = (is_array($books_ids) && count($books_ids) > 0 || $info_id > 0) && $book_pages > 0
    ? 'ج: ' . $book_ids_counter . ' / ص: ' . $book_pages 
    : 'غير متوفر';
$books_links = @unserialize(get_post_meta($post_id, "books_links"));
$audios_ids = @unserialize(get_post_meta($post_id, "audios_ids"));

if (is_array($audios_ids) && count($audios_ids) > 0) {
    foreach ($audios_ids as $audio_data) {
        // Decode the JSON string
        $audio_info = json_decode($audio_data, true);

        if ($audio_info && isset($audio_info['file_id'])) {
            // Get the audio file URL using the get_file() function
            $file_url = get_file($audio_info['file_id']);

            // If the file exists, add it to the array
            if ($file_url) {
                $audio_files[] = [
                    'track_name' => $audio_info['track_name'],
                    'file_url' => $file_url
                ];
            }
        }
    }
    $_SESSION['audio_files'] = $audio_files;
}

?>
<!-- modal download pdf -->
<div class="modal fade" id="downloadPdf" tabindex="-1" aria-labelledby="downloadPdfLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: 1px solid #f0ae027a;border-top: 5px solid #f0ae02;border-radius: 9px;overflow: hidden">
            <div class="modal-body">
                <?php
                    if(!empty($text_file_local_download)) {
                        ?>
                            <div class="alert alert-danger">
                                <?php echo $text_file_local_download->$current_lang; ?>
                            </div>
                        <?php
                    }
                ?>
                <?php
                    if (is_array($books_ids)) {
                        foreach ($books_ids as $index => $book_id) {
                            ?>
                            <a class="btn btn-primary btn-block d-flex justify-content-between my-2" href="<?php echo siteurl() . "/download.php?key=" . get_file_key($book_id); ?>&post_id=<?= $post_id ;?>">
                                <span class="d-flex">
                                    <input type="checkbox" class="form-control mr-2" name="books_download" data-id="<?= $book_id ;?>" value="<?= $book_id ;?>">
                                    <?php echo _t("الجزء" . ' ('. $index + 1 .') '); ?>
                                </span>
                                <span><?php echo round(filesize(UPLOAD_DIR . get_file($book_id, false, true)) / (1024 * 1024), 2) . ' ميغا '; ?></span>
                            </a>
                            <?php
                        }
                    }
                    if (is_array($books_links)) {
                        foreach ($books_links as $index => $books_link) {
                            ?>
                            <a href="<?php echo siteurl() . "/download.php?file_url=" . $books_link; ?>&post_id=<?= $post_id ;?>">
                                <span><?php echo _t("الجزء" . ' ('. $index + 1 .') '); ?></span>
                                <span><?php echo round(filesize($books_link) / (1024 * 1024), 2) . ' ميغا '; ?></span>
                            </a>
                            <?php
                        }
                    }
                ?>
                <div class="my-4"></div>
                <a id="total-download-btn" class="btn btn-success btn-block d-flex justify-content-center mt-5" href="<?php echo siteurl() . "/download.php?key=" . get_file_key($book_id); ?>&post_id=<?= $post_id ;?>">
                    <span class="mr-1"><?php echo _t("تحميل"); ?></span>
                    <span>(<span class="counter">0</span>) ميغا</span>
                </a>
                <input type="hidden" name="books[file_id]">
                <div class="my-2"></div>
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal"><?php echo _t('اغلاق') ?></button>
            </div>
        </div>
    </div>
</div>
<!-- end of modal download pdf -->

<!-- book reader scripts -->
<script src="<?php echo siteurl(); ?>/assets/js/pdf.worker.min.js"></script>
<script src="<?php echo siteurl(); ?>/assets/js/pdf.min.js"></script>
<!-- modal book reader -->
<div class="modal fade p-0" id="bookReaderModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="bookReaderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl my-1">
        <div class="modal-content">
            <div style="overflow: hidden; height:100vh" class="modal-body">
                <!-- Toolbar with book part selector, page number, and zoom controls -->
                <div class="d-flex justify-content-between mb-3">
                    <!-- More Options Dropdown -->
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-outline-warning btn-sm" type="button" id="moreOptionsDropdown" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bars"></i>
                            </button>
                            <ul style="right: 0 !important; left:unset !important" class="dropdown-menu" aria-labelledby="moreOptionsDropdown">
                                <li><a class="dropdown-item" href="#" id="downloadPdf"><?php echo _t("اكتب تعليق"); ?></a></li>
                                <li><a class="dropdown-item" href="#" id="printPdf"><?php echo _t("قيم الكتاب"); ?></a></li>
                            </ul>
                        </div>
                    </div>
                    <!-- Select Book Part -->
                    <div class="d-flex align-items-center">
                        <!-- <label for="bookPartSelect" class="form-label">الجزء:</label> -->
                        <select id="bookPartSelect" class="form-control form-control-sm">
                            <?php if (is_array($books_ids)): ?>
                                <?php foreach ($books_ids as $index => $book_id): ?>
                                    <option value="<?php echo esc_html(get_file($book_id)); ?>">الجزء <?php echo $index + 1; ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">لايوجد</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Page Navigation -->
                    <div class="d-flex align-items-center">
                        <!-- <label for="currentPage" class="form-label me-2">Page:</label> -->
                        <input type="number" id="currentPage" class="form-control form-control-sm" style="width: 80px;" min="1" value="1">
                        <span class="mx-2">/</span>
                        <span id="totalPages">100</span>
                    </div>

                    <!-- Zoom Controls -->
                    <div class="d-flex flex-column align-items-center position-relative">
                        <!-- Zoom Button with Icon -->
                        <button id="zoomBtn" class="btn btn-outline-warning btn-sm mb-2" type="button">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        
                        <!-- Vertical Range Input (Hidden Initially) -->
                        <input type="range" id="zoomRange" class="form-range vertical-range d-none" min="50" max="200" value="100" style="position: absolute; top: 83px;">
                    </div>

                    <!-- return page (close modal) -->
                    <div class="d-flex flex-column align-items-center position-relative">
                        <button type="button" class="btn btn-outline-warning btn-sm" data-dismiss="modal">العودة</button>
                    </div>
                </div>
                <!-- Progress bar container -->
                <div id="progressBarContainer">
                    <div id="progressBar"></div>
                </div>

                <!-- PDF Viewer Canvas -->
                <div id="pdfViewerContainer" style="height: 91%; overflow-y: auto;">
                    <div id="pdfViewer"></div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- end modal book reader -->
<!-- Book post -->
<div class="row w-100 m-auto pt-3">
    <div class="col-12 col-sm-6 book-details order-sm-1 order-2 pt-2">
        <ul class="list-unstyled">
            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("العنوان"); ?></span> : <?php esc_html($post["post_title"]); ?></li>
            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("المؤلف"); ?></span> : <a <?php echo $book_author_id && !empty($book_author_id) ? 'href="' . siteurl() . "/m/" . $book_author_id . '"' : ''; ?> class="link"><?php echo  $book_author && !empty($book_author) ? esc_html($book_author) : 'غير متوفر'; ?></a></li>
            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("المترجم"); ?></span> : <?php esc_html($book_translator); ?></li>
            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("الاقسام"); ?></span> : <?php echo $cats; ?></li>
            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("اللغة"); ?></span> : <?php esc_html($book_lang); ?></li>
            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("ج/ص"); ?></span> : <?= esc_html($book_pages_book_parts) ;?></li>
            <?php
            $sharer = get_post_meta($post_id, 'book_published_name') ?? 'غير متوفر';
            $sharer = !empty($sharer) ? $sharer : 'غير متوفر';
            ?>
            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("الناشر"); ?></span> : <?php esc_html($sharer); ?></li>

            <?php
                $total_size = 0;
                if (is_array($books_ids)):
                    foreach ($books_ids as $index => $book_id):
                        $file_path = ROOT . '/uploads/' . get_file($book_id, false, true); // Assuming this returns the file path
                        if (file_exists($file_path)) {
                            $total_size += filesize($file_path); // Add file size to total
                        }
                    endforeach;
                endif;
                $total_size = round($total_size / (1024 * 1024), 2);
            ?>

            <li>
                <i class="fas fa-check-square mr-3"></i>
                <span class=""><?php echo _t("الحجم"); ?></span> : <?php esc_html($total_size); ?>
                <?php echo _t("ميغا"); ?>
            </li>

            <li>
                <i class="fas fa-check-square mr-3"></i>
                <span class=""><?php echo _t("الصوت"); ?></span> :
                <a id="audio-link" href="#" class="link" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php esc_html("معلومات"); ?></a>
                <div id="audio-dropdown" style="width: 271px;border: 2px solid #f0ae027a;border-top: 5px solid #f0ae02;border-radius: 14px;overflow: hidden" class="dropdown-menu">
                    <!-- <div class="dropdown-divider"></div> -->
                    <span class="dropdown-item d-flex align-items-center px-2">
                        <span class="">القارئ :</span>
                        <span class="text-truncate w-100 ml-1"><?php echo esc_html(get_post_meta($post_id, "book_audio_reader")); ?></span>
                    </span>
                    <span class="dropdown-item d-flex align-items-center px-2">
                        <span class="">مونتاج :</span>
                        <span class="text-truncate w-100 ml-1"><?php echo esc_html(get_post_meta($post_id, "book_audio_maker")); ?></span>
                    </span>
                    <span class="dropdown-item d-flex align-items-center px-2">
                        <span class="">المصدر :</span>
                        <span class="text-truncate w-100 ml-1"><?php echo esc_html(get_post_meta($post_id, "book_audio_source")); ?></span>
                    </span>
                    <span class="dropdown-item d-flex align-items-center px-2">
                        <span class="">المدة :</span>
                        <span id="d-duration" class="">00:00:00</span>
                    </span>
                </div>
            </li>

            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("النوع"); ?></span> : <?php esc_html("PDF"); ?>
            </li>

            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("الاصدار"); ?></span> : <?php esc_html($book_published_year); ?></li>

            <?php
            
            $count_comment = $dsql->dsql()->expr("SELECT COUNT(*) AS counter FROM comments WHERE post_id = $post_id;")->getOne() ?? 0;
            
            ?>

            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("التعليقات"); ?></span> : <?php esc_html($count_comment); ?></li>

            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("المشاركات"); ?></span> : <?php esc_html($post["post_share"]); ?></li>

            <li><i class="fas fa-check-square mr-3"></i><span class=""><?php echo _t("المشاهدات"); ?></span> : <?php esc_html($post["post_views"]); ?></li>

            <?php
                // Fetch the average rating and count of ratings for the specified post_id
                $result = $dsql->dsql()
                ->table('rating_sys')
                ->where('post_id', $post_id)
                ->field('AVG(rate_stars) AS avg_rating')
                ->field('COUNT(rate_stars) AS total_ratings')
                ->getRow();
                $avg = $result['avg_rating'] ?? 0;
                $average_rating = round($avg, 1);
                $total_ratings = (int) $result['total_ratings']; // Ensure it's an integer

                function display_stars($rating) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($rating >= $i) {
                        $stars .= '<i class="fas fa-star text-warning"></i>'; // Full star
                    } elseif ($rating >= $i - 0.5) {
                        $stars .= '<i class="fas fa-star-half-alt text-warning"></i>'; // Half star
                    } else {
                        $stars .= '<i class="far fa-star text-warning"></i>'; // Empty star
                    }
                }
                return $stars;
                }
            ?>

            <li>
                <i class="fas fa-check-square mr-3"></i>
                <span class=""><?php echo _t("التقييمات"); ?></span> :
                <div class="d-inline-flex align-items-center">
                    <div class="rating-stars">
                        <!-- Display the stars -->
                        <?= display_stars($average_rating); ?>
                    </div>

                    <small class="average-rating ml-2 text-muted">
                        <?= sprintf(_t("%.1f (%d)"), $average_rating, $total_ratings); ?>
                    </small>
                </div>
            </li>
        </ul>
    </div>
    <div class="col-12 col-sm-6 order-sm-2 order-1 mb-3 mb-sm-0 ml-auto p-0 text-right">
        <img style="width: 93%;" src="<?php echo get_thumb($post["post_thumbnail"], ["w" => 320, "h" => 450]); ?>" class="img-fluid mr-sm-8 mr-lg-3" alt="" />
        <?php
        if($reviewed == 'on') {
            echo '<img data-toggle="tooltip" title="تمت المراجعة" style="width: 23px;position: absolute;top: 5px;right: 21px;" src="'. siteurl() . "/assets/images/icons/book/reviewed.svg" .'" />';
        }
        ?>
    </div>
</div>
<!-- / Book post -->

<div class="my-2"></div>
<div class="post-content">
    <div class="border-top border-warning"></div>
    <div class="my-2"></div>
    <div class="post-details">
        <p><?php echo validate_html($post["post_content"]) ?? 'غير محدد'; ?></p>
    </div>
</div>
<div class="my-2"></div>
<?php
    // Retrieve the meta value
    $book_preview_meta = get_post_meta($post_id, 'book_preview');
    $preview_count = 0;
    // Check if meta value exists and is not empty
    if (!empty($book_preview_meta)) {
        // Deserialize the meta value
        $book_preview_data = json_decode($book_preview_meta, true);
        // Extract the preview count if available
        if (is_array($book_preview_data) && isset($book_preview_data['preview'])) {
            $preview_count = $book_preview_data['preview'];
        }
    }

    // downloads
    $downloads_count = get_post_meta($post_id, 'book_downloads') ?? 0;

    // audio listens

    $book_listen_meta = get_post_meta($post_id, 'book_listen');
    $listen_count = 0;

    // Check if meta value exists and is not empty
    if (!empty($book_listen_meta)) {
        // Deserialize the meta value
        $book_listen_data = json_decode($book_listen_meta, true);
        // Extract the listen count if available
        if (is_array($book_listen_data) && isset($book_listen_data['listen'])) {
            $listen_count = $book_listen_data['listen'];
        }
    }

?>
<style>
    @media (min-width: 992px) {
        .col-lg-3 {
            -webkit-box-flex: 0 !important;
            -ms-flex: 0 0 31% !important;
            flex: 0 0 31% !important;
            max-width: 31% !important;
        }
    }
    .mr-sm-8 {
        margin-left: .81rem !important;
    }
</style>
<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle the audio player state
if (isset($_POST['open_player'])) {
    $_SESSION['audio_files'] = $audio_files;
}

if (isset($_POST['close_player'])) {
    unset($_SESSION['audio_files']);
}

// Your existing code to fetch book details and other data
?>
<div class="d-flex px-4 justify-content-between align-items-center btns-book flex-wrap" style="bottom:0;">
    <button id="book-preview-btn" data-toggle="modal" data-target="#bookReaderModal" data-uid="<?= get_current_user_info()->id ;?>" data-pid="<?= $post_id ;?>" type="button" class="btn btn-secondary d-flex justify-content-between align-items-center col-12 col-lg-3 my-1 <?= is_array($books_ids) && count($books_ids) > 0 && $preview_stat === true ? "book-preview" : "" ;?>">
        <i class="fas fa-eye"></i>
        <span><?php echo _t("معاينة الكتاب"); ?></span>
        <span class="count"><?= $preview_count; ?></span>
    </button>
    <button id="book-download-btn" type="button" class="btn btn-success d-flex justify-content-between align-items-center col-12 col-lg-3 my-1 <?= is_array($books_ids) && count($books_ids) > 0 && $download_stat === true ? "book-downloadable" : "" ;?>">
        <i class="fas fa-download"></i>
        <span><?php echo _t("تحميل الكتاب"); ?></span>
        <span class="count"><?= $downloads_count; ?></span>
    </button>
    <button data-uid="<?= get_current_user_info()->id ;?>" data-pid="<?= $post_id ;?>" id="book-audio-btn" class="btn btn-sp d-flex justify-content-between align-items-center col-12 col-lg-3 my-1 <?= is_array($audios_ids) && count($audios_ids) > 0 ? "audio-readable" : "" ;?>">
        <i class="fas fa-headphones-alt"></i>
        <span><?php echo _t("استمع للكتاب"); ?></span>
        <span class="count"><?= $listen_count; ?></span>
    </button>

    <div id="audio-player-container">

    </div>

</div>
<?php
if(get_post_meta($post_id, 'book_summary')) {
    ?>
    <div class="my-4"></div>
    <div class="accordion" id="accordionSummary">
        <div class="card border-0 rounded-0">
            <div style="background-color: #f8d995;" class="card-header border-0 rounded-0" id="headingSummary">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left text-dark" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                عرض ملخص الكتاب
                </button>
            </h2>
            </div>

            <div id="collapseOne" class="collapse" aria-labelledby="headingSummary" data-parent="#accordionSummary">
                <div class="card-body text-justify mx-3">
                    <?php echo get_post_meta($post_id, 'book_summary'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<div class="my-4"></div>
<div class="alert alert-danger mx-4" role="alert">
    <?php echo _t("حقوق الكتاب محفوظة لصاحبه .. اذا وجدت كتابك هنا ولا توافق على نشره .. لك حق المطالبة بحذف الكتاب (عن طريق زر الابلاغ) وذلك بعد اثبات ملكيتك للكتاب"); ?>
</div>
<div class="my-2"></div>