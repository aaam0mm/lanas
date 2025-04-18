<?php
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Smalot\PdfParser\Parser;
ini_set('max_execution_time', 300);
$action = isset($_POST['action']) ? $_POST['action'] : 'default';
require_once 'func_driver.php';

if($action == 'startScrapingOld') {
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
      error_log("Failed to download image: " . curl_error($ch)); // Log the error
      curl_close($ch);
      // Skip image handling and move to the next part of the code
      return false;
    }

    // Check HTTP response code
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpStatus >= 400) {
      error_log("Invalid HTTP response code: " . $httpStatus); // Log the error
      curl_close($ch);
      // Skip image handling and move to the next part of the code
      return false;
    }

    // Validate if imageData is not empty
    if (strlen($imageData) == 0) {
      error_log("Empty content received from: " . $image); // Log the error
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

    // if (!is_null($allowed_ext) && !in_array($extention, $allowed_ext)) {
    //   error_log("Invalid file extension."); // Log the error
    //   return false;
    // }

    if ($site_max_upload < $file_size) {
      error_log("File size exceeds maximum upload limit."); // Log the error
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
        error_log("Failed to save the image."); // Log the error
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
  function downloadpdf($pdfUrl, $filename = 'default') {
    global $dsql;
    $books_ids = [];
    // Generate the final file name and upload directory
    $extension = 'pdf';
    $newFileName = generateRandomString(32) . '.' . $extension;

    $folder_name = generateRandomString();
    $folder_name = "book/" . $folder_name;

    // Create the folder if it doesn't exist
    $upload_dir = UPLOAD_DIR . $folder_name . '/';
    if (!file_exists($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }

    // Full path to the new PDF file
    $uploadFilePath = $upload_dir . $newFileName;

    // Initialize cURL for file download
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pdfUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Set a reasonable timeout
    curl_setopt($ch, CURLOPT_FAILONERROR, true); // Fail on HTTP errors

    // Open a file pointer for writing
    $fp = fopen($uploadFilePath, 'w');

    if (!$fp) {
      error_log("Failed to open file for writing: $uploadFilePath");
      curl_close($ch);
      return false;
    }

    // Set the file pointer as the output target for cURL
    curl_setopt($ch, CURLOPT_FILE, $fp);

    // Execute the cURL request to download the file directly to the target directory
    $result = curl_exec($ch);

    // Check for errors
    if ($result === false) {
      error_log("Failed to download PDF: " . curl_error($ch)); // Log the error
      fclose($fp);
      curl_close($ch);
      unlink($uploadFilePath); // Remove partially downloaded file
      return false;
    }

    // Check HTTP response code
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpStatus >= 400) {
      error_log("Invalid HTTP response code: " . $httpStatus); // Log the error
      fclose($fp);
      curl_close($ch);
      unlink($uploadFilePath); // Remove partially downloaded file
      return false;
    }

    // Close the file pointer and cURL session
    fclose($fp);
    curl_close($ch);

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
      error_log("Failed to insert file data into the database.");
      return false;
    }
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
  function fetchData($driver,$url,$data, $dsql, $image = false) {
    $title = '';
    $image_src = '';
    $description = '';
    $success = 0;
    $fails = 0;
    $exists = 0;

    if(!checkFetchLimit($data['id'], $data['number_fetch'])) {
      return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
    }
    
    try {
      $driver->wait()->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName('body'))
      );
      // sleep(3);
      $container = $driver->findElement(WebDriverBy::xpath("
        //div[p and (h1 or h2 or h3 or h4 or h5 or h6)]
        | //section[p and (h1 or h2 or h3 or h4 or h5 or h6)]
        | //article[p and (h1 or h2 or h3 or h4 or h5 or h6)]
        | //div[contains(concat(' ', normalize-space(@class), ' '), ' content ')]
        | //div[contains(concat(' ', normalize-space(@class), ' '), ' post-content ')]
        | //div[contains(concat(' ', normalize-space(@class), ' '), ' post_content ')]
        | //div[contains(concat(' ', normalize-space(@class), ' '), ' news-loader ')]
      "));

    } catch (Exception $e) {
      // Handle missing title
      return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
    }
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
        // if(count($imageElem) > 0) {
          
        // }
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
          error_log($e->getMessage());
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
            // $dsql->dsql()->table('post_info')->set(["number_art" => $data['number_art'] + 1])->where('id', $data['id'])->update();
          }
        } else {
          $fails++;
        }
      }
    } catch (Exception $e) {
      // echo $e->getMessage() . "\n";
    }
    return ['status'=> [
      'success' => $success,
      'fails' => $fails,
      'exists' => $exists,
    ]];
  }
  function fetchDataBook($url,$datasql, $dsql) {
    global $driver;
    $success = 0;
    $fails = 0;
    $exists = 0;
    if(strpos($url, 'archive.org')) {
      // $category = urlencode(get_category_by_ids($datasql['post_category'])[0]['cat_title']);
      $category = get_category_by_ids($datasql['post_category'])[0]['cat_title'];
      $rows = $datasql['number_fetch'] ?? 15;
      $start = $datasql['number_art'] ?? 0;
      $lang_autority = 'on';
      $language = $datasql['post_lang'] && $datasql['post_lang'] == 'ar' ? 'Arabic' : 'Kurdish' ?? 'Arabic';
      $lang_query = $lang_autority == 'on' ? "%20AND%20language:$language" : '';
      // Extract collection name
      $fetch_url = $datasql['post_fetch_url'] ?? '';
      $collection = $fetch_url ? basename($fetch_url) : '';
      // API endpoint with pagination
      // $url = "https://archive.org/advancedsearch.php?q=collection:$category$lang_query&output=json&rows=$rows&start=$start";
      $url = "https://archive.org/advancedsearch.php?q=collection:$collection$lang_query&output=json&rows=$rows&start=$start";
      // Get the API response using file_get_contents() (you could replace this with cURL as well)
      $response = file_get_contents($url);
      // Check if the request was successful
      if ($response !== false) {
          // Decode the JSON response
          $data = json_decode($response, true);
          if ($data !== null && isset($data['response']['docs'])) {
            // Process each book
            foreach ($data['response']['docs'] as $doc) {
              if(!checkFetchLimit($datasql['id'], $datasql['number_fetch'])) {
                return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
              }
              $identifier = $doc['identifier'];
              // Extract the basic fields
              $title = $doc['title'] ?? '';
              if($dsql->dsql()->table('posts')->where('post_title', 'LIKE', $title)->get()) {
                $exists++;
                continue;
              }
              if (!isset($doc['creator'])) {
                  // Try to extract the creator (author) from the title using the hyphen as a delimiter
                  $titleParts = explode('-', $title);
              
                  // If the title has a hyphen, assume the last part is the author
                  if (count($titleParts) > 1) {
                      $book_author = trim($titleParts[count($titleParts) - 1]);  // The part after the hyphen is the author
                  } else {
                      // If no hyphen is found, fallback to unknown author
                      $book_author = '';
                  }
              } else {
                $book_author = is_array($doc['creator']) ? implode(', ', $doc['creator']) : $doc['creator'] ?? '';
              }
              $book_published_year = $doc['year'] ?? $doc['publicdate'] ?? '';

              // Generate thumbnail image source
              $image = "https://archive.org/services/img/" . $identifier;
              
              $image_src = NULL;
              try {
                if (!empty($image)) {
                  $imageFunc = upimage($url, $image);
                  if($imageFunc == false) {
                    goto skip_image_book_step;
                  } else {
                    $image_src = $imageFunc;
                  }
                } else {
                  // No image URL found, skip image step
                  goto skip_image_book_step;
                }
              } catch (Exception $e) {
                  error_log($e->getMessage());
                  goto skip_image_book_step;
              }

              skip_image_book_step:
              // Description (fallback if not available)
              $description = isset($doc['description']) ? (is_array($doc['description']) ? implode(' ', $doc['description']) : $doc['description']) : $doc['subject'] ?? '';

              // Translator (may be in contributor or other custom metadata)
              $book_translator = isset($doc['contributor']) ? (is_array($doc['contributor']) ? implode(', ', $doc['contributor']) : $doc['contributor']) : '';

              // Number of Pages
              $book_pages = $doc['number_of_pages'] ?? 0;

              // Book Link
              $books_links = "https://archive.org/details/" . $identifier;

              if($datasql['book_without_pdf'] == 'off') {
                $metadataUrl = "https://archive.org/metadata/$identifier";
                $metadataResponse = file_get_contents($metadataUrl);
                $metadata = json_decode($metadataResponse, true);
                
                if (isset($metadata['files'])) {
                  $pdfFound = false;  // Flag to check if a PDF is found
                  // Find the PDF file in the metadata
                  foreach ($metadata['files'] as $file) {
                      if (isset($file['format']) && strpos(strtolower($file['format']), 'pdf') !== false) {
                        $encodedFileName = rawurlencode($file['name']);  // Use rawurlencode() for the file name

                        // Construct the URL using the encoded file name
                        $pdfUrl = "https://archive.org/download/$identifier/$encodedFileName";
                        if($book_pages == 0) {
                          $book_pages = @getNumberOfPagesFromPDF($pdfUrl);
                        }
                        $dwpdf_response = downloadpdf($pdfUrl, $file['name']);
                        if($dwpdf_response) {
                          $books_ids = $dwpdf_response;
                        } else {
                          goto skip_pdf_book_step;
                        }
                        // break;
                      }
                    }
                    // If no PDF was found, skip to the next item
                    if (!$pdfFound) {
                      goto skip_pdf_book_step;
                    }
                } else {
                  goto skip_pdf_book_step;
                }
              } else {
                goto skip_pdf_book_step;
              }
              skip_pdf_book_step:

              try {
                // Ensure we have at least a valid link and title before saving the article
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
                    "post_content" => $description ?? 'لا يوجد',
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
                    $dsql->dsql()->table('post_info')->where('id', $datasql['id'])->set(["number_fetch" => $datasql['number_fetch'] + 1])->update();
                    $success++;
                    $post_id = get_last_inserted_id();
                    $cat_cols = [
                      "post_id" => $post_id,
                      "post_category" => $datasql['post_category']
                    ];
                    $dsql->dsql()->table('post_category')->set($cat_cols)->insert();


                    $metas = [
                      ['meta_key' => 'is_book_author', 'meta_value' => "no"],
                      ['meta_key' => 'book_author', 'meta_value' => $book_author],
                      ['meta_key' => 'is_book_translator', 'meta_value' => "no"],
                      ['meta_key' => 'book_translator', 'meta_value' => $book_translator],
                      ['meta_key' => 'is_for_read', 'meta_value' => "off"],
                      ['meta_key' => 'book_published_year', 'meta_value' => $book_published_year],
                      ['meta_key' => 'book_pages', 'meta_value' => $book_pages],
                      ['meta_key' => 'disable_copy', 'meta_value' => 1],
                      ['meta_key' => 'disable_comments', 'meta_value' => "off"],
                      ['meta_key' => 'notice', 'meta_value' => 1],
                    ];
                    $book_author = empty($book_author) ? 'غير محدد' : $book_author;
                    $author = $dsql->dsql()->table('authors')->field('id')->where('name', $book_author)->limit(1)->getRow();
                    if(!$author) {
                      $save = $dsql->dsql()->table('authors')->set(['name' => $book_author])->insert();
                      if($save) {
                        $id = $dsql->lastInsertId();
                      }
                    } else {
                      $id = $author['id'];
                    }
                    if ($id > 0) {
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
              } catch (Exception $e) {
                // echo $e->getMessage() . "\n";
                $fails++;
              }
            }
          } else {
            $fails++;
          }
      } else {
        $fails++;
      }
    } else {
      try {
        $driver->get($url);
        sleep(1);
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
        foreach ($containers as $container) {
          if(!checkFetchLimit($datasql['id'], $datasql['number_fetch'])) {
            return ['status' => ['success' => $success, 'fails' => $fails, 'exists' => $exists]];
          }
          $image_src = '';
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
                  error_log($e->getMessage());
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
              error_log($e->getMessage());
            }

            try {
              if (!$title) {
                $captionElem = $container->findElement(WebDriverBy::cssSelector("[class*='caption']"));
                if ($captionElem) {
                  $title = $captionElem->getText(); // Get the text of the caption
                }
              }
            } catch (Exception $e) {
              error_log($e->getMessage());
            }

            try {
              if (!$title && preg_match($regexTitle, $outerHtml, $matches)) {
                $title = $matches[1]; // Extracted title attribute from outerHTML
              }
            } catch (Exception $e) {
              error_log($e->getMessage());
            }

            if($title) {
              if($dsql->dsql()->table('posts')->where('post_title', 'LIKE', $title)->get()) {
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
                sleep(2);

                $book_author = '';
                $book_published_year = '';
                $book_translator = '';
                $book_pages = 0;
                try {
                  $authorElem = $driver->findElement(WebDriverBy::xpath(
                      "//*[contains(text(), 'Author') or contains(text(), 'By') or contains(text(), 'كاتب') or contains(text(), 'نوسەر') or contains(text(), 'ئەم بابەتە لەلایەن')]"
                  ));
                  
                  if ($authorElem) {
                      $book_author = $authorElem->getText(); // Extract the author text
                  }
                } catch (Exception $e) {
                  error_log($e->getMessage());
                }
                if(!$book_author) {
                  try {
                    // Try to find the author's name near typical containers, e.g., under <span> or <p>
                    $possibleAuthorElems = $driver->findElements(WebDriverBy::xpath(
                      "//*[contains(@class, 'author') or contains(@class, 'creator') or contains(@class, 'nawand')]"
                    ));
                    if (count($possibleAuthorElems) > 0) {
                      $book_author = $possibleAuthorElems[0]->getText(); // Get the author text
                    } else {
                      $bodyTextAuthor = $driver->findElement(WebDriverBy::tagName('body'))->getText();
                      $authorRegex = '/(?:ئەم بابەتە لەلایەن|نووسەر|مؤلف|بڵاوکەرەوە|ناشر|كاتب|author)\s*[:\-–]\s*([^<\n]+)/iu';
                      
                      if (preg_match($authorRegex, $bodyTextAuthor, $matches)) {
                          $book_author = trim($matches[1]); // Extract the first matching year
                      }
                    }
                  } catch (Exception $e) {
                    error_log($e->getMessage());
                  }
                }

                try {
                  $yearElem = $driver->findElement(WebDriverBy::xpath(
                      "//*[contains(text(), 'Published') or contains(text(), 'Year') or contains(text(), 'النشر') or contains(text(), 'پەخشی') or contains(text(), 'سالی') or contains(text(), 'ساڵی')]"
                  ));
                  
                  if ($yearElem) {
                      $book_published_year = $yearElem->getText(); // Extract the year text
                  }
              
                  // 3. If no direct year is found, use regex to find a 4-digit year in the page text
                  if (!$book_published_year) {
                      $bodyText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
                      
                      // Search for a year pattern (4 digits) using regex
                      if (preg_match('/\b(19|20)\d{2}\b/', $bodyText, $matches)) {
                          $book_published_year = $matches[0]; // Extract the first matching year
                      }
                  }
                } catch (Exception $e) {
                  error_log($e->getMessage());
                }

                if (!$book_published_year) {
                  try {
                    $bodyText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
                    
                    // Search for a year pattern (4 digits) using regex
                    if (preg_match('/\b(19|20)\d{2}\b/', $bodyText, $matches)) {
                        $book_published_year = $matches[0]; // Extract the first matching year
                    }
                  } catch (Exception $e) {
                    error_log($e->getMessage());
                  }
                }

                try {
                  // 1. Try to find the translator using specific keywords in multiple languages (Kurdish, Arabic, English)
                  $transElem = $driver->findElement(WebDriverBy::xpath(
                    "//*[contains(text(), 'translator') or contains(text(), 'ترجمة') or contains(text(), 'وەرگێڕانی') or contains(text(), 'مترجم')]"
                  ));
                  
                  if ($transElem) {
                      // Get the element next to the label to find the translator's name
                      $book_translator = $transElem->findElement(WebDriverBy::xpath("following-sibling::*[1]"))->getText();
                  }
              
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }

                if (!$book_translator) {
                  try {
                    $bodyText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
                      // 3. Use regex to match for the translator's name near the known labels
                      $translatorRegex = '/(?:وەرگێڕانی|مترجم|ترجمة|translator)\s*[:\-–]\s*([^<\n]+)/iu';
                      if (preg_match($translatorRegex, $bodyText, $matches)) {
                          $book_translator = trim($matches[1]); // Extract the translator's name
                      }
                    
                  } catch (Exception $e) {
                      error_log($e->getMessage());
                  }
                }

                if($datasql['book_without_pdf'] == 'off') {
                  try {
                    $downloadLink = $driver->findElement(WebDriverBy::xpath(
                      "//a[contains(text(), 'خوێندنەوە') or contains(text(), 'داونلۆدکردنی') or contains(text(), 'تحميل') or contains(text(), 'قراءة')]"
                    ));
                
                    // Get the href attribute of the found link
                    $pdfUrl = $downloadLink->getAttribute('href');
                    if($pdfUrl) {
                      $base_url = getBaseUrl($url);
                      if (filter_var($pdfUrl, FILTER_VALIDATE_URL) === false) {
                        // Prepend base domain if it's a relative URL
                        $pdfUrl = rtrim($base_url, '/') . '/' . ltrim($pdfUrl, '/');
                      }
                      $books_links = $pdfUrl;
                      if($book_pages == 0) {
                        $book_pages = @getNumberOfPagesFromPDF($pdfUrl);
                      }
                      $dwpdf_response = downloadpdf($pdfUrl, $title ?? 'default');
                      if($dwpdf_response) {
                        $books_ids = $dwpdf_response;
                        // dd(convertPdfToTextUsingOCR(get_file($books_ids[0])));
                      } else {
                        goto skip_pdf_book;
                      }
                    } else {
                      goto skip_pdf_book;
                    }
                  } catch (Exception $e) {
                    error_log($e->getMessage());
                    goto skip_pdf_book;
                  }
                } else {
                  goto skip_pdf_book;
                }
                skip_pdf_book:


                try {
                  // Ensure we have at least a valid link and title before saving the article
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
                      "post_content" => $description ?? 'لا يوجد',
                      // "post_thumbnail" => filter_var($image_src, FILTER_VALIDATE_URL) ? $image_src : NULL,
                      "post_thumbnail" => intval($image_src) > 0 ? $image_src : NULL,
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
                      $post_id = get_last_inserted_id();
                      $cat_cols = [
                        "post_id" => $post_id,
                        "post_category" => $datasql['post_category']
                      ];
                      $dsql->dsql()->table('post_category')->set($cat_cols)->insert();

                      $metas = [
                        ['meta_key' => 'is_book_author', 'meta_value' => "no"],
                        ['meta_key' => 'book_author', 'meta_value' => $book_author],
                        ['meta_key' => 'is_book_translator', 'meta_value' => "no"],
                        ['meta_key' => 'book_translator', 'meta_value' => $book_translator],
                        ['meta_key' => 'is_for_read', 'meta_value' => "off"],
                        ['meta_key' => 'book_published_year', 'meta_value' => $book_published_year],
                        ['meta_key' => 'book_pages', 'meta_value' => $book_pages],
                        ['meta_key' => 'disable_copy', 'meta_value' => 1],
                        ['meta_key' => 'disable_comments', 'meta_value' => "off"],
                        ['meta_key' => 'notice', 'meta_value' => 1],
                      ];
                      $book_author = empty($book_author) ? 'غير محدد' : $book_author;
                      $insert = $dsql->dsql()->table('authors')->set(['name' => $book_author])->insert();
                      if ($insert) {
                        $metas[] = ['meta_key' => 'book_author_id', 'meta_value' => $dsql->lastInsertId()];
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
                } catch (Exception $e) {
                  // echo $e->getMessage() . "\n";
                  $fails++;
                }

                // Close the new tab and switch back to the original window
                $driver->executeScript("window.close();");
                $driver->switchTo()->window($driver->getWindowHandles()[0]);
            } else {
                // No valid URL found, continue to next container
                continue;
            }
            
          } catch (Exception $e) {
              error_log($e->getMessage());
              continue;
          }
        }
      } catch (Exception $e) {
        error_log('no containers');
        $fails++;
      }
    }
    return ['status'=> [
      'success' => $success,
      'fails' => $fails,
      'exists' => $exists,
    ]];
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
  $response = [];
  $errors = [];
  $id =  isset($_POST["id"]) ? $_POST["id"] : 0;

  $data = $dsql->dsql()->table('post_info')->where('id', $id)->get();
  $data = $data[0];

  try {
    if(!checkFetchLimit($id, $data['number_fetch'])) {
      $response["status"] = "error";
      $response["msg"] = _t("لقد تم جلب اقصى عدد محدد من البيانات لهذا اليوم");
      die(json_encode($response));
    }

    for($i = 0;$i <= 3; $i++) {
      try {
        $driver = createDriver($headless = true, $blockAds = false, $capatcha = true, $displayImages = false);
      } catch (Exception $e) {
        continue;
      }
      if($driver) {
        break;
      }
    }
    
    function getBookFromPage($url) {
      global $data, $dsql;
      $stats = [
        'status' => [
          'success' => 0,
          'fails' => 0,
          'exists' => 0,
        ]
      ];

      $status = fetchDataBook($url, $data, $dsql);

      $stats['status']['success'] += $status['status']['success'];
      $stats['status']['fails'] += $status['status']['fails'];
      $stats['status']['exists'] += $status['status']['exists'];

      if($stats['status']['success'] > 0 || $stats['status']['exists'] > 0) {
        $number_art = $data['number_art'] + $stats['status']['success'];
        $dsql->dsql()->table('post_info')->set(['number_art' => $number_art])->where('id', $data['id'])->update();
        $response["status"] = "success";
        $response["msg"] = _t("البيانات المرفوعة بنجاح:(". $stats['status']['success'] ."), البيانات التي تم تجاهلها(". $stats['status']['exists'] .")");
      } else {
        $response["status"] = "error";
        $response["msg"] = _t("البيانات المرفوعة بنجاح:(". $stats['status']['success'] ."), البيانات التي فشلت:(". $stats['status']['fails'] .")");
      }

      if ($stats['status']['success'] > 0 || $stats['status']['exists'] > 0 || $stats['status']['fails'] > 0) {
    
        // Add current date to the stats
        $stats['date'] = date('Y-m-d H:i:s');  // Format the date as 'YYYY-MM-DD HH:MM:SS'
    
        // Fetch existing `fetch_details` from the database
        $details = $dsql->dsql()->table('post_info')->field('fetch_details')->where('id', $data['id'])->getOne();
        
        // Decode the existing details if not empty, or start with an empty array
        $details_array = !empty($details) ? json_decode($details, true) : [];
    
        // Append the new stats (with date) to the existing data
        $details_array[] = $stats;  // Append the new stats array with the date to the decoded data
    
        // Encode the updated details back to JSON
        $new_details = json_encode($details_array);
    
        // Update the `fetch_details` column in the database
        $dsql->dsql()->table('post_info')->set(['fetch_details' => $new_details])->where('id', $data['id'])->update();
      }

      if(isset($_POST['from']) && $_POST['from'] == "auto") {
        $program = $dsql->dsql()->table('post_info_program')->where('id', $_POST['program_id'])->getRow();
        if($program) {
          $programs = json_decode($program['program'], true);
          if($programs) {
            foreach($programs as $key => $v) {
              if($v['sub_id'] == $_POST['sub_program_id'] && $v['day'] != 'every') {
                $programs[$key]['stat'] = "passed";
              }
            }
          }
          $program = json_encode($programs);
        }
        $dsql->dsql()->table('post_info_program')->where('id', $_POST['program_id'])->set(['program' => $program])->update();
      }

      $response = json_encode($response);
      return $response;
    }

    function getArticlesFromPage($driver, $url) {
      global $data, $dsql;
      $stats = [
        'status' => [
          'success' => 0,
          'fails' => 0,
          'exists' => 0,
        ]
      ];
      $driver->get($url);
      // humanSleep(5, 7);
      try {
        $cnt = $driver->findElement(WebDriverBy::xpath("
          //div[p and (h1 or h2 or h3 or h4 or h5 or h6)]
          | //section[p and (h1 or h2 or h3 or h4 or h5 or h6)]
          | //article[p and (h1 or h2 or h3 or h4 or h5 or h6)]
          | //div[contains(concat(' ', normalize-space(@class), ' '), ' content ')]
          | //div[contains(concat(' ', normalize-space(@class), ' '), ' post-content ')]
          | //div[contains(concat(' ', normalize-space(@class), ' '), ' post_content ')]
          | //table[contains(concat(' ', normalize-space(@class), ' '), ' item ')]
        "));
        if($cnt) {
          $status = fetchData($driver,$url,$data, $dsql, false);
          $stats['status']['success'] += $status['status']['success'];
          $stats['status']['fails'] += $status['status']['fails'];
          $stats['status']['exists'] += $status['status']['exists'];
        }
        sleep(1);
      } catch (Exception $e) {
        // goto not_sigle_post;
        error_log("no single data");
        // echo "Failed to retrieve articles from page: " . $e->getMessage() . "\n";
      }
      not_sigle_post:
      try {
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
          //div[
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
          //article[
            .//img
            and (
              .//h1[.//a] or .//h2[.//a] or .//h3[.//a] or .//h4[.//a] or .//h5[.//a] or .//h6[.//a]
            )
          ]
          |
          //table[
            .//img
            and (
              .//h1[.//a] or .//h2[.//a] or .//h3[.//a] or .//h4[.//a] or .//h5[.//a] or .//h6[.//a]
            )
          ]
        "));

        foreach ($containers as $container) {
          $image = false;
          try {
            $imageElem = $container->findElements(WebDriverBy::cssSelector('img'));
            if(count($imageElem) > 0) {
              $imageElem = $imageElem[0];
            }
            if($imageElem) {
              $image = $imageElem->getAttribute('src') ?? $imageElem->getAttribute('data-src') ?? '';
            } else {
              $image = false;
            }
          } catch (Exception $e) {
            // Handle any exception, e.g., if no matching container is found
            $imageElem = false;
            goto skip_image;
          }
          skip_image:
          try {
            $linkElem = $container->findElement(WebDriverBy::xpath(".//a[@href]"));
            if ($linkElem) {
              $linkUrl = $linkElem->getAttribute('href');

              $driver->executeScript("window.open('$linkUrl', '_blank');");
              $driver->switchTo()->window($driver->getWindowHandles()[1]); // Switch to the new window
              sleep(1);
              try {
                $result = fetchData($driver,$url, $data, $dsql, $image);
                $stats['status']['success'] += $result['status']['success'];
                $stats['status']['fails'] += $result['status']['fails'];
                $stats['status']['exists'] += $result['status']['exists'];
              
              } catch (Exception $e) {
                // Handle any exception, e.g., if no matching container is found
              }

            }
            $driver->executeScript("window.close();");
            $driver->switchTo()->window($driver->getWindowHandles()[0]);
          } catch (Exception $e) {
            if($driver) {
              $driver->executeScript("window.close();");
              $driver->switchTo()->window($driver->getWindowHandles()[0]);
            }
          }
        }
        
        final_each:
        if($stats['status']['success'] > 0 || $stats['status']['exists'] > 0) {
          $number_art = $data['number_art'] + $stats['status']['success'];
          $dsql->dsql()->table('post_info')->set(['number_art' => $number_art])->where('id', $data['id'])->update();
          $response["status"] = "success";
          $response["msg"] = _t("البيانات المرفوعة بنجاح:(". $stats['status']['success'] ."), البيانات التي تم تجاهلها(". $stats['status']['exists'] .")");
        } else {
          $response["status"] = "error";
          $response["msg"] = _t("البيانات المرفوعة بنجاح:(". $stats['status']['success'] ."), البيانات التي فشلت:(". $stats['status']['fails'] .")");
        }
        
        if ($stats['status']['success'] > 0 || $stats['status']['exists'] > 0 || $stats['status']['fails'] > 0) {
    
          // Add current date to the stats
          $stats['date'] = date('Y-m-d H:i:s');  // Format the date as 'YYYY-MM-DD HH:MM:SS'
      
          // Fetch existing `fetch_details` from the database
          $details = $dsql->dsql()->table('post_info')->field('fetch_details')->where('id', $data['id'])->getOne();
          
          // Decode the existing details if not empty, or start with an empty array
          $details_array = !empty($details) ? json_decode($details, true) : [];
      
          // Append the new stats (with date) to the existing data
          $details_array[] = $stats;  // Append the new stats array with the date to the decoded data
      
          // Encode the updated details back to JSON
          $new_details = json_encode($details_array);
      
          // Update the `fetch_details` column in the database
          $dsql->dsql()->table('post_info')->set(['fetch_details' => $new_details])->where('id', $data['id'])->update();
        }
      } catch (Exception $e) {
        error_log('no containers');
        $response["status"] = "error";
        $response["msg"] = _t("لم يتم العثور على اي بيانات في هذا الرابط");
      }
      if(isset($_POST['from']) && $_POST['from'] == "auto") {
        $program = $dsql->dsql()->table('post_info_program')->where('id', $_POST['program_id'])->getRow();
        if($program) {
          $programs = json_decode($program['program'], true);
          if($programs) {
            foreach($programs as $key => $v) {
              if($v['sub_id'] == $_POST['sub_program_id'] && $v['day'] != 'every') {
                $programs[$key]['stat'] = "passed";
              }
            }
          }
          $program = json_encode($programs);
        }
        $dsql->dsql()->table('post_info_program')->where('id', $_POST['program_id'])->set(['program' => $program])->update();
      }
      $response = json_encode($response);
      return $response;
    }

    if($data['post_type'] == 'book') {
      echo getBookFromPage($data['post_fetch_url']);
    } else {
      echo getArticlesFromPage($driver, $data['post_fetch_url']);
    }
    $driver->quit();
  } finally {
    // Stop Selenium and ChromeDriver after use
    stopSeleniumAndChromeDriver();
  }
} elseif($action == 'startScraping') {
  // Start the scraping process in the background
  $id = isset($_POST["id"]) ? $_POST["id"] : 0;

  // Use shell_exec to run the scraping script in the background
  $command = "php ". ROOT ."/admin/scraping_script.php $id > /dev/null 2>&1 &";
  shell_exec($command);

  // Immediately return a response to the client
  $response = [
    "status" => "success",
    "msg" => "Scraping process started in the background."
  ];

  echo json_encode($response);
  exit;
} elseif($action == 'saveLink') {
  $response = [];
  if (!function_exists("generateRandomString")) {

    /**
     * generateRandomString()
     *
     * @param int $length
     * @return string
     */
    function generateRandomString($length = 10)
    {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
    }
  }

  $account =  isset($_POST["account"]) ? $_POST["account"] : 'off';
  $auto_share =  isset($_POST["auto_share"]) ? $_POST["auto_share"] : 'off';
  $post_status = $auto_share != 'off' ? 'publish' : 'auto-draft';
  $type =  isset($_POST["type"]) ? $_POST["type"] : 'article';
  $count =  isset($_POST["count"]) ? $_POST["count"] : 10;
  $post_category =  isset($_POST["department"]) ? $_POST["department"] : null;
  $language =  isset($_POST["language"]) ? $_POST["language"] : 'off';
  $show_pic =  isset($_POST["show_pic"]) ? 'on' : 'off';
  $book_without_pdf =  $_POST["book_without_pdf"] ?? 'off';
  $source1 =  isset($_POST["source1"]) ? $_POST["source1"] : 'off';
  $source2 =  isset($_POST["source2"]) ? $_POST["source2"] : 'off';
  $type =  isset($_POST["type"]) ? $_POST["type"] : 'off';
  $url =  isset($_POST["url"]) ? $_POST["url"] : 'off';

  global $dsql;

  $post_key = generateRandomString(9);
  // $post_key = 'j8ypnnke7';

  while($dsql->dsql()->table('post_info')->where('post_key', $post_key)->get()) {
    $post_key = generateRandomString(9);
  }

  $cols = [
    "post_author" => $account ?? 1,
    "post_key" => $post_key,
    "post_date_gmt" => gmdate("Y-m-d H:i:s"),
    "post_status" => $post_status,
    "post_type" => $type,
    "number_fetch" => $count,
    "post_category" => $post_category,
    "post_show_pic" => $show_pic,
    "book_without_pdf" => $book_without_pdf,
    "post_lang" => $language,
    "post_source_1" => $source1,
    "post_source_2" => $source2,
    "post_fetch_url" => $url,
    "post_in" => 'trusted'
  ];

  if(isset($_POST['info_id'])) {
    $save = $dsql->dsql()->table('post_info')->where('id', $_POST['info_id'])->set($cols)->update();
  } else {
    $save = $dsql->dsql()->table('post_info')->set($cols)->insert();
  }

  if($save) {
    $response['status'] = "success";
    $response['redirect'] = siteurl() . '/admin/dashboard/contents';
    $response['msg'] = 'تم حفظ الرابط بنجاج';
  } else {
    $response['status'] = "error";
    $response['msg'] = 'يوجد خلل في الاضافة';
  }
  $response = json_encode($response);
  echo $response;

} elseif($action == 'saveProgram') {  
  $response = [$general_settings['timezone']];
  $user_timezone = $_POST["user_timezone"] ?? 'UTC';

  //$general_settings = @unserialize(get_settings("site_general_settings"));
  //$general_settings['timezone'] = $user_timezone;
  //$general_settings = serialize($general_settings);
  //$update_meta_settings = update_meta_settings("site_general_settings", $general_settings);
  
  $info_id = $_POST["info_id"] ?? 0;
  $schedule_type = $_POST["schedule_type"] ?? 'every';
  $days = isset($_POST["days"]) ? array_map('trim', explode(',', $_POST["days"])) : [];
  $program_time = $_POST["program_time"] ?? gmdate("Y-m-d H:i:s");

  // 'timezone' => $user_timezone
  
  if($info_id == 0) {
      $response['status'] = "error";
      $response['msg'] = 'Info ID is required';
      die(json_encode($response));
  }

  $program = [];
  if(count($days) > 0) {
      foreach($days as $index => $day) {
          $program[] = [
              'sub_id' => $index + 1,
              'day' => $day,
              'time' => $program_time,
              'stat' => 'not'
          ];
      }
  } else {
      $program[] = [
          'sub_id' => 1,
          'day' => 'every',
          'time' => $program_time,
          'stat' => 'not'
      ];
  }

  // Delete existing program
  $dsql->dsql()->table('post_info_program')
        ->where('post_info_id', $info_id)
        ->delete();

  // Insert new program
  $insert = $dsql->dsql()->table('post_info_program')
          ->set([
              'post_info_id' => $info_id,
              'program' => json_encode($program)
          ])
          ->insert();

  if($insert) {
      $response['status'] = "success";
      $response['msg'] = 'Program saved successfully';
      $response['redirect'] = siteurl() . '/admin/dashboard/contents';
  } else {
      $response['status'] = "error";
      $response['msg'] = 'Error saving program';
  }
  
  echo json_encode($response);
  exit();

} elseif($action == 'getCloserProgram') {
    $general_settings = @unserialize(get_settings("site_general_settings"));
    $timezone = $general_settings['timezone'] ?? 'UTC';
    // Get current time in UTC
    $current_time = new DateTime('now', new DateTimeZone($timezone));
    $future_programs = [];

    // Fetch all programs
    $programs = $dsql->dsql()->table('post_info_program')->get();
    if ($programs) {
        foreach ($programs as $program) {
            $program_details = json_decode($program['program'], true);

            foreach ($program_details as $details) {
                if ($details['stat'] == 'not') {
                    $program_time = null;

                    if ($details['day'] == 'every') {
                        // For daily programs, use today's date
                        $time_parts = explode(':', $details['time']);
                        $program_time = new DateTime('now', new DateTimeZone($timezone));
                        $program_time->setTime(
                            intval($time_parts[0]), 
                            intval($time_parts[1]), 
                            isset($time_parts[2]) ? intval($time_parts[2]) : 0
                        );
                    } else {
                        // For specific dates
                        $program_time = new DateTime($details['day'] . ' ' . $details['time'], new DateTimeZone($timezone));
                    }

                    // if ($program_time) {
                    //   $program_time->modify('-1 hour');
                    // }

                    if ($program_time && $program_time >= $current_time) {
                        $details['program_id'] = $program['id'];
                        $details['post_info_id'] = $program['post_info_id'];

                        $future_programs[] = [
                            'program_time' => $program_time->getTimestamp(),
                            'details' => $details
                        ];
                    }
                }
            }
        }
    }

    // Sort by closest time
    usort($future_programs, function($a, $b) {
        return $a['program_time'] - $b['program_time'];
    });

    if (!empty($future_programs)) {
        $closest_program = $future_programs[0]['details'];
        $delay_in_ms = ($future_programs[0]['program_time'] - $current_time->getTimestamp()) * 1000;

        echo json_encode([
            'status' => 'success',
            'delay_in_ms' => $delay_in_ms,
            'program' => $closest_program
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No upcoming programs found'
        ]);
    }
    exit();
} elseif($action == 'getcatlang') {
  $texo = $_POST["texo"] ?? null;
  $texo = empty($texo) ? null : $texo;
  // $content_lang = $_POST["language"] ?? current_lang();
  $content_lang = $_POST["language"] ?? false;
  $categories = get_categories($texo, null, $content_lang);
  echo json_encode($categories);
} elseif($action == 'getfetchdetails') {
  $info_id = $_POST["info_id"];
  $details = $dsql->dsql()->table('post_info')->field('fetch_details')->where('id', $info_id)->getOne();
  echo $details;
} elseif($action == 'getfetchcomments') {
  $comment_name = $_POST["comment_name"];
  $details = $dsql->dsql()->table('boot_comments')->field('boot_comments.*')->where('comment_name', $comment_name)->get();
  echo json_encode($details);
} elseif($action == 'summarybook') {
  try {
    $driver = createDriver($headless = true, $blockAds = false, $capatcha = true, $displayImages = false);
    $driver->get("https://chatgpt.com/");
    sleep(3);
    try {
      // $inputFile = $driver->findElement(WebDriverBy::cssSelector('input[type="file"].hidden'));
      // if($inputFile) {
      //   $driver->executeScript("return arguments[0].classList;", $inputFile);
      //   $inputFile->sendKeys("/Users/salaheddine/Desktop/books/تاريخ-الاكراد.pdf");
      // }
      $textarea = $inputFile->findElement(WebDriverBy::cssSelector("#prompt-textarea"));
      sleep(1);
      $textarea->sendKeys("اريد منك تلخيص الكتاب المرفق");
      sleep(2);
      $driver->takeScreenshot("./chatgpt.png");
    } catch (Exception $e) {
      dd($e->getMessage());
    }
  } catch (Exception $e) {
    dd($e->getMessage());
  }
} elseif($action == "getbootfamily") {
  $boot_id = $_POST['boot_id'] ?? "";
  if(empty($boot_id)) {
    return "";
  }
  $boots = $dsql->dsql()->table('boots')->where('id', $boot_id)->field('users_family')->getOne();
  $boots = json_decode($boots, true);
  $users_family_str = implode(",", $boots);
  $users = $dsql->dsql()
  ->expr("SELECT users.* FROM users WHERE users.id IN($users_family_str);")
  ->get();
  $html = '';
  if ($users):
    foreach ($users as $user):
      $html.= '<div class="user-item mb-3 py-2" data-user-id="'. $user['id'] .'">
                  <img src="'. get_thumb($user["user_picture"]) .'" alt="'. htmlspecialchars($user['user_name']) .'" class="rounded-circle" width="40" height="40">
                  <span class="ml-2">'. htmlspecialchars($user['user_name']) .'</span>
              </div>';
    endforeach;
  else:
    $html.= '<p>لا يوجد مقيمين</p>';
  endif;
  echo $html;
} elseif($action == 'bootstartwork') {
  require_once 'boot-automation.php';
  // Usage
  $id = $_POST['id'] ?? 0;
  $stat = $_POST['stat'] ?? null;

  if ($stat == 0) {
      $automation = new BootAutomation($dsql);
      $result = $automation->startWork($id);
      echo json_encode($result);
      exit;
  } else {
      $dsql->dsql()
          ->table('boots')
          ->set(["stat" => 0])
          ->where("id", $id)
          ->update();
          
      echo json_encode([
        'success' => true,
        'stat' => 0,
        'msg' => 'البوت لم يتم جميع عملياته بعد'
      ]);
      exit;
  }
}