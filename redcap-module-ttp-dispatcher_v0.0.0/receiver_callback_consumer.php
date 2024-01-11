<?php
header('Content-Type: application/json');

if (!$module->getProjectSetting($module::TTP_SYNC_RECEIVER_ENABLED)) {
    die(json_encode(['status' => 'error', 'message' => 'consumer disabled']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // TODO: Security

    $data = json_decode(file_get_contents('php://input'));
    // Debug
    error_log(print_r($data, 1));

    exit(json_encode($data));
} else {
    die(json_encode(['status' => 'error', 'message' => "method {$_SERVER['REQUEST_METHOD']} not allowed"]));
}