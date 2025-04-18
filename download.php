<?php
require_once 'init.php';

$post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;

if ($post_id == 0) {
    return false;
}

// Fetch the existing book_preview meta value
$book_downloads = $dsql->dsql()
    ->table('post_meta')
    ->where('post_id', $post_id)
    ->where('meta_key', 'book_downloads')
    ->field('meta_value')
    ->limit(1)
    ->getRow();

$downloads = 0;

if (!$book_downloads || empty($book_downloads)) {
    // Insert new record if no book_downloads exists
    $downloads++;
    $insert = $dsql->dsql()->table('post_meta')->set([
        "meta_key" => "book_downloads",
        "meta_value" => $downloads,
        "post_id" => $post_id
    ])->insert();
} else {
    // Deserialize the existing value
    $book_downloads['meta_value']++;
    $downloads = $book_downloads['meta_value'];

    // Update the existing record
    $update = $dsql->dsql()->table('post_meta')->set([
        "meta_value" => $downloads
    ])->where('post_id', $post_id)
        ->where('meta_key', 'book_downloads')
        ->update();
}


// Handle multiple file downloads via POST (ZIP creation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['books_ids'])) {
    $books_ids = $_POST['books_ids'];

    if (empty($books_ids)) {
        exit('No files selected.');
    }

    $zip = new ZipArchive();
    $zipFileName = 'downloads_' . time() . '.zip';
    $zipFilePath = 'temp/' . $zipFileName;

    // Ensure temp directory exists
    if (!is_dir('temp')) {
        if (!mkdir('temp', 0777, true) && !is_dir('temp')) {
            error_log('Failed to create temp directory.');
            exit('Failed to create temp directory.');
        }
    }

    $zipStatus = $zip->open($zipFilePath, ZipArchive::CREATE);
    if ($zipStatus !== TRUE) {
        error_log('Error opening ZIP file: ' . $zip->getStatusString() . ' (Status code: ' . $zipStatus . ')');
        exit('Could not create ZIP file.');
    } else {
        error_log("ZIP file opened successfully: $zipFilePath");
    }

    // Attempt to open ZIP file
    $zipStatus = $zip->open($zipFilePath, ZipArchive::CREATE);
    if ($zipStatus !== TRUE) {
        error_log('Error opening ZIP file: ' . $zip->getStatusString() . ' (Status code: ' . $zipStatus . ')');
        exit('Could not create ZIP file.');
    }

    foreach ($books_ids as $key) {
        $get_file = get_files(['id' => $key]);
        if ($get_file) {
            $file = $get_file[0];
            $filePath = 'uploads/' . $file['file_dir'] . '/' . $file['file_name'];

            error_log("Checking file path: $filePath");

            if (file_exists($filePath)) {
                if (!$zip->addFile($filePath, $file['file_original_name'])) {
                    error_log("Failed to add file to ZIP: $filePath");
                } else {
                    error_log("Successfully added file to ZIP: $filePath");
                }
            } else {
                error_log("File not found: $filePath");
            }
        }
    }

    $zip->close();
    // Final check for ZIP file existence
    if (file_exists($zipFilePath)) {
        ob_end_clean();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($zipFilePath);
        unlink($zipFilePath);

        exit();
    } else {
        error_log("ZIP file not created: $zipFilePath");
        exit('Failed to create the ZIP file.');
    }
}

// Handle single file download via GET (unchanged)
$key = $_GET['key'] ?? null;
$file_url = $_GET['file_url'] ?? null;

if (!filter_var($file_url, FILTER_VALIDATE_URL) && $key) {
    $get_file = get_files(['file_key' => $key]);
    if (!$get_file) {
        exit(0);
    }
    $file = $get_file[0];
    $mime_type = $file['mime_type'];
    $file_name = $file['file_name'];
    $file_dir = $file['file_dir'];
    $file_original_name = $file['file_original_name'];
    $file_url = 'uploads/' . $file_dir . '/' . $file_name;
} else {
    $mime_type = 'application/pdf';
    $file_original_name = basename($file_url);
    if (@parse_url($file_url)) {
        $file_url = ltrim(parse_url($file_url)['path'], '/');
    }
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_original_name . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
ob_clean();
flush();
readfile($file_url);
exit(0);
