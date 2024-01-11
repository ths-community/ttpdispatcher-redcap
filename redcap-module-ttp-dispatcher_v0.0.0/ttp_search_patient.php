<?php

?>

    <h3 style="color:#800000;">
        Search Patient
    </h3>

<?php

$redirectUrl = $module->getUrl('ttp_view_record_redirect_consumer.php', false, true);

try {
    $ttpClient = $module->getTTPClient();
    $src = $ttpClient->getSearchPatientForm($redirectUrl);

    $module->log('Successful initialized search patient form', ['source' => 'ttp_search_patient.php', 'user' => USERID]);
    echo '<iframe src="' . $src . '" style="width: 90%; height: 800px; border: none;"></iframe>';
} catch (\Exception $e) {
    $module->log($e->getMessage(), ['source' => 'ttp_search_patient.php', 'user' => USERID]);
    echo '<div class="red helper-info" style="margin-bottom: 10px;">' . $e->getMessage() . '</div>';
}
