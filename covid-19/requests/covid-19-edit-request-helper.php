<?php

ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . '/models/Covid19.php');
$general = new General($db);

// echo "<pre>";
// var_dump($_POST);
// die;


$tableName = "form_covid19";
$tableName1 = "activity_log";

try {
  //system config
  $systemConfigQuery = "SELECT * FROM system_config";
  $systemConfigResult = $db->query($systemConfigQuery);
  $sarr = array();
  // now we create an associative array so that we can easily create view variables
  for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
  }
  $instanceId = '';
  if (isset($_SESSION['instanceId'])) {
    $instanceId = $_SESSION['instanceId'];
  }

  if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != "") {
    $sampleCollectionDate = explode(" ", $_POST['sampleCollectionDate']);
    $_POST['sampleCollectionDate'] = $general->dateFormat($sampleCollectionDate[0]) . " " . $sampleCollectionDate[1];
  } else {
    $_POST['sampleCollectionDate'] = NULL;
  }

  //Set sample received date
  if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != "") {
    $sampleReceivedDate = explode(" ", $_POST['sampleReceivedDate']);
    $_POST['sampleReceivedDate'] = $general->dateFormat($sampleReceivedDate[0]) . " " . $sampleReceivedDate[1];
  } else {
    $_POST['sampleReceivedDate'] = NULL;
  }

  if (isset($_POST['sampleTestedDateTime']) && trim($_POST['sampleTestedDateTime']) != "") {
    $sampleTestedDate = explode(" ", $_POST['sampleTestedDateTime']);
    $_POST['sampleTestedDateTime'] = $general->dateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
  } else {
    $_POST['sampleTestedDateTime'] = NULL;
  }

  if (isset($_POST['patientDob']) && trim($_POST['patientDob']) != "") {
    $_POST['patientDob'] = $general->dateFormat($_POST['patientDob']);
  } else {
    $_POST['patientDob'] = NULL;
  }

  if (isset($_POST['dateOfSymptomOnset']) && trim($_POST['dateOfSymptomOnset']) != "") {
    $_POST['dateOfSymptomOnset'] = $general->dateFormat($_POST['dateOfSymptomOnset']);
  } else {
    $_POST['dateOfSymptomOnset'] = NULL;
  }

  if (isset($_POST['returnDate']) && trim($_POST['returnDate']) != "") {
    $_POST['returnDate'] = $general->dateFormat($_POST['returnDate']);
  } else {
    $_POST['returnDate'] = NULL;
  }


  if ($sarr['user_type'] == 'remoteuser') {
    $sampleCode = 'remote_sample_code';
    $sampleCodeKey = 'remote_sample_code_key';
  } else {
    $sampleCode = 'sample_code';
    $sampleCodeKey = 'sample_code_key';
  }




  if ($sarr['user_type'] == 'remoteuser') {
    $status = 9;
  }

  if (isset($_POST['oldStatus']) && !empty($_POST['oldStatus'])) {
    $status = $_POST['oldStatus'];
  }

  if ($sarr['user_type'] == 'vluser' && $_POST['oldStatus'] == 9) {
    $status = 6;
  }

  if (isset($_POST['isSampleRejected']) && $_POST['isSampleRejected'] == 'yes') {
    $_POST['result'] = null;
    $status = 4;
  }


  if ($sarr['user_type'] == 'remoteuser' && $_POST['oldStatus'] == 9) {
    $_POST['status'] = 9;
  } else if ($sarr['user_type'] == 'vluser' && $_POST['oldStatus'] == 9) {
    $_POST['status'] = 6;
  }
  if ($_POST['status'] == '') {
    $_POST['status']  = $_POST['oldStatus'];
  }


  $covid19Data = array(
    'facility_id' => isset($_POST['facilityId']) ? $_POST['facilityId'] : null,
    'province_id' => isset($_POST['provinceId']) ? $_POST['provinceId'] : null,
    'lab_id' => isset($_POST['labId']) ? $_POST['labId'] : null,
    'implementing_partner' => isset($_POST['implementingPartner']) ? $_POST['implementingPartner'] : null,
    'funding_source' => isset($_POST['fundingSource']) ? $_POST['fundingSource'] : null,
    'patient_id' => isset($_POST['patientId']) ? $_POST['patientId'] : null,
    'patient_name' => isset($_POST['firstName']) ? $_POST['firstName'] : null,
    'patient_surname' => isset($_POST['lastName']) ? $_POST['lastName'] : null,
    'patient_dob' => isset($_POST['patientDob']) ? $_POST['patientDob'] : null,
    'patient_gender' => isset($_POST['patientGender']) ? $_POST['patientGender'] : null,
    'patient_age' => isset($_POST['patientAge']) ? $_POST['patientAge'] : null,
    'patient_phone_number' => isset($_POST['patientPhoneNumber']) ? $_POST['patientPhoneNumber'] : null,
    'patient_address' => isset($_POST['patientAddress']) ? $_POST['patientAddress'] : null,
    'specimen_type' => isset($_POST['specimenType']) ? $_POST['specimenType'] : null,
    'sample_collection_date' => isset($_POST['sampleCollectionDate']) ? $_POST['sampleCollectionDate'] : null,
    'is_sample_post_mortem' => isset($_POST['isSamplePostMortem']) ? $_POST['isSamplePostMortem'] : null,
    'priority_status' => isset($_POST['priorityStatus']) ? $_POST['priorityStatus'] : null,
    'date_of_symptom_onset' => isset($_POST['dateOfSymptomOnset']) ? $_POST['dateOfSymptomOnset'] : null,
    'contact_with_confirmed_case' => isset($_POST['contactWithConfirmedCase']) ? $_POST['contactWithConfirmedCase'] : null,
    'has_recent_travel_history' => isset($_POST['hasRecentTravelHistory']) ? $_POST['hasRecentTravelHistory'] : null,
    'travel_country_names' => isset($_POST['countryName']) ? $_POST['countryName'] : null,
    'travel_return_date' => isset($_POST['returnDate']) ? $_POST['returnDate'] : null,
    'sample_received_at_vl_lab_datetime' => isset($_POST['sampleReceivedDate']) ? $_POST['sampleReceivedDate'] : null,
    'sample_tested_datetime' => isset($_POST['sampleTestedDateTime']) ? $_POST['sampleTestedDateTime'] : null,
    'is_sample_rejected' => isset($_POST['isSampleRejected']) ? $_POST['isSampleRejected'] : null,
    'result' => isset($_POST['result']) ? $_POST['result'] : null,
    'result_status' => $status,
    'data_sync' => 0,
    'reason_for_sample_rejection' => isset($_POST['sampleRejectionReason']) ? $_POST['sampleRejectionReason'] : null,
    'sample_registered_at_lab' => $general->getDateTime(),
    'last_modified_by' => $_SESSION['userId'],
    'last_modified_datetime' => $general->getDateTime()
  );


  if ($sarr['user_type'] == 'remoteuser') {
    //$covid19Data['remote_sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] : NULL;
  } else {
    if ($_POST['sampleCodeCol'] != '') {
      //$covid19Data['sample_code'] = (isset($_POST['sampleCodeCol']) && $_POST['sampleCodeCol'] != '') ? $_POST['sampleCodeCol'] : NULL;
    } else {
      $covid19Model = new Model_Covid19($db);



      $sampleCodeKeysJson = $covid19Model->generateCovid19SampleCode($_POST['provinceCode'], $_POST['sampleCollectionDate']);
      $sampleCodeKeys = json_decode($sampleCodeKeysJson, true);
      $covid19Data['sample_code'] = $sampleCodeKeys['sampleCode'];
      $covid19Data['sample_code_key'] = $sampleCodeKeys['sampleCodeKey'];
      $covid19Data['sample_code_format'] = $sampleCodeKeys['sampleCodeFormat'];
    }
  }


  //echo "<pre>"; var_dump($covid19Data); die;

  if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '') {
    $db = $db->where('covid19_id', $_POST['covid19SampleId']);
    $id = $db->update($tableName, $covid19Data);
  }



  if ($id > 0) {
    $_SESSION['alertMsg'] = "Covid-19 request updated successfully";
    //Add event log
    $eventType = 'update-covid-19-request';
    $action = ucwords($_SESSION['userName']) . ' updated Covid-19 request data with the sample id ' . $_POST['covid19SampleId'];
    $resource = 'covid-19-edit-request';

    $general->activityLog($eventType, $action, $resource);

    // $data=array(
    // 'event_type'=>$eventType,
    // 'action'=>$action,
    // 'resource'=>$resource,
    // 'date_time'=>$general->getDateTime()
    // );
    // $db->insert($tableName1,$data);

  } else {
    $_SESSION['alertMsg'] = "Please try again later";
  }
  header("location:/covid-19/requests/covid-19-requests.php");
} catch (Exception $exc) {
  error_log($exc->getMessage());
  error_log($exc->getTraceAsString());
}