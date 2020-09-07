<?php
ob_start();
$province = '';
$province .= "<option value=''> -- Select -- </option>";
foreach ($pdResult as $provinceName) {
  $province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}

$facility = '';
$facility .= "<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";
foreach ($fResult as $fDetails) {
  $facility .= "<option data-code='" . $fDetails['facility_code'] . "' data-emails='" . $fDetails['facility_emails'] . "' data-mobile-nos='" . $fDetails['facility_mobile_numbers'] . "' data-contact-person='" . ucwords($fDetails['contact_person']) . "' value='" . $fDetails['facility_id'] . "'>" . ucwords($fDetails['facility_name']) . "</option>";
}

$artRegimenQuery = "SELECT DISTINCT headings FROM r_art_code_details WHERE nation_identifier ='rwd'";
$artRegimenResult = $db->rawQuery($artRegimenQuery);
$aQuery = "SELECT * from r_art_code_details where nation_identifier='rwd' AND art_status = 'active'";
$aResult = $db->query($aQuery);

//facility details
if (isset($vlQueryInfo[0]['facility_id']) && $vlQueryInfo[0]['facility_id'] > 0) {
  $facilityQuery = "SELECT * from facility_details where facility_id='" . $vlQueryInfo[0]['facility_id'] . "' AND status='active'";
  $facilityResult = $db->query($facilityQuery);
}
if (!isset($facilityResult[0]['facility_code'])) {
  $facilityResult[0]['facility_code'] = '';
}
if (!isset($facilityResult[0]['facility_mobile_numbers'])) {
  $facilityResult[0]['facility_mobile_numbers'] = '';
}
if (!isset($facilityResult[0]['contact_person'])) {
  $facilityResult[0]['contact_person'] = '';
}
if (!isset($facilityResult[0]['facility_emails'])) {
  $facilityResult[0]['facility_emails'] = '';
}
if (!isset($facilityResult[0]['facility_state']) || $facilityResult[0]['facility_state'] == '') {
  $facilityResult[0]['facility_state'] = '';
}
if (!isset($facilityResult[0]['facility_district']) || $facilityResult[0]['facility_district'] == '') {
  $facilityResult[0]['facility_district'] = '';
}
$stateName = $facilityResult[0]['facility_state'];
if (trim($stateName) != '') {
  $stateQuery = "SELECT * from province_details where province_name='" . $stateName . "'";
  $stateResult = $db->query($stateQuery);
}
if (!isset($stateResult[0]['province_code']) || $stateResult[0]['province_code'] == '') {
  $stateResult[0]['province_code'] = '';
}
//district details
$districtResult = array();
if (trim($stateName) != '') {
  $districtQuery = "SELECT DISTINCT facility_district from facility_details where facility_state='" . $stateName . "' AND status='active'";
  $districtResult = $db->query($districtQuery);
  $facilityQuery = "SELECT * from facility_details where `status`='active' AND facility_type='2'";
  $lResult = $db->query($facilityQuery);
}

//set reason for changes history
$rch = '';
if (isset($vlQueryInfo[0]['reason_for_vl_result_changes']) && $vlQueryInfo[0]['reason_for_vl_result_changes'] != '' && $vlQueryInfo[0]['reason_for_vl_result_changes'] != null) {
  $rch .= '<h4>Result Changes History</h4>';
  $rch .= '<table style="width:100%;">';
  $rch .= '<thead><tr style="border-bottom:2px solid #d3d3d3;"><th style="width:20%;">USER</th><th style="width:60%;">MESSAGE</th><th style="width:20%;text-align:center;">DATE</th></tr></thead>';
  $rch .= '<tbody>';
  $splitChanges = explode('vlsm', $vlQueryInfo[0]['reason_for_vl_result_changes']);
  for ($c = 0; $c < count($splitChanges); $c++) {
    $getData = explode("##", $splitChanges[$c]);
    $expStr = explode(" ", $getData[2]);
    $changedDate = $general->humanDateFormat($expStr[0]) . " " . $expStr[1];
    $rch .= '<tr><td>' . ucwords($getData[0]) . '</td><td>' . ucfirst($getData[1]) . '</td><td style="text-align:center;">' . $changedDate . '</td></tr>';
  }
  $rch .= '</tbody>';
  $rch .= '</table>';
}
$disable = "disabled = 'disabled'";
?>
<style>
  .table>tbody>tr>td {
    border-top: none;
  }

  .form-control {
    width: 100% !important;
  }

  .row {
    margin-top: 6px;
  }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM </h1>
    <ol class="breadcrumb">
      <li><a href="/dashboard/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Enter Vl Request</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- SELECT2 EXAMPLE -->
    <div class="box box-default">
      <div class="box-header with-border">
        <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
      </div>
      <div class="box-body">
        <!-- form start -->

        <div class="box-body">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Clinic Information: (To be filled by requesting Clinican/Nurse)</h3>
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
                    <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Enter Sample ID" title="Please enter sample id" value="<?php echo $vlQueryInfo[0]['sample_code']; ?>" <?php echo $disable; ?> style="width:100%;" />
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="sampleReordered">
                      <input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" <?php echo (trim($vlQueryInfo[0]['sample_reordered']) == 'yes') ? 'checked="checked"' : '' ?> <?php echo $disable; ?> title="Please check sample reordered"> Sample Reordered
                    </label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <label for="province">Province <span class="mandatory">*</span></label>
                  <select class="form-control isRequired" name="province" id="province" title="Please choose province" <?php echo $disable; ?> style="width:100%;" onchange="getfacilityDetails(this);">
                    <option value=""> -- Select -- </option>
                    <?php foreach ($pdResult as $provinceName) { ?>
                      <option value="<?php echo $provinceName['province_name'] . "##" . $provinceName['province_code']; ?>" <?php echo ($facilityResult[0]['facility_state'] . "##" . $stateResult[0]['province_code'] == $provinceName['province_name'] . "##" . $provinceName['province_code']) ? "selected='selected'" : "" ?>><?php echo ucwords($provinceName['province_name']); ?></option>;
                    <?php } ?>
                  </select>
                </div>
                <div class="col-xs-3 col-md-3">
                  <label for="district">District <span class="mandatory">*</span></label>
                  <select class="form-control isRequired" name="district" id="district" title="Please choose district" <?php echo $disable; ?> style="width:100%;" onchange="getfacilityDistrictwise(this);">
                    <option value=""> -- Select -- </option>
                    <?php
                    foreach ($districtResult as $districtName) {
                    ?>
                      <option value="<?php echo $districtName['facility_district']; ?>" <?php echo ($facilityResult[0]['facility_district'] == $districtName['facility_district']) ? "selected='selected'" : "" ?>><?php echo ucwords($districtName['facility_district']); ?></option>
                    <?php
                    }
                    ?>
                  </select>
                </div>
                <div class="col-xs-3 col-md-3">
                  <label for="fName">Clinic/Health Center <span class="mandatory">*</span></label>
                  <select class="form-control isRequired" id="fName" name="fName" title="Please select clinic/health center name" <?php echo $disable; ?> style="width:100%;" onchange="autoFillFacilityCode();">
                    <option data-code="" data-emails="" data-mobile-nos="" data-contact-person="" value=""> -- Select -- </option>
                    <?php foreach ($fResult as $fDetails) { ?>
                      <option data-code="<?php echo $fDetails['facility_code']; ?>" data-emails="<?php echo $fDetails['facility_emails']; ?>" data-mobile-nos="<?php echo $fDetails['facility_mobile_numbers']; ?>" data-contact-person="<?php echo ucwords($fDetails['contact_person']); ?>" value="<?php echo $fDetails['facility_id']; ?>" <?php echo ($vlQueryInfo[0]['facility_id'] == $fDetails['facility_id']) ? "selected='selected'" : "" ?>><?php echo ucwords($fDetails['facility_name']); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="fCode">Clinic/Health Center Code </label>
                    <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Clinic/Health Center Code" title="Please enter clinic/health center code" value="<?php echo $facilityResult[0]['facility_code']; ?>" <?php echo $disable; ?>>
                  </div>
                </div>
              </div>
              <div class="row facilityDetails" style="display:<?php echo (trim($facilityResult[0]['facility_emails']) != '' || trim($facilityResult[0]['facility_mobile_numbers']) != '' || trim($facilityResult[0]['contact_person']) != '') ? '' : 'none'; ?>;">
                <div class="col-xs-2 col-md-2 femails" style="display:<?php echo (trim($facilityResult[0]['facility_emails']) != '') ? '' : 'none'; ?>;"><strong>Clinic/Health Center Email(s)</strong></div>
                <div class="col-xs-2 col-md-2 femails facilityEmails" style="display:<?php echo (trim($facilityResult[0]['facility_emails']) != '') ? '' : 'none'; ?>;"><?php echo $facilityResult[0]['facility_emails']; ?></div>
                <div class="col-xs-2 col-md-2 fmobileNumbers" style="display:<?php echo (trim($facilityResult[0]['facility_mobile_numbers']) != '') ? '' : 'none'; ?>;"><strong>Clinic/Health Center Mobile No.(s)</strong></div>
                <div class="col-xs-2 col-md-2 fmobileNumbers facilityMobileNumbers" style="display:<?php echo (trim($facilityResult[0]['facility_mobile_numbers']) != '') ? '' : 'none'; ?>;"><?php echo $facilityResult[0]['facility_mobile_numbers']; ?></div>
                <div class="col-xs-2 col-md-2 fContactPerson" style="display:<?php echo (trim($facilityResult[0]['contact_person']) != '') ? '' : 'none'; ?>;"><strong>Clinic Contact Person -</strong></div>
                <div class="col-xs-2 col-md-2 fContactPerson facilityContactPerson" style="display:<?php echo (trim($facilityResult[0]['contact_person']) != '') ? '' : 'none'; ?>;"><?php echo ucwords($facilityResult[0]['contact_person']); ?></div>
              </div>
            </div>
          </div>
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Patient Information</h3>
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="artNo">ART (TRACNET) No. <span class="mandatory">*</span></label>
                    <input type="text" name="artNo" id="artNo" class="form-control isRequired" placeholder="Enter ART Number" title="Enter art number" value="<?php echo $vlQueryInfo[0]['patient_art_no']; ?>" <?php echo $disable; ?> />
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="dob">Date of Birth <span class="mandatory">*</span></label>
                    <input type="text" name="dob" id="dob" class="form-control date isRequired" placeholder="Enter DOB" title="Enter dob" value="<?php echo $vlQueryInfo[0]['patient_dob']; ?>" <?php echo $disable; ?> />
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="ageInYears">If DOB unknown, Age in Year </label>
                    <input type="text" name="ageInYears" id="ageInYears" class="form-control checkNum" maxlength="2" placeholder="Age in Year" title="Enter age in years" <?php echo $disable; ?> value="<?php echo $vlQueryInfo[0]['patient_age_in_years']; ?>" />
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="ageInMonths">If Age
                      < 1, Age in Month </label> <input type="text" name="ageInMonths" id="ageInMonths" class="form-control checkNum" maxlength="2" placeholder="Age in Month" title="Enter age in months" <?php echo $disable; ?> value="<?php echo $vlQueryInfo[0]['patient_age_in_months']; ?>" />
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="patientFirstName">Patient Name </label>
                    <input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="Enter Patient Name" title="Enter patient name" <?php echo $disable; ?> value="<?php echo $patientFirstName; ?>" />
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="gender">Gender <span class="mandatory">*</span></label><br>
                    <label class="radio-inline" style="margin-left:0px;">
                      <input type="radio" class="isRequired" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['patient_gender'] == 'male') ? "checked='checked'" : "" ?>> Male
                    </label>&nbsp;&nbsp;
                    <label class="radio-inline" style="margin-left:0px;">
                      <input type="radio" id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['patient_gender'] == 'female') ? "checked='checked'" : "" ?>> Female
                    </label>
                    <!--<label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" < ?php echo $disable;?> < ?php echo ($vlQueryInfo[0]['patient_gender']=='not_recorded')?"checked='checked'":""?>>Not Recorded
                          </label>-->
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="patientPhoneNumber">Phone Number</label>
                    <input type="text" name="patientPhoneNumber" id="patientPhoneNumber" class="form-control checkNum" maxlength="15" placeholder="Enter Phone Number" title="Enter phone number" value="<?php echo $vlQueryInfo[0]['patient_mobile_number']; ?>" <?php echo $disable; ?> />
                  </div>
                </div>
              </div>
            </div>
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Sample Information</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                      <label for="">Date of Sample Collection <span class="mandatory">*</span></label>
                      <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" value="<?php echo $vlQueryInfo[0]['sample_collection_date']; ?>" <?php echo $disable; ?>>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                      <label for="specimenType">Sample Type <span class="mandatory">*</span></label>
                      <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose sample type" <?php echo $disable; ?>>
                        <option value=""> -- Select -- </option>
                        <?php
                        foreach ($sResult as $name) {
                        ?>
                          <option value="<?php echo $name['sample_id']; ?>" <?php echo ($vlQueryInfo[0]['sample_type'] == $name['sample_id']) ? "selected='selected'" : "" ?>><?php echo ucwords($name['sample_name']); ?></option>
                        <?php
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Treatment Information</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-xs-3 col-md-3">
                      <div class="form-group">
                        <label for="">Date of Treatment Initiation</label>
                        <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of Treatment Initiated" title="Date Of treatment initiated" value="<?php echo $vlQueryInfo[0]['treatment_initiated_date']; ?>" <?php echo $disable; ?> style="width:100%;">
                      </div>
                    </div>
                    <div class="col-xs-3 col-md-3">
                      <div class="form-group">
                        <label for="artRegimen">Current Regimen <?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                        <select class="form-control <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" <?php echo $disable; ?> style="width:100%;" onchange="checkARTValue();">
                          <option value="">-- Select --</option>
                          <?php foreach ($artRegimenResult as $heading) { ?>
                            <optgroup label="<?php echo ucwords($heading['headings']); ?>">
                              <?php
                              foreach ($aResult as $regimen) {
                                if ($heading['headings'] == $regimen['headings']) {
                              ?>
                                  <option value="<?php echo $regimen['art_code']; ?>" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['current_regimen'] == $regimen['art_code']) ? "selected='selected'" : "" ?>><?php echo $regimen['art_code']; ?></option>
                              <?php
                                }
                              }
                              ?>
                            </optgroup>
                          <?php } ?>
                          <option value="other">Other</option>
                        </select>
                        <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" <?php echo $disable; ?> style="width:100%;display:none;margin-top:2px;">
                      </div>
                    </div>
                    <div class="col-xs-3 col-md-3">
                      <div class="form-group">
                        <label for="">Date of Initiation of Current Regimen<?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                        <input type="text" class="form-control date <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" style="width:100%;" name="regimenInitiatedOn" id="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" <?php echo $disable; ?> value="<?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen']; ?>">
                      </div>
                    </div>
                    <div class="col-xs-3 col-md-3">
                      <div class="form-group">
                        <label for="arvAdherence">ARV Adherence <?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                        <select name="arvAdherence" id="arvAdherence" class="form-control <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" title="Please choose adherence" <?php echo $disable; ?>>
                          <option value=""> -- Select -- </option>
                          <option value="good" <?php echo ($vlQueryInfo[0]['arv_adherance_percentage'] == 'good') ? "selected='selected'" : "" ?>>Good >= 95%</option>
                          <option value="fair" <?php echo ($vlQueryInfo[0]['arv_adherance_percentage'] == 'fair') ? "selected='selected'" : "" ?>>Fair (85-94%)</option>
                          <option value="poor" <?php echo ($vlQueryInfo[0]['arv_adherance_percentage'] == 'poor') ? "selected='selected'" : "" ?>>Poor < 85%</option> </select> </div> </div> </div> <div class="row femaleSection" style="display:<?php echo ($vlQueryInfo[0]['patient_gender'] == 'female') ? "" : "none" ?>" ;>
                              <div class="col-xs-3 col-md-3">
                                <div class="form-group">
                                  <label for="patientPregnant">Is Patient Pregnant? <span class="mandatory">*</span></label><br>
                                  <label class="radio-inline">
                                    <input type="radio" class="<?php echo ($vlQueryInfo[0]['patient_gender'] == 'female') ? "isRequired" : ""; ?>" id="pregYes" name="patientPregnant" value="yes" title="Please check patient pregnant status" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['is_patient_pregnant'] == 'yes') ? "checked='checked'" : "" ?>> Yes
                                  </label>
                                  <label class="radio-inline">
                                    <input type="radio" id="pregNo" name="patientPregnant" value="no" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['is_patient_pregnant'] == 'no') ? "checked='checked'" : "" ?>> No
                                  </label>
                                </div>
                              </div>
                              <div class="col-xs-3 col-md-3">
                                <div class="form-group">
                                  <label for="breastfeeding">Is Patient Breastfeeding? <span class="mandatory">*</span></label><br>
                                  <label class="radio-inline">
                                    <input type="radio" class="<?php echo ($vlQueryInfo[0]['patient_gender'] == 'female') ? "isRequired" : ""; ?>" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check patient breastfeeding status" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding'] == 'yes') ? "checked='checked'" : "" ?>> Yes
                                  </label>
                                  <label class="radio-inline">
                                    <input type="radio" id="breastfeedingNo" name="breastfeeding" value="no" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding'] == 'no') ? "checked='checked'" : "" ?>> No
                                  </label>
                                </div>
                              </div>
                      </div>
                    </div>
                    <div class="box box-primary">
                      <div class="box-header with-border">
                        <h3 class="box-title">Indication for Viral Load Testing <span class="mandatory">*</span></h3><small> (Please tick one):(To be completed by clinician)</small>
                      </div>
                      <div class="box-body">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <div class="col-lg-12">
                                <label class="radio-inline">
                                  <?php
                                  $vlTestReasonQueryRow = "SELECT * from r_vl_test_reasons where test_reason_id='" . trim($vlQueryInfo[0]['reason_for_vl_testing']) . "' OR test_reason_name = '" . trim($vlQueryInfo[0]['reason_for_vl_testing']) . "'";
                                  $vlTestReasonResultRow = $db->query($vlTestReasonQueryRow);
                                  $checked = '';
                                  $display = '';
                                  $vlValue = '';
                                  if (trim($vlQueryInfo[0]['reason_for_vl_testing']) == 'routine' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'routine') {
                                    $checked = 'checked="checked"';
                                    $display = 'block';
                                    if ($vlQueryInfo[0]['last_vl_result_routine'] != NULL && trim($vlQueryInfo[0]['last_vl_result_routine']) != '' && trim($vlQueryInfo[0]['last_vl_result_routine']) != '<20' && trim($vlQueryInfo[0]['last_vl_result_routine']) != 'tnd') {
                                      $vlValue = $vlQueryInfo[0]['last_vl_result_routine'];
                                    }
                                  } else {
                                    $checked = '';
                                    $display = 'none';
                                  }
                                  ?>
                                  <input type="radio" class="isRequired" id="rmTesting" name="stViralTesting" value="routine" title="Please check viral load indication testing type" <?php echo $disable; ?> <?php echo $checked; ?> onclick="showTesting('rmTesting');">
                                  <strong>Routine Monitoring</strong>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row rmTesting hideTestData" style="display:<?php echo $display; ?>;">
                          <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control date viralTestData" id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo (trim($vlQueryInfo[0]['last_vl_date_routine']) != '' && $vlQueryInfo[0]['last_vl_date_routine'] != null && $vlQueryInfo[0]['last_vl_date_routine'] != '0000-00-00') ? $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_routine']) : ''; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                          <div class="col-md-6">
                            <label for="rmTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control checkNum viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlValue; ?>" <?php echo $disable; ?> />
                              (copies/ml)<br>
                              <input type="checkbox" id="rmTestingVlCheckValuelt20" name="rmTestingVlCheckValue" <?php echo ($vlQueryInfo[0]['last_vl_result_routine'] == '<20') ? 'checked="checked"' : ''; ?> value="<20" <?php echo $disable; ?> title="Please check VL value">
                              < 20<br>
                                <input type="checkbox" id="rmTestingVlCheckValueTnd" name="rmTestingVlCheckValue" <?php echo ($vlQueryInfo[0]['last_vl_result_routine'] == 'tnd') ? 'checked="checked"' : ''; ?> value="tnd" <?php echo $disable; ?> title="Please check VL value"> Target Not Detected
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-8">
                            <div class="form-group">
                              <div class="col-lg-12">
                                <label class="radio-inline">
                                  <?php
                                  $checked = '';
                                  $display = '';
                                  $vlValue = '';
                                  if (trim($vlQueryInfo[0]['reason_for_vl_testing']) == 'failure' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'failure') {
                                    $checked = 'checked="checked"';
                                    $display = 'block';
                                    if ($vlQueryInfo[0]['last_vl_result_failure_ac'] != NULL && trim($vlQueryInfo[0]['last_vl_result_failure_ac']) != '' && trim($vlQueryInfo[0]['last_vl_result_failure_ac']) != '<20' && trim($vlQueryInfo[0]['last_vl_result_failure_ac']) != 'tnd') {
                                      $vlValue = $vlQueryInfo[0]['last_vl_result_failure_ac'];
                                    }
                                  } else {
                                    $checked = '';
                                    $display = 'none';
                                  }
                                  ?>
                                  <input type="radio" id="repeatTesting" name="stViralTesting" value="failure" title="Please check viral load indication testing type" <?php echo $disable; ?> <?php echo $checked; ?> onclick="showTesting('repeatTesting');">
                                  <strong>Repeat VL test after suspected treatment failure adherence counselling </strong>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row repeatTesting hideTestData" style="display: <?php echo $display; ?>;">
                          <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control date viralTestData" id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo (trim($vlQueryInfo[0]['last_vl_date_failure_ac']) != '' && $vlQueryInfo[0]['last_vl_date_failure_ac'] != null && $vlQueryInfo[0]['last_vl_date_failure_ac'] != '0000-00-00') ? $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_failure_ac']) : ''; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                          <div class="col-md-6">
                            <label for="repeatTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control checkNum viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlValue; ?>" <?php echo $disable; ?> />
                              (copies/ml)<br>
                              <input type="checkbox" id="repeatTestingVlCheckValuelt20" name="repeatTestingVlCheckValue" <?php echo ($vlQueryInfo[0]['last_vl_result_failure_ac'] == '<20') ? 'checked="checked"' : ''; ?> value="<20" <?php echo $disable; ?> title="Please check VL value">
                              < 20<br>
                                <input type="checkbox" id="repeatTestingVlCheckValueTnd" name="repeatTestingVlCheckValue" <?php echo ($vlQueryInfo[0]['last_vl_result_failure_ac'] == 'tnd') ? 'checked="checked"' : ''; ?> value="tnd" <?php echo $disable; ?> title="Please check VL value"> Target Not Detected
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <div class="col-lg-12">
                                <label class="radio-inline">
                                  <?php
                                  $checked = '';
                                  $display = '';
                                  $vlValue = '';
                                  if (trim($vlQueryInfo[0]['reason_for_vl_testing']) == 'suspect' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'suspect') {
                                    $checked = 'checked="checked"';
                                    $display = 'block';
                                    if ($vlQueryInfo[0]['last_vl_result_failure'] != NULL && trim($vlQueryInfo[0]['last_vl_result_failure']) != '' && trim($vlQueryInfo[0]['last_vl_result_failure']) != '<20' && trim($vlQueryInfo[0]['last_vl_result_failure']) != 'tnd') {
                                      $vlValue = $vlQueryInfo[0]['last_vl_result_failure'];
                                    }
                                  } else {
                                    $checked = '';
                                    $display = 'none';
                                  }
                                  ?>
                                  <input type="radio" id="suspendTreatment" name="stViralTesting" value="suspect" title="Please check viral load indication testing type" <?php echo $disable; ?> <?php echo $checked; ?> onclick="showTesting('suspendTreatment');">
                                  <strong>Suspect Treatment Failure</strong>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row suspendTreatment hideTestData" style="display: <?php echo $display; ?>;">
                          <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control date viralTestData" id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo (trim($vlQueryInfo[0]['last_vl_date_failure']) != '' && $vlQueryInfo[0]['last_vl_date_failure'] != null && $vlQueryInfo[0]['last_vl_date_failure'] != '0000-00-00') ? $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_failure']) : ''; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                          <div class="col-md-6">
                            <label for="suspendTreatmentVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control checkNum viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlValue; ?>" <?php echo $disable; ?> />
                              (copies/ml)<br>
                              <input type="checkbox" id="suspendTreatmentVlCheckValuelt20" name="suspendTreatmentVlCheckValue" <?php echo ($vlQueryInfo[0]['last_vl_result_failure'] == '<20') ? 'checked="checked"' : ''; ?> value="<20" <?php echo $disable; ?> title="Please check VL value">
                              < 20<br>
                                <input type="checkbox" id="suspendTreatmentVlCheckValueTnd" name="suspendTreatmentVlCheckValue" <?php echo ($vlQueryInfo[0]['last_vl_result_failure'] == 'tnd') ? 'checked="checked"' : ''; ?> value="tnd" <?php echo $disable; ?> title="Please check VL value"> Target Not Detected
                            </div>
                          </div>
                        </div>
                        <?php if (isset($recencyConfig['vlsync']) && $recencyConfig['vlsync'] == true) {  ?>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <div class="col-lg-12">
                                <label class="radio-inline">
                                  <input type="radio" class="" id="recencyTest" name="stViralTesting" value="recency" title="Please check viral load indication testing type" <?php echo $disable; ?> <?php echo trim($vlQueryInfo[0]['reason_for_vl_testing']) == '9999' ? "checked='checked'" : ""; ?> onclick="showTesting('recency')">
                                  <strong>Confirmation Test for Recency</strong>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <?php }  ?>
                        <hr>
                        <div class="row">
                          <div class="col-md-4">
                            <label for="reqClinician" class="col-lg-5 control-label">Request Clinician <?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" id="reqClinician" name="reqClinician" placeholder="Request Clinician" title="Please enter request clinician" value="<?php echo $vlQueryInfo[0]['request_clinician_name']; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                          <div class="col-md-4">
                            <label for="reqClinicianPhoneNumber" class="col-lg-5 control-label">Phone Number <?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control checkNum <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter request clinician phone number" value="<?php echo $vlQueryInfo[0]['request_clinician_phone_number']; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                          <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="requestDate">Request Date <?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control date <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" id="requestDate" name="requestDate" placeholder="Request Date" title="Please select request date" value="<?php echo $vlQueryInfo[0]['test_requested_on']; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <label for="vlFocalPerson" class="col-lg-5 control-label">VL Focal Person<?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter vl focal person name" value="<?php echo $vlQueryInfo[0]['vl_focal_person']; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                          <div class="col-md-4">
                            <label for="vlFocalPersonPhoneNumber" class="col-lg-5 control-label">VL Focal Person Phone Number <?php echo ($sarr['user_type'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control checkNum <?php echo ($sarr['user_type'] == 'remoteuser') ? "isRequired" : ''; ?>" id="vlFocalPersonPhoneNumber" name="vlFocalPersonPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter vl focal person phone number" value="<?php echo $vlQueryInfo[0]['vl_focal_person_phone_number']; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                          <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="emailHf">Email for HF </label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" value="<?php echo $facilityResult[0]['facility_emails']; ?>" <?php echo $disable; ?> />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <form class="form-inline" method="post" name="vlRequestFormRwd" id="vlRequestFormRwd" autocomplete="off" action="updateVlRequestHelperRwd.php">
                      <div class="box box-primary" style="<?php if ($sarr['user_type'] == 'remoteuser') { ?> pointer-events:none; <?php } ?>">
                        <div class="box-header with-border">
                          <h3 class="box-title">Laboratory Information</h3>
                        </div>
                        <div class="box-body">
                          <div class="row">
                            <div class="col-md-4">
                              <label for="labId" class="col-lg-5 control-label">Lab Name <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                                <select name="labId" id="labId" class="isRequired form-control labSection" title="Please choose lab">
                                  <option value="">-- Select --</option>
                                  <?php
                                  foreach ($lResult as $labName) {
                                  ?>
                                    <option value="<?php echo $labName['facility_id']; ?>" <?php echo ($vlQueryInfo[0]['lab_id'] == $labName['facility_id']) ? "selected='selected'" : "" ?>><?php echo ucwords($labName['facility_name']); ?></option>
                                  <?php
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <label for="testingPlatform" class="col-lg-5 control-label">VL Testing Platform<span class="mandatory">*</span> </label>
                              <div class="col-lg-7">
                                <select name="testingPlatform" id="testingPlatform" class="isRequired form-control labSection" title="Please choose VL Testing Platform">
                                  <option value="">-- Select --</option>
                                  <?php foreach ($importResult as $mName) { ?>
                                    <option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit']; ?>" <?php echo ($vlQueryInfo[0]['vl_test_platform'] == $mName['machine_name']) ? 'selected="selected"' : ''; ?>><?php echo $mName['machine_name']; ?></option>
                                  <?php
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>
                            <!--<div class="col-md-4">
                            <label for="testMethods" class="col-lg-5 control-label">Test Methods</label>
                            <div class="col-lg-7">
                              <select name="testMethods" id="testMethods" class="form-control labSection" title="Please choose test methods">
                                <option value=""> -- Select -- </option>
                                <option value="individual" < ?php echo($vlQueryInfo[0]['test_methods'] == 'individual')? 'selected="selected"':''; ?>>Individual</option>
                                <option value="minipool" < ?php echo($vlQueryInfo[0]['test_methods'] == 'minipool')? 'selected="selected"':''; ?>>Minipool</option>
                                <option value="other pooling algorithm" < ?php echo($vlQueryInfo[0]['test_methods'] == 'other pooling algorithm')? 'selected="selected"':''; ?>>Other Pooling Algorithm</option>
                               </select>
                            </div>
                        </div>-->
                          </div>
                          <div class="row">
                            <div class="col-md-4">
                              <label class="col-lg-5 control-label" for="sampleReceivedOn">Date Sample Received at Testing Lab <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                                <input type="text" class="form-control labSection isRequired" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Sample Received Date" title="Please select sample received date" value="<?php echo $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']; ?>" />
                              </div>
                            </div>
                            <div class="col-md-4">
                              <label class="col-lg-5 control-label" for="sampleTestingDateAtLab">Sample Testing Date <span class="mandatory">*</span> </label>
                              <div class="col-lg-7">
                                <input type="text" class="isRequired form-control labSection" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Sample Testing Date" title="Please select sample testing date" value="<?php echo $vlQueryInfo[0]['sample_tested_datetime']; ?>" />
                              </div>
                            </div>
                            <div class="col-md-4">
                              <label class="col-lg-5 control-label" for="resultDispatchedOn">Date Results Dispatched </label>
                              <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatched Date" title="Please select result dispatched date" value="<?php echo $vlQueryInfo[0]['result_dispatched_datetime']; ?>" />
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-4">
                              <label class="col-lg-5 control-label" for="noResult">Sample Rejection </label>
                              <div class="col-lg-7">
                                <label class="radio-inline">
                                  <input class="labSection" id="noResultYes" name="noResult" value="yes" title="Please check one" type="radio" <?php echo ($vlQueryInfo[0]['is_sample_rejected'] == 'yes') ? 'checked="checked"' : ''; ?>> Yes
                                </label>
                                <label class="radio-inline">
                                  <input class="labSection" id="noResultNo" name="noResult" value="no" title="Please check one" type="radio" <?php echo ($vlQueryInfo[0]['is_sample_rejected'] == 'no') ? 'checked="checked"' : ''; ?>> No
                                </label>
                              </div>
                            </div>
                            <div class="col-md-4 rejectionReason" style="display:<?php echo ($vlQueryInfo[0]['is_sample_rejected'] == 'yes') ? '' : 'none'; ?>;">
                              <label class="col-lg-5 control-label" for="rejectionReason">Rejection Reason <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                                <select name="rejectionReason" id="rejectionReason" class="form-control labSection" title="Please choose reason" onchange="checkRejectionReason();">
                                  <option value="">-- Select --</option>
                                  <?php foreach ($rejectionTypeResult as $type) { ?>
                                    <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                      <?php
                                      foreach ($rejectionResult as $reject) {
                                        if ($type['rejection_type'] == $reject['rejection_type']) {
                                      ?>
                                          <option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($vlQueryInfo[0]['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($reject['rejection_reason_name']); ?></option>
                                      <?php
                                        }
                                      }
                                      ?>
                                    </optgroup>
                                  <?php } ?>
                                  <option value="other">Other (Please Specify) </option>
                                </select>
                                <input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" style="width:100%;display:none;margin-top:2px;">
                              </div>
                            </div>
                            <div class="col-md-4 vlResult" style="visibility:<?php echo ($vlQueryInfo[0]['is_sample_rejected'] == 'yes') ? 'hidden' : 'visible'; ?>;">
                              <label class="col-lg-5 control-label" for="vlResult">Viral Load Result<span class="mandatory">*</span> (copiesl/ml) </label>
                              <div class="col-lg-7">
                                <input type="text" class="<?php echo ($vlQueryInfo[0]['is_sample_rejected'] == 'no' && $vlQueryInfo[0]['result'] != 'Target Not Detected' && $vlQueryInfo[0]['result'] == 'Below Detection Level') ? 'isRequired' : ''; ?> vlResult checkNum form-control labSection" id="vlResult" name="vlResult" placeholder="Viral Load Result" title="Please enter viral load result" value="<?php echo $vlQueryInfo[0]['result_value_absolute']; ?>" <?php echo ($vlQueryInfo[0]['result'] == 'Target Not Detected' || $vlQueryInfo[0]['result'] == 'Below Detection Level') ? 'readonly="readonly"' : ''; ?> style="width:100%;" onchange="calculateLogValue(this);" />

                                <input type="checkbox" class="labSection specialResults" id="lt20" name="lt20" value="yes" <?php echo ($vlQueryInfo[0]['result'] == '<20') ? 'checked="checked"' : '';
                                                                                                                            echo ($vlQueryInfo[0]['result'] == '<20' || $vlQueryInfo[0]['result'] == '< 20') ? 'disabled="disabled"' : '' ?> title="Please check <20">
                                <20<br>
                                  <input type="checkbox" class="labSection specialResults" id="tnd" name="tnd" value="yes" <?php echo ($vlQueryInfo[0]['result'] == 'Target Not Detected') ? 'checked="checked"' : '';
                                                                                                                            echo ($vlQueryInfo[0]['result'] == 'Below Detection Level') ? 'disabled="disabled"' : '' ?> title="Please check tnd"> Target Not Detected<br>
                                  <input type="checkbox" class="labSection specialResults" id="bdl" name="bdl" value="yes" <?php echo ($vlQueryInfo[0]['result'] == 'Below Detection Level') ? 'checked="checked"' : '';
                                                                                                                            echo ($vlQueryInfo[0]['result'] == 'Target Not Detected') ? 'disabled="disabled"' : '' ?> title="Please check bdl"> Below Detection Level
                              </div>
                            </div>
                            <div class="col-md-4 vlResult" style="visibility:<?php echo ($vlQueryInfo[0]['is_sample_rejected'] == 'yes') ? 'hidden' : 'visible'; ?>;">
                              <label class="col-lg-5 control-label" for="vlLog">Viral Load Log </label>
                              <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="vlLog" name="vlLog" placeholder="Viral Load Log" title="Please enter viral load log" value="<?php echo $vlQueryInfo[0]['result_value_log']; ?>" <?php echo ($vlQueryInfo[0]['result'] == 'Target Not Detected' || $vlQueryInfo[0]['result'] == 'Below Detection Level') ? 'readonly="readonly"' : ''; ?> style="width:100%;" onchange="calculateLogValue(this);" />
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-4">
                              <label class="col-lg-5 control-label" for="approvedBy">Approved By </label>
                              <div class="col-lg-7">
                                <select name="approvedBy" id="approvedBy" class="form-control labSection" title="Please choose approved by">
                                  <option value="">-- Select --</option>
                                  <?php
                                  foreach ($userResult as $uName) {
                                  ?>
                                    <option value="<?php echo $uName['user_id']; ?>" <?php echo ($vlQueryInfo[0]['result_approved_by'] == $uName['user_id']) ? "selected=selected" : ""; ?>><?php echo ucwords($uName['user_name']); ?></option>
                                  <?php
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>
                            <div class="col-md-8">
                              <label class="col-lg-2 control-label" for="labComments">Lab Tech. Comments </label>
                              <div class="col-lg-10">
                                <textarea class="form-control labSection" name="labComments" id="labComments" placeholder="Lab comments" style="width:100%"><?php echo trim($vlQueryInfo[0]['approver_comments']); ?></textarea>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <!-- <div class="col-md-4" style="<?php echo (($sarr['user_type'] == 'remoteuser')) ? 'display:none;' : ''; ?>">
                            <label class="col-lg-5 control-label" for="status">Status <span class="mandatory">*</span></label>
                            <div class="col-lg-7">
                              <select class="form-control labSection  <?php echo (($sarr['user_type'] != 'remoteuser')) ? 'isRequired' : ''; ?>" id="status" name="status" title="Please select test status">
                                <option value="">-- Select --</option>
                                <?php
                                foreach ($statusResult as $status) {
                                ?>
                                  <option value="<?php echo $status['status_id']; ?>"<?php echo ($vlQueryInfo[0]['result_status'] == $status['status_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($status['status_name']); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                        </div> -->
                            <div class="col-md-8 reasonForResultChanges" style="visibility:hidden;">
                              <label class="col-lg-2 control-label" for="reasonForResultChanges">Reason For Changes in Result <span class="mandatory">*</span></label>
                              <div class="col-lg-10">
                                <textarea class="form-control" name="reasonForResultChanges" id="reasonForResultChanges" placeholder="Enter Reason For Result Changes" title="Please enter reason for result changes" style="width:100%;"></textarea>
                              </div>
                            </div>
                          </div>
                          <?php
                          if (trim($rch) != '') {
                          ?>
                            <div class="row">
                              <div class="col-md-12"><?php echo $rch; ?></div>
                            </div>
                          <?php } ?>
                        </div>
                      </div>
                  </div>
                  <div class="box-footer">
                    <input type="hidden" name="vlSampleId" id="vlSampleId" value="<?php echo $vlQueryInfo[0]['vl_sample_id']; ?>" />
                    <input type="hidden" name="reasonForResultChangesHistory" id="reasonForResultChangesHistory" value="<?php echo $vlQueryInfo[0]['reason_for_vl_result_changes']; ?>" />
                    <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>&nbsp;
                    <a href="vlTestResult.php" class="btn btn-default"> Cancel</a>
                  </div>
                  </form>
                </div>
  </section>
</div>
<script>
  $(document).ready(function() {
    $("#vlResult").on('keyup keypress blur change paste', function() {
      if ($('#vlResult').val() != '') {
        if ($('#vlResult').val() != $('#vlResult').val().replace(/[^\d\.]/g, "")) {
          $('#vlResult').val('');
          alert('Please enter only numeric values for Viral Load Result')
        }
      }
    });

    $('#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').datetimepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      timeFormat: "HH:mm",
      maxDate: "Today",
      onChangeMonthYear: function(year, month, widget) {
        setTimeout(function() {
          $('.ui-datepicker-calendar').show();
        });
      },
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
    }).click(function() {
      $('.ui-datepicker-calendar').show();
    });
    $('#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').mask('99-aaa-9999 99:99');
    __clone = $("#vlRequestFormRwd .labSection").clone();
    reason = ($("#reasonForResultChanges").length) ? $("#reasonForResultChanges").val() : '';
    result = ($("#vlResult").length) ? $("#vlResult").val() : '';
  });

  $("input:radio[name=noResult]").click(function() {
    if ($(this).val() == 'yes') {
      $('.rejectionReason').show();
      $('.vlResult').css('display', 'none');
      $('#rejectionReason').addClass('isRequired');
      $("#status").val(4);
      $('#vlResult').removeClass('isRequired');
    } else {
      $('.vlResult').css('display', 'block');
      $('.rejectionReason').hide();
      $('#rejectionReason').removeClass('isRequired');
      $('#rejectionReason').val('');
      $("#status").val('');
      $('#vlResult').addClass('isRequired');
      // if any of the special results like tnd,bld are selected then remove isRequired from vlResult
      if ($('.specialResults:checkbox:checked').length) {
        $('#vlResult').removeClass('isRequired');
      }
    }
  });

  $('.specialResults').change(function() {
    if ($(this).is(':checked')) {
      $('#vlResult,#vlLog').attr('readonly', true);
      $('#vlResult').removeClass('isRequired');
      $(".specialResults").not(this).attr('disabled', true);
      $('.specialResults').not(this).prop('checked', false).removeAttr('checked');
    } else {
      $('#vlResult,#vlLog').attr('readonly', false);
      $(".specialResults").not(this).attr('disabled', false);
      if ($('#noResultNo').is(':checked')) {
        $('#vlResult').addClass('isRequired');
      }
    }
  });



  if ($(".specialResults").is(':checked')) {
    $('#vlResult, #vlLog').val('');
    $('#vlResult,#vlLog').attr('readonly', true);
    $('#vlResult, #vlLog').removeClass('isRequired');
    //$(".specialResults").not(this).attr('disabled',true);
    //$('.specialResults').not(this).prop('checked', false).removeAttr('checked');
  }
  if ($('#vlResult, #vlLog').val() != '') {
    $(".specialResults").attr('disabled', true);
    $('#vlResult').addClass('isRequired');
  }

  $('#vlResult,#vlLog').on('input', function(e) {
    if (this.value != '') {
      $(".specialResults").not(this).attr('disabled', true);
    } else {
      $(".specialResults").not(this).attr('disabled', false);
    }
  });

  $("#vlRequestFormRwd .labSection").on("change", function() {
    if ($.trim(result) != '') {
      if ($("#vlRequestFormRwd .labSection").serialize() == $(__clone).serialize()) {
        $(".reasonForResultChanges").css("visibility", "hidden");
        $("#reasonForResultChanges").removeClass("isRequired");
      } else {
        $(".reasonForResultChanges").css("visibility", "visible");
        $("#reasonForResultChanges").addClass("isRequired");
      }
    }
  });

  function checkRejectionReason() {
    var rejectionReason = $("#rejectionReason").val();
    if (rejectionReason == "other") {
      $("#newRejectionReason").show();
      $("#newRejectionReason").addClass("isRequired");
    } else {
      $("#newRejectionReason").hide();
      $("#newRejectionReason").removeClass("isRequired");
      $('#newRejectionReason').val("");
    }
  }

  function calculateLogValue(obj) {
    if (obj.id == "vlResult") {
      absValue = $("#vlResult").val();
      if (absValue != '' && absValue != 0 && !isNaN(absValue)) {
        $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
      } else {
        $("#vlLog").val('');
      }
    }
    if (obj.id == "vlLog") {
      logValue = $("#vlLog").val();
      if (logValue != '' && logValue != 0 && !isNaN(logValue)) {
        var absVal = Math.round(Math.pow(10, logValue) * 100) / 100;
        if (absVal != 'Infinity') {
          $("#vlResult").val(Math.round(Math.pow(10, logValue) * 100) / 100);
        }
      } else {
        $("#vlResult").val('');
      }
    }
  }

  function validateNow() {
    flag = deforayValidator.init({
      formId: 'vlRequestFormRwd'
    });

    $('.isRequired').each(function() {
      ($(this).val() == '') ? $(this).css('background-color', '#FFFF99'): $(this).css('background-color', '#FFFFFF')
    });
    if (flag) {
      // if($('#noResultYes').is(':checked')){
      //   if($("#status").val()!=4){
      //     alert("Status should be Rejected.Because you have chosen Sample Rejection");
      //     return false;
      //   }
      // }
      $.blockUI();
      document.getElementById('vlRequestFormRwd').submit();
    }
  }
</script>