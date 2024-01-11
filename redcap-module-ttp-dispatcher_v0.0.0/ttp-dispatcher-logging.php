<h3 style="color:#800000;">
    TTP Dispatcher Logging
</h3>

<?php

$link = $module->getUrl('notification_consumer.php', true, true);
echo '<pre style="width: 90%;"><b>Notification Consumer URL:</b> <a href="' . $link . '" target="_blank">' . $link . '</a></pre>';

$link = $module->getUrl('receiver_consumer.php', true, true);
echo '<pre style="width: 90%;"><b>Receiver Consumer URL:</b> <a href="' . $link . '" target="_blank">' . $link . '</a></pre>';

$link = $module->getUrl('receiver_callback_consumer.php', true, true);
echo '<pre style="width: 90%;"><b>Receiver Callback Consumer URL:</b> <a href="' . $link . '" target="_blank">' . $link . '</a></pre>';

$link = $module->getUrl('ttp_add_patient_redirect_consumer.php', true, true);
echo '<pre style="width: 90%;"><b>TTP Add Patient Redirect Consumer URL:</b> <a href="' . $link . '" target="_blank">' . $link . '</a></pre>';

echo '<hr>';

try {
    $ttpClient = $module->getTTPClient();
    //This harcoded URL has to be changed to reflect the correct redcap-api adress and the correct project id (PID)
    $token = $ttpClient->requestPsnToken('mdat', 'http://localhost/redcap/api/?type=module&prefix=redcap-module-ttp-dispatcher&page=notification_consumer&pid=16&NOAUTH');
    echo '<pre style="width: 90%;">' . $token->call->action->url . '</pre>';

    // $res = $ttpClient->requestPsnTokenCall($token->call->action->url, 'demo.system.mdat_ext', 'PSN', 'mdat_ext_280118', 'patientPSN');
    $res = $ttpClient->requestPsnTokenCall($token->call->action->url, 'mdat', 'pat_id', '84d43a53-d448-40c9-a4db-ad86b7968127', 'localIdentifier');
    $res = json_decode($res);
    // echo '<pre style="width: 90%;">' . $res->patients[0]->patientIdentifier->id . ' => '. $res->patients[0]->tempId . '</pre>';
    echo '<pre style="width: 90%;">' . $res->patients[0]->patientIdentifier->id . ' => '. $res->patients[0]->targetId . '</pre>';
} catch (\Exception $e) {
    echo '<div class="red helper-info" style="margin-bottom: 10px;">' . $e->getMessage() . '</div>';
}

echo '<hr>';

echo '<pre style="width: 90%;">' . $module->getQueryLogsSql('select log_id, timestamp, user, ip, project_id, record, message, source, api_key order by log_id desc limit 20') . '</pre>';

echo '<hr>';

$res = $module->queryLogs('select log_id, timestamp, user, ip, project_id, record, message, source, api_key order by log_id desc limit 20');

$fields = $res->fetch_fields();

echo '<table class="table table-striped" style="width: 90%;">';

echo '<thead><th>' . implode('</th><th>', array_map(function ($obj) {
        return $obj->name;
    }, $fields)) . '</th></thead><tbody>';

while ($row = $res->fetch_assoc()) {
    echo '<tr>';

    foreach ($row as $key => $value) {
        echo '<td>' . $value . '</td>';
    }

    echo '</tr>';
}

echo '</tbody></table>';
