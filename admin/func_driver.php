<?php
require_once "../init.php";

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

function startSeleniumAndChromeDriver()
{
    $chromeDriverPath = EXE . 'chromedriver';
    $seleniumJarPath = EXE . 'selenium-server-4.28.1.jar';

    // Check if Selenium is already running
    $seleniumRunning = exec("netstat -tuln | grep ':4444'");

    if (empty($seleniumRunning)) {
        // Start Selenium Server if not running
        exec("java -jar $seleniumJarPath standalone > selenium.log 2>&1 &", $outputSelenium, $seleniumStatus);
        if ($seleniumStatus !== 0) {
            die('Failed to start Selenium Server. Output: ' . implode("\n", $outputSelenium));
        }
        //echo "Selenium Server started.\n";
    }

    // Check if ChromeDriver is already running
    $chromeDriverRunning = exec("pgrep chromedriver");

    if (empty($chromeDriverRunning)) {
        // Start ChromeDriver if not running
        exec("$chromeDriverPath > chromedriver.log 2>&1 &", $outputChrome, $chromeStatus);
        if ($chromeStatus !== 0) {
            die('Failed to start ChromeDriver. Output: ' . implode("\n", $outputChrome));
        }
        //echo "ChromeDriver started.\n";
    }

    // Wait a few seconds to ensure tools are ready
    sleep(5);
}


function createDriver($headless = false, $blockAds = false, $captcha = false, $displayImages = true, $pageLoadTimeout = 600000)
{
    // Start the Selenium server and ChromeDriver
    startSeleniumAndChromeDriver();

    $chromeOptions = new ChromeOptions();

    // Generate a unique user data directory
    $userDataDir = sys_get_temp_dir() . '/chrome_user_data_' . uniqid();
    if (!is_dir($userDataDir)) {
        mkdir($userDataDir, 0777, true); // Create the directory if it doesn't exist
    }

    $chromeOptions->addArguments([
      	'--disable-popup-blocking',
        '--disable-notifications',
        '--disable-blink-features=AutomationControlled',
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--window-size=800x600',
        '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        '--user-data-dir=' . $userDataDir, // Add a unique user data directory
    ]);

    // Apply headless options if headless mode is enabled
    if ($headless) {
        $chromeOptions->addArguments([
            '--headless',
            '--disable-gpu',
        ]);
    }

    if (!$displayImages) {
        $chromeOptions->addArguments(['--blink-settings=imagesEnabled=false']);
    }

    // Configure Chrome options to avoid detection
    $chromeOptions->setExperimentalOption('excludeSwitches', ['enable-automation']);
    $chromeOptions->setExperimentalOption('useAutomationExtension', false);

    $serverUrl = 'http://localhost:4444'; // Selenium server URL
    $capabilities = DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

    try {
        // Create the driver
        $driver = RemoteWebDriver::create($serverUrl, $capabilities);

        // Bypass detection
        $driver->executeScript('Object.defineProperty(navigator, "webdriver", {get: () => undefined});');

        // Set the page load timeout
        $driver->manage()->timeouts()->pageLoadTimeout($pageLoadTimeout / 1000); // Convert milliseconds to seconds

        return $driver;
    } catch (Exception $e) {
        // Display the error message
        //echo "Error: Unable to create WebDriver instance.\n";
        //echo "Details: " . $e->getMessage() . "\n";

        // Optional: Log the error to a file for debugging
        file_put_contents('webdriver_error.log', $e->getMessage(), FILE_APPEND);

        return false;
    }
}

// Function to stop Selenium and ChromeDriver
function stopSeleniumAndChromeDriver()
{
    // Stop Selenium and ChromeDriver (Linux/Mac)
    exec('pkill -f chromedriver');
    exec('pkill -f selenium-server');
}

function humanSleep($min = 4, $max = 7) {
    usleep(rand($min * 1000000, $max * 1000000)); // sleep in microseconds (1 second = 1,000,000 microseconds)
}


function downloadImage($imageUrl, $title) {
    // Check if the image URL is relative and convert to absolute if necessary
    if (filter_var($imageUrl, FILTER_VALIDATE_URL) === true) {
        $imageContent = @file_get_contents($imageUrl);
        if ($imageContent === false) {
            return '-'; // Handle failed image download
        }
    
        // Extract the file extension from the image URL
        $pathInfo = pathinfo($imageUrl);
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : 'jpg'; // Default to jpg if no extension found
    
        // Clean title to create a valid filename
        $imageFileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $title) . '_' . time() . '.' . $extension;
    
        // Define where to save the image (e.g., in an "images" folder in your project)
        $imagePath = __DIR__ . '/images/' . $imageFileName;
    
        // Save the image locally
        file_put_contents($imagePath, $imageContent);
        
        return $imagePath;
    }
    return false;
}

function getBaseUrl($url) {
    $parsed_url = parse_url($url);

    // Check if 'scheme' and 'host' keys are set
    if (isset($parsed_url['scheme']) && isset($parsed_url['host'])) {
        // Construct the base URL from the scheme and host
        return $parsed_url['scheme'] . '://' . $parsed_url['host'];
    }

    // If scheme or host is missing, return the original URL
    return $url;
}

function log_stat($msg) {
    file_put_contents("display_stats.txt", $msg);
}

function findShadowElement($driver, $shadowHosts, $selector) {
    // Execute JavaScript to traverse shadow DOM and find elements
    $script = '';
    foreach ($shadowHosts as $host) {
        $script .= "document.querySelector('$host').shadowRoot.querySelector('";
    }
    $script .= "$selector')";
    return $driver->executeScript($script);
}