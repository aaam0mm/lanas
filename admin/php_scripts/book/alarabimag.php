<?php
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\WebDriverWait;

$i = 0;
$finishedStat = false;
$init_url = $url;
for (;;) {
    $i++;
    if($finishedStat || $i >= 10) {
        break;
    }
    $url = $init_url . "?p=" . $i;
    try {
        $driver->get($url);
        sleep(1);
        $containers = $driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath("/html/body/div[1]/section/div[1]/div"))
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
                        $link = $container->findElement(WebDriverBy::xpath('./h2/a'))->getAttribute('href');
                        $title = $container->findElement(WebDriverBy::xpath('./h2/a'))->getText();
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
                                // pages
                                try {
                                    $pageElem = $driver->findElement(WebDriverBy::xpath(
                                        "//div[contains(text(), 'عدد الصفحات')]"
                                    ));

                                    if ($pageElem) {
                                        $book_pages = $driver->executeScript("return arguments[0].nextElementSibling.innerText", [$pageElem]);
                                        if($book_pages) {
                                            $book_pages = (int) $book_pages;
                                        }
                                    }

                                } catch (Exception $e) {
                                    log_stat($e->getMessage());
                                }
                                // author
                                try {
                                    $authorElem = $driver->findElement(WebDriverBy::xpath(
                                        "//div[contains(text(), 'مؤلف العمل')]"
                                    ));
                                    if ($authorElem) {
                                        $book_author = $driver->executeScript("return arguments[0].nextElementSibling.innerText", [$authorElem]);
                                    }
                                } catch (Exception $e) {
                                    log_stat($e->getMessage());
                                }
                                // image download
                                $image_src = NULL;
                                try {
                                    $image = $driver->findElement(WebDriverBy::xpath('/html/body/div[2]/div[1]/div[2]/div[2]/div[1]/figure/img'))->getAttribute('src');
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
                                        $driver->findElement(WebDriverBy::xpath('/html/body/div[2]/div[1]/div[2]/div[2]/div[1]/div[1]/div[1]/a'))->click();
                                        sleep(1);
                                        $wait = new WebDriverWait($driver, 35); // Wait up to 30 seconds
                                        $wait->until(function($driver) {
                                            $element = $driver->findElement(WebDriverBy::cssSelector('#download'));
                                            $display = $element->getCssValue('visibility');
                                            return $display === 'visible'; // Wait until display is 'none'
                                        });
                                        sleep(1);
                                        $btn = $driver->findElement(WebDriverBy::xpath("/html/body/div[2]/div[1]/div[2]/div[2]/div/div[2]/a[1]"));
                                        if($btn) {
                                            $scraper = new PdfScraper($driver);

                                            $init_folder_name = generateRandomString();
                                            $folder_name = "book/" . $init_folder_name;

                                            // Create the folder if it doesn't exist
                                            $upload_dir = UPLOAD_DIR . $folder_name;

                                            if (!file_exists($upload_dir)) {
                                                mkdir($upload_dir, 0755, true);
                                            }

                                            // Get Chrome DevTools
                                            $devTools = new ChromeDevToolsDriver($driver);

                                            // Enable DevTools and set new download directory
                                            $devTools->execute('Page.setDownloadBehavior', [
                                                'behavior' => 'allow',
                                                'downloadPath' => $upload_dir,
                                            ]);

                                            $scraper->setDownloadPath($upload_dir);

                                            $filesBefore = $scraper->getDirectoryFiles($scraper->downloadPath);

                                            $btn->click();

                                            if ($scraper->waitForDownload($filesBefore)) {
                                                // A download completed successfully
                                                $newFiles = array_diff($scraper->getDirectoryFiles($scraper->downloadPath), $filesBefore);

                                                if ($scraper->isPdfFile($newFiles[0])) {
                                                    $dwpdf_response = downloadpdf('', 'default', false, $init_folder_name);
                                                    if($dwpdf_response) {
                                                        $books_ids = $dwpdf_response;
                                                        rename($newFiles[0], $upload_dir . "/default.pdf");
                                                    }
                                                }
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
/*.Zebra_Pagination > ul:nth-child(1) > li*/