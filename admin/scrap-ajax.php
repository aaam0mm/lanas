<?php
ini_set('max_execution_time', 300);
set_time_limit(0); // Prevent timeout for long-running processes
ignore_user_abort(true); // Continue processing even if user closes the connection
$action = isset($_POST['action']) ? $_POST['action'] : 'default';

require_once 'func_driver.php';

// Function to get/update scraping status from session
function getScrapingStatus($id) {
    $status_file = ROOT . "/admin/temp/scraping_status_$id.json";
    
    if (file_exists($status_file)) {
        global $dsql;
        $content = file_get_contents($status_file);
        error_log("Status file content for ID $id: " . $content);
        $content_array = json_decode($content, true);
        $content_array['date'] = date('Y-m-d H:i:s');
        $dsql->dsql()->table('post_info')->set(['fetch_details' => json_encode($content_array)])->where('id', $id)->update();
        return $content_array;
    }
    return null;
}

function updateScrapingStatus($id, $status) {
    if($status['status']  == "running" || $status['status']  == "completed") {
        $status_file = ROOT . "/admin/temp/scraping_status_$id.json";
        $content = json_encode($status);
        error_log("Updating status file for ID $id with: " . $content);
        file_put_contents($status_file, $content);
        chmod($status_file, 0666);
    }
}

function clearScrapingStatus($id) {
    $status_file = ROOT . "/admin/temp/scraping_status_$id.json";
    if (file_exists($status_file)) {
        unlink($status_file);
    }
}

// Handle different actions
switch ($action) {
    case 'initScraping':
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        
        // Initialize scraping status
        $status = [
            'status' => 'running',
            'progress' => 0,
            'success' => 0,
            'fails' => 0,
            'exists' => 0,
            'pid' => null
        ];
        updateScrapingStatus($id, $status);

        $data = $dsql->dsql()->table('post_info')->where('id', $id)->get();
        if (!$data) {
            throw new Exception("Post info not found");
        }
        $data = $data[0];

        // Create log directory if it doesn't exist
        $logDir = __DIR__ . '/logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // Start scraping process in background with nohup
        $logFile = $logDir . "/scraper_{$id}.log";
        /*strpos($data['post_fetch_url'], 'archive.org')*/
        if(!strpos($data['post_fetch_url'], 'noor-book')) {
            // Get absolute path to background_scraper.php
            $scriptPath = realpath(__DIR__ . '/background_scraper.php');
            error_log("Starting background scraper with script path: " . $scriptPath);

            // Get PHP executable path
          	$phpPath = '/usr/bin/php';

            $cmd = sprintf(
                'nohup %s %s %d > %s 2>&1 & echo $!',
                escapeshellarg($phpPath),
                escapeshellarg($scriptPath),
                $id,
                escapeshellarg($logFile)
            );
        } else {

            $email_password_passed_text = '';

            if (strpos($data['post_fetch_url'], 'noor-book')) {
                $general_settings = @unserialize(get_settings("site_general_settings"));
                $email = $general_settings['chat_gpt_email'] ?? 'salahbellal394@gmail.com';
                $password = $general_settings['chat_gpt_password'] ?? '(oQl3dkbG3%BYdRQ5K';

                $escaped_email = escapeshellarg($email);
                $escaped_password = escapeshellarg($password);
                $email_password_passed_text = " --email $escaped_email --password $escaped_password";
                $pythonScript = escapeshellarg(PY . 'scraping_books_articles_noor.py');
            } else {
                $pythonScript = escapeshellarg(PY . 'scraping_books_articles.py');
            }
            $script_id = (int) $id;  // Ensure it is an integer
            $cmd = "HEADLESS=false /usr/local/bin/python3 " . $pythonScript .
                " --id " . $script_id . "$email_password_passed_text > " . escapeshellarg($logFile) . " 2>&1 & echo $!";
        }
        
        error_log("Executing command: " . $cmd);
        $pid = shell_exec($cmd);
        
        if ($pid) {
            $pid = trim($pid);
            error_log("Background process started with PID: " . $pid);
            
            // Update status with process ID
            $status['pid'] = $pid;
            updateScrapingStatus($id, $status);
            
            echo json_encode(['status' => 'success']);
        } else {
            error_log("Failed to start background process");
            echo json_encode([
                'status' => 'error',
                'msg' => 'Failed to start scraping process'
            ]);
        }
        break;
        
    case 'checkScrapingProgress':
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $status = getScrapingStatus($id);
        if (!$status) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'لم يتم العثور على حالة الجلب'
            ]);
            break;
        }
        
        // Check if process is still running
        if ($status['pid']) {
            if (function_exists('posix_kill')) {
                $running = posix_kill($status['pid'], 0);
            } else {
                $running = file_exists("/proc/{$status['pid']}");
            }
            error_log("Process {$status['pid']} running status: " . ($running ? 'yes' : 'no'));
            
            if (!$running && $status['status'] === 'running') {
                // Check error log
                $logFile = __DIR__ . "/logs/scraper_{$id}.log";
                $errorLog = @file_get_contents($logFile);
                if ($errorLog) {
                    error_log("Process error log: " . $errorLog);
                }
                $status['status'] = 'error';
                $status['msg'] = $errorLog ? "Process error: " . $errorLog : 'Process terminated unexpectedly';
                updateScrapingStatus($id, $status);
            }
        }
        if ($status['status'] === 'completed') {
            clearScrapingStatus($id);
            echo json_encode([
                'status' => 'completed',
                'msg' => sprintf(
                    "البيانات المرفوعة بنجاح:(%d), البيانات التي تم تجاهلها(%d)",
                    $status['success'],
                    $status['exists']
                )
            ]);
        } else if ($status['status'] === 'error') {
            clearScrapingStatus($id);
            echo json_encode([
                'status' => 'error',
                'msg' => $status['msg']
            ]);
        } else {
            echo json_encode([
                'status' => 'running',
                'progress' => $status['progress']
            ]);
        }
        break;
    case 'stopScrapingProgress':
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $status = getScrapingStatus($id);
    
        if (!$status || empty($status['pid'])) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'لا يوجد عملية جلب تعمل لهذا المعرف'
            ]);
            break;
        }
    
        $pid = (int) $status['pid']; // Ensure it's an integer
        
        // Check if process is still running and attempt to kill it
        if (function_exists('posix_kill')) {
            $killed = posix_kill($pid, SIGTERM); // SIGTERM to gracefully stop
        } else {
            exec("kill $pid", $output, $killed);
            $killed = ($killed === 0); // `kill` returns 0 on success
        }
    
        if ($killed) {
            error_log("Process $pid stopped successfully.");
        } else {
            error_log("Failed to stop process $pid.");
        }
    
        // Clear the stored status
        clearScrapingStatus($id);
    
        echo json_encode([
            'status' => 'stopped',
            'msg' => sprintf(
                "تم إيقاف العملية. البيانات المرفوعة: (%d), البيانات التي تم تجاهلها: (%d)",
                $status['success'],
                $status['exists']
            )
        ]);
        break;
}