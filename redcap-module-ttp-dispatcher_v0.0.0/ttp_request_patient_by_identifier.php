<?php

?>
    <h3 style="color:#800000;">
        Request Patient By Identifier
    </h3>

<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['psn']) || empty($_POST['psn'])) {

    ?>

    <form method="post">
        <label for="psn">Pseudonym:</label>
        <input id="psn" name="psn" type="text">
        <input type="submit" value="Submit">
    </form>

    <?php

} else {
    try {
        $ttpClient = $module->getTTPClient();
        ///$response = $ttpClient->requestPatientByIdentifier($_POST['psn']);
$src = $ttpClient->requestPatientByIdentifier($_POST['psn']);

        $module->log('Successful requested patient by identifier', ['source' => 'ttp_request_patient_by_identifier.php', 'record' => $_POST['psn'], 'user' => USERID]);
echo '<iframe src="' . $src . '" style="width: 90%; height: 800px; border: none;"></iframe>';

        ///echo '<pre style="width: 90%;">';
        ///echo json_encode($response->patients[0]->patient, JSON_PRETTY_PRINT);
        ///echo '</pre>';
    } catch (\Exception $e) {
        $module->log($e->getMessage(), ['source' => 'ttp_request_patient_by_identifier.php', 'record' => $_POST['psn'], 'user' => USERID]);
        echo '<div class="red helper-info" style="margin-bottom: 10px;">' . $e->getMessage() . '</div>';
    }
}
