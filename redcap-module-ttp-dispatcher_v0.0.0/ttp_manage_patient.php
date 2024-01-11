<?php

?>

    <h3 style="color:#800000;">
        Manage Patient
    </h3>

<?php

$redirectUrl = $module->getUrl('ttp_view_record_redirect_consumer.php', false, true);

try {
    $ttpClient = $module->getTTPClient();
    $src = $ttpClient->getManagePatientForm($redirectUrl);

    $module->log('Successful initialized manage patient form', ['source' => 'ttp_manage_patient.php', 'record' => $_GET['id'], 'user' => USERID]);

    echo '<iframe src="' . $src . '" style="width: 90%; height: 800px; border: none;"></iframe>';
} catch (\Exception $e) {
    $module->log($e->getMessage(), ['source' => 'ttp_manage_patient.php', 'record' => $_GET['id'], 'user' => USERID]);
    echo '<div class="red helper-info" style="margin-bottom: 10px;">' . $e->getMessage() . '</div>';
}
