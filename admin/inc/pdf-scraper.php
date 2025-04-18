<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;

class PdfScraper {
    public $driver;
    public $pdfLinks = [];
    public $downloadButtons = [];
    public $downloadPath;
    public $maxWaitTime = 240; // Maximum time to wait for download in seconds

    /**
     * Initialize the PdfScraper with an existing WebDriver instance
     *
     * @param RemoteWebDriver $driver Existing WebDriver instance
     * @param string $url The website URL to scrape
     * @param string $downloadPath Path to monitor for downloads
     */
    public function __construct($driver) {
        $this->driver = $driver;
    }

    /**
     * Set the download path to monitor
     *
     * @param string $path
     * @return $this
     */
    public function setDownloadPath(string $path): self {
        $this->downloadPath = $path;
        return $this;
    }

    /**
     * Navigate to the URL and find PDF links
     *
     * @return array Array of PDF links found
     */
    public function findPdfLinks($directProcess = false): array {

        if($directProcess) {
            $this->processDownloadButtons();
        } else {
            // Wait for the page to load
            $this->driver->wait(10, 500)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('body'))
            );

            // Strategy 1: Find direct PDF links
            $this->findDirectPdfLinks();

            // Strategy 2: Find download buttons if no direct PDF links found
            if (empty($this->pdfLinks)) {
                $this->findDownloadButtons();

                // Process download buttons to reveal PDF links
                $this->processDownloadButtons();
            }
        }
        return $this->pdfLinks;
    }

    /**
     * Find direct PDF links in the page
     */
    public function findDirectPdfLinks(): void {
        // Method 1: Find links with href ending in .pdf
        $pdfLinkElements = $this->driver->findElements(
            WebDriverBy::xpath('//a[contains(@href, ".pdf")]')
        );

        foreach ($pdfLinkElements as $element) {
            try {
                $href = $element->getAttribute('href');
                if ($this->isPdfUrl($href)) {
                    $this->pdfLinks[] = [
                        'url' => $href,
                        'text' => $element->getText() ?: 'PDF Document',
                        'type' => 'direct_link'
                    ];
                }
            } catch (\Exception $e) {
                // Skip this element if there's an error
                continue;
            }
        }

        // Method 2: Find links with text containing PDF-related keywords in multiple languages
        // This includes both direct text and text in child elements like spans
        /*$pdfTextXpath = '//a[' .
            'contains(translate(text(), "PDF", "pdf"), "pdf") or ' .
            'contains(text(), "بي دي اف") or ' . // Arabic PDF
            'contains(text(), "پی دی ئێف") or ' . // Kurdish PDF
            'contains(text(), "ملف") or ' .       // Arabic file
            'contains(text(), "فایل") or ' .      // Kurdish file
            'contains(text(), "تنزيل") or ' .     // Arabic download
            'contains(text(), "داگرتن") or ' .    // Kurdish download
            'contains(text(), "دابەزاندن") or ' . // Kurdish download (alternative)
            // Check for nested elements with PDF-related text
            'descendant::*[contains(translate(text(), "PDF", "pdf"), "pdf")] or ' .
            'descendant::*[contains(text(), "بي دي اف")] or ' .
            'descendant::*[contains(text(), "پی دی ئێف")] or ' .
            'descendant::*[contains(text(), "ملف")] or ' .
            'descendant::*[contains(text(), "فایل")] or ' .
            'descendant::*[contains(text(), "تنزيل")] or ' .
            'descendant::*[contains(text(), "داگرتن")] or ' .
            'descendant::*[contains(text(), "دابەزاندن")]' .
            ']';

        $pdfTextElements = $this->driver->findElements(
            WebDriverBy::xpath($pdfTextXpath)
        );

        foreach ($pdfTextElements as $element) {
            try {
                $href = $element->getAttribute('href');
                if (!$this->linkAlreadyFound($href)) {
                    $this->pdfLinks[] = [
                        'url' => $href,
                        'text' => $element->getText() ?: 'PDF Document',
                        'type' => 'text_contains_pdf_multilingual'
                    ];
                }
            } catch (\Exception $e) {
                // Skip this element if there's an error
                continue;
            }
        }*/
    }

    /**
     * Find download buttons that might lead to PDFs
     */
    public function findDownloadButtons(): void {
        // Method 1: Find buttons/links with download-related text in multiple languages
        $downloadKeywords = [
            'download', 'télécharger', 'herunterladen', 'descargar', // Western languages
            'تنزيل', 'تحميل', 'حمل', 'رابط', 'لينك', // Arabic
            'داگرتن', 'دابەزاندن', 'وەرگرتن', 'داونلۆدکردنی', 'خوێندنەوە', // Kurdish
            'pdf', 'ملف', 'فایل', 'بي دي اف', 'پی دی ئێف' // PDF in different languages
        ];

        foreach ($downloadKeywords as $keyword) {
            // Look for buttons/links with download text (including nested elements)
            $buttonXpath = sprintf(
                '//button[contains(text(), "%1$s") or descendant::*[contains(text(), "%1$s")]] | ' .
                '//a[contains(text(), "%1$s") or descendant::*[contains(text(), "%1$s")]]',
                $keyword
            );

            $buttonElements = $this->driver->findElements(
                WebDriverBy::xpath($buttonXpath)
            );

            foreach ($buttonElements as $element) {
                try {
                    $this->downloadButtons[] = $element;
                } catch (\Exception $e) {
                    // Skip this element if there's an error
                    continue;
                }
            }
        }

        // Method 2: Find elements with download classes or IDs
        $downloadSelectors = [
            '//a[contains(@class, "download")]',
            '//button[contains(@class, "download")]',
            '//a[contains(@id, "download")]',
            '//button[contains(@id, "download")]',
            '//a[contains(@class, "pdf")]',
            '//button[contains(@class, "pdf")]',
            '//a[contains(@title, "PDF")]',
            '//a[contains(@title, "pdf")]',
            '//a[contains(@aria-label, "PDF")]',
            '//a[contains(@aria-label, "pdf")]',
            // Arabic/Kurdish selectors
            '//a[contains(@class, "تنزيل")]',
            '//button[contains(@class, "تنزيل")]',
            '//a[contains(@class, "تحميل")]',
            '//button[contains(@class, "تحميل")]',
            '//a[contains(@class, "داگرتن")]',
            '//button[contains(@class, "داگرتن")]',
            '//a[contains(@title, "تنزيل")]',
            '//a[contains(@title, "داگرتن")]',
            '//a[contains(@aria-label, "تنزيل")]',
            '//a[contains(@aria-label, "داگرتن")]',
            // Additional selectors for common download button patterns
            '//a[contains(@class, "btn-download")]',
            '//button[contains(@class, "btn-download")]',
            '//a[contains(@class, "download-btn")]',
            '//button[contains(@class, "download-btn")]',
            '//a[contains(@class, "download-link")]',
            '//a[contains(@class, "file-download")]',
            '//a[contains(@class, "icon-download")]',
            '//a[i[contains(@class, "fa-download")]]',
            '//a[i[contains(@class, "icon-download")]]',
            '//a[svg[contains(@class, "download")]]'
        ];

        foreach ($downloadSelectors as $selector) {
            $elements = $this->driver->findElements(WebDriverBy::xpath($selector));
            foreach ($elements as $element) {
                try {
                    $this->downloadButtons[] = $element;
                } catch (\Exception $e) {
                    // Skip this element if there's an error
                    continue;
                }
            }
        }

        // Method 3: Find elements with download-related attributes
        $attributeSelectors = [
            '//a[@download]',
            '//a[contains(@data-download, "pdf")]',
            '//a[contains(@data-filetype, "pdf")]',
            '//a[contains(@data-type, "pdf")]',
            '//a[contains(@type, "application/pdf")]',
            '//a[contains(@data-format, "pdf")]'
        ];

        foreach ($attributeSelectors as $selector) {
            $elements = $this->driver->findElements(WebDriverBy::xpath($selector));
            foreach ($elements as $element) {
                try {
                    $this->downloadButtons[] = $element;
                } catch (\Exception $e) {
                    // Skip this element if there's an error
                    continue;
                }
            }
        }
    }

    /**
     * Process download buttons to find PDF links
     * Monitors download directory to check if downloads complete
     */
    public function processDownloadButtons(): void {

        $init_folder_name = generateRandomString();
        $folder_name = "book/" . $init_folder_name;

        // Create the folder if it doesn't exist
        $upload_dir = UPLOAD_DIR . $folder_name;

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Get Chrome DevTools
        $devTools = new ChromeDevToolsDriver($this->driver);

        // Enable DevTools and set new download directory
        $devTools->execute('Page.setDownloadBehavior', [
            'behavior' => 'allow',
            'downloadPath' => $upload_dir,
        ]);

        $this->setDownloadPath($upload_dir);

        // Remove duplicate buttons
        $this->downloadButtons = array_unique($this->downloadButtons, SORT_REGULAR);

        foreach ($this->downloadButtons as $index => $button) {
            try {
                // Get the href attribute if it's a link
                $href = $button->getAttribute('href');

                // If it's not a link or href is empty/javascript, try to get onclick attribute
                if (empty($href) || strpos($href, 'javascript:') === 0 || $href === '#') {
                    // Try to extract URL from onclick attribute
                    $onclick = $button->getAttribute('onclick');
                    if (!empty($onclick)) {
                        // Try to extract URL from onclick using regex
                        if (preg_match('/window\.open\([\'"]([^\'"]+)[\'"]/i', $onclick, $matches)) {
                            $href = $matches[1];
                        } elseif (preg_match('/location\.href\s*=\s*[\'"]([^\'"]+)[\'"]/i', $onclick, $matches)) {
                            $href = $matches[1];
                        }
                    }

                    // If still no href, try to get data-href or data-url attributes
                    if (empty($href)) {
                        $href = $button->getAttribute('data-href') ?: $button->getAttribute('data-url');
                    }

                    // If still no href, try clicking the button as a last resort
                    if (empty($href)) {
                        // Take snapshot of download directory before clicking
                        $filesBefore = $this->getDirectoryFiles($this->downloadPath);

                        // Save current window handles
                        $originalWindowHandles = $this->driver->getWindowHandles();

                        // Click the button
                        $button->click();

                        // Wait a moment for any new windows or elements to appear
                        sleep(1);

                        // Check if a download started
                        if ($this->waitForDownload($filesBefore)) {
                            // A download completed successfully
                            $newFiles = array_diff($this->getDirectoryFiles($this->downloadPath), $filesBefore);
                            foreach ($newFiles as $file) {
                                if ($this->isPdfFile($file)) {
                                    rename($file, $upload_dir . "/default.pdf");
                                    $this->pdfLinks[] = [
                                        'url' => 'file://' . $file,
                                        'folder_name' => $init_folder_name,
                                        'text' => basename($file),
                                        'type' => 'downloaded_file'
                                    ];
                                    // Download successful, break out of the loop
                                    break;
                                }
                            }
                        }

                        // Check if a new window/tab was opened
                        $newWindowHandles = $this->driver->getWindowHandles();
                        if (count($newWindowHandles) > count($originalWindowHandles)) {
                            // Switch to the new window
                            $newWindowHandle = array_diff($newWindowHandles, $originalWindowHandles);
                            $this->driver->switchTo()->window(reset($newWindowHandle));

                            // Check if the new window contains a PDF
                            $currentUrl = $this->driver->getCurrentUrl();
                            if ($this->isPdfUrl($currentUrl)) {
                                $this->pdfLinks[] = [
                                    'url' => $currentUrl,
                                    'text' => $button->getText() ?: 'PDF Document from button click',
                                    'type' => 'button_new_window'
                                ];
                            }

                            // Close the new window and switch back to the original
                            $this->driver->close();
                            $this->driver->switchTo()->window($originalWindowHandles[0]);
                        }

                        // Check if clicking the button revealed new PDF links on the same page
                        $this->findDirectPdfLinks();

                        // Continue to the next button
                        continue;
                    }
                }

                // If we have a valid href, open it in a new tab using JavaScript
                if (!empty($href)) {
                    // Take snapshot of download directory before navigating
                    $filesBefore = $this->getDirectoryFiles($this->downloadPath);

                    // Save current window handles
                    $originalWindowHandles = $this->driver->getWindowHandles();

                    // Use JavaScript to open the URL in the same window
                    $script = "window.open(arguments[0], '_self');";
                    $this->driver->executeScript($script, [$href]);

                    // Wait a moment for the page to load or download to start
                    sleep(1);

                    // Check if a download started
                    if ($this->waitForDownload($filesBefore)) {
                        // A download completed successfully
                        $newFiles = array_diff($this->getDirectoryFiles($this->downloadPath), $filesBefore);
                        foreach ($newFiles as $file) {
                            if ($this->isPdfFile($file)) {
                                rename($file, $upload_dir . "/default.pdf");
                                $this->pdfLinks[] = [
                                    'url' => 'file://' . $file,
                                    'folder_name' => $init_folder_name,
                                    'text' => basename($file),
                                    'type' => 'downloaded_file'
                                ];
                                // Download successful, break out of the loop
                                break;
                            }
                        }
                    } else {
                        // No download detected, try to find download buttons on the new page
                        $downloadKeywords = [
                            'تنزيل', 'تحميل', 'حمل', 'رابط', 'لينك', 'داگرتن', 'دابەزاندن', 'وەرگرتن',
                            'pdf', 'ملف', 'فایل', 'بي دي اف', 'پی دی ئێف', 'داونلۆدکردنی', 'خوێندنەوە'
                        ];
                        foreach ($downloadKeywords as $keyword) {
                            // Look for buttons/links with download text (including nested elements)
                            $buttonXpath = sprintf(
                                '//button[contains(text(), "%1$s") or descendant::*[contains(text(), "%1$s")]] | ' .
                                '//a[contains(text(), "%1$s") or descendant::*[contains(text(), "%1$s")]]',
                                $keyword
                            );

                            try {
                                $buttonElements = $this->driver->findElements(
                                    WebDriverBy::xpath($buttonXpath)
                                );

                                if (count($buttonElements) > 0) {
                                    foreach ($buttonElements as $element) {
                                        try {
                                            // Take snapshot of download directory before clicking
                                            $filesBefore = $this->getDirectoryFiles($this->downloadPath);

                                            // Click the element
                                            $element->click();

                                            // Wait for download to complete
                                            if ($this->waitForDownload($filesBefore)) {
                                                // A download completed successfully
                                                $newFiles = array_diff($this->getDirectoryFiles($this->downloadPath), $filesBefore);
                                                foreach ($newFiles as $file) {
                                                    if ($this->isPdfFile($file)) {
                                                        rename($file, $upload_dir . "/default.pdf");
                                                        $this->pdfLinks[] = [
                                                            'url' => 'file://' . $file,
                                                            'folder_name' => $init_folder_name,
                                                            'text' => basename($file),
                                                            'type' => 'downloaded_file_from_subpage'
                                                        ];
                                                        // Download successful, break out of all loops
                                                        break 3;
                                                    }
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            // Skip this element if there's an error
                                            continue;
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                // Skip this selector if there's an error
                                continue;
                            }
                        }
                    }

                    // Navigate back to the original page
                    $this->driver->navigate()->back();
                }
            } catch (\Exception $e) {
                // Skip this button if there's an error
                continue;
            }
        }
    }

    /**
     * Get a list of files in a directory
     *
     * @param string $dir Directory path
     * @return array List of full file paths
     */
    public function getDirectoryFiles(string $dir): array {
        $files = [];
        if (is_dir($dir)) {
            $dirIterator = new \DirectoryIterator($dir);
            foreach ($dirIterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $files[] = $fileInfo->getPathname();
                }
            }
        }
        return $files;
    }


    /**
     * Wait for a download to complete by monitoring the download directory
     *
     * @param array $filesBefore Files in the directory before the action
     * @return bool True if a download completed successfully
     */
    public function waitForDetect(array $filesBefore): bool {
        $startTime = time();
        $downloadDetected = false;

        // Wait for download to start and complete
        while (time() - $startTime < 25) {
            $currentFiles = $this->getDirectoryFiles($this->downloadPath);
            $newFiles = array_diff($currentFiles, $filesBefore);

            // Check for partial download files (.part, .crdownload, etc.)
            $partialDownloadFiles = array_filter($newFiles, function($file) {
                return $this->isPartialDownloadFile($file);
            });
            if (!empty($partialDownloadFiles)) {
                // Download has started
                $downloadDetected = true;
                $partialFiles = $partialDownloadFiles;
            }
            // Wait a bit before checking again
            usleep(500000); // 0.5 seconds
        }
        return $downloadDetected;
    }

    /**
     * Wait for a download to complete by monitoring the download directory
     *
     * @param array $filesBefore Files in the directory before the action
     * @return bool True if a download completed successfully
     */
    public function waitForDownload(array $filesBefore): bool {
        $startTime = time();
        $downloadDetected = false;
        $downloadComplete = false;
        $partialFiles = [];

        if($this->waitForDetect($filesBefore)) {
            // Wait for download to start and complete
            while (time() - $startTime < $this->maxWaitTime) {
                $currentFiles = $this->getDirectoryFiles($this->downloadPath);
                $newFiles = array_diff($currentFiles, $filesBefore);

                // Check for partial download files (.part, .crdownload, etc.)
                $partialDownloadFiles = array_filter($newFiles, function($file) {
                    return $this->isPartialDownloadFile($file);
                });
                if (!empty($partialDownloadFiles)) {
                    // Download has started
                    $downloadDetected = true;
                    $partialFiles = $partialDownloadFiles;
                } elseif ($downloadDetected && empty($partialDownloadFiles)) {
                    // Download was detected earlier but partial files are gone now
                    // This likely means the download completed
                    $downloadComplete = true;
                    break;
                } elseif (!empty($newFiles) && empty($partialDownloadFiles)) {
                    // New files appeared without partial files being detected
                    // This could mean the download was very fast
                    $downloadComplete = true;
                    break;
                }

                // Wait a bit before checking again
                usleep(500000); // 0.5 seconds
            }
            // If download was detected but not completed, check if the partial files
            // were renamed to complete files
            if ($downloadDetected && !$downloadComplete) {
                $currentFiles = $this->getDirectoryFiles($this->downloadPath);
                foreach ($partialFiles as $partialFile) {
                    $baseFile = $this->getBaseFilenameFromPartial($partialFile);
                    foreach ($currentFiles as $file) {
                        if (strpos($file, $baseFile) === 0 && !$this->isPartialDownloadFile($file)) {
                            $downloadComplete = true;
                            break 2;
                        }
                    }
                }
            }
            return $downloadComplete;
        } else {
            return false;
        }

    }

    /**
     * Check if a file is a partial download file
     *
     * @param string $file File path
     * @return bool True if it's a partial download file
     */
    public function isPartialDownloadFile(string $file): bool {
        $partialExtensions = ['.part', '.crdownload', '.download', '.partial'];
        foreach ($partialExtensions as $ext) {
            if (substr($file, -strlen($ext)) === $ext) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the base filename from a partial download file
     *
     * @param string $partialFile Partial file path
     * @return string Base filename without the partial extension
     */
    public function getBaseFilenameFromPartial(string $partialFile): string {
        $partialExtensions = ['.part', '.crdownload', '.download', '.partial'];
        $baseFile = $partialFile;

        foreach ($partialExtensions as $ext) {
            if (substr($partialFile, -strlen($ext)) === $ext) {
                $baseFile = substr($partialFile, 0, -strlen($ext));
                break;
            }
        }

        return $baseFile;
    }

    /**
     * Check if a file is a PDF
     *
     * @param string $file File path
     * @return bool True if it's a PDF file
     */
    public function isPdfFile(string $file): bool {
        // Check file extension
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf') {
            return true;
        }

        // Check file content (first few bytes)
        if (file_exists($file) && is_readable($file)) {
            $handle = fopen($file, 'rb');
            if ($handle) {
                $header = fread($handle, 5);
                fclose($handle);
                if ($header === '%PDF-') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a URL is likely a PDF
     *
     * @param string $url
     * @return bool
     */
    public function isPdfUrl(string $url): bool {
        // Check if URL ends with .pdf
        if (preg_match('/\.pdf(\?.*)?$/i', $url)) {
            return true;
        }

        // Check if URL contains PDF indicators in multiple languages
        if (preg_match('/pdf|document|download|تنزيل|تحميل|داگرتن|دابەزاندن|ملف|فایل/i', $url)) {
            // Additional check could be made with a HEAD request to check Content-Type
            return true;
        }

        return false;
    }

    /**
     * Check if a link is already in our found links
     *
     * @param string $url
     * @return bool
     */
    public function linkAlreadyFound(string $url): bool {
        foreach ($this->pdfLinks as $link) {
            if ($link['url'] === $url) {
                return true;
            }
        }
        return false;
    }

    /**
     * Close the WebDriver
     * Note: This method is kept for backward compatibility but should not be used
     * if the driver was passed externally
     */
    public function close(): void {
        // This method is intentionally empty as we don't want to close
        // an externally provided driver
    }
}