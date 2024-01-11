<?php

namespace UKE\TTPDispatcherModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

require_once 'TTPClient.php';


class TTPDispatcherModule extends AbstractExternalModule
{
    /**
     * EM config
     */
    const TTP_ENABLED = 'ttp-enabled';
    const TTP_NAME = 'ttp-name';
    const TTP_BASE_URL = 'ttp-base-url';
    const TTP_APIKEY = 'ttp-apikey';
    const TTP_STUDY_ID = 'ttp-study-id';
    const TTP_STUDY_NAME = 'ttp-study-name';
    const TTP_POLICIES_QUERY = 'ttp-policies-query';
    const TTP_POLICIES_QUERY_APIKEY = 'ttp-policies-query-apikey';
    const TTP_USERS = 'ttp-users';
    const TTP_USER = 'ttp-user';
    const TTP_USER_ENABLED = 'ttp-user-enabled';
    const TTP_USER_F_SEARCH_PATIENT = 'ttp-user-function-search-patient';
    const TTP_USER_F_MANAGE_PATIENT = 'ttp-user-function-manage-patient';
    const TTP_USER_F_ADD_PATIENT = 'ttp-user-function-add-patient';
    const TTP_USER_F_REQUEST_PATIENT_BY_IDENTIFIER = 'ttp-user-function-request-patient-by-identifier';
    const TTP_NOTIFICATION_CONSUMER_ENABLED = 'ttp-notification-consumer-enabled';
    const TTP_USE_SECONDARY_PK = 'ttp-use-secondary-pk';
    const TTP_SECONDARY_PK_FIELD = 'ttp-secondary-pk-field';
    const TTP_SECONDARY_PK_FIELD_RO = 'ttp-secondary-pk-field-ro';
    const TTP_SYNC_RECEIVER_ENABLED = 'ttp-sync-receiver-enabled';
    const TTP_SYNC_PROVIDER_ENABLED = 'ttp-sync-provider-enabled';
    const TTP_SYNC_PROVIDER_TARGET = 'ttp-sync-provider-target';

    /**
     * other
     */
    // WIP: sync stuff
    const TTP_RECORD_EXPORT_PREFIX = 'record-status-';

    public function __construct()
    {
        parent::__construct();

        // my stuff goes here
    }

    // Override EM function
    public function validateSettings($settings)
    {
        $message = "";

        /**
         * check user config is unique
         */
        foreach (array_count_values($settings[self::TTP_USER]) as $user => $count) {
            if ($count > 1) {
                $message .= "The user $user has multiple configuration items.\n";
            }
        }

        /**
         * check is secondary unique field enabled in project settings
         *
         * TODO: remove setting from EM or use only for validation???
         */
        if ($settings[self::TTP_USE_SECONDARY_PK]) {
            $Proj = new \Project($this->getProjectId());
            $secondary_pk_field = $Proj->project["secondary_pk"];

            if ($secondary_pk_field == '') {
                $message .= "Use secondary unique field is not enabled in this project.\n";
            }

            $secondary_pk_field_from_settings = $settings[self::TTP_SECONDARY_PK_FIELD];
            if ($secondary_pk_field != '' && $secondary_pk_field != $secondary_pk_field_from_settings) {
                $message .= "$secondary_pk_field_from_settings is not the secondary unique field in this project. Must be $secondary_pk_field.\n";
            }
        }

        return $message;
    }

    // EM hook
    public function redcap_module_link_check_display($project_id, $link)
    {
        $url = $link['url'];

        if (!$this->getProjectSetting(self::TTP_ENABLED)) {
            return null;
        }

        $userSettings = $this->getTTPUserSettings();

        if (!$userSettings[self::TTP_USER_ENABLED]) {
            return null;
        }

        if (strpos($url, 'ttp-dispatcher-logging') !== false) {
            if (SUPER_USER) {
                return $link;
            }
        }

        if (strpos($url, 'ttp_add_patient') !== false) {
            if ($userSettings['ttp-user-function-add-patient']) {
                return $link;
            }
        } elseif (strpos($url, 'ttp_search_patient') !== false) {
            if ($userSettings['ttp-user-function-search-patient']) {
                return $link;
            }
        } elseif (strpos($url, 'ttp_manage_patient') !== false) {
            if ($userSettings['ttp-user-function-manage-patient']) {
                return $link;
            }
        } elseif (strpos($url, 'ttp_request_patient_by_identifier') !== false) {
            if ($userSettings['ttp-user-function-request-patient-by-identifier']) {
                return $link;
            }
        }

        return null;
    }

    // REDCap hook
    public function redcap_every_page_top($project_id)
    {
        if ($this->isPage("DataEntry/record_home.php") && isset($_GET['id']) && !empty($_GET['id'])) {
            $thsBaseUrl = $this->getProjectSetting(self::TTP_BASE_URL);
            $studyId = $this->getProjectSetting(self::TTP_STUDY_ID);
            $studyName = $this->getProjectSetting(self::TTP_STUDY_NAME);
            $thsApiKey = $this->getProjectSetting(self::TTP_POLICIES_QUERY_APIKEY);
            $policies = $this->getProjectSetting(self::TTP_POLICIES_QUERY);


            try {
                $ttpClient = new TTPClient($thsBaseUrl, $thsApiKey, USERID, $studyId, $studyName);
                $psn = $this->getRecordIdToSecondaryPk($_GET['id']);
                $policyStates = $ttpClient->queryPolicies($psn, $policies);

                usort($policyStates, function ($a, $b) {
                    return strcmp($a->policyId, $b->policyId);
                });

                $this->log('Successful quarried policy states', ['source' => 'redcap_every_page_top', 'record' => $_GET['id'], 'user' => USERID, 'api_key' => $thsApiKey]);

                $s = implode('<br>', array_map(function ($obj) {
                    return $obj->policyId . " - " . ($obj->isConsented ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>');
                }, $policyStates));
            } catch (\Exception $e) {
                $this->log($e->getMessage(), ['source' => 'redcap_every_page_top', 'record' => $_GET['id'], 'user' => USERID, 'api_key' => $thsApiKey]);
                $s = '<i class="fas fa-exclamation-triangle"></i> COULD NOT RETRIEVE POLICY STATES!';
            } finally {
                echo "<script>$(function() { $('#record_display_name').after('<div class=\"yellow helper-info\" style=\"margin-bottom: 10px;\">" . $s . "</div>'); });</script>";
            }
        }
    }

    // REDCap hook
    public function redcap_survey_complete($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        // TODO: WIP: syncronizing pseudonymized project 
        // TODO: only if sync target is enabled
        // $this->setTTPRecordExportStatus($record, 'update queued');
    }

    // WIP: cron handler
    private function cron($cronAttributes)
    {
        
        $originalPid = $_GET['pid'];

        $cron_name = $cronAttributes['cron_name'];

        foreach ($this->getProjectsWithModuleEnabled() as $p_id) {
            
            $_GET['pid'] = $p_id;

            if ($cron_name === 'exports') {
                $this->handleExports();
            } else {
                throw new \Exception("Unsupported cron name: $cron_name");
            }

        }

        
        $_GET['pid'] = $originalPid;

        return 'The "' . $cronAttributes['cron_description'] . '" cron job completed successfully.';
    }

    // WIP: sync functions...
    private function setTTPRecordExportStatus($record, $status)
    {
        // $this->setProjectSetting(self::TTP_RECORD_EXPORT_PREFIX . $record, $status);

        // $pid = self::requireProjectId($pid);
        $pid = $this->getProjectId();
        $key = self::TTP_RECORD_EXPORT_PREFIX . $record;
        $key = $this->prefixSettingKey($key);
        ExternalModules::setProjectSetting('api-sync-module', $pid, $key, $status);
    }

    private function handleExports()
    {
        $server = $this->getProjectSetting('ttp-sync-target');


        // $data = REDCap::getData($this->getProjectId(), 'json', $recordIds);

    }

    // sync functions end...

    public function requestMdatSend()
    {
        $receiverEndpointUrl = $this->getProjectSetting(self::TTP_SYNC_PROVIDER_TARGET);
        // TODO: call receiver and ask for permit to send mdat
    }

    public function requestMdatSendAction()
    {
        // TODO: get token and send back to provider
    }

    public function sendMdat()
    {
        // TODO: redeem token with provider psn, replace provider psn with temp id and send mdat to receiver
    }

    public function sendMdatAction()
    {
        // TODO: receive mdat and exchange temp id with receiver psn from project EM settings
    }

    public function receiverCallbackHandler()
    {
        // TODO: save temp id and receiver psn to project EM settings (with timestamp (for purge later?))
    }


    /**
     * @return |null
     */
    public function getTTPUserSettings()
    {
        return $this->getTTPUserSettingsByKey(null);
    }

    /**
     * @param string|null $key
     * @return |null
     */
    public function getTTPUserSettingsByKey(string $key = null)
    {
        $projectUserSettings = $this->getSubSettings(self::TTP_USERS);
        foreach ($projectUserSettings as $userSettings) {
            if ($userSettings[self::TTP_USER] == USERID) {
                return ($key) ? $userSettings[$key] : $userSettings;
            }
        }
        return null;
    }

    /**
     * @param $psn
     * @return int|string|null
     */
    public function getSecondaryPkToRecordId($psn)
    {
        if ($this->getProjectSetting(self::TTP_USE_SECONDARY_PK)) {
            $secondary_pk_field = $this->getProjectSetting(self::TTP_SECONDARY_PK_FIELD);
            $secondary_pk = \REDCap::getData($this->getProjectId(), 'array', null, [$secondary_pk_field], null, null, false, false, false, '[' . $secondary_pk_field . '] = "' . $psn . '"');
            $psn = array_key_first($secondary_pk);
        }
        return $psn;
    }

    /**
     * @param $r_id
     * @return mixed
     */
    public function getRecordIdToSecondaryPk($r_id)
    {
        if ($this->getProjectSetting(self::TTP_USE_SECONDARY_PK)) {
            $secondary_pk_field = $this->getProjectSetting(self::TTP_SECONDARY_PK_FIELD);
            $secondary_pk = \REDCap::getData($this->getProjectId(), 'array', [$r_id], [$secondary_pk_field]);
            $r_id = $secondary_pk[$r_id][array_key_first($secondary_pk[$r_id])][$secondary_pk_field];
        }
        return $r_id;
    }

    /**
     * @return string
     */
    public function getDataEntryRedirectUrl()
    {
        $appPathWebRootFull = substr(APP_PATH_WEBROOT_FULL, 0, strlen(APP_PATH_WEBROOT_FULL) - strlen(APP_PATH_WEBROOT_PARENT)) . APP_PATH_WEBROOT;
        return $appPathWebRootFull . 'DataEntry/record_home.php?pid=' . $this->getProjectId();
    }

    /**
     * @return TTPClient
     */
    public function getTTPClient()
    {
        $thsBaseUrl = $this->getProjectSetting($this::TTP_BASE_URL);
        $studyId = $this->getProjectSetting($this::TTP_STUDY_ID);
        $studyName = $this->getProjectSetting($this::TTP_STUDY_NAME);

        $thsApiKey = $this->getProjectSetting($this::TTP_APIKEY);

        return new TTPClient($thsBaseUrl, $thsApiKey, USERID, $studyId, $studyName);
    }
}
