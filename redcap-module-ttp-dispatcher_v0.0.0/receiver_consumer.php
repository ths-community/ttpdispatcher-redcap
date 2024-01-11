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

    if (!isset($data->method) || empty($data->method)) {
        die(json_encode(['status' => 'error', 'message' => 'missing method']));
    }

    $method = $data->method;

    switch ($method) {
        case 'requestMdatSend':
            $module->requestMdatSendAction();
            exit(json_encode(['status' => 'ok', 'message' => $method]));
            break;
        case 'sendMdat':
            $module->sendMdatAction();
            exit(json_encode(['status' => 'ok', 'message' => $method]));
            break;
        default:
            die(json_encode(['status' => 'error', 'message' => 'unknown method']));
    }

} else {
    die(json_encode(['status' => 'error', 'message' => "method {$_SERVER['REQUEST_METHOD']} not allowed"]));
}