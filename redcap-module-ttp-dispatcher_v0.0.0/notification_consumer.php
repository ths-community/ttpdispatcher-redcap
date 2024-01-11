<?php
header('Content-Type: application/json');

if (!$module->getProjectSetting($module::TTP_NOTIFICATION_CONSUMER_ENABLED)) {
    die(json_encode(['status' => 'error', 'message' => 'consumer disabled']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // TODO: Security

    $data = json_decode(file_get_contents('php://input'));
    // Debug
    // error_log(print_r($data, 1));

    $module->log('Received notification', ['source' => 'notification_consumer.php']);

    $recordIdField = $module->getRecordIdField();

    $jsonData = [$recordIdField => $data->targetId];
    $res = \REDCap::saveData($module->getProjectId(), 'json', json_encode(array($jsonData)));
    echo json_encode($res);
} else {
    die(json_encode(['status' => 'error', 'message' => "method {$_SERVER['REQUEST_METHOD']} not allowed"]));
}