<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
set_time_limit(0); // Prevent timeout for long-running processes
ignore_user_abort(true); // Continue processing even if user closes the connection
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverTargetLocator;
use Smalot\PdfParser\Parser;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;

require_once 'func_driver.php';
require_once 'inc/pdf-scraper.php';

// Get info ID from command line argument
$id = intval($argv[1]);
// Function to update scraping status
function updateStatus($id, $status) {
    $status_file = ROOT . "/admin/temp/scraping_status_$id.json";
    file_put_contents($status_file, json_encode($status));
}
function getScrapingStatus($id) {
  $status_file = ROOT . "/admin/temp/scraping_status_$id.json";
  if (file_exists($status_file))
  {
    return json_decode(file_get_contents($status_file), true);
  }
  return null;
}
function updateProgress($id, $current, $total, $success = false, $fails = false, $exists = false) {
  $progress = ($total > 0) ? round(($current / $total) * 100) : 0;
  $status = getScrapingStatus($id);
  if ($status) {
    if($success) {
      $status['success'] = $success;
    }
    if($fails) {
      $status['fails'] = $fails;
    }
    if($exists) {
      $status['exists'] = $exists;
    }
    $status['progress'] = $progress;
    updateStatus($id, $status);
    
    // Force flush the output buffer to ensure immediate updates
    if (ob_get_level() > 0) {
        ob_flush();
        flush();
    }
  }
}
function fetchData($driver, $url, $data, $dsql, $image = false, $progressCallback = null) {
    $title = '';
    $image_src = '';
    $description = '';
    $success = 0;
    $fails = 0;
    $exists = 0;
    $total = $data['number_fetch']; // Total items to fetch
    $current = 0; // Current progress

    if(!checkFetchLimit($data['id'], $data['number_fetch'])) {
        return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
    }

    try {
        $driver->get($url);
        log_stat("Navigating to URL: " . $url);
        
        // Wait for body to be present
        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('body'))
        );

        // Update initial progress
        if ($progressCallback) {
          $progressCallback($current, $total, $success, $fails, $exists);
        }

        $container = $driver->findElement(WebDriverBy::xpath("
          //div[p and (h1 or h2 or h3 or h4 or h5 or h6)]
          | //section[p and (h1 or h2 or h3 or h4 or h5 or h6)]
          | //article[p and (h1 or h2 or h3 or h4 or h5 or h6)]
          | //div[contains(concat(' ', normalize-space(@class), ' '), ' content ')]
          | //div[contains(concat(' ', normalize-space(@class), ' '), ' post-content ')]
          | //div[contains(concat(' ', normalize-space(@class), ' '), ' post_content ')]
          | //div[contains(concat(' ', normalize-space(@class), ' '), ' news-loader ')]
        "));

        try {
          $titleElem = $container->findElement(WebDriverBy::xpath("
              //ancestor::*[h1 or h2 or h3 or h4 or h5 or h6][1]
              //h1 | //h2 | //h3 | //h4 | //h5 | //h6
          "));
          $title = $titleElem->getText();
          if($dsql->dsql()->table('posts')->where('post_title', 'LIKE', $title)->get()) {
            $exists++;
            return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
          }
        } catch (Exception $e) {
          // Handle missing title
          $title = $title;
        }
        // Extract image if the option is enabled
        if ($data['post_show_pic'] == 'off') {
          if($image === false) {
            $imageElem = $container->findElements(WebDriverBy::cssSelector('img'));
            $image = isset($imageElem[0]) ? $imageElem[0]->getAttribute('src') ?? $imageElem->getAttribute('data-src') : '';
          }
          try {
            if (!empty($image)) {
              $imageFunc = upimage($url, $image);
              if($imageFunc === false) {
                goto skip_image_step;
              } else {
                $image_src = $imageFunc;
              }
            } else {
              // No image URL found, skip image step
              goto skip_image_step;
            }
          } catch (Exception $e) {
              log_stat($e->getMessage());
              goto skip_image_step;
          }
          
        }
        skip_image_step:
        try {
          $articleHtml = $driver->executeScript("return arguments[0].outerHTML;", [$container]);
      
          if (empty($articleHtml)) {
            return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
          }
      
          if ($articleHtml) {
            // Load HTML into DOMDocument for parsing
            libxml_use_internal_errors(true);
            $doc = new DOMDocument('1.0', 'UTF-8');
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $articleHtml);
        
            // Initialize content variables
            $formattedContent = '';
        
            // Extract all headings (h1 to h6) and paragraphs, while keeping the order
            $blockElements = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img', 'br'];
            
            // Iterate over all elements in the container
            foreach ($doc->getElementsByTagName('*') as $element) {
                $tagName = strtolower($element->nodeName);
                
                // Handle block elements
                if (in_array($tagName, $blockElements)) {
                  switch ($tagName) {
                    case 'p':
                      $formattedContent .= "<p>{$element->nodeValue}</p>\n"; // Add paragraphs
                      break;
                    case 'h1':
                    case 'h2':
                    case 'h3':
                    case 'h4':
                    case 'h5':
                    case 'h6':
                      $formattedContent .= "<{$tagName}>{$element->nodeValue}</{$tagName}>\n\n"; // Add headings
                      break;
                    case 'img':
                      // Handle lazy-loaded images with 'src' or 'data-src'
                      $src = $element->getAttribute('data-original')
                          ?: $element->getAttribute('data-src')
                          ?: $element->getAttribute('data-lazy')
                          ?: $element->getAttribute('src');
      
                      if (!empty($src)) {
                        $imageFunc = upimage($url, $src);
                        if($imageFunc === false) {
                          break;
                        } else {
                          $id = $imageFunc;
                          $source_url = get_thumb($id, null);
                        }
                        $formattedContent .= "<img data-fid='". $id ."' src='". $source_url ."' alt='' />\n\n";
                      } else {
                        // No image URL found, skip image step
                        break;
                      }
                      break;
                    case 'br':
                      $formattedContent .= "<br />\n\n"; // Add line breaks
                      break;
                  }
                }
            }
            // Now $formattedContent contains the full HTML structure with headings, paragraphs, images, and line breaks.
            $description = $formattedContent;
          }
        } catch (Exception $e) {
          // Handle missing title
          $description = $description;
        }
      
        // Extract description (look for paragraph tags or other descriptive elements)
        try {
          // Ensure we have at least a valid link and title before saving the article
          if (!empty($title)) {
            $post_url_title =  preg_replace('/[^\w\/s+]+/u', '-', $title);
            $post_url_title = str_replace("/", "", $post_url_title);
      
            $cols = [
              "post_author" => $data['post_author'] ?? 1,
              "post_title" => $title,
              "post_url_title" => $post_url_title,
              "post_date_gmt" => gmdate("Y-m-d H:i:s"),
              "post_status" => $data['post_status'],
              "post_type" => $data['post_type'],
              "post_content" => $description ?? 'لا يوجد',
              "post_thumbnail" => intval($image_src) > 0 ? $image_src : NULL,
              "post_in" => 'trusted',
              "post_lang" => $data['post_lang'],
              "comments_count" => 0,
              "reactions_count" => 0,
              "post_views" => 0,
              "post_share" => 0,
              "in_slide" => 'off',
              "in_special" => 'off',
              "info_id" => $data['id'],
            ];
      
            $insert = $dsql->dsql()->table('posts')->set($cols)->insert();
            if ($insert) {
              $success++;
              $current++;
              if ($progressCallback) {
                $progressCallback($current, $total, $success, $fails, $exists);
              }
              $dsql->dsql()->table('post_info')->set(['number_art' => $data['number_art'] + $success])->where('id', $data['id'])->update();
              $post_id = get_last_inserted_id();
              $cat_cols = [
                "post_id" => $post_id,
                "post_category" => $data['post_category']
              ];
              $dsql->dsql()->table('post_category')->set($cat_cols)->insert();
              $source = [];
      
              if($data['post_source_1']) {
                if (preg_match('/^(.*?):(https?:\/\/.+)$/', $data['post_source_1'], $matches)) {
                  $text = $matches[1]; // First part before colon
                  $url = $matches[2];  // Second part (URL format) after colon
                  $source[] = ['text' => $text, 'url' =>$url];
                }
              }
              if($data['post_source_2']) {
                if (preg_match('/^(.*?):(https?:\/\/.+)$/', $data['post_source_2'], $matches)) {
                  $text = $matches[1]; // First part before colon
                  $url = $matches[2];  // Second part (URL format) after colon
                  $source[] = ['text' => $text, 'url' =>$url];
                }
              }
              $source = json_encode($source);
              
              $metas = [
                ['meta_key' => 'disable_copy', 'meta_value' => 1],
                ['meta_key' => 'disable_comments', 'meta_value' => "off"],
                ['meta_key' => 'notice', 'meta_value' => 1],
                ['meta_key' => 'source', 'meta_value' => $source ?? ''],
              ];
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
          }
        } catch (Exception $e) {
          // echo $e->getMessage() . "\n";
        }
        return ['status' => [
            'success' => $success,
            'fails' => $fails,
            'exists' => $exists
        ]];
    } catch (Exception $e) {
        log_stat("Error in fetchData: " . $e->getMessage());
        throw $e;
    }
}
function fetchDataBook($driver, $url, $datasql, $dsql, $progressCallback = null) {
    $success = 0;
    $fails = 0;
    $exists = 0;
    $total = $datasql['number_fetch']; // Total items to fetch
    $current = 0; // Current progress
    $exceptDatas = $datasql;
    try {
        if(strpos($url, 'archive.org')) {
            include './php_scripts/book/archive.php';
        }
        elseif (strpos($url, 'pdfdrive.com')) {
            include './php_scripts/book/pdfdrive.php';
        }
        elseif(strpos($url, 'alarabimag.com')) {
            include './php_scripts/book/alarabimag.php';
        }
        elseif(strpos($url, 'kutub-pdf.net')) {
            include './php_scripts/book/kutub-pdf.php';
        }
        elseif(strpos($url, 'pdf-ebooks.com')) {
            include './php_scripts/book/pdf-ebooks.php';
        }
        else {
            // Initial progress update
            if ($progressCallback) {
                $progressCallback($current, $total, $success, $fails, $exists);
            }
            try {
                $driver->get($url);
                $containers = $driver->wait(10, 1000)->until(
                    WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath("
                      //div[
                        .//img
                        and (
                          .//h1[.//a] or .//h2[.//a] or .//h3[.//a] or .//h4[.//a] or .//h5[.//a] or .//h6[.//a]
                        )
                      ]
                      [
                        @class = preceding-sibling::div/@class 
                        or 
                        @class = following-sibling::div/@class
                      ]
                      |
                      //div[
                        .//img
                      ][
                          @class = preceding-sibling::div/@class 
                          or 
                          @class = following-sibling::div/@class
                      ][
                        .//a 
                        or 
                        contains(@onclick, 'window.location.href')
                        or 
                        contains(@onclick, 'href')
                      ]
                      |
                      //div[
                        .//a
                        and (
                          .//h1 or .//h2 or .//h3 or .//h4 or .//h5 or .//h6
                        )
                      ]
                      [
                        @class = preceding-sibling::div/@class 
                        or 
                        @class = following-sibling::div/@class
                      ]
                      |
                      //article[
                        @class = preceding-sibling::article/@class 
                        or 
                        @class = following-sibling::article/@class
                      ]
                      |
                      //div[contains(concat(' ', normalize-space(@class), ' '), ' news-loader ')][
                        .//img
                        and (
                          .//h1[.//a] or .//h2[.//a] or .//h3[.//a] or .//h4[.//a] or .//h5[.//a] or .//h6[.//a]
                        )
                      ]
                    "))
                );
                if(is_array($containers) && count($containers) > 0) {
                    if(count($containers) < $datasql['number_fetch']) {
                        $scroll_script = "
                          let fetch_number = arguments[0];
                          let scrollHeight = (fetch_number * 9000) / 100;
                          let scrollInterval = setInterval(() => {{
                                if (window.scrollY < document.body.scrollHeight || window.scrollY < scrollHeight) {{
                                    window.scrollTo(0, window.scrollY + 300);
                                }} else {{
                                    clearInterval(scrollInterval);
                                }}
                            }}, 1000);
                        ";
                        $seconds = round(($datasql['number_fetch'] * 30) / 100);
                        $driver->executeScript($scroll_script, [$datasql['number_fetch']]);
                        sleep($seconds);
                        $containers = $driver->findElements(WebDriverBy::xpath("
                          //div[
                            .//img
                            and (
                              .//h1[.//a] or .//h2[.//a] or .//h3[.//a] or .//h4[.//a] or .//h5[.//a] or .//h6[.//a]
                            )
                          ]
                          [
                            @class = preceding-sibling::div/@class 
                            or 
                            @class = following-sibling::div/@class
                          ]
                          |
                          //div[
                            .//img
                          ][
                              @class = preceding-sibling::div/@class 
                              or 
                              @class = following-sibling::div/@class
                          ][
                            .//a 
                            or 
                            contains(@onclick, 'window.location.href')
                            or 
                            contains(@onclick, 'href')
                          ]
                          |
                          //div[
                            .//a
                            and (
                              .//h1 or .//h2 or .//h3 or .//h4 or .//h5 or .//h6
                            )
                          ]
                          [
                            @class = preceding-sibling::div/@class 
                            or 
                            @class = following-sibling::div/@class
                          ]
                          |
                          //article[
                            @class = preceding-sibling::article/@class 
                            or 
                            @class = following-sibling::article/@class
                          ]
                          |
                          //div[contains(concat(' ', normalize-space(@class), ' '), ' news-loader ')][
                            .//img
                            and (
                              .//h1[.//a] or .//h2[.//a] or .//h3[.//a] or .//h4[.//a] or .//h5[.//a] or .//h6[.//a]
                            )
                          ]
                        "));
                    }
                    foreach ($containers as $container) {
                        if(!checkFetchLimit($datasql['id'], $datasql['number_fetch'])) {
                            return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
                        }
                        $image_src = NULL;
                        try {
                            $imageElem = $container->findElements(WebDriverBy::cssSelector('img'));
                            if(count($imageElem) > 0) {
                                $imageElem = $imageElem[0];
                            }
                            if($imageElem) {
                                $image = $imageElem->getAttribute('src') ?? $imageElem->getAttribute('data-src') ?? '';
                                try {
                                    if ($image) {
                                        $imageFunc = upimage($url, $image);
                                        if($imageFunc === false) {
                                            goto skip_image;
                                        } else {
                                            $image_src = $imageFunc;
                                        }
                                    } else {
                                        // No image URL found, skip image step
                                        goto skip_image;
                                    }
                                } catch (Exception $e) {
                                    log_stat($e->getMessage());
                                    goto skip_image;
                                }
                            } else {
                                goto skip_image;
                            }
                        } catch (Exception $e) {
                            goto skip_image;
                        }
                        skip_image:
                        try {
                            // Get the container's outerHTML
                            $outerHtml = $driver->executeScript("return arguments[0].outerHTML;", [$container]);

                            // Regular expression to match hrefs and onclick URLs
                            $regexHref = '/href=["\']([^"\']+)["\']/i';
                            $regexOnclick = '/window\.location\.href=["\']([^"\']+)["\']/i';
                            $regexTitle = '/title=["\']([^"\']+)["\']/i';

                            $linkUrl = null;
                            $title = null;

                            try {
                                // 1. Try to find a title in heading tags (h1 to h6)
                                $headingElem = $container->findElement(WebDriverBy::xpath(".//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]"));
                                if ($headingElem) {
                                    $title = $headingElem->getText(); // Get the text of the heading
                                }
                            } catch (Exception $e) {
                                log_stat($e->getMessage());
                            }

                            try {
                                if (!$title) {
                                    $captionElem = $container->findElement(WebDriverBy::cssSelector("[class*='caption']"));
                                    if ($captionElem) {
                                        $title = $captionElem->getText(); // Get the text of the caption
                                    }
                                }
                            } catch (Exception $e) {
                                log_stat($e->getMessage());
                            }

                            try {
                                if (!$title && preg_match($regexTitle, $outerHtml, $matches)) {
                                    $title = $matches[1]; // Extracted title attribute from outerHTML
                                }
                            } catch (Exception $e) {
                                log_stat($e->getMessage());
                            }

                            if($title) {
                                if($dsql->dsql()->table('posts')->where('post_title', 'LIKE', $title)->get()) {
                                    log_stat("post exists");
                                    $exists++;
                                    continue;
                                }
                            }


                            // Try to find a href link in the outerHTML
                            if (preg_match($regexHref, $outerHtml, $matches)) {
                                $linkUrl = $matches[1];
                            }
                            // If no href, try to find an onclick URL
                            elseif (preg_match($regexOnclick, $outerHtml, $matches)) {
                                $linkUrl = $matches[1];
                            }

                            // If a URL is found, open it in a new tab
                            if ($linkUrl) {
                                $driver->executeScript("window.open('$linkUrl', '_blank');");
                                $driver->switchTo()->window($driver->getWindowHandles()[1]); // Switch to the new window
                                sleep(5);
                                $book_author = '';
                                $book_published_year = '';
                                $book_translator = '';
                                $book_pages = 0;
                                try {
                                    $authorElem = $driver->findElement(WebDriverBy::xpath(
                                        "//div[contains(text(), 'كاتب') or contains(text(), 'نوسەر') or contains(text(), 'ئەم بابەتە لەلایەن') or contains(text(), 'مؤلف')]" .
                                        "| //p[contains(text(), 'كاتب') or contains(text(), 'نوسەر') or contains(text(), 'ئەم بابەتە لەلایەن') or contains(text(), 'مؤلف')]" .
                                        "| //span[contains(text(), 'كاتب') or contains(text(), 'نوسەر') or contains(text(), 'ئەم بابەتە لەلایەن') or contains(text(), 'مؤلف')]" .
                                        "| //td[contains(text(), 'كاتب') or contains(text(), 'نوسەر') or contains(text(), 'ئەم بابەتە لەلایەن') or contains(text(), 'مؤلف')]" .
                                        "| //tr[contains(text(), 'كاتب') or contains(text(), 'نوسەر') or contains(text(), 'ئەم بابەتە لەلایەن') or contains(text(), 'مؤلف')]"
                                    ));
                                    if ($authorElem) {
                                        $label = $authorElem->getText();
                                        $book_author = $driver->executeScript("return arguments[0].nextElementSibling.innerText", [$authorElem]);
                                        if(empty($book_author)) {
                                            $authorParentText = $driver->executeScript("return arguments[0].parentElement.innerText", [$authorElem]);
                                            $book_author = str_replace($label, "", $authorParentText);
                                        }
                                        if(empty($book_author)) {
                                            $book_author = $authorElem->getText();
                                        }
                                    }
                                } catch (Exception $e) {
                                    log_stat($e->getMessage());
                                }

                                try {
                                    $yearElem = $driver->findElement(WebDriverBy::xpath(
                                        "//div[contains(text(), 'نشر') or contains(text(), 'سنة النشر') or contains(text(), 'اصدار') or contains(text(), 'پەخشی') or contains(text(), 'سالی') or contains(text(), 'ساڵی')]" .
                                        "| //p[contains(text(), 'نشر') or contains(text(), 'سنة النشر') or contains(text(), 'اصدار') or contains(text(), 'پەخشی') or contains(text(), 'سالی') or contains(text(), 'ساڵی')]" .
                                        "| //span[contains(text(), 'نشر') or contains(text(), 'سنة النشر') or contains(text(), 'اصدار') or contains(text(), 'پەخشی') or contains(text(), 'سالی') or contains(text(), 'ساڵی')]" .
                                        "| //td[contains(text(), 'نشر') or contains(text(), 'سنة النشر') or contains(text(), 'اصدار') or contains(text(), 'پەخشی') or contains(text(), 'سالی') or contains(text(), 'ساڵی')]" .
                                        "| //tr[contains(text(), 'نشر') or contains(text(), 'سنة النشر') or contains(text(), 'اصدار') or contains(text(), 'پەخشی') or contains(text(), 'سالی') or contains(text(), 'ساڵی')]"
                                    ));

                                    if ($yearElem) {
                                        $label = $yearElem->getText();
                                        $book_published_year = $driver->executeScript("return arguments[0].nextElementSibling.innerText", [$yearElem]);
                                        if(empty($book_published_year)) {
                                            $yearParentText = $driver->executeScript("return arguments[0].parentElement.innerText", [$yearElem]);
                                            $book_published_year = str_replace($label, "", $yearParentText);
                                        }
                                        if(empty($book_published_year)) {
                                            $book_published_year = $yearElem->getText();
                                        }
                                    }

                                } catch (Exception $e) {
                                    log_stat($e->getMessage());
                                }

                                try {

                                    $transElem = $driver->findElement(WebDriverBy::xpath(
                                        "//div[contains(text(), 'ترجمة') or contains(text(), 'وەرگێڕانی') or contains(text(), 'مترجم')]" .
                                        "| //p[contains(text(), 'ترجمة') or contains(text(), 'وەرگێڕانی') or contains(text(), 'مترجم')]" .
                                        "| //span[contains(text(), 'ترجمة') or contains(text(), 'وەرگێڕانی') or contains(text(), 'مترجم')]" .
                                        "| //td[contains(text(), 'ترجمة') or contains(text(), 'وەرگێڕانی') or contains(text(), 'مترجم')]" .
                                        "| //tr[contains(text(), 'ترجمة') or contains(text(), 'وەرگێڕانی') or contains(text(), 'مترجم')]"
                                    ));

                                    if ($transElem) {
                                        $label = $transElem->getText();
                                        $book_translator = $driver->executeScript("return arguments[0].nextElementSibling.innerText", [$transElem]);
                                        if(empty($book_translator)) {
                                            $translatorParentText = $driver->executeScript("return arguments[0].parentElement.innerText", [$transElem]);
                                            $book_translator = str_replace($label, "", $translatorParentText);
                                        }
                                        if(empty($book_translator)) {
                                            $book_translator = $transElem->getText();
                                        }
                                    }

                                } catch (Exception $e) {
                                    log_stat($e->getMessage());
                                }

                                try {
                                    $pageElem = $driver->findElement(WebDriverBy::xpath(
                                        "//div[contains(text(), 'صفحات') or contains(text(), 'لاپەڕە')]" .
                                        "| //p[contains(text(), 'صفحات') or contains(text(), 'لاپەڕە')]" .
                                        "| //span[contains(text(), 'صفحات') or contains(text(), 'لاپەڕە')]" .
                                        "| //td[contains(text(), 'صفحات') or contains(text(), 'لاپەڕە')]" .
                                        "| //tr[contains(text(), 'صفحات') or contains(text(), 'لاپەڕە')]"
                                    ));

                                    if ($pageElem) {
                                        $label = $pageElem->getText();
                                        $book_pages = $driver->executeScript("return arguments[0].nextElementSibling.innerText", [$pageElem]);
                                        if(empty($book_pages)) {
                                            $pageParentText = $driver->executeScript("return arguments[0].parentElement.innerText", [$pageElem]);
                                            $book_pages = str_replace($label, "", $pageParentText);
                                        }
                                        if(empty($book_pages)) {
                                            $book_pages = $pageElem->getText();
                                        }
                                        if($book_pages) {
                                            $book_pages = (int) $book_pages;
                                        }
                                    }

                                } catch (Exception $e) {
                                    log_stat($e->getMessage());
                                }

                                $books_ids = [];
                                if($datasql['book_without_pdf'] == 'off') {
                                    try {
                                        // Initialize the PDF scraper
                                        $scraper = new PdfScraper($driver);

                                        // Find PDF links
                                        $results = $scraper->findPdfLinks();

                                        if(is_array($results)) {
                                            $hasFileUrl = array_filter($results, function($item) {
                                                return isset($item['url']) && (strpos($item['url'], 'file://') !== false);
                                            });

                                            if (!empty($hasFileUrl)) {
                                                log_stat($hasFileUrl[0]['folder_name']);
                                                $dwpdf_response = downloadpdf('', 'default', false, $hasFileUrl[0]['folder_name']);
                                                if($dwpdf_response) {
                                                    $books_ids = $dwpdf_response;
                                                }
                                            } else {
                                                $hasExternalPdf = array_filter($results, function($item) {
                                                    return isset($item['url']) &&
                                                        (preg_match('/^https?:\/\/.+\.pdf$/i', $item['url']));
                                                });
                                                if (!empty($hasExternalPdf)) {
                                                    foreach ($hasExternalPdf as $pdf) {
                                                        $dwpdf_response = downloadpdf($pdf['url']);
                                                        if($dwpdf_response) {
                                                            $books_ids[] = $dwpdf_response;
                                                        } else {
                                                            continue;
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                    } catch (Exception $e) {
                                        $error = "Error: " . $e->getMessage();
                                        log_stat($error);
                                        goto skip_pdf_book;
                                    }
                                } else {
                                    goto skip_pdf_book;
                                }
                                skip_pdf_book:

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

                                // Close the new tab and switch back to the original window
                                $driver->executeScript("window.close();");
                                $driver->switchTo()->window($driver->getWindowHandles()[0]);
                            } else {
                                // No valid URL found, continue to next container
                                log_stat('No valid URL found');
                                continue;
                            }

                        } catch (Exception $e) {
                            log_stat('no outer html' . $e->getMessage());
                            continue;
                        }
                    }
                } else {
                    $fails++;
                }
            } catch (Exception $e) {
                $fails++;
            }

        }
    } catch (Exception $e) {
      log_stat("Error in fetch Data Book: " . $e->getMessage());
      throw $e;
    }
    $status = ['status'=> [
      'success' => $success,
      'fails' => $fails,
      'exists' => $exists,
    ]];
    return $status;
}
function upimage($url, $image) {
  global $dsql;
  $image_src = '';
  if(!is_null($url)) {
    $base_url = getBaseUrl($url);
    if (filter_var($image, FILTER_VALIDATE_URL) === false) {
      // Prepend base domain if it's a relative URL
      $image = rtrim($base_url, '/') . '/' . ltrim($image, '/');
    }
  }
  log_stat("image is: $image");
  $image_url_without_query = strtok($image, '?');

  // Initialize cURL
  $ch = curl_init($image);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set a reasonable timeout
  curl_setopt($ch, CURLOPT_FAILONERROR, true); // Fail on HTTP errors

  // Fetch the image
  $imageData = curl_exec($ch);

  // Check for cURL errors or empty content
  if ($imageData === false || curl_errno($ch)) {
    log_stat("Failed to download image: " . curl_error($ch)); // Log the error
    curl_close($ch);
    // Skip image handling and move to the next part of the code
    return false;
  }

  // Check HTTP response code
  $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($httpStatus >= 400) {
    log_stat("Invalid HTTP response code: " . $httpStatus); // Log the error
    curl_close($ch);
    // Skip image handling and move to the next part of the code
    return false;
  }

  // Validate if imageData is not empty
  if (strlen($imageData) == 0) {
    log_stat("Empty content received from: " . $image); // Log the error
    curl_close($ch);
    // Skip image handling and move to the next part of the code
    return false;
  }

  // Save to temporary file
  $tempFilePath = tempnam(sys_get_temp_dir(), 'upload_');
  file_put_contents($tempFilePath, $imageData);

  // Close cURL session
  curl_close($ch);

  // Continue with further validation and file handling
  $fileName = basename($image_url_without_query);
  // $extention = pathinfo($fileName, PATHINFO_EXTENSION);
  $extention_array = explode(".", $fileName);
  // $extention = end($extention_array) ?? "jpeg";
  $extention = "jpeg";
  $newFileName = generateRandomString(32) . '.' . $extention;
  $file_size = convert_size(filesize($tempFilePath));

  $general_settings = @unserialize(get_settings("site_general_settings"));
  $allowed_ext = $general_settings["site_allowed_ext"] ?? null;
  $ext_max_upload = $general_settings["ext_max_upload"] ?? null;
  $site_max_upload = $general_settings["site_max_upload"] ?? null;

  if ($site_max_upload < $file_size) {
    log_stat("File size exceeds maximum upload limit."); // Log the error
    return false;
  }

  // Create folder and move the file
  $folder_name = generateRandomString();
  if ($extention == "pdf") {
    $folder_name = "book/" . $folder_name;
  }
  mkdir(UPLOAD_DIR . $folder_name);

  $upload_dir = UPLOAD_DIR . $folder_name . '/';
  $uploadFilePath = $upload_dir . $newFileName;

  if (file_put_contents($uploadFilePath, $imageData) === false) {
      log_stat("Failed to save the image."); // Log the error
      return false;
  }

  // Insert file data into the database
  $file_access_key = generateRandomString(32);
  $datas = [
    "file_name" => $newFileName,
    "file_original_name" => $image_url_without_query,
    "file_dir" => $folder_name,
    "file_key" => $file_access_key,
    "mime_type" => 'image/jpeg',
    "file_upload_date" => gmdate("Y-m-d h:i:s"),
    "file_uploader" => $data['post_author'] ?? 1,
    "file_type" => 'user_attachment',
    "file_category" => 1,
  ];

  $insert = $dsql->dsql()->table('files')->set($datas)->insert();
  if ($insert) {
    $image_src = get_last_inserted_id();
  }
  unlink($tempFilePath);
  return $image_src;
}
function downloadpdf($pdfUrl, $filename = 'default', $curl_stat = true, $dirname = null) {
  global $dsql, $driver;
  $books_ids = [];

  // Generate the final file name and upload directory
  $extension = 'pdf';
  $newFileName = $filename . '.' . $extension;

  $folder_name = !is_null($dirname) ? $dirname : generateRandomString();
  $folder_name = "book/" . $folder_name;

  // Create the folder if it doesn't exist
  $upload_dir = UPLOAD_DIR . $folder_name . '/';
  if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
  }

  // Full path to the new PDF file
  $uploadFilePath = $upload_dir . $newFileName;

  if($curl_stat) {
      // Initialize cURL for file download
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $pdfUrl);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Set a reasonable timeout
      curl_setopt($ch, CURLOPT_FAILONERROR, true); // Fail on HTTP errors

      // Open a file pointer for writing
      $fp = fopen($uploadFilePath, 'w');

      if (!$fp) {
          log_stat("Failed to open file for writing: $uploadFilePath");
          curl_close($ch);
          return false;
      }

      // Set the file pointer as the output target for cURL
      curl_setopt($ch, CURLOPT_FILE, $fp);

      // Execute the cURL request to download the file directly to the target directory
      $result = curl_exec($ch);

      // Check for errors
      if ($result === false) {
          log_stat("Failed to download PDF: " . curl_error($ch)); // Log the error
          fclose($fp);
          curl_close($ch);
          unlink($uploadFilePath); // Remove partially downloaded file
          return false;
      }

      // Check HTTP response code
      $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($httpStatus >= 400) {
          log_stat("Invalid HTTP response code: " . $httpStatus); // Log the error
          fclose($fp);
          curl_close($ch);
          unlink($uploadFilePath); // Remove partially downloaded file
          return false;
      }

      // Close the file pointer and cURL session
      fclose($fp);
      curl_close($ch);
  }

  // Insert file data into the database
  $file_access_key = generateRandomString(32);
  $datas = [
    "file_name" => $newFileName,
    "file_original_name" => $filename,
    "file_dir" => $folder_name,
    "file_key" => $file_access_key,
    "mime_type" => 'application/pdf',
    "file_upload_date" => gmdate("Y-m-d h:i:s"),
    "file_uploader" => $datasql['post_author'] ?? 1,
    "file_type" => 'user_attachment',
    "file_category" => 1,
  ];

  $insert = $dsql->dsql()->table('files')->set($datas)->insert();
  if ($insert) {
    $book_id = get_last_inserted_id();
    $books_ids = [$book_id];
    return $books_ids;
  } else {
    log_stat("Failed to insert file data into the database.");
    return false;
  }
}
function waitForDownloadComplete($downloadPath, $timeout = 30) {
    $startTime = time();
    while ((time() - $startTime) < $timeout) {
        $files = glob($downloadPath . "/*.pdf"); // Look for the downloaded PDF

        // Check if a .crdownload file exists (indicating an incomplete download)
        $incompleteFiles = glob($downloadPath . "/*.crdownload");

        if (!empty($files) && empty($incompleteFiles)) {
            return reset($files); // Return the downloaded file path
        }

        sleep(1); // Wait for 1 second before checking again
    }

    return false; // Timeout reached without a valid file
}
function getNumberOfPagesFromPDF($pdfFilePath) {
  $parser = new Parser();
    try {
        $pdf = $parser->parseFile($pdfFilePath);
        $pages = $pdf->getPages();
        return count($pages);
    } catch (Exception $e) {
        // Handle error
        return 0;
    }
}
function checkFetchLimit($id, $number_fetch) {
  global $dsql;
  $fetch = true;
  $posts = $dsql->dsql()
    ->table('posts')
    ->where("DATE(post_date_gmt) = '". gmdate("Y-m-d") ."'")
    ->where("info_id", $id);

  $count = $posts->field('COUNT(*) as post_count')->getRow() ?? 0;
  $count = $count ? $count['post_count'] : 0;
  
  if($count >= $number_fetch && $number_fetch != 0) {
    $fetch = false;
  }
  return $fetch;
}
try {
    // Get post info data
    $data = $dsql->dsql()->table('post_info')->where('id', $id)->get();
    if (!$data) {
        throw new Exception("Post info not found");
    }
    $data = $data[0];

    log_stat("Starting scraping process for ID: " . $id);
    log_stat("Post type: " . $data['post_type']);
    log_stat("URL to scrape: " . $data['post_fetch_url']);

    // Check fetch limit
    if (!checkFetchLimit($id, $data['number_fetch'])) {
        throw new Exception("لقد تم جلب اقصى عدد محدد من البيانات لهذا اليوم");
    }

    // Initialize WebDriver
    for($i = 0; $i <= 3; $i++) {
        try {
            $driver = createDriver($headless = true, $blockAds = false, $capatcha = true, $displayImages = false);
            if($driver) {
                log_stat("WebDriver initialized successfully");
                break;
            }
        } catch (Exception $e) {
            log_stat("WebDriver initialization attempt " . ($i + 1) . " failed: " . $e->getMessage());
            continue;
        }
        sleep(1);
    }

    if (!$driver) {
        throw new Exception("Failed to initialize WebDriver after multiple attempts");
    }
    
    // Initialize status
    $status = [
        'status' => 'running',
        'progress' => 0,
        'success' => 0,
        'fails' => 0,
        'exists' => 0
    ];
    updateStatus($id, $status);
    
    // Start scraping based on post type
    if ($data['post_type'] == 'book') {
        $result = fetchDataBook($driver, $data['post_fetch_url'], $data, $dsql, function($current, $total, $success = false, $fails = false, $exists = false) use ($id) {
          updateProgress($id, $current, $total, $success, $fails, $exists);
        });
    } else {
        $result = fetchData($driver, $data['post_fetch_url'], $data, $dsql, false, function($current, $total, $success = false, $fails = false, $exists = false) use ($id) {
          updateProgress($id, $current, $total, $success, $fails, $exists);
        });
    }
    
    // Update final status
    $status['status'] = 'completed';
    $status['success'] = $result['status']['success'];
    $status['fails'] = $result['status']['fails'];
    $status['exists'] = $result['status']['exists'];
    $status['progress'] = 100;
    
    log_stat("Scraping completed for ID $id. Success: {$status['success']}, Fails: {$status['fails']}, Exists: {$status['exists']}");
    updateStatus($id, $status);
    
} catch (Exception $e) {
    log_stat("Error in background scraper: " . $e->getMessage());
    updateStatus($id, [
        'status' => 'error',
        'msg' => $e->getMessage(),
        'progress' => 0
    ]);
} finally {
    if (isset($driver)) {
        $driver->quit();
    }
    stopSeleniumAndChromeDriver();
}