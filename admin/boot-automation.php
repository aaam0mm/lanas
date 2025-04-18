<?php
class BootAutomation
{
    private $dsql;
    private $boot_id;
    private $analytics;
    private $data;

    public function __construct($dsql)
    {
        $this->dsql = $dsql;
    }

    private function getBootMeta($metaKey)
    {
        return get_boot_meta($this->boot_id, $metaKey);
    }

    private function saveBootMeta($metaKey, $metaValue)
    {
        $existing_record = $this->dsql->dsql()
            ->table('boot_meta')
            ->where('boot_id', $this->boot_id)
            ->where('meta_key', $metaKey)
            ->limit(1)
            ->getRow();

        if ($existing_record) {
            return $this->dsql->dsql()
                ->table('boot_meta')
                ->set('meta_value', $metaValue)
                ->where('boot_id', $this->boot_id)
                ->where('meta_key', $metaKey)
                ->update();
        } else {
            return $this->dsql->dsql()
                ->table('boot_meta')
                ->set([
                    'meta_key' => $metaKey,
                    'meta_value' => $metaValue,
                    'boot_id' => $this->boot_id
                ])
                ->insert();
        }
    }

    private function archiveAnalytics()
    {
        if ($this->analytics) {
            $data = json_decode($this->analytics, true) ?? [];
            $data['archived'] = 'yes';
            $this->saveBootMeta('boots_analytics', json_encode($data));
        }
    }

    private function handleFollowAccounts($users_family, $users, $permissions)
    {
        if (!in_array('follow_accounts', $permissions)) {
            return 0;
        }

        $progress = 0;
        if (count($users) == 0 || is_null($users_family)) {
            throw new Exception('الاعضاء المستهدفين او المنفذين غير موجودين');
        }

        $fullProgressNumber = count($users_family) * count($users);
        $progressNumber = 0;
        $data = $this->data;

        // Get existing progress
        if (isset($data['follow_accounts'])) {
            $families_success = json_decode($data['follow_accounts']['families_success'] ?? '[]', true);
            $users_success = json_decode($data['follow_accounts']['users_success'] ?? '[]', true);
            $progressNumber = $data['follow_accounts']['progressNumber'] ?? 0;
            $progress = $data['follow_accounts']['progress'] ?? 0;
        } else {
            $families_success = [];
            $users_success = [];
        }

        $new_users_family = array_diff($users_family, $families_success);
        $new_users = array_diff($users, $users_success);

        // Check if process was already completed
        if ($fullProgressNumber <= $progressNumber && $progressNumber > 0) {
            return $progress;
        }

        foreach ($new_users_family as $family) {
            $families_success[] = $family;
            foreach ($new_users as $user) {
                if ($user != $family) {
                    if ($this->followUser($user, $family)) {
                        $users_success[] = $user;
                        $progressNumber++;
                        $progress = round(($progressNumber * 100) / $fullProgressNumber);

                        $data['follow_accounts'] = [
                            'families_success' => json_encode(array_unique($families_success)),
                            'users_success' => json_encode(array_unique($users_success)),
                            'updated_at' => gmdate('Y-m-d H:i:s'),
                            'progressNumber' => $progressNumber,
                            'progress' => $progress,
                            'archived' => ($progress >= 100) ? 'yes' : 'no'
                        ];

                        $this->saveBootMeta('boots_analytics', json_encode($data));
                        $this->data = $data;
                    }
                }
            }
        }

        return $progress;
    }

    private function handleComments($users_family, $users, $permissions, $boot)
    {
        if (!in_array('add_comments', $permissions)) {
            return 0;
        }

        $comments = json_decode($this->getBootMeta('comments') ?? '[]', true);
        $count_of_comments = (int)($this->getBootMeta('count_of_comments') ?? 1);

        if ((count($comments) == 0 && !in_array("comment_execept", $permissions) && !in_array("comment_events", $permissions)) || is_null($users_family) || count($users) == 0) {
            throw new Exception('الاعضاء المنفذين او عائلات التعليقات غير موجودين');
        }

        $cats = implode(",", json_decode($boot['cats'], true));
        $users_str = implode(",", $users);

        $posts = $this->dsql->dsql()->expr(
            "SELECT DISTINCT posts.id FROM posts 
              INNER JOIN post_category ON post_category.post_id = posts.id 
              WHERE posts.post_status = 'publish' 
              AND post_category.post_category IN($cats) 
              AND posts.post_author IN($users_str)
              ORDER BY posts.id DESC"
        )->get();

        $posts = array_map(function ($post) {
            return $post['id'];
        }, $posts);

        $data = $this->data;

        // Get existing progress
        if (isset($data['add_comments'])) {
            $terminated_commentators = json_decode($data['add_comments']['terminated_commentators'] ?? '[]', true);
            $terminated_posts = json_decode($data['add_comments']['terminated_posts'] ?? '[]', true);
            $commentProgressNumber = $data['add_comments']['commentProgressNumber'] ?? 0;
            $commentProgress = $data['add_comments']['commentProgress'] ?? 0;
        } else {
            $terminated_commentators = [];
            $terminated_posts = [];
            $commentProgressNumber = 0;
            $commentProgress = 0;
        }

        $commentfullProgressNumber = count($posts) * (count($users_family) * $count_of_comments);

        // Check if process was already completed
        if ($commentfullProgressNumber <= $commentProgressNumber && $commentProgressNumber > 0) {
            return $commentProgress;
        }

        $new_commentators = array_diff($users_family, $terminated_commentators);

        foreach ($new_commentators as $commentator_id) {
            $new_posts = array_diff($posts, $terminated_posts);

            if (count($new_posts) == 0) {
                $new_posts = $posts;
                $terminated_commentators[] = $commentator_id;
            }

            foreach ($new_posts as $post_id) {
                $terminated_posts[] = $post_id;
                $new_comments = $this->getRandomComments($comments, $count_of_comments, $permissions);

                foreach ($new_comments as $comment) {
                    if ($this->savePostComment($post_id, $comment, $commentator_id)) {
                        $commentProgressNumber++;
                        $commentProgress = round(($commentProgressNumber * 100) / $commentfullProgressNumber);
                        $commentProgress = min($commentProgress, 100);

                        $data['add_comments'] = [
                            'terminated_commentators' => json_encode(array_unique($terminated_commentators)),
                            'terminated_posts' => json_encode(array_unique($terminated_posts)),
                            'commentProgressNumber' => $commentProgressNumber,
                            'updated_at' => gmdate('Y-m-d H:i:s'),
                            'commentProgress' => $commentProgress,
                            'archived' => ($commentProgress >= 100) ? 'yes' : 'no'
                        ];

                        $this->saveBootMeta('boots_analytics', json_encode($data));
                        $this->data = $data;
                    }
                }
            }

            $terminated_posts = [];
        }

        return $commentProgress;
    }

    private function un_rate($post_id, $user_id, $rate_stars)
    {

        if (empty($post_id) || empty($user_id) || empty($rate_stars)) {
            return false;
        }
        global $dsql;

        $check = $dsql->dsql()->table('rating_sys')->where('user_id', $user_id)->where('post_id', $post_id)->field('id')->getRow();
        if ($check) {
            $id = $check["id"];
            $delete_rate = $dsql->dsql()->table('rating_sys')->where('id', $id)->delete();
            if (!$delete_rate) {
                return false;
            }
        }

        $insert_rate = $dsql->dsql()->table('rating_sys')->set(['post_id' => $post_id, 'user_id' => $user_id, 'rate_stars' => $rate_stars])->insert();
        if ($insert_rate) {
            $post_author = get_post_field($post_id, "post_author");
            $this->insertNotification($user_id, $post_author, $post_id, "post_rate");
            if ($rate_stars == 5) {
                $points_remaining = get_user_info($user_id)->points_remaining;
                $new_remaining_points = $points_remaining + distribute_points("rate5s", "add", $post_author);
                if ($new_remaining_points > $points_remaining) {
                    update_user_meta($post_author, "points_remaining", $new_remaining_points);
                }
            }
            // return get_rates($post_id);
            return true;
        }
        return false;
    }

    private function handleRatePosts($users_family, $users, $permissions, $boot)
    {
        if (!in_array('add_reviews', $permissions)) {
            return 0;
        }
        if (is_null($users_family) || count($users) == 0) {
            throw new Exception('الاعضاء المنفذين او عائلات التعليقات غير موجودين');
        }

        $cats = implode(",", json_decode($boot['cats'], true));
        $users_str = implode(",", $users);

        $posts = $this->dsql->dsql()->expr(
            "SELECT DISTINCT posts.id FROM posts 
            INNER JOIN post_category ON post_category.post_id = posts.id 
            WHERE posts.post_status = 'publish' 
            AND post_category.post_category IN($cats) 
            AND posts.post_author IN($users_str)
            ORDER BY posts.id DESC"
        )->get();

        $posts = array_map(function ($post) {
            return $post['id'];
        }, $posts);

        $data = $this->data;
        // Get existing progress
        if (isset($data['add_reviews'])) {
            $terminated_reviewers = json_decode($data['add_reviews']['terminated_reviewers'] ?? '[]', true);
            $terminated_posts = json_decode($data['add_reviews']['terminated_posts'] ?? '[]', true);
            $reviewProgressNumber = $data['add_reviews']['reviewProgressNumber'] ?? 0;
            $reviewProgress = $data['add_reviews']['reviewProgress'] ?? 0;
        } else {
            $terminated_reviewers = [];
            $terminated_posts = [];
            $reviewProgressNumber = 0;
            $reviewProgress = 0;
        }

        $reviewfullProgressNumber = count($posts) * count($users_family);

        // Check if process was already completed
        if ($reviewfullProgressNumber <= $reviewProgressNumber && $reviewProgressNumber > 0) {
            return $reviewProgress;
        }

        $new_reviewers = array_diff($users_family, $terminated_reviewers);
        $rates = [1, 2, 3, 4, 5];
        foreach ($new_reviewers as $reviewer_id) {
            $new_posts = array_diff($posts, $terminated_posts);

            if (count($new_posts) == 0) {
                $new_posts = $posts;
                $terminated_reviewers[] = $reviewer_id;
            }
            foreach ($new_posts as $post_id) {
                $terminated_posts[] = $post_id;
                $rate_stars = $rates[array_rand($rates)];
                if ($this->un_rate($post_id, $reviewer_id, $rate_stars)) {
                    $reviewProgressNumber++;
                    $reviewProgress = round(($reviewProgressNumber * 100) / $reviewfullProgressNumber);
                    $reviewProgress = min($reviewProgress, 100);

                    $data['add_reviews'] = [
                        'terminated_reviewers' => json_encode(array_unique($terminated_reviewers)),
                        'terminated_posts' => json_encode(array_unique($terminated_posts)),
                        'reviewProgressNumber' => $reviewProgressNumber,
                        'updated_at' => gmdate('Y-m-d H:i:s'),
                        'reviewProgress' => $reviewProgress,
                        'archived' => ($reviewProgress >= 100) ? 'yes' : 'no'
                    ];

                    $this->saveBootMeta('boots_analytics', json_encode($data));
                    $this->data = $data;
                }
            }
            $terminated_posts = [];
        }

        return $reviewProgress;
    }

    private function getip()
    {
        switch (true) {
            case (!empty($_SERVER['HTTP_X_REAL_IP'])):
                return $_SERVER['HTTP_X_REAL_IP'];
            case (!empty($_SERVER['HTTP_CLIENT_IP'])):
                return $_SERVER['HTTP_CLIENT_IP'];
            case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])):
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            default:
                return $_SERVER['REMOTE_ADDR'];
        }
    }

    private function getOS()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $os_platform = "Unknown OS Platform";
        $os_array = array(
            '/windows nt 10.0/i'     =>  'Windows 10.0',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/Windows NT 7.0/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/Windows NT5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/Windows 2000/i'     =>  'Windows 2000',
            '/Windows NT 4.0/i'     =>  'Windows NT',
            '/Windows NT 5.2/i'     =>  'Windows Server 2003',
            '/WinNT4.0/i'     =>  'Windows NT',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/Win 9x 4.90/i'         =>  'Windows ME',
            '/Windows CE/i'         =>  'Windows CE',
            '/Windows 98/i'              =>  'Windows 98',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/Windows 95/i'              =>  'Windows 95',
            '/win32/i'              =>  'Windows',
            '/microsoft/i'              =>  'Windows',
            '/teleport/i'              =>  'Windows',
            '/web downloader/i'              =>  'Windows',
            '/flashget/i'              =>  'Windows',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/mac|Macintosh/i'        =>  'Mac OS',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/dos x86/i'         =>  'DOS',
            '/unix/i'         =>  'Unix',
            '/webos/i'              =>  'Mobile'
        );
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform    =   $value;
            }
        }
        return $os_platform;
    }

    private function getBrowser()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = "Unknown Browser";
        $browser_array  = array(
            '/msie/i'       =>  'Internet Explorer',
            '/firefox/i'    =>  'Firefox',
            '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Chrome',
            '/opera/i'      =>  'Opera',
            '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon',
            '/konqueror/i'  =>  'Konqueror',
            '/mobile/i'     =>  'Handheld Browser'
        );
        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser    =   $value;
            }
        }
        return $browser;
    }

    private function _csrf()
    {
        @session_start();
        if (empty($_SESSION["csrf"])) {
            $_SESSION["csrf"] = generateRandomString(8);
        }
        return $_SESSION["csrf"];
    }

    private function insert_analytics($analysis_key, $analysis_value)
    {
        if (empty($analysis_key) || empty($analysis_value)) {
            return false;
        }
        global $dsql;
        $session_ip = $this->getip();
        // $session_ip = '8.8.8.8';

        $csrf =  $this->_csrf();

        $data_exist = $dsql->dsql()->table('analytics');

        $data_exist->where($data_exist->orExpr()->where('session_id', $csrf)->where('session_ip', $session_ip))->where('analysis_key', $analysis_key)->where('analysis_value', $analysis_value)->field('count(*)', 'records');

        if (((int) $data_exist->getRow()['records']) > 0) {
            return true;
        }

        $session_os = $this->getOS();
        $session_browser = $this->getBrowser();
        $session_date = date("Y-m-d h:i:s");
        $session_id = NULL;

        $ip_info = file_get_contents("http://ip-api.com/json/" . $session_ip);
        $ip_info = json_decode($ip_info);
        $session_countryCode =  @strtolower($ip_info->countryCode);

        $data = [
            "session_ip" => $session_ip,
            "session_id" => $csrf, // Use $csrf for the session identifier
            "session_browser" => $session_browser,
            "session_os"  => $session_os,
            "session_date" => $session_date,
            "session_user" => $session_id, // This should be the user ID, which is 1
            "session_activities" => '', // Make sure to define this
            "analysis_key" => $analysis_key,
            "analysis_value" => $analysis_value,
            "session_countryCode" => $session_countryCode
        ];
        $insert_session = $dsql->dsql()->table('analytics')->set($data)->insert();
        if ($insert_session) {
            if ($analysis_key == "post_views") {
                $post_author = get_post_field($analysis_value, 'post_author');
                $update_posts_views = $dsql->dsql()->table('posts');
                $update_posts_views->set($update_posts_views->expr($analysis_key), $update_posts_views->expr("{$analysis_key} + 1"))->where('id', $analysis_value)->update();
                $points_remaining = @get_user_info($post_author)->points_remaining;
                $new_remaining_points = $points_remaining + distribute_points("posts_views", "add", $post_author);
                if ($new_remaining_points > $points_remaining) {
                    update_user_meta($post_author, "points_remaining", $new_remaining_points);
                }
            } elseif ((strpos($analysis_key, "post_share") !== false)) {
                $update_post_shares = $dsql->dsql()->table('posts');
                $update_post_shares->set("post_share", $update_post_shares->expr('post_share + 1'))->where('id', $analysis_value)->update();
            }
        }
        return true;
    }

    private function handlePreviewPosts($users_family, $users, $permissions, $boot)
    {
        if (!in_array('add_previews', $permissions)) {
            return 0;
        }
        if (is_null($users_family) || count($users) == 0) {
            throw new Exception('الاعضاء المنفذين او عائلات التعليقات غير موجودين');
        }

        $cats = implode(",", json_decode($boot['cats'], true));
        $users_str = implode(",", $users);

        $posts = $this->dsql->dsql()->expr(
            "SELECT DISTINCT posts.id FROM posts 
            INNER JOIN post_category ON post_category.post_id = posts.id 
            WHERE posts.post_status = 'publish' 
            AND post_category.post_category IN($cats) 
            AND posts.post_author IN($users_str)
            ORDER BY posts.id DESC"
        )->get();

        $posts = array_map(function ($post) {
            return $post['id'];
        }, $posts);

        $data = $this->data;
        // Get existing progress
        if (isset($data['add_previews'])) {
            $terminated_previewers = json_decode($data['add_previews']['terminated_previewers'] ?? '[]', true);
            $terminated_posts_previews = json_decode($data['add_previews']['terminated_posts_previews'] ?? '[]', true);
            $previewProgressNumber = $data['add_previews']['previewProgressNumber'] ?? 0;
            $previewProgress = $data['add_previews']['previewProgress'] ?? 0;
        } else {
            $terminated_previewers = [];
            $terminated_posts_previews = [];
            $previewProgressNumber = 0;
            $previewProgress = 0;
        }

        $previewfullProgressNumber = count($posts) * count($users_family);

        // Check if process was already completed
        if ($previewfullProgressNumber <= $previewProgressNumber && $previewProgressNumber > 0) {
            return $previewProgress;
        }

        $new_previewers = array_diff($users_family, $terminated_previewers);
        foreach ($new_previewers as $previewer_id) {
            $new_posts = array_diff($posts, $terminated_posts_previews);

            if (count($new_posts) == 0) {
                $new_posts = $posts;
                $terminated_previewers[] = $previewer_id;
            }
            foreach ($new_posts as $post_id) {
                $terminated_posts_previews[] = $post_id;
                if ($this->insert_analytics("post_views", $post_id)) {
                    $previewProgressNumber++;
                    $previewProgress = round(($previewProgressNumber * 100) / $previewfullProgressNumber);
                    $previewProgress = min($previewProgress, 100);

                    $data['add_previews'] = [
                        'terminated_previewers' => json_encode(array_unique($terminated_previewers)),
                        'terminated_posts_previews' => json_encode(array_unique($terminated_posts_previews)),
                        'previewProgressNumber' => $previewProgressNumber,
                        'updated_at' => gmdate('Y-m-d H:i:s'),
                        'previewProgress' => $previewProgress,
                        'archived' => ($previewProgress >= 100) ? 'yes' : 'no'
                    ];

                    $this->saveBootMeta('boots_analytics', json_encode($data));
                    $this->data = $data;
                }
            }
            $terminated_posts_previews = [];
        }

        return $previewProgress;
    }

    private function paged($offset = null, $per_page = 12)
    {

        $p = (int) ($_GET["paged"] ?? 1);

        $end = $per_page;
        $start = ($p - 1) * $end;

        return $$offset;
    }

    private function get_files($args, $order_by = "DESC", $get = 'user_files', $mime_type = false, $order_column = 'files.id')
    {

        global $dsql;

        $id = $args['id'] ?? null;
        $file_uploader = $args['file_uploader'] ?? null;
        $file_key = $args['file_key'] ?? null;
        $file_type = $args['file_type'] ?? null;
        $mime_type = $args['mime_type'] ?? null;
        $file_cat = $args['file_cat'] ?? null;
        $limit = $args['limit'] ?? 12;
        $file_type__not = $args['file_type__not'] ?? null;

        $get_files = $dsql->dsql()->table('files');

        if ($id) {
            $get_files->where('files.id', $id);
        }
        if ($file_key) {
            $get_files->where('files.file_key', $file_key);
        }
        if ($file_type) {
            $get_files->where('files.file_type', $file_type);
        }
        if ($mime_type) {
            $get_files->where('files.mime_type', $mime_type);
        }
        if ($file_cat) {
            $get_files->where('files.file_category', $file_cat);
        }
        if ($get == 'my') {
            $get_files->where('files.file_uploader', $file_uploader);
        } elseif ($get == 'trusted') {
            $get_files->join('posts.post_thumbnail', 'files.id', 'inner');
            $get_files->where('posts.post_in', 'trusted')->where('posts.post_status', 'publish')->where('files.file_type', '!=', 'site_images');
        } elseif ($get == 'gallery') {
            $get_files->where('files.file_type', 'site_images');
        }
        if ($file_type__not) {
            $get_files->where('files.file_type', 'not in', $file_type__not);
        }

        $offset = $this->paged('start', $limit);
        $order_column = "files.id";
        if ($order_by == 'rand') {
            $order_by = 'desc';
        }
        $get_files->order($order_column, $order_by);
        $get_files->field($get_files->expr('DISTINCT SQL_CALC_FOUND_ROWS files.*'));
        $get_files->limit($limit, $offset);
        $get_files = $get_files->get();
        return $get_files;
    }

    private function downloadBook($post_id, $books_ids)
    {
        global $dsql;
        if ($post_id == 0 || empty($books_ids)) {
            error_log("Invalid post ID or empty books IDs.");
            return false;
        }

        // Fetch the existing book_downloads meta value
        $book_downloads = $dsql->dsql()
            ->table('post_meta')
            ->where('post_id', $post_id)
            ->where('meta_key', 'book_downloads')
            ->field('meta_value')
            ->limit(1)
            ->getRow();

        $downloads = 0;

        if (!$book_downloads || empty($book_downloads)) {
            $downloads++;
            $dsql->dsql()->table('post_meta')->set([
                "meta_key" => "book_downloads",
                "meta_value" => $downloads,
                "post_id" => $post_id
            ])->insert();
        } else {
            $book_downloads['meta_value']++;
            $downloads = $book_downloads['meta_value'];

            $dsql->dsql()->table('post_meta')->set([
                "meta_value" => $downloads
            ])->where('post_id', $post_id)
                ->where('meta_key', 'book_downloads')
                ->update();
        }

        $zip = new ZipArchive();
        $zipFileName = 'downloads_' . time() . '.zip';
        $zipFilePath = ROOT . '/temp/' . $zipFileName;

        if (!is_dir(ROOT . '/temp')) {
            if (!mkdir(ROOT . '/temp', 0777, true) && !is_dir(ROOT . '/temp')) {
                error_log("Failed to create temp directory.");
                return false;
            }
        }

        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            error_log("Failed to open ZIP file: $zipFilePath");
            return false;
        }

        foreach ($books_ids as $key) {
            $get_file = $this->get_files(['id' => $key]);
            if ($get_file) {
                $file = $get_file[0];
                $filePath = ROOT . '/uploads/' . $file['file_dir'] . '/' . $file['file_name'];

                if (file_exists($filePath)) {
                    if (!$zip->addFile($filePath, $file['file_original_name'])) {
                        error_log("Failed to add file to ZIP: $filePath");
                    }
                } else {
                    error_log("File not found: $filePath");
                }
            }
        }

        $zip->close();

        if (!file_exists($zipFilePath)) {
            error_log("ZIP file not created: $zipFilePath");
            return false;
        } else {
            sleep(5);
            unlink($zipFilePath);
            return true;
        }
    }

    private function previewBook($user_id, $post_id, $books_ids)
    {
        if ($user_id == 0 || $post_id == 0) {
            return false;
        }
        if (!(is_array($books_ids) && count($books_ids) > 0)) {
            return false;
        }

        global $dsql;
        // Fetch the existing book_preview meta value
        $book_preview = $dsql->dsql()
            ->table('post_meta')
            ->where('post_id', $post_id)
            ->where('meta_key', 'book_preview')
            ->field('meta_value')
            ->limit(1)
            ->getRow();

        $preview = 0;

        if (!$book_preview || empty($book_preview)) {
            // Insert new record if no book_preview exists
            $preview++;
            $book_preview_info = json_encode([
                "user_ids" => [$user_id], // Track user_ids who viewed
                "preview" => $preview
            ]);

            $insert = $dsql->dsql()->table('post_meta')->set([
                "meta_key" => "book_preview",
                "meta_value" => $book_preview_info,
                "post_id" => $post_id
            ])->insert();

            if ($insert) {
                return true;
            }
        } else {
            // Deserialize the existing value
            $book_preview_info = json_decode($book_preview['meta_value'], true);

            if (!isset($book_preview_info['user_ids']) || !in_array($user_id, $book_preview_info['user_ids'])) {
                // Only increment preview if the user has not viewed
                $book_preview_info['user_ids'][] = $user_id;
                $book_preview_info['preview']++;
            }

            // Update the existing record
            $update = $dsql->dsql()->table('post_meta')->set([
                "meta_value" => serialize($book_preview_info)
            ])->where('post_id', $post_id)
                ->where('meta_key', 'book_preview')
                ->update();
            if ($update) {
                return true;
            }
        }
    }

    private function listenBook($user_id, $post_id, $audios_ids)
    {
        global $dsql;
        if ($user_id == 0 || $post_id == 0) {
            return false; // Invalid input
        }
        if (!(is_array($audios_ids) && count($audios_ids) > 0)) {
            return false;
        }

        // Fetch the existing book_preview meta value
        $book_listen = $dsql->dsql()
            ->table('post_meta')
            ->where('post_id', $post_id)
            ->where('meta_key', 'book_listen')
            ->field('meta_value')
            ->limit(1)
            ->getRow();

        $listen = 0;
        if (!$book_listen || empty($book_listen)) {
            // Insert new record if no book_listen exists
            $listen++;
            $book_listen_info = json_encode([
                "user_ids" => [$user_id], // Track user_ids who viewed
                "listen" => $listen
            ]);

            $insert = $dsql->dsql()->table('post_meta')->set([
                "meta_key" => "book_listen",
                "meta_value" => $book_listen_info,
                "post_id" => $post_id
            ])->insert();

            if ($insert) {
                return true;
            }
        } else {
            // Deserialize the existing value
            $book_listen_info = json_decode($book_listen['meta_value'], true);
            if (!isset($book_listen_info['user_ids']) || !in_array($user_id, $book_listen_info['user_ids'])) {
                // Only increment listen if the user has not viewed
                $book_listen_info['user_ids'][] = $user_id;
                $book_listen_info['listen']++;
            }
            // Update the existing record
            $update = $dsql->dsql()->table('post_meta')->set([
                "meta_value" => json_encode($book_listen_info)
            ])->where('post_id', $post_id)
                ->where('meta_key', 'book_listen')
                ->update();
            if ($update) {
                return true;
            }
        }
    }

    private function handleBooks($users_family, $users, $permissions, $boot)
    {
        if (!in_array('books_and_subject_tools', $permissions)) {
            return 0;
        }
        if (is_null($users_family) || count($users) == 0) {
            throw new Exception('الاعضاء المنفذين او عائلات التعليقات غير موجودين');
        }

        $cats = implode(",", json_decode($boot['cats'], true));
        $users_str = implode(",", $users);

        $posts = $this->dsql->dsql()->expr(
            "SELECT DISTINCT posts.id FROM posts 
            INNER JOIN post_category ON post_category.post_id = posts.id 
            WHERE posts.post_status = 'publish' 
            AND posts.post_type = 'book'
            AND post_category.post_category IN($cats) 
            AND posts.post_author IN($users_str)
            ORDER BY posts.id DESC"
        )->get();

        $posts = array_map(function ($post) {
            return $post['id'];
        }, $posts);
        $data = $this->data;
        // Get existing progress
        if (isset($data['books_and_subject_tools'])) {
            $terminated_managers = json_decode($data['books_and_subject_tools']['terminated_managers'] ?? '[]', true);
            $terminated_books_manager = json_decode($data['books_and_subject_tools']['terminated_books_manager'] ?? '[]', true);
            $bookProgressNumber = $data['books_and_subject_tools']['bookProgressNumber'] ?? 0;
            $bookProgress = $data['books_and_subject_tools']['bookProgress'] ?? 0;
            $listen = json_decode($data['books_and_subject_tools']['listen'] ?? '[]', true);
            $listenSuccess = $listen['success'] ?? 0;
            $listenFails = $listen['fails'] ?? 0;
            $preview = json_decode($data['books_and_subject_tools']['preview'] ?? '[]', true);
            $prevSuccess = $preview['success'] ?? 0;
            $prevFails = $preview['fails'] ?? 0;
            $download = json_decode($data['books_and_subject_tools']['download'] ?? '[]', true);
            $downloadSuccess = $download['success'] ?? 0;
            $downloadFails = $download['fails'] ?? 0;
        } else {
            $terminated_managers = [];
            $terminated_books_manager = [];
            $bookProgressNumber = 0;
            $bookProgress = 0;
            $listenSuccess = 0;
            $listenFails = 0;
            $prevSuccess = 0;
            $prevFails = 0;
            $downloadSuccess = 0;
            $downloadFails = 0;
        }

        $bookfullProgressNumber = count($posts) * count($users_family);

        // Check if process was already completed
        if ($bookfullProgressNumber <= $bookProgressNumber && $bookProgressNumber > 0) {
            return $bookProgress;
        }
        $new_managers = array_diff($users_family, $terminated_managers);
        foreach ($new_managers as $manager_id) {
            $new_posts = array_diff($posts, $terminated_books_manager);
            if (count($new_posts) == 0) {
                $new_posts = $posts;
                $terminated_managers[] = $manager_id;
            }
            foreach ($new_posts as $post_id) {
                $terminated_books_manager[] = $post_id;
                $audios_ids = @unserialize(get_post_meta($post_id, "audios_ids"));
                if (!empty($audios_ids)) {
                    if ($this->listenBook($manager_id, $post_id, $audios_ids)) {
                        $listenSuccess++;
                    } else {
                        $listenFails++;
                    }
                } else {
                    $listenFails++;
                }
                $books_ids = @unserialize(get_post_meta($post_id, "books_ids"));
                if (!empty($books_ids)) {
                    if ($this->previewBook($manager_id, $post_id, $books_ids)) {
                        $prevSuccess++;
                    } else {
                        $prevFails++;
                    }
                    if ($this->downloadBook($post_id, $books_ids)) {
                        $downloadSuccess++;
                    } else {
                        $downloadFails++;
                    }
                } else {
                    $prevFails++;
                    $downloadFails++;
                }
                $bookProgressNumber++;
                $bookProgress = round(($bookProgressNumber * 100) / $bookfullProgressNumber);
                $bookProgress = min($bookProgress, 100);

                $data['books_and_subject_tools'] = [
                    'terminated_managers' => json_encode(array_unique($terminated_managers)),
                    'terminated_books_manager' => json_encode(array_unique($terminated_books_manager)),
                    'listen' => json_encode(array_unique(['success' => $listenSuccess, "fails" => $listenFails])),
                    'preview' => json_encode(array_unique(['success' => $prevSuccess, "fails" => $prevFails])),
                    'download' => json_encode(array_unique(['success' => $downloadSuccess, "fails" => $downloadFails])),
                    'bookProgressNumber' => $bookProgressNumber,
                    'updated_at' => gmdate('Y-m-d H:i:s'),
                    'bookProgress' => $bookProgress,
                    'archived' => ($bookProgress >= 100) ? 'yes' : 'no'
                ];

                $this->saveBootMeta('boots_analytics', json_encode($data));
                $this->data = $data;
            }
            $terminated_books_manager = [];
        }
        return $bookProgress;
    }

    private function followUser($userId, $subscriberId)
    {
        if ($userId == $subscriberId) {
            return false;
        }

        $check = $this->dsql->dsql()
            ->table('subscribe_sys')
            ->where('user_id', $userId)
            ->where('subscriber', $subscriberId)
            ->limit(1)
            ->getRow();

        if (!$check) {
            $insert = $this->dsql->dsql()
                ->table('subscribe_sys')
                ->set([
                    "user_id" => $userId,
                    "subscriber" => $subscriberId,
                    'subscribe_date' => gmdate('Y-m-d H:i:s')
                ])
                ->insert();

            if ($insert) {
                $this->insertNotification($subscriberId, $userId, null, "follow_user");
                return true;
            }
        }

        return true;
    }

    private function insertNotification($from, $to, $content, $type, $case = 1)
    {
        $date = gmdate('Y-m-d H:i:s');

        if ($type != "site_management" && $type != "group_alert") {
            $existing = $this->dsql->dsql()
                ->table('notifications_sys')
                ->where('notif_from', $from)
                ->where('notif_to', $to)
                ->where('notif_content', $content)
                ->where('notif_type', $type)
                ->limit(1)
                ->getRow();

            if ($existing) {
                return $this->dsql->dsql()
                    ->table('notifications_sys')
                    ->set([
                        "notif_case" => $case,
                        "notif_date" => $date
                    ])
                    ->where('id', $existing['id'])
                    ->update();
            }
        }

        return $this->dsql->dsql()
            ->table('notifications_sys')
            ->set([
                "notif_from" => $from,
                "notif_to" => $to,
                "notif_content" => $content,
                "notif_type" => $type,
                "notif_case" => $case,
                "notif_date" => $date
            ])
            ->insert();
    }

    private function getRandomComments($commentTypes, $count, $exceptPermissions = [])
    {
        $result = [];

        // Check if 'comment_execept' permission is in $exceptPermissions
        $includeQuotes = !in_array('comment_execept', $exceptPermissions);

        $includeEvents = !in_array('comment_events', $exceptPermissions);

        // Fetch comments by types
        if (is_array($commentTypes)) {
            foreach ($commentTypes as $type) {
                $comments = $this->dsql->dsql()
                    ->table('boot_comments')
                    ->where('comment_name', $type)
                    ->field('comment')
                    ->get();

                if (!empty($comments)) {
                    $result[] = $comments[array_rand($comments)]['comment'];
                }
            }
        }

        // If we need more items to meet the $count
        while (count($result) < $count) {
            $allComments = $this->dsql->dsql()
                ->table('boot_comments')
                ->field('comment')
                ->get();

            if (!empty($allComments)) {
                $result[] = $allComments[array_rand($allComments)]['comment'];
            }
        }

        // If 'comment_execept' is NOT in $exceptPermissions, add quotes
        if ($includeQuotes) {
            $quotes = $this->dsql->dsql()
                ->table('posts')
                ->where('post_type', 'quote')
                ->field('post_content')
                ->get();

            if (!empty($quotes)) {
                while (count($result) < $count) {
                    $randomQuote = $quotes[array_rand($quotes)]['post_content'];
                    $result[] = strip_tags($randomQuote); // Strip HTML tags from quotes
                }
            }
        }

        if ($includeEvents) {
            $histories = $this->dsql->dsql()
                ->table('posts')
                ->where('post_type', 'history')
                ->field('post_content')
                ->get();

            if (!empty($histories)) {
                while (count($result) < $count) {
                    $randomHistory = $histories[array_rand($histories)]['post_content'];
                    $result[] = strip_tags($randomHistory); // Strip HTML tags from histories
                }
            }
        }

        // Return exactly $count items, slicing if necessary
        return array_slice($result, 0, $count);
    }

    private function savePostComment($postId, $comment, $userId)
    {
        if (empty($postId)) {
            return false;
        }

        $disable_comments = get_post_meta($postId, "disable_comments");
        if ($disable_comments != "off") {
            return false;
        }

        $commentData = [
            "comment" => $comment,
            "post_id" => $postId,
            "comment_user" => $userId,
            "comment_date" => gmdate('Y-m-d H:i:s'),
            "comment_type" => 'comment',
            "comment_status" => 'publish',
            "comment_parent" => 0
        ];

        $insert = $this->dsql->dsql()
            ->table('comments')
            ->set($commentData)
            ->insert();

        if ($insert) {
            $commentId = get_last_inserted_id();
            $postAuthor = get_post_field($postId, "post_author");

            // Update comment count
            $this->dsql->dsql()
                ->table('posts')
                ->set(["comments_count" => $this->dsql->expr('comments_count + 1')])
                ->where('id', $postId)
                ->update();

            // Handle points system
            if ($postAuthor) {
                $points = get_user_info($postAuthor)->points_remaining;
                $newPoints = $points + distribute_points("posts_comments", "add", $postAuthor);
                if ($newPoints > $points) {
                    update_user_meta($postAuthor, "points_remaining", $newPoints);
                }
            }

            // Add notification
            $this->insertNotification($userId, $postAuthor, $postId, "post_comment");

            return true;
        }

        return false;
    }

    public function startWork($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('المعطيات غير محددة');
            }

            $boot = $this->dsql->dsql()
                ->table('boots')
                ->where("id", $id)
                ->limit(1)
                ->getRow();

            if (!$boot) {
                throw new Exception('البوت غير موجود');
            }

            $this->boot_id = $boot['id'];
            $users_family = json_decode($boot['users_family'], true) ?? null;
            $users = $this->getBootMeta('users');

            if ($users == "all") {
                $users = array_map(
                    fn($user) => $user['id'],
                    $this->dsql->dsql()->table('users')->field('id')->get()
                );
            } elseif (!empty($users)) {
                $users = json_decode($users, true);
            } else {
                $users = [];
            }

            $permissions = json_decode($boot['permissions'], true);
            $this->analytics = $this->getBootMeta('boots_analytics');
            $this->data = @json_decode($this->analytics, true) ?? [];

            // Check for new permissions and initialize their analytics
            foreach ($permissions as $permission) {
                if (!isset($this->data[$permission]) || $this->data[$permission]['archived'] === 'yes') {
                    // Initialize analytics for new or archived permission
                    switch ($permission) {
                        case 'follow_accounts':
                            $this->data[$permission] = [
                                'families_success' => '[]',
                                'users_success' => '[]',
                                'progressNumber' => 0,
                                'progress' => 0,
                                'updated_at' => gmdate('Y-m-d H:i:s'),
                                'archived' => 'no'
                            ];
                            break;
                        case 'add_comments':
                            $this->data[$permission] = [
                                'terminated_commentators' => '[]',
                                'terminated_posts' => '[]',
                                'commentProgressNumber' => 0,
                                'commentProgress' => 0,
                                'updated_at' => gmdate('Y-m-d H:i:s'),
                                'archived' => 'no'
                            ];
                            break;
                            // Add cases for other permissions as needed
                    }

                    // Save the initialized analytics
                    $this->saveBootMeta('boots_analytics', json_encode($this->data));
                }
            }

            $totalProgress = 0;

            // Handle follow accounts
            if (in_array('follow_accounts', $permissions)) {
                $totalProgress += $this->handleFollowAccounts($users_family, $users, $permissions);
            }

            // Handle comments
            if (in_array('add_comments', $permissions)) {
                $totalProgress += $this->handleComments($users_family, $users, $permissions, $boot);
            }

            if (in_array('add_reviews', $permissions)) {
                $totalProgress += $this->handleRatePosts($users_family, $users, $permissions, $boot);
            }

            if (in_array('add_previews', $permissions)) {
                $totalProgress += $this->handlePreviewPosts($users_family, $users, $permissions, $boot);
            }

            if (in_array('books_and_subject_tools', $permissions)) {
                $totalProgress += $this->handleBooks($users_family, $users, $permissions, $boot);
            }

            // Check if all processes are complete
            $filterPermissions = array_filter($permissions, function ($permission) {
                return $permission !== "comment_execept" && $permission !== 'comment_events';
            });
            $completedPermissions = count($filterPermissions);
            $expectedTotalProgress = 100 * $completedPermissions;

            if ($totalProgress >= $expectedTotalProgress) {
                $this->dsql->dsql()
                    ->table('boots')
                    ->set(["stat" => 1])
                    ->where("id", $id)
                    ->update();

                $this->archiveAnalytics();

                return [
                    'success' => true,
                    'stat' => 1,
                    'analytics' => $this->data
                ];
            }

            return [
                'success' => false,
                'stat' => 0,
                'msg' => 'البوت لم يتم جميع عملياته بعد',
                'progress' => ($totalProgress / $expectedTotalProgress) * 100
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'stat' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}