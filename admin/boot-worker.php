<?php
require_once '../init.php'; // Adjust path as needed
require_once 'boot-automation.php';
set_time_limit(0);
ignore_user_abort(true);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

class BootWorker
{
    private $dsql;
    private $processFile;
    private $statusFile;

    public function __construct($dsql)
    {
        $this->dsql = $dsql;
        $this->processFile = ROOT . '/admin/temp/boot_processes.json';
        $this->statusFile = ROOT . '/admin/temp/boot_status.json';
        $this->initializeFiles();
    }

    private function initializeFiles()
    {
        if (!file_exists($this->processFile)) {
            file_put_contents($this->processFile, json_encode([]));
        }
        if (!file_exists($this->statusFile)) {
            file_put_contents($this->statusFile, json_encode([]));
        }
    }

    public function startProcess($bootId)
    {
        $processes = json_decode(file_get_contents($this->processFile), true);

        // Check if process is already running
        if (isset($processes[$bootId])) {
            return ['success' => false, 'message' => 'Boot process already running'];
        }

        // Add process to tracking file
        $processes[$bootId] = [
            'started_at' => time(),
            'status' => 'running',
            'progress' => 0
        ];
        file_put_contents($this->processFile, json_encode($processes));

        // Start the actual boot automation process
        $automation = new BootAutomation($this->dsql);
        $result = $automation->startWork($bootId);

        // Update process status
        $processes[$bootId]['status'] = $result['success'] ? 'completed' : 'failed';
        $processes[$bootId]['progress'] = $result['progress'] ?? 100;
        $processes[$bootId]['completed_at'] = time();
        file_put_contents($this->processFile, json_encode($processes));

        return $result;
    }

    public function getStatus($bootId)
    {
        $processes = json_decode(file_get_contents($this->processFile), true);
        return $processes[$bootId] ?? ['status' => 'not_found'];
    }

    public function stopProcess($bootId)
    {
        $processes = json_decode(file_get_contents($this->processFile), true);

        if (isset($processes[$bootId])) {
            $processes[$bootId]['status'] = 'stopped';
            $processes[$bootId]['stopped_at'] = time();
            file_put_contents($this->processFile, json_encode($processes));

            // Update boot status in database
            $this->dsql->dsql()
                ->table('boots')
                ->set(["stat" => 0])
                ->where("id", $bootId)
                ->update();

            return true;
        }

        return false;
    }
}

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bootId = $_POST['id'] ?? 0;
    $worker = new BootWorker($dsql);

    switch ($action) {
        case 'start':
            $result = $worker->startProcess($bootId);

            echo json_encode($result);
            break;

        case 'stop':
            $result = $worker->stopProcess($bootId);
            echo json_encode(['success' => $result]);
            break;

        case 'status':
            $status = $worker->getStatus($bootId);
            echo json_encode($status);
            break;
    }
}
