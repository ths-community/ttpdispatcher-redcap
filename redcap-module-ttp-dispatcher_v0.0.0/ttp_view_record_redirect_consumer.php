<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && !empty($_GET['id'])) {
    // TODO: Security
    header('Location: ' . $module->getDataEntryRedirectUrl() . '&id=' . $module->getSecondaryPkToRecordId($_GET['id']));
    exit();
} else {
    // TODO: show error in redcap ui
    die(json_encode(['status' => 'error']));
}