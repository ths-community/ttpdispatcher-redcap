# REDCap External Module (EM) to integrate the TTP-Tools of the University Medicine Greifswald via TTP-Dispatcher

The [TTP-Dispatcher](https://www.ths-greifswald.de/forscher/dispatcher/) offers workflow-management for the basic functionalities of the [TTP-Tools of the University Medicine Greifswald](https://www.ths-greifswald.de/projekte/mosaic-projekt/) - including record linkage/identity management (E-PIX), pseudonym creation and administration (gPAS) and informed consent management (gICS).

This REDCap EM utilizes the dispatchers ability to deliver general functions of a Trusted Third Party (TTP) implemented in the TTP-Dispatcher as forms/iframes and via REST-API to the users directly inside REDCap. 
It allows Users e.g. to add, update or search patient records without handling any identifiying data inside the REDCap instance. 
The data is directly send to the TTP, and processed and stored only there. 
This EM provides endpoints that are used by the TTP-Dispatcher to send notifications or redirects to, to create or open records in the context of the pseudonymized patients.

To provide a minmal working example the code currently contains some hardcoded parts referring to the demo data/setup of the dispatcher v.2023.1.2 - such as e.g. 'demo.manage' and 'demo.recruit' those would need refitting if not using the demo setup.  
Additionally some urls have to be changed to setup the example code to work with your REDCap Project, as described below.

## Disclaimer

As with many community contributions under Open Source Licences such as [MIT-Licence](LICENCE) this is neither a fully supported product nor is it in any way a finished product and currently still undergoing development.
It therefore may contain bugs and may lack handling of certain situations and incidents. Also it may have been reduced and simplified for clarity to understand the concepts rather than provide a full scale solution. 

## Prerequisites
- REDCap with external modules framework: min v10.0.0
- PHP-Version: min 7.4.0
- TTP-dispatcher: v.2023.1.2
    - gICS
    - gPAS
    - E-PIX

## Installation
Assuming your REDCap is up and running under http://localhost/redcap and a dispatcher as provided by UMG:

- Copy the module to the **modules** directory of your REDCap folder
- Go to **Control Center -> External Modules -> Manage** and enable the module
- For the project you want to use this module in, go to the projects page, click on **External Modules -> Manage** and enable the module for that project.
- Use the 'Demo Setup' column to setup the [module configuration](#module-configuration)
- Change **ttp_dispatcher_config.env** to contain :
```
REQUIERD VARIABLES
#####################
TTP_DISP_EXT_SERVER_PROTOCOL=http
TTP_DISP_EXT_SERVER_PORT=8080
TTP_DISP_EXT_SERVER_DOMAIN=localhost
```
- Copy the demo sql-files from **demo** to **sql** folder.
- Start the dispatcher and navigate to localhost:8080/config-web
- Change the corresponding url in the provided [dispatcher config file](dispatcher.config.redcap.xml) to refere to your REDCap projects id. See also [below](#dispatcher-configuration).
- Upload the provided dispatcher config, either by overwriting the currently active or adding a new one (alternatively you could also change the content of  **04_init_demo_ttp_dispatcher.sql** to include this configuration instead of the default)

## Module Configuration

Setup of the module's connection to the ttp-dispatcher has to be configured under: **External Modules -> Manage -> Configure** (see  the 'Demo Setup' column below as reference)

### System Configuration

| Parameter             | Description                            | Demo Setup |
|-----------------------|----------------------------------------|----------|
| TTP Enabled | Enable / Disable module content              | true |
| TTP Name    | Instance name                  | Demo |
| TTP Base URL| URL for the dispatcher rest interface (by default ending on /ths/rest ) | http://localhost:8080/ths/rest |
| TTP API Key | API-key as defined in dispatcher config   | admin |
| TTP Target ID Type      | As defined in dispatcher config | mdat |
| TTP Study ID     | As defined in dispatcher config | demo |
| TTP Study Name          | As defined in dispatcher config  | demo            |
| TTP Policy Query          | Policy Object to be queried as described in dispatcher doku                  | [see below](#policy-query-for-the-ttp-demo-setup) |
| TTP Policy Query API Key    | API-key for policy queries as defined in dispatcher config       | admin |
| TTP Use Secondary Unique Field   | Whether the generated PSN should be used as Secondary Unique Field or as Record-ID in REDCap             | false |
| TTP Secondary Unique Value Field     | Which field to use as Secondary Unique Field (if enabled) to store PSN | |
| TTP Notification Consumer Enabled     | Enable ability to receive dispatcher notifications and create records on Add-Patient notification    | true |
| TTP Sync Receiver Enable  | Currently not used       | |
| TTP Sync Provider Enable           | Currently not used             | |

#### Policy query for the TTP demo setup
```json
[
  {
    "policyId":"MDAT_speichern_verarbeiten",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"BIOMAT_Analysedaten_zusammenfuehren_Dritte",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"BIOMAT_Eigentum_uebertragen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"BIOMAT_Zusatzmengen_entnehmen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"BIOMAT_erheben",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"BIOMAT_lagern_verarbeiten",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"BIOMAT_wissenschaftlich_nutzen_EU_DSGVO_konform",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"IDAT_bereitstellen_EU_DSGVO_konform",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"IDAT_erheben",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"IDAT_speichern_verarbeiten",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"IDAT_zusammenfuehren_Dritte",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_5J_pro_speichern_verarbeiten",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_5J_pro_uebertragen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_5J_pro_wissenschaftlich_nutzen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_5J_retro_speichern_verarbeiten",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_5J_retro_uebertragen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_5J_retro_wissenschaftlich_nutzen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_KVNR_5J_pro_uebertragen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"KKDAT_KVNR_5J_retro_uebertragen",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"MDAT_erheben",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"MDAT_speichern_verarbeiten",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"MDAT_wissenschaftlich_nutzen_EU_DSGVO_konform",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"MDAT_zusammenfuehren_Dritte",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"Rekontaktierung_Ergebnisse_erheblicher_Bedeutung",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"Rekontaktierung_Verknuepfung_Datenbanken",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"Rekontaktierung_Zusatzbefund",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"Rekontaktierung_weitere_Erhebung",
    "policyVersionRange":"[1.0,2.0]"
  },
  {
    "policyId":"Rekontaktierung_weitere_Studien",
    "policyVersionRange":"[1.0,2.0]"
  }
]
```
### User Rights Configuration

In addition to the module configuration above the user rights to access the different functions of the module can be configured for each REDCap-User individually:

| Parameter             | Description                            |
|-----------------------|-----------------------------------------|
| TTP User Enabled            | Enable / Disable user rights              | 
| TTP User                    | REDCap user to give rights to                             |
| TTP Function Add Patient    | Allow user to add patient records                         |
| TTP Function Search Patient | Allow user to search patient records via identifying data |
| TTP Function Manage Patient | Allow user to manage/change patient's identifiying data   |
| TTP Function Request Patient by Identifier | Allow user to access patient's identifying data via PSN |

## Dispatcher Configuration

Changes to the default dispatcher config from the demo setup provided by UMG:
The following REDCap specific notificationConsumer config has to be added and the URL has to be replaced by the one from the actual project (can be found on the 'TTP_ Dispatcher Logging' admin page header). Additionally to the new notificationConsumer the **\<redirectUrlParameterNameForId\>** was changed from "pid" to "id" since in REDCap context the PID referrs to the Project-ID.

The (changed) full configuration file can be found [here](dispatcher.config.redcap.xml).

```xml
<notificationConsumer>
    <id>redcap</id>
    <targedIdType>PSN</targedIdType>
    <targetIdDomain>demo.system.mdat</targetIdDomain>
    <url>http://localhost/redcap/api/?type=module&amp;prefix=redcap-module-ttp-dispatcher&amp;page=notification_consumer&amp;pid=1&amp;NOAUTH</url>
    <dataFormat>JSON</dataFormat>
    <connectorType>HTTP_POST</connectorType>
    <externalTargetIdType>PSN</externalTargetIdType>
    <timeout>3000</timeout>
    <notificationTypeActivations>
        <org.icmvc.ttp.dispatcher.common.config.notification.NotificationTypeActivation>
            <notificationType>REVOCATION</notificationType>
            <studyFilterMode>EXCLUDE</studyFilterMode>
            <studyFilter/>
            <active>true</active>
            <sendEventStatusTrigger>ALWAYS</sendEventStatusTrigger>
            <eventFilter/>
            <sendDelay>0</sendDelay>
        </org.icmvc.ttp.dispatcher.common.config.notification.NotificationTypeActivation>
        <org.icmvc.ttp.dispatcher.common.config.notification.NotificationTypeActivation>
            <notificationType>LEGITIMATION_STATUS</notificationType>
            <studyFilterMode>EXCLUDE</studyFilterMode>
            <studyFilter/>
            <active>true</active>
            <sendEventStatusTrigger>ALWAYS</sendEventStatusTrigger>
            <eventFilter/>
            <sendDelay>0</sendDelay>
        </org.icmvc.ttp.dispatcher.common.config.notification.NotificationTypeActivation>
        <org.icmvc.ttp.dispatcher.common.config.notification.NotificationTypeActivation>
            <notificationType>NEW_PATIENT</notificationType>
            <studyFilterMode>EXCLUDE</studyFilterMode>
            <studyFilter/>
            <active>true</active>
            <sendEventStatusTrigger>ALWAYS</sendEventStatusTrigger>
            <eventFilter/>
            <sendDelay>0</sendDelay>
        </org.icmvc.ttp.dispatcher.common.config.notification.NotificationTypeActivation>
    </notificationTypeActivations>
    <additionalParameter/>
    <activated>true</activated>
    <allowedLocalIdentifierDomains class="set"/>
    <systemConfiguration>mdat</systemConfiguration>
    <apiKey>admin</apiKey>
</notificationConsumer>
```

### Additional Permissions, Roles and Actors

Following additional Changes can be included (currently not part of the provided config file) to add more specific roles and actors for the integration functionalities. To fully utilize this, the API-Keys configured in the [module configuration](### System Configuration) have to be changed accordingly and also the `<systemConfigurations>` within the dispatcher configuration would have to be adapted to reflect the new keys.

```xml
...
<permissions>
      <permission action="create" description="" identifier="create_queryPolicies" object="queryPolicies" type="simple"/>
      <permission action="call" description="" identifier="call_queryPolicies" object="queryPolicies" type="simple"/>
...
```
```xml
...
<roles>
    <role description="Session and Token Request" identifier="createSessionAndToken"> 
      <simplePermissions> 
        <simplePermission identifier="create_session"/> 
        <simplePermission identifier="create_token"/> 
      </simplePermissions> 
    </role> 
    <role description="Add and Search Patients" identifier="addAndSearchPatient"> 
      <simplePermissions> 
        <simplePermission identifier="event.demo.recruitment"/> 
        <simplePermission identifier="event.demo.search"/> 
        <simplePermission identifier="create_addPatient"/> 
        <simplePermission identifier="create_searchPatient"/> 
      </simplePermissions> 
    </role> 
    <role description="Manage Patients" identifier="managePatient"> 
      <simplePermissions> 
        <simplePermission identifier="event.demo.manage"/> 
        <simplePermission identifier="create_managePatient"/> 
        <simplePermission identifier="call_managePatient"/> 
      </simplePermissions> 
    </role> 
    <role description="Query Consent Policies" identifier="queryPolicies"> 
      <simplePermissions> 
        <simplePermission identifier="event.demo.transferData"/> 
        <simplePermission identifier="call_queryPolicies"/> 
        <simplePermission identifier="create_queryPolicies"/> 
        <simplePermission identifier="create_queryLegitimationStatus"/> 
        <simplePermission identifier="call_queryLegitimationStatus"/> 
      </simplePermissions> 
    </role> 
    <role description="Request Patient IDAT by PSN" identifier="requestPatientByIdentifier"> 
      <simplePermissions> 
        <simplePermission identifier="event.demo.resolvePsn"/> 
        <simplePermission identifier="call_requestPatientByIdentifier"/> 
        <simplePermission identifier="create_requestPatientByIdentifier"/> 
      </simplePermissions> 
    </role> 
    <role description="" identifier="requestPsn"> 
      <simplePermissions> 
        <simplePermission identifier="event.demo.requestPsn"/> 
        <simplePermission identifier="call_requestPSN"/> 
        <simplePermission identifier="create_requestPSN"/> 
      </simplePermissions> 
    </role> 
...
```

```xml
...
  <actors> 
    <actor description="technical_site_admin" identifier="[TTP API Key]"> 
      <actorRoles> 
        <actorRole identifier="createSessionAndToken"/> 
        <actorRole identifier="addAndSearchPatient"/> 
        <actorRole identifier="managePatient"/> 
        <actorRole identifier="queryPolicies"/> 
        <actorRole identifier="requestPatientByIdentifier"/> 
        <actorRole identifier="requestPsn"/> 
      </actorRoles> 
    </actor> 
    <actor description="policiy_token_read" identifier="[TTP Policy Query API Key]"> 
      <actorRoles> 
        <actorRole identifier="createSessionAndToken"/> 
        <actorRole identifier="queryPolicies"/> 
      </actorRoles> 
    </actor> 
...
```
