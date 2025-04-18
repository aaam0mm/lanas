<?php
log_stat("Processing archive.org URL: " . $url);

// Initial progress update
if ($progressCallback) {
    $progressCallback($current, $total, $success, $fails, $exists);
}

// $category = urlencode(get_category_by_ids($datasql['post_category'])[0]['cat_title']);
/*$category = get_category_by_ids($datasql['post_category'])[0]['cat_title'];*/
$rows = $datasql['number_fetch'] ?? 15;
$start = $datasql['number_art'] ?? 0;
$lang_autority = 'on';
$language = $datasql['post_lang'] && $datasql['post_lang'] == 'ar' ? 'Arabic' : 'Kurdish' ?? 'Arabic';
$lang_query = $lang_autority == 'on' ? "%20AND%20language:$language" : '';
// Extract collection name
$fetch_url = $datasql['post_fetch_url'] ?? '';
$collection = $fetch_url ? basename($fetch_url) : '';
// API endpoint with pagination
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
                log_stat($e->getMessage());
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
                        // "post_content" => $description ?? 'غير محدد',
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