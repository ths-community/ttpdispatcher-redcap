<?php

namespace UKE\TTPDispatcherModule;

use stdClass;

class TTPClient
{
    private $ch;

    private string $ttpBaseUrl;
    private string $ttpApiKey;
    private string $userId;
    private string $studyId;
    private string $studyName;

    public function __construct($ttpBaseUrl, $ttpApiKey, $userId, $studyId, $studyName)
    {
        $this->ttpBaseUrl = $ttpBaseUrl;
        $this->ttpApiKey = $ttpApiKey;
        $this->userId = $userId;
        $this->studyId = $studyId;
        $this->studyName = $studyName;

        $this->initCurl();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    /**
     *
     */
    private function initCurl()
    {
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 3);

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'apiKey: ' . $this->ttpApiKey));
        curl_setopt($this->ch, CURLOPT_POST, 1);
		
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getSession()
    {
        $sessionUrl = $sessionUrl = $this->ttpBaseUrl . '/sessions';

        $sessionData = json_decode('{ "data": { "fields": { "user_id": "' . $this->userId . '", "user_name": "' . $this->userId . '" } } }');

        curl_setopt($this->ch, CURLOPT_URL, $sessionUrl);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($sessionData));

        $sessionResult = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new \Exception(curl_error($this->ch), curl_errno($this->ch));
        }

        return json_decode($sessionResult);
    }

    /**
     * @param $type
     * @param $event
     * @param array|null $additionalFields
     * @param null $redirect
     * @param null $callback
     * @param null $method
     * @return mixed
     * @throws \Exception
     */
    private function getToken($type, $event, array $additionalFields = null, $redirect = null, $callback = null, $method = null)
    {
        $session = $this->getSession();

        $tokenUrl = $this->ttpBaseUrl . '/sessions/' . $session->sessionId . '/tokens';

        $tokenData = new stdClass;
        $tokenData->type = $type;
        $tokenData->data = new stdClass;
        $tokenData->data->fields = new stdClass;
        $tokenData->data->fields->study_id = $this->studyId;
        $tokenData->data->fields->study_name = $this->studyName;
        $tokenData->data->fields->event = $event;

        // add additional fields, e.g. location data, tagetIdType, options
        foreach ($additionalFields as $key => $value) {
            $tokenData->data->fields->$key = $value;
        }

        if ($redirect) {
            $tokenData->data->redirect = $redirect;
        }

        if ($callback) {
            $tokenData->data->callback = $callback;
        }

        if ($method) {
            $tokenData->method = $method;
        }

        curl_setopt($this->ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($tokenData));

        $tokenResult = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new \Exception(curl_error($this->ch), curl_errno($this->ch));
        }

        return json_decode($tokenResult);
    }

    /**
     * @param $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function getSearchPatientForm($redirectUrl)
    {
        $token = $this->getToken('searchPatient', 'demo.manage', ['location_id' => 'loc1', 'location_name' => 'loc1', 'targetIdType' => 'mdat'], $redirectUrl);

        return $token->call->form->url;
    }

    /**
     * @param $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function getManagePatientForm($redirectUrl)
    {
        $token = $this->getToken('managePatient', 'demo.manage', ['location_id' => 'loc1', 'location_name' => 'loc1', 'targetIdType' => 'mdat'], $redirectUrl);

        return $token->call->form->url;
    }

    /**
     * @param $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function getAddPatientForm($redirectUrl)
    {
        $token = $this->getToken('addPatient', 'demo.recruitment', ['location_id' => 'loc1', 'location_name' => 'loc1', 'targetIdType' => 'mdat'], $redirectUrl);

        return $token->call->form->url;
    }

    /**
     * @param $psn
     * @param $policies
     * @return mixed
     * @throws \Exception
     */
    public function queryPolicies($psn, $policies)
    {
        $token = $this->getToken('queryPolicies', 'demo.transferData', ['reason' => 'example reason']);

        $patient = new stdClass;
        $patient->index = 1;
        $patient->patientIdentifier = new stdClass;
        $patient->patientIdentifier->domain = 'mdat';
        $patient->patientIdentifier->name = 'PSN';
        $patient->patientIdentifier->id = $psn;
        $patient->patientIdentifier->type = 'patientPSN';
        $options = new stdClass;
        $options->resultType = 'detailed';
        $options->queryType = 'policyBased';
        $options->unknownStateIsConsideredAsDeclined = true;

        $tokenCallData = new stdClass;
        $tokenCallData->patients = [$patient];
        $tokenCallData->options = $options;
        $tokenCallData->policies = json_decode($policies);

        $tokenCallUrl = $token->call->action->url;

        curl_setopt($this->ch, CURLOPT_URL, $tokenCallUrl);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($tokenCallData));

		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);

        $tokenCallResult = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new \Exception(curl_error($this->ch), curl_errno($this->ch));
        }

        $tokenCall = json_decode($tokenCallResult);

        return $tokenCall->patients[0]->policies;
    }

    /**
     * @param $psn
     * @return mixed
     * @throws \Exception
     */
    public function requestPatientByIdentifier($psn)
    {
        ///$token = $this->getToken('requestPatientByIdentifier', 'demo.requestPatientByIdentifier', ['reason' => 'example reason']);

        $patient = new stdClass;
        ///$patient->index = 1;
        $patient->patientIdentifier = new stdClass;
        $patient->patientIdentifier->domain = 'mdat';
        $patient->patientIdentifier->name = 'PSN';
        $patient->patientIdentifier->id = $psn;
        $patient->patientIdentifier->type = 'patientPSN';
        ///$tokenCallData = new stdClass;
        ///$tokenCallData->patients = [$patient];

        ///$tokenCallUrl = $token->call->action->url;

        ///curl_setopt($this->ch, CURLOPT_URL, $tokenCallUrl);
        ///curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($tokenCallData));

        ///$tokenCallResult = curl_exec($this->ch);

        ///if (curl_errno($this->ch)) {
        ///    throw new \Exception(curl_error($this->ch), curl_errno($this->ch));
        ///}

        ///return json_decode($tokenCallResult);
        $token = $this->getToken('requestPatientByIdentifier', 'demo.resolvePsn', ['patient' => $patient ,'reason' => 'example reason']);
        
        return $token->call->form->url;

    }

    public function requestPsnToken($targetType, $callback, $method = null)
    {
        $method = ($method) ? $method : 'getOrCreate';
        // $token = $this->getToken('requestPSN', 'demo.requestPsn', ['reason' => 'example reason', 'targetType' => $targetType], null, $callback, $method);
        $token = $this->getToken('requestPSN', 'demo.requestPsn', ['reason' => 'example reason', 'targetType' => $targetType], null, null, $method);

        return $token;
    }

    public function requestPsnTokenCall($url, $domain, $name, $id, $type)
    {
        $tokenCallUrl = $url;

        $patient = new stdClass;
        $patient->index = "1";
        $patient->patientIdentifier = new stdClass;
        $patient->patientIdentifier->domain = $domain;
        $patient->patientIdentifier->name = $name;
        $patient->patientIdentifier->id = $id;
        $patient->patientIdentifier->type = $type;
        $tokenCallData = new stdClass;
        $tokenCallData->patients = [$patient];

        curl_setopt($this->ch, CURLOPT_URL, $tokenCallUrl);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($tokenCallData));

        $tokenCallResult = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            #throw new \Exception(json_encode($tokenCallData) . "\n" . $tokenCallUrl, -1);
            throw new \Exception(curl_error($this->ch), curl_errno($this->ch));
        }

        return $tokenCallResult;
    }
}