<?php
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\WebDriverWait;


if (!function_exists("get_file")) {
    /**
     * get_file()
     *
     * @param int $file_id
     * @return mixed
     */
    function get_file($file_id, $domain = true, $withount_prefix = false)
    {
        global $dsql;
        $get_file = $dsql->dsql()->table('files')->where('id', $file_id)->field('file_name,file_dir,file_key,mime_type')->limit(1)->getRow();

        if (!$get_file) {
            return false;
        }

        $file = $get_file;
        $file_key = $file["file_key"];
        $file_name = $file["file_name"];
        $file_dir = $file["file_dir"];
        $mime_type = $file["mime_type"];
        if ($domain) {
            return siteurl() . "/uploads/" . $file_dir . "/" . $file_name;
        } else {
            if ($withount_prefix) {
                return $file_dir . "/" . $file_name;
            } else {
                return "uploads/" . $file_dir . "/" . $file_name;
            }
            // return UPLOAD_DIR . $file_dir . "/" . $file_name;
        }
    }
}

function extract_author($str, $pattern) {
    if (preg_match($pattern, $str, $matches)) {
        return trim($matches[1]); // Return the author's name
    }
    return null; // No match found
}

$i = 0;
$finishedStat = false;
$init_url = $url;
$url_host = parse_url($init_url, PHP_URL_HOST);

$url_path = str_replace("/", "", parse_url($init_url, PHP_URL_PATH));
$domain = parse_url($init_url, PHP_URL_SCHEME) . "://" . parse_url($init_url, PHP_URL_HOST);
for (;;) {
    $i++;
    if($finishedStat || $i >= 10) {
        break;
    }
    $url_path = preg_replace("/^(\d+)-(\d+)(.*)/", "$1-". $i ."$3", $url_path);
    $url = $domain . "/" . $url_path;
    try {
        $driver->get($url);
        sleep(1);
        $containers = $driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath('//*[@id="results"]/div'))
        );
        if(is_array($containers) && count($containers) > 0) {
            // Initial progress update
            if ($progressCallback) {
                $progressCallback($current, $total, $success, $fails, $exists);
            }
            try {
                foreach ($containers as $index => $container) {
                    if (!checkFetchLimit($datasql['id'], $datasql['number_fetch'])) {
                        $finishedStat = true;
                        return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
                    }
                    try {
                        $link = $container->findElement(WebDriverBy::xpath('./div[3]/a[2]'))->getAttribute('href');
                        $title = $container->findElement(WebDriverBy::xpath('./div[3]/a[2]'))->getText();
                        if($title) {
                            if($dsql->dsql()->table('posts')->where('post_title', 'LIKE', $title)->get()) {
                                log_stat("post exists");
                                $exists++;
                                continue;
                            }
                        }
                        try {
                            $driver->executeScript("window.open('$link', '_blank');");
                            $driver->switchTo()->window($driver->getWindowHandles()[1]); // Switch to the new window
                            try {
                                $book_author = '';
                                $book_published_year = '';
                                $book_translator = '';
                                $book_pages = 0;
                                $dataElement = $driver->findElement(WebDriverBy::xpath(
                                    "/html/body/main/div/div[1]/div/div/h1"
                                ));
                                if($dataElement) {
                                    $str = $dataElement->getText();
                                    $pattern = "/❝\s*⏤\s*(.+)$/u";
                                    $book_author = extract_author($str, $pattern);
                                }
                                // image download
                                $image_src = NULL;
                                try {
                                    $image = $driver->findElement(WebDriverBy::xpath('/html/body/main/div/div[1]/div/div/img'))->getAttribute('src');
                                    log_stat("image before try to uploading: $image");
                                    if($image) {
                                        $imageFunc = upimage($url, $image);
                                        if($imageFunc === false) {
                                            goto skip_image;
                                        } else {
                                            $image_src = $imageFunc;
                                        }
                                    } else {
                                        goto skip_image;
                                    }
                                } catch (Exception $e) {
                                    goto skip_image;
                                }
                                skip_image:

                                $books_ids = [];
                                if($datasql['book_without_pdf'] == 'off') {
                                    try {
                                        $btn = $driver->findElement(WebDriverBy::xpath('/html/body/main/div/div[3]/div[2]/div/div[4]/div[2]/a[2]'));
                                        if($btn) {
                                            $pdfUrl = $btn->getAttribute('href');
                                            $dwpdf_response = downloadpdf($pdfUrl);
                                            if($dwpdf_response) {
                                                $books_ids = $dwpdf_response;
                                            }
                                        }
                                    } catch (Exception $e) {
                                        $error = "Error: " . $e->getMessage();
                                        log_stat($error);
                                    }
                                } else {
                                    log_stat("book pdf stat is on");
                                }
                                /*skip_pdf_book:*/


                                if (!empty($title)) {
                                    $post_url_title =  preg_replace('/[^\w\/s+]+/u', '-', $title);
                                    $post_url_title = str_replace("/", "", $post_url_title);

                                    $cols = [
                                        "post_author" => $datasql['post_author'] ?? 1,
                                        "post_title" => $title,
                                        "post_url_title" => $post_url_title,
                                        "post_date_gmt" => gmdate("Y-m-d H:i:s"),
                                        "post_status" => $datasql['post_status'],
                                        "post_type" => $datasql['post_type'],
                                        "post_content" => '',
                                        "post_thumbnail" => $image_src,
                                        "post_in" => 'trusted',
                                        "post_lang" => $datasql['post_lang'],
                                        "comments_count" => 0,
                                        "reactions_count" => 0,
                                        "post_views" => 0,
                                        "post_share" => 0,
                                        "in_slide" => 'off',
                                        "in_special" => 'off',
                                        "info_id" => $datasql['id'],
                                    ];
                                    $insert = $dsql->dsql()->table('posts')->set($cols)->insert();
                                    if ($insert) {
                                        $success++;
                                        $current++;
                                        if ($progressCallback) {
                                            $progressCallback($current, $total, $success, $fails, $exists);
                                        }
                                        $dsql->dsql()->table('post_info')->set(['number_art' => $datasql['number_art'] + $success])->where('id', $datasql['id'])->update();
                                        $post_id = get_last_inserted_id();
                                        $cat_cols = [
                                            "post_id" => $post_id,
                                            "post_category" => $datasql['post_category']
                                        ];
                                        $dsql->dsql()->table('post_category')->set($cat_cols)->insert();

                                        $is_for_read = $datasql['book_without_pdf'] != "off" ? 'on' : 'off';
                                        $book_author = empty($book_author) ? 'غير محدد' : $book_author;
                                        $metas = [
                                            ['meta_key' => 'is_book_author', 'meta_value' => "no"],
                                            ['meta_key' => 'book_author', 'meta_value' => $book_author],
                                            ['meta_key' => 'is_book_translator', 'meta_value' => "no"],
                                            ['meta_key' => 'book_translator', 'meta_value' => $book_translator],
                                            ['meta_key' => 'is_for_read', 'meta_value' => $is_for_read],
                                            ['meta_key' => 'book_published_year', 'meta_value' => $book_published_year],
                                            ['meta_key' => 'book_pages', 'meta_value' => $book_pages],
                                            ['meta_key' => 'disable_copy', 'meta_value' => 1],
                                            ['meta_key' => 'disable_comments', 'meta_value' => "off"],
                                            ['meta_key' => 'notice', 'meta_value' => 1],
                                        ];

                                        $author_checker = $dsql->dsql()->table('authors')->where('name', $book_author)->limit(1)->getRow();
                                        if(!isset($author_checker['id'])) {
                                            $insert = $dsql->dsql()->table('authors')->set(['name' => $book_author])->insert();
                                            if ($insert) {
                                                $id = $dsql->lastInsertId();
                                            }
                                        } else {
                                            $id = $author_checker['id'];
                                        }

                                        if($id) {
                                            $metas[] = ['meta_key' => 'book_author_id', 'meta_value' => $id];
                                        }

                                        $source = [];

                                        if($datasql['post_source_1']) {
                                            if (preg_match('/^(.*?):(https?:\/\/.+)$/', $datasql['post_source_1'], $matches)) {
                                                $text = $matches[1]; // First part before colon
                                                $url = $matches[2];  // Second part (URL format) after colon
                                                $source[] = ['text' => $text, 'url' =>$url];
                                            }
                                        }
                                        if($datasql['post_source_2']) {
                                            if (preg_match('/^(.*?):(https?:\/\/.+)$/', $datasql['post_source_2'], $matches)) {
                                                $text = $matches[1]; // First part before colon
                                                $url = $matches[2];  // Second part (URL format) after colon
                                                $source[] = ['text' => $text, 'url' =>$url];
                                            }
                                        }
                                        $source = json_encode($source);
                                        $metas[] = ['meta_key' => 'source', 'meta_value' => $source ?? ''];
                                        $books_links = $books_links ?? "";
                                        if(!empty($books_links)) {
                                            $books_links = @serialize(@explode(PHP_EOL, $books_links));
                                            $metas[] = ['meta_key' => 'books_links', 'meta_value' => $books_links];
                                        }
                                        if(!empty($books_ids)) {
                                            $books_ids = @serialize($books_ids);
                                            $metas[] = ['meta_key' => 'books_ids', 'meta_value' => $books_ids];
                                        }
                                        foreach($metas as $meta) {
                                            $meta_cols = [
                                                "post_id" => $post_id,
                                                'meta_key' => $meta['meta_key'],
                                                'meta_value' => $meta['meta_value'],
                                            ];
                                            $dsql->dsql()->table('post_meta')->set($meta_cols)->insert();
                                        }
                                    } else {
                                        $fails++;
                                    }
                                } else {
                                    $fails++;
                                }



                            } catch(Exception $e) {
                                log_stat("no title");
                                $fails++;
                                continue;
                            }
                        } catch(Exception $e) {
                            log_stat("no link found");
                            $fails++;
                            continue;
                        } finally {
                            // Close the new tab and switch back to the original window
                            $driver->executeScript("window.close();");
                            $driver->switchTo()->window($driver->getWindowHandles()[0]);
                        }

                    } catch(Exception $e) {
                        log_stat("no link or no title found");
                        $fails++;
                        continue;
                    }
                }
            } catch (Exception $e) {
                log_stat("no containers found");
            }
        }
    } catch (Exception $e) {
        log_stat("no count found containers");
        $fails++;
    }
}