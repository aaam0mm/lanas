<?php
require_once '../init.php';
set_time_limit(0);
ignore_user_abort(true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($argc < 2) {
    die("Boot ID required\n");
}

$bootId = (int)$argv[1];
$processFile = ROOT . '/admin/temp/boot_processes.json';

function updateProgress($bootId, $progress, $status = 'running') {
    global $processFile;
    
    $processes = [];
    if (file_exists($processFile)) {
        $content = file_get_contents($processFile);
        $processes = json_decode($content, true) ?: [];
    }
    
    if (isset($processes[$bootId])) {
        $processes[$bootId]['progress'] = $progress;
        $processes[$bootId]['status'] = $status;
        $processes[$bootId]['last_updated'] = time();
        
        if ($status === 'completed') {
            $processes[$bootId]['completed_at'] = time();
        }
        
        file_put_contents($processFile, json_encode($processes));
    }
}

// try {
//     $automation = new BootAutomation($dsql);
//     $result = $automation->startWork($bootId);
    
//     if ($result['success']) {
//         updateProgress($bootId, 100, 'completed');
//     } else {
//         updateProgress($bootId, $result['progress'] ?? 0, 'failed');
//     }
// } catch (Exception $e) {
//     error_log("Error in boot process: " . $e->getMessage());
//     updateProgress($bootId, 0, 'error');
// }

function handleError($bootId, $error) {
  error_log("Boot process error (ID: $bootId): " . $error->getMessage());
  updateProgress($bootId, 0, 'error', [
      'error' => $error->getMessage(),
      'trace' => $error->getTraceAsString()
  ]);
}

try {
  $automation = new BootAutomation($dsql);
  
  // Add signal handling for clean shutdown
  pcntl_signal(SIGTERM, function() use ($bootId) {
      updateProgress($bootId, 0, 'stopped');
      exit;
  });
  
  $result = $automation->startWork($bootId);
  
  if ($result['success']) {
      updateProgress($bootId, 100, 'completed', $result['analytics'] ?? null);
  } else {
      updateProgress($bootId, $result['progress'] ?? 0, 'failed', [
          'message' => $result['msg'] ?? 'Unknown error'
      ]);
  }
} catch (Exception $e) {
  handleError($bootId, $e);
} catch (Error $e) {
  handleError($bootId, $e);
}