<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && !empty($_GET['id'])) {
    // TODO: Security

    $recordIdField = $module->getRecordIdField();

    $recordPsn = $_GET['id'];

    if ($module->getProjectSetting($module::TTP_USE_SECONDARY_PK)) {
        // handle already existing records
        $t_id = $module->getSecondaryPkToRecordId($recordPsn);
        if ($t_id) {
            header('Location: ' . $module->getDataEntryRedirectUrl() . '&id=' . $t_id);
            exit();
        }
        $importRecordId = $module->addAutoNumberedRecord($module->getProjectId());
        $jsonData = [$recordIdField => $importRecordId, $module->getProjectSetting($module::TTP_SECONDARY_PK_FIELD) => $recordPsn];
    } else {
        // TODO: handle existing records; is this needed?
        $jsonData = [$recordIdField => $recordPsn];
    }

    $results = REDCap::saveData($module->getProjectId(), 'json', json_encode(array($jsonData)));

    if (!empty($results['errors'])) {
        // TODO: error handling
        die(json_encode($results, JSON_PRETTY_PRINT));
    }

    $id = $results['ids'][array_key_first($results['ids'])];
    header('Location: ' . $module->getDataEntryRedirectUrl() . '&id=' . $id);
    exit();
} else {
    // TODO: show error in redcap ui
    die(json_encode(['status' => 'error']));
}