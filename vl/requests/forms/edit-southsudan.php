<?php
ob_start();

//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);
//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);



if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'alphanumeric') {
	$sampleClass = '';
	$maxLength = '';
	if ($arr['max_length'] != '' && $arr['sample_code'] == 'alphanumeric') {
		$maxLength = $arr['max_length'];
		$maxLength = "maxlength=" . $maxLength;
	}
} else {
	$sampleClass = 'checkNum';
	$maxLength = '';
	if ($arr['max_length'] != '') {
		$maxLength = $arr['max_length'];
		$maxLength = "maxlength=" . $maxLength;
	}
}
//check remote user
$pdQuery = "SELECT * FROM province_details";
if ($sarr['user_type'] == 'remoteuser') {
	$sampleCode = 'remote_sample_code';
	//check user exist in user_facility_map table
	$chkUserFcMapQry = "Select user_id from vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
	$chkUserFcMapResult = $db->query($chkUserFcMapQry);
	if ($chkUserFcMapResult) {
		$pdQuery = "SELECT * FROM province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "'";
	}
} else {
	$sampleCode = 'sample_code';
}
$pdResult = $db->query($pdQuery);
$province = '';
$province .= "<option value=''> -- Select -- </option>";
foreach ($pdResult as $provinceName) {
	$province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}

$facility = $general->generateSelectOptions($healthFacilities, $vlQueryInfo['facility_id'], '-- Select --');

//regimen heading
$artRegimenQuery = "SELECT DISTINCT headings FROM r_vl_art_regimen";
$artRegimenResult = $db->rawQuery($artRegimenQuery);
$aQuery = "SELECT * from r_vl_art_regimen where art_status ='active'";
$aResult = $db->query($aQuery);
//facility details
if (isset($vlQueryInfo['facility_id']) && $vlQueryInfo['facility_id'] > 0) {
	$facilityQuery = "SELECT * from facility_details where facility_id='" . $vlQueryInfo['facility_id'] . "' AND status='active'";
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
if (!isset($facilityResult[0]['facility_state'])) {
	$facilityResult[0]['facility_state'] = '';
}
if (!isset($facilityResult[0]['facility_district'])) {
	$facilityResult[0]['facility_district'] = '';
}
if (trim($facilityResult[0]['facility_state']) != '') {
	$stateQuery = "SELECT * FROM province_details where province_name='" . $facilityResult[0]['facility_state'] . "'";
	$stateResult = $db->query($stateQuery);
}
if (!isset($stateResult[0]['province_code'])) {
	$stateResult[0]['province_code'] = '';
}
//district details
$districtResult = array();
if (trim($facilityResult[0]['facility_state']) != '') {
	$districtQuery = "SELECT DISTINCT facility_district from facility_details where facility_state='" . $facilityResult[0]['facility_state'] . "' AND status='active'";
	$districtResult = $db->query($districtQuery);
}
//set reason for changes history
$rch = '';
$allChange = array();
if (isset($vlQueryInfo['reason_for_vl_result_changes']) && $vlQueryInfo['reason_for_vl_result_changes'] != '' && $vlQueryInfo['reason_for_vl_result_changes'] != null) {
	$allChange = json_decode($vlQueryInfo['reason_for_vl_result_changes'], true);
	if (count($allChange) > 0) {
		$rch .= '<h4>Result Changes History</h4>';
		$rch .= '<table style="width:100%;">';
		$rch .= '<thead><tr style="border-bottom:2px solid #d3d3d3;"><th style="width:20%;">USER</th><th style="width:60%;">MESSAGE</th><th style="width:20%;text-align:center;">DATE</th></tr></thead>';
		$rch .= '<tbody>';
		$allChange = array_reverse($allChange);
		foreach ($allChange as $change) {
			$usrQuery = "SELECT user_name FROM user_details where user_id='" . $change['usr'] . "'";
			$usrResult = $db->rawQuery($usrQuery);
			$name = '';
			if (isset($usrResult[0]['user_name'])) {
				$name = ucwords($usrResult[0]['user_name']);
			}
			$expStr = explode(" ", $change['dtime']);
			$changedDate = $general->humanDateFormat($expStr[0]) . " " . $expStr[1];
			$rch .= '<tr><td>' . $name . '</td><td>' . ucfirst($change['msg']) . '</td><td style="text-align:center;">' . $changedDate . '</td></tr>';
		}
		$rch .= '</tbody>';
		$rch .= '</table>';
	}
}

//var_dump($vlQueryInfo['sample_received_at_hub_datetime']);die;

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

	#sampleCode {
		background-color: #fff;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM </h1>
		<ol class="breadcrumb">
			<li><a href="/dashboard/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Edit Vl Request</li>
		</ol>
	</section>
	<?php
	//print_r(array_column($vlTestReasonResult, 'last_name')$oneDimensionalArray = array_map('current', $vlTestReasonResult));die;
	?>
	<!-- Main content -->
	<section class="content">

		<div class="box box-default">
			<div class="box-header with-border">
				<div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
			</div>
			<div class="box-body">
				<!-- form start -->
				<form class="form-inline" method="post" name="vlRequestFormRwd" id="vlRequestFormRwd" autocomplete="off" action="editVlRequestHelper.php">
					<div class="box-body">
						<div class="box box-primary">
							<div class="box-header with-border">
								<h3 class="box-title">Clinic Information: (To be filled by requesting Clinican/Nurse)</h3>
							</div>
							<div class="box-body">
								<div class="row">
									<div class="col-xs-4 col-md-4">
										<div class="form-group">
											<label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
											<input type="text" class="form-control isRequired <?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" <?php echo $maxLength; ?> placeholder="Enter Sample ID" readonly="readonly" title="Please enter sample id" value="<?php echo ($sCode != '') ? $sCode : $vlQueryInfo[$sampleCode]; ?>" style="width:100%;" onchange="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode; ?>',this.id,'<?php echo "vl_sample_id##" . $vlQueryInfo["vl_sample_id"]; ?>','This sample number already exists.Try another number',null)" />
											<input type="hidden" name="sampleCodeCol" value="<?php echo $vlQueryInfo['sample_code']; ?>" />
										</div>
									</div>
									<div class="col-xs-4 col-md-4">
										<div class="form-group">
											<label for="sampleReordered">
												<input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" <?php echo (trim($vlQueryInfo['sample_reordered']) == 'yes') ? 'checked="checked"' : '' ?> title="Please check sample reordered"> Sample Reordered
											</label>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-4 col-md-4">
										<div class="form-group">
											<label for="province">State/Province <span class="mandatory">*</span></label>
											<select class="form-control isRequired" name="province" id="province" title="Please choose state" style="width:100%;" onchange="getProvinceDistricts(this);">
												<?php echo $province; ?>
											</select>
										</div>
									</div>
									<div class="col-xs-4 col-md-4">
										<div class="form-group">
											<label for="district">District/County <span class="mandatory">*</span></label>
											<select class="form-control isRequired" name="district" id="district" title="Please choose county" style="width:100%;" onchange="getFacilities(this);">
												<option value=""> -- Select -- </option>
											</select>
										</div>
									</div>
									<div class="col-xs-4 col-md-4">
										<div class="form-group">
											<label for="fName">Clinic/Health Center <span class="mandatory">*</span></label>
											<select class="form-control isRequired" id="fName" name="fName" title="Please select clinic/health center name" style="width:100%;" onchange="fillFacilityDetails(this);">

												<?= $facility; ?>
											</select>
										</div>
									</div>
									<div class="col-xs-3 col-md-3" style="display:none;">
										<div class="form-group">
											<label for="fCode">Clinic/Health Center Code </label>
											<input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Clinic/Health Center Code" title="Please enter clinic/health center code" value="<?php echo $facilityResult[0]['facility_code']; ?>">
										</div>
									</div>
								</div>
								<div class="row facilityDetails" style="display:<?php echo (trim($facilityResult[0]['facility_emails']) != '' || trim($facilityResult[0]['facility_mobile_numbers']) != '' || trim($facilityResult[0]['contact_person']) != '') ? '' : 'none'; ?>;">
									<div class="col-xs-2 col-md-2 femails" style="display:<?php echo (trim($facilityResult[0]['facility_emails']) != '') ? '' : 'none'; ?>;"><strong>Clinic Email(s)</strong></div>
									<div class="col-xs-2 col-md-2 femails facilityEmails" style="display:<?php echo (trim($facilityResult[0]['facility_emails']) != '') ? '' : 'none'; ?>;"><?php echo $facilityResult[0]['facility_emails']; ?></div>
									<div class="col-xs-2 col-md-2 fmobileNumbers" style="display:<?php echo (trim($facilityResult[0]['facility_mobile_numbers']) != '') ? '' : 'none'; ?>;"><strong>Clinic Mobile No.(s)</strong></div>
									<div class="col-xs-2 col-md-2 fmobileNumbers facilityMobileNumbers" style="display:<?php echo (trim($facilityResult[0]['facility_mobile_numbers']) != '') ? '' : 'none'; ?>;"><?php echo $facilityResult[0]['facility_mobile_numbers']; ?></div>
									<div class="col-xs-2 col-md-2 fContactPerson" style="display:<?php echo (trim($facilityResult[0]['contact_person']) != '') ? '' : 'none'; ?>;"><strong>Clinic Contact Person -</strong></div>
									<div class="col-xs-2 col-md-2 fContactPerson facilityContactPerson" style="display:<?php echo (trim($facilityResult[0]['contact_person']) != '') ? '' : 'none'; ?>;"><?php echo ucwords($facilityResult[0]['contact_person']); ?></div>
								</div>


								<div class="row">
									<div class="col-xs-4 col-md-4">
										<div class="form-group">
											<label for="implementingPartner">Implementing Partner</label>
											<select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose implementing partner" style="width:100%;">
												<option value=""> -- Select -- </option>
												<?php
												foreach ($implementingPartnerList as $implementingPartner) {
												?>
													<option value="<?php echo base64_encode($implementingPartner['i_partner_id']); ?>" <?php echo ($implementingPartner['i_partner_id'] == $vlQueryInfo['implementing_partner']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-xs-4 col-md-4">
										<div class="form-group">
											<label for="fundingSource">Funding Source</label>
											<select class="form-control" name="fundingSource" id="fundingSource" title="Please choose implementing partner" style="width:100%;">
												<option value=""> -- Select -- </option>
												<?php
												foreach ($fundingSourceList as $fundingSource) {
												?>
													<option value="<?php echo base64_encode($fundingSource['funding_source_id']); ?>" <?php echo ($fundingSource['funding_source_id'] == $vlQueryInfo['funding_source']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
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
											<input type="text" name="artNo" id="artNo" class="form-control isRequired" placeholder="Enter ART Number" title="Enter art number" value="<?php echo $vlQueryInfo['patient_art_no']; ?>" />
										</div>
									</div>
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<label for="dob">Date of Birth </label>
											<input type="text" name="dob" id="dob" class="form-control date" placeholder="Enter DOB" title="Enter dob" value="<?php echo $vlQueryInfo['patient_dob']; ?>" onchange="getAge();checkARTInitiationDate();" />
										</div>
									</div>
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<label for="ageInYears">If DOB unknown, Age in Years </label>
											<input type="text" name="ageInYears" id="ageInYears" class="form-control checkNum" maxlength="2" placeholder="Age in Year" title="Enter age in years" value="<?php echo $vlQueryInfo['patient_age_in_years']; ?>" />
										</div>
									</div>
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<label for="ageInMonths">If Age
												< 1, Age in Months </label> <input type="text" name="ageInMonths" id="ageInMonths" class="form-control checkNum" maxlength="2" placeholder="Age in Month" title="Enter age in months" value="<?php echo $vlQueryInfo['patient_age_in_months']; ?>" />
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<label for="patientFirstName">Patient Name (First Name, Last Name) </label>
											<input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="Enter Patient Name" title="Enter patient name" value="<?php echo $patientFirstName; ?>" />
										</div>
									</div>
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<label for="gender">Gender</label><br>
											<label class="radio-inline" style="margin-left:0px;">
												<input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo ($vlQueryInfo['patient_gender'] == 'male') ? "checked='checked'" : "" ?>> Male
											</label>
											<label class="radio-inline" style="margin-left:0px;">
												<input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo ($vlQueryInfo['patient_gender'] == 'female') ? "checked='checked'" : "" ?>> Female
											</label>
											<label class="radio-inline" style="margin-left:0px;">
												<input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" <?php echo ($vlQueryInfo['patient_gender'] == 'not_recorded') ? "checked='checked'" : "" ?>>Not Recorded
											</label>
										</div>
									</div>
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<label for="gender">Patient consent to receive SMS?</label><br>
											<label class="radio-inline" style="margin-left:0px;">
												<input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);" <?php echo ($vlQueryInfo['consent_to_receive_sms'] == 'yes') ? "checked='checked'" : "" ?>> Yes
											</label>
											<label class="radio-inline" style="margin-left:0px;">
												<input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);" <?php echo ($vlQueryInfo['consent_to_receive_sms'] == 'no') ? "checked='checked'" : "" ?>> No
											</label>
										</div>
									</div>
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<label for="patientPhoneNumber">Phone Number</label>
											<input type="text" name="patientPhoneNumber" id="patientPhoneNumber" class="form-control checkNum" maxlength="15" placeholder="Enter Phone Number" title="Enter phone number" value="<?php echo $vlQueryInfo['patient_mobile_number']; ?>" />
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
												<input type="text" class="form-control isRequired dateTime" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" value="<?php echo $vlQueryInfo['sample_collection_date']; ?>" onchange="checkSampleReceviedDate();checkSampleTestingDate();">
											</div>
										</div>
										<div class="col-xs-3 col-md-3">
											<div class="form-group">
												<label for="specimenType">Sample Type <span class="mandatory">*</span></label>
												<select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose sample type">
													<option value=""> -- Select -- </option>
													<?php foreach ($sResult as $name) { ?>
														<option value="<?php echo $name['sample_id']; ?>" <?php echo ($vlQueryInfo['sample_type'] == $name['sample_id']) ? "selected='selected'" : "" ?>><?php echo ucwords($name['sample_name']); ?></option>
													<?php } ?>
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
												<input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of Treatment Initiated" title="Date Of treatment initiated" value="<?php echo $vlQueryInfo['treatment_initiated_date']; ?>" style="width:100%;" onchange="checkARTInitiationDate();">
											</div>
										</div>
										<div class="col-xs-3 col-md-3">
											<div class="form-group">
												<label for="artRegimen">Current Regimen</label>
												<select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" style="width:100%;" onchange="checkARTRegimenValue();">
													<option value="">-- Select --</option>
													<?php foreach ($artRegimenResult as $heading) { ?>
														<optgroup label="<?php echo ucwords($heading['headings']); ?>">
															<?php foreach ($aResult as $regimen) {
																if ($heading['headings'] == $regimen['headings']) { ?>
																	<option value="<?php echo $regimen['art_code']; ?>" <?php echo ($vlQueryInfo['current_regimen'] == $regimen['art_code']) ? "selected='selected'" : "" ?>><?php echo $regimen['art_code']; ?></option>
															<?php }
															} ?>
														</optgroup>
													<?php }
													if ($sarr['user_type'] != 'vluser') {  ?>
														<option value="other">Other</option>
													<?php } ?>
												</select>
												<input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" style="width:100%;display:none;margin-top:2px;">
											</div>
										</div>
										<div class="col-xs-3 col-md-3">
											<div class="form-group">
												<label for="">Date of Initiation of Current Regimen </label>
												<input type="text" class="form-control date" style="width:100%;" name="regimenInitiatedOn" id="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" value="<?php echo $vlQueryInfo['date_of_initiation_of_current_regimen']; ?>">
											</div>
										</div>
										<div class="col-xs-3 col-md-3">
											<div class="form-group">
												<label for="arvAdherence">ARV Adherence </label>
												<select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose adherence">
													<option value=""> -- Select -- </option>
													<option value="good" <?php echo ($vlQueryInfo['arv_adherance_percentage'] == 'good') ? "selected='selected'" : "" ?>>Good >= 95%</option>
													<option value="fair" <?php echo ($vlQueryInfo['arv_adherance_percentage'] == 'fair') ? "selected='selected'" : "" ?>>Fair (85-94%)</option>
													<option value="poor" <?php echo ($vlQueryInfo['arv_adherance_percentage'] == 'poor') ? "selected='selected'" : "" ?>>Poor < 85%</option> 
												</select> 
											</div> 
										</div> 
									</div> 
									<div class="row ">
										<div class="col-xs-3 col-md-3 femaleSection" style="display:<?php echo ($vlQueryInfo['patient_gender'] == 'female' || $vlQueryInfo['patient_gender'] == '' || $vlQueryInfo['patient_gender'] == null) ? "" : "none" ?>" ;>
											<div class="form-group">
												<label for="patientPregnant">Is Patient Pregnant? </label><br>
												<label class="radio-inline">
													<input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check one" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'yes') ? "checked='checked'" : "" ?>> Yes
												</label>
												<label class="radio-inline">
													<input type="radio" class="" id="pregNo" name="patientPregnant" value="no" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'no') ? "checked='checked'" : "" ?>> No
												</label>
											</div>
										</div>
										<div class="col-xs-3 col-md-3 femaleSection" style="display:<?php echo ($vlQueryInfo['patient_gender'] == 'female' || $vlQueryInfo['patient_gender'] == '' || $vlQueryInfo['patient_gender'] == null) ? "" : "none" ?>" ;>
											<div class="form-group">
												<label for="breastfeeding">Is Patient Breastfeeding? </label><br>
												<label class="radio-inline">
													<input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check one" <?php echo ($vlQueryInfo['is_patient_breastfeeding'] == 'yes') ? "checked='checked'" : "" ?>> Yes
												</label>
												<label class="radio-inline">
													<input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" <?php echo ($vlQueryInfo['is_patient_breastfeeding'] == 'no') ? "checked='checked'" : "" ?>> No
												</label>
											</div>
										</div>
										<div class="col-xs-3 col-md-3" style="display:none;">
											<div class="form-group">
												<label for="">How long has this patient been on treatment ? </label>
												<input type="text" class="form-control" id="treatPeriod" name="treatPeriod" placeholder="Enter Treatment Period" title="Please enter how long has this patient been on treatment" value="<?php echo $vlQueryInfo['treatment_initiation']; ?>" />
											</div>
										</div>
									</div>
								</div>
								<div class="box box-primary">
									<div class="box-header with-border">
										<h3 class="box-title">Indication for Viral Load Testing</h3><small> (Please tick one):(To be completed by clinician)</small>
									</div>
									<div class="box-body">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<div class="col-lg-12">
														<label class="radio-inline">
															<?php
															$vlTestReasonQueryRow = "SELECT * from r_vl_test_reasons where test_reason_id='" . trim($vlQueryInfo['reason_for_vl_testing']) . "' OR test_reason_name = '" . trim($vlQueryInfo['reason_for_vl_testing']) . "'";
															$vlTestReasonResultRow = $db->query($vlTestReasonQueryRow);
															$checked = '';
															$display = '';
															if (trim($vlQueryInfo['reason_for_vl_testing']) == 'routine' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'routine') {
																$checked = 'checked="checked"';
																$display = 'block';
															} else {
																$checked = '';
																$display = 'none';
															}
															?>
															<input type="radio" class="" id="rmTesting" name="stViralTesting" value="routine" title="Please check routine monitoring" <?php echo $checked; ?> onclick="showTesting('rmTesting');">
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
													<input type="text" class="form-control date viralTestData" id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo (trim($vlQueryInfo['last_vl_date_routine']) != '' && $vlQueryInfo['last_vl_date_routine'] != null && $vlQueryInfo['last_vl_date_routine'] != '0000-00-00') ? $general->humanDateFormat($vlQueryInfo['last_vl_date_routine']) : ''; ?>" />
												</div>
											</div>
											<div class="col-md-6">
												<label for="rmTestingVlValue" class="col-lg-3 control-label">VL Value</label>
												<div class="col-lg-7">
													<input type="text" class="form-control checkNum viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlQueryInfo['last_vl_result_routine']; ?>" />
													(copies/ml)
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
															if (trim($vlQueryInfo['reason_for_vl_testing']) == 'failure' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'failure') {
																$checked = 'checked="checked"';
																$display = 'block';
															} else {
																$checked = '';
																$display = 'none';
															}
															?>
															<input type="radio" class="" id="repeatTesting" name="stViralTesting" value="failure" title="Repeat VL test after suspected treatment failure adherence counseling" <?php echo $checked; ?> onclick="showTesting('repeatTesting');">
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
													<input type="text" class="form-control date viralTestData" id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo (trim($vlQueryInfo['last_vl_date_failure_ac']) != '' && $vlQueryInfo['last_vl_date_failure_ac'] != null && $vlQueryInfo['last_vl_date_failure_ac'] != '0000-00-00') ? $general->humanDateFormat($vlQueryInfo['last_vl_date_failure_ac']) : ''; ?>" />
												</div>
											</div>
											<div class="col-md-6">
												<label for="repeatTestingVlValue" class="col-lg-3 control-label">VL Value</label>
												<div class="col-lg-7">
													<input type="text" class="form-control checkNum viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlQueryInfo['last_vl_result_failure_ac']; ?>" />
													(copies/ml)
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
															if (trim($vlQueryInfo['reason_for_vl_testing']) == 'suspect' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'suspect') {
																$checked = 'checked="checked"';
																$display = 'block';
															} else {
																$checked = '';
																$display = 'none';
															}
															?>
															<input type="radio" class="" id="suspendTreatment" name="stViralTesting" value="suspect" title="Suspect Treatment Failure" <?php echo $checked; ?> onclick="showTesting('suspendTreatment');">
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
													<input type="text" class="form-control date viralTestData" id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo (trim($vlQueryInfo['last_vl_date_failure']) != '' && $vlQueryInfo['last_vl_date_failure'] != null && $vlQueryInfo['last_vl_date_failure'] != '0000-00-00') ? $general->humanDateFormat($vlQueryInfo['last_vl_date_failure']) : ''; ?>" />
												</div>
											</div>
											<div class="col-md-6">
												<label for="suspendTreatmentVlValue" class="col-lg-3 control-label">VL Value</label>
												<div class="col-lg-7">
													<input type="text" class="form-control checkNum viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlQueryInfo['last_vl_result_failure']; ?>" />
													(copies/ml)
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4">
												<label for="reqClinician" class="col-lg-5 control-label">Request Clinician</label>
												<div class="col-lg-7">
													<input type="text" class="form-control" id="reqClinician" name="reqClinician" placeholder="Request Clinician" title="Please enter request clinician" value="<?php echo $vlQueryInfo['request_clinician_name']; ?>" />
												</div>
											</div>
											<div class="col-md-4">
												<label for="reqClinicianPhoneNumber" class="col-lg-5 control-label">Phone Number</label>
												<div class="col-lg-7">
													<input type="text" class="form-control checkNum" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter request clinician phone number" value="<?php echo $vlQueryInfo['request_clinician_phone_number']; ?>" />
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="requestDate">Request Date </label>
												<div class="col-lg-7">
													<input type="text" class="form-control date" id="requestDate" name="requestDate" placeholder="Request Date" title="Please select request date" value="<?php echo $vlQueryInfo['test_requested_on']; ?>" />
												</div>
											</div>
										</div>
										<div class="row" style="display:none;">

											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="emailHf">Email for HF </label>
												<div class="col-lg-7">
													<input type="text" class="form-control isEmail" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" value="<?php echo $facilityResult[0]['facility_emails']; ?>" />
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="box box-primary" style="<?php if ($sarr['user_type'] == 'remoteuser') { ?> pointer-events:none;<?php } ?>">
									<div class="box-header with-border">
										<h3 class="box-title">Laboratory Information</h3>
									</div>
									<div class="box-body">
										<div class="row">
											<div class="col-md-4">
												<label for="labId" class="col-lg-5 control-label">Lab Name </label>
												<div class="col-lg-7">
													<select name="labId" id="labId" class="form-control labSection" title="Please choose lab" onchange="autoFillFocalDetails();">
														<?= $general->generateSelectOptions($testingLabs, $vlQueryInfo['lab_id'], '-- Select --'); ?>
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<label for="vlFocalPerson" class="col-lg-5 control-label">VL Focal Person </label>
												<div class="col-lg-7">
													<input type="text" class="form-control labSection" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter vl focal person name" value="<?php echo $vlQueryInfo['vl_focal_person']; ?>" />
												</div>
											</div>
											<div class="col-md-4">
												<label for="vlFocalPersonPhoneNumber" class="col-lg-5 control-label">VL Focal Person Phone Number</label>
												<div class="col-lg-7">
													<input type="text" class="form-control checkNum labSection" id="vlFocalPersonPhoneNumber" name="vlFocalPersonPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter vl focal person phone number" value="<?php echo $vlQueryInfo['vl_focal_person_phone_number']; ?>" />
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="sampleReceivedAtHubOn">Date Sample Received at Hub (PHL) </label>
												<div class="col-lg-7">
													<input type="text" class="form-control dateTime" id="sampleReceivedAtHubOn" name="sampleReceivedAtHubOn" placeholder="Sample Received at HUB Date" title="Please select sample received at HUB date" value="<?php echo $vlQueryInfo['sample_received_at_hub_datetime']; ?>" onchange="checkSampleReceviedAtHubDate()" />
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="sampleReceivedDate">Date Sample Received at Testing Lab </label>
												<div class="col-lg-7">
													<input type="text" class="form-control labSection dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="Sample Received Date" title="Please select sample received date" value="<?php echo $vlQueryInfo['sample_received_at_vl_lab_datetime']; ?>" onchange="checkSampleReceviedDate()" />
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="sampleTestingDateAtLab">Sample Testing Date </label>
												<div class="col-lg-7">
													<input type="text" class="form-control labSection dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Sample Testing Date" title="Please select sample testing date" value="<?php echo $vlQueryInfo['sample_tested_datetime']; ?>" onchange="checkSampleTestingDate();" />
												</div>
											</div>

										</div>
										<div class="row">
											<br>
											<div class="col-md-4">
												<label for="testingPlatform" class="col-lg-5 control-label">VL Testing Platform </label>
												<div class="col-lg-7">
													<select name="testingPlatform" id="testingPlatform" class="form-control labSection" title="Please choose VL Testing Platform">
														<option value="">-- Select --</option>
														<?php foreach ($importResult as $mName) { ?>
															<option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit']; ?>" <?php echo ($vlQueryInfo['vl_test_platform'] == $mName['machine_name']) ? 'selected="selected"' : ''; ?>><?php echo $mName['machine_name']; ?></option>
														<?php } ?>
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="noResult">Sample Rejection </label>
												<div class="col-lg-7">
													<label class="radio-inline">
														<input class="labSection" id="noResultYes" name="noResult" value="yes" title="Please check one" type="radio" <?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? 'checked="checked"' : ''; ?>> Yes
													</label>
													<label class="radio-inline">
														<input class="labSection" id="noResultNo" name="noResult" value="no" title="Please check one" type="radio" <?php echo ($vlQueryInfo['is_sample_rejected'] == 'no') ? 'checked="checked"' : ''; ?>> No
													</label>
												</div>
											</div>
											<div class="col-md-4 rejectionReason" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? '' : 'none'; ?>;">
												<label class="col-lg-5 control-label" for="rejectionReason">Rejection Reason<span class="mandatory">*</span> </label>
												<div class="col-lg-7">
													<select name="rejectionReason" id="rejectionReason" class="form-control labSection" title="Please choose reason" onchange="checkRejectionReason();">
														<option value="">-- Select --</option>
														<?php foreach ($rejectionTypeResult as $type) { ?>
															<optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
																<?php
																foreach ($rejectionResult as $reject) {
																	if ($type['rejection_type'] == $reject['rejection_type']) { ?>
																		<option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($vlQueryInfo['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($reject['rejection_reason_name']); ?></option>
																<?php }
																} ?>
															</optgroup>
														<?php }
														if ($sarr['user_type'] != 'vluser') {  ?>
															<option value="other">Other (Please Specify) </option>
														<?php } ?>
													</select>
													<input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" style="width:100%;display:none;margin-top:2px;">
												</div>
											</div>
											<div class="col-md-4 vlResult" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? 'none' : 'block'; ?>;">
												<label class="col-lg-5 control-label" for="vlResult">Viral Load Result (copiesl/ml) </label>
												<div class="col-lg-7">
													<input type="text" class="form-control labSection" id="vlResult" name="vlResult" placeholder="Viral Load Result" title="Please enter viral load result" value="<?php echo $vlQueryInfo['result_value_absolute']; ?>" <?php echo ($vlQueryInfo['result'] == 'Target Not Detected' || $vlQueryInfo['result'] == 'Below Detection Level') ? 'readonly="readonly"' : ''; ?> style="width:100%;" onchange="calculateLogValue(this);" />
													<input type="checkbox" class="labSection" id="tnd" name="tnd" value="yes" <?php echo ($vlQueryInfo['result'] == 'Target Not Detected') ? 'checked="checked"' : '';
																																echo ($vlQueryInfo['result'] == 'Below Detection Level') ? 'disabled="disabled"' : '' ?> title="Please check tnd"> Target Not Detected<br>
													<input type="checkbox" class="labSection" id="bdl" name="bdl" value="yes" <?php echo ($vlQueryInfo['result'] == 'Below Detection Level') ? 'checked="checked"' : '';
																																echo ($vlQueryInfo['result'] == 'Target Not Detected') ? 'disabled="disabled"' : '' ?> title="Please check bdl"> Below Detection Level
												</div>
											</div>
										</div>
										<div class="row">
											<br>
											<div class="col-md-4 vlLog" style="visibility:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? 'hidden' : 'visible'; ?>;">
												<label class="col-lg-5 control-label" for="vlLog">Viral Load Log </label>
												<div class="col-lg-7">
													<input type="text" class="form-control labSection" id="vlLog" name="vlLog" placeholder="Viral Load Log" title="Please enter viral load log" value="<?php echo $vlQueryInfo['result_value_log']; ?>" <?php echo ($vlQueryInfo['result'] == 'Target Not Detected' || $vlQueryInfo['result'] == 'Below Detection Level') ? 'readonly="readonly"' : ''; ?> style="width:100%;" onchange="calculateLogValue(this);" />
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="resultDispatchedOn">Date Results Dispatched </label>
												<div class="col-lg-7">
													<input type="text" class="form-control labSection dateTime" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatched Date" title="Please select result dispatched date" value="<?php echo $vlQueryInfo['result_dispatched_datetime']; ?>" />
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="testedBy">Tested By </label>
												<div class="col-lg-7">
													<select name="testedBy" id="testedBy" class="select2 form-control" title="Please choose approved by">
														<?= $general->generateSelectOptions($userInfo, $vlQueryInfo['tested_by'], '-- Select --'); ?>
													</select>
												</div>
											</div>
											<?php
											$styleStatus = '';
											if ((($sarr['user_type'] == 'remoteuser') && $vlQueryInfo['result_status'] == 9) || ($sCode != '')) {
												$styleStatus = "display:none";
											?>
												<input type="hidden" name="status" value="<?php echo $vlQueryInfo['result_status']; ?>" />
											<?php
											}
											?>

										</div>
										<div class="row">
											<br>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="approvedBy">Approved By </label>
												<div class="col-lg-7">
													<select name="approvedBy" id="approvedBy" class="form-control labSection" title="Please choose approved by">
														<?= $general->generateSelectOptions($userInfo, $vlQueryInfo['result_approved_by'], '-- Select --'); ?>
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-lg-5 control-label" for="approvedOnDateTime">Approved On </label>
												<div class="col-lg-7">
													<input type="text" value="<?php echo $vlQueryInfo['result_approved_datetime']; ?>" class="form-control dateTime" id="approvedOnDateTime" name="approvedOnDateTime" placeholder="e.g 09-Jan-1992 05:30" <?php echo $labFieldDisabled; ?> style="width:100%;" />
												</div>
											</div>
											<div class="col-md-4" style="<?php echo $styleStatus; ?>">
												<label class="col-lg-5 control-label" for="status">Status <span class="mandatory">*</span></label>
												<div class="col-lg-7">
													<select class="form-control labSection <?php echo ($sarr['user_type'] != 'remoteuser' && $sCode == '') ? 'isRequired' : ''; ?>" id="status" name="status" title="Please select test status">
														<option value="">-- Select --</option>
														<?php foreach ($statusResult as $status) { ?>
															<option value="<?php echo $status['status_id']; ?>" <?php echo ($vlQueryInfo['result_status'] == $status['status_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($status['status_name']); ?></option>
														<?php } ?>
													</select>
												</div>
											</div>

										</div>
										<br>
										<br>
										<div class="row">
											<div class="col-md-6">
												<label class="col-lg-5 control-label" for="labComments">Lab Tech. Comments </label>
												<div class="col-lg-7">
													<textarea class="form-control labSection" name="labComments" id="labComments" placeholder="Lab comments" style="width:100%"><?php echo trim($vlQueryInfo['approver_comments']); ?></textarea>
												</div>
											</div>
											<div class="col-md-6 reasonForResultChanges" style="display:none;">
												<label class="col-lg-2 control-label" for="reasonForResultChanges">Reason For Changes in Result<span class="mandatory">*</span></label>
												<div class="col-lg-6">
													<textarea class="form-control" name="reasonForResultChanges" id="reasonForResultChanges" placeholder="Enter Reason For Result Changes" title="Please enter reason for result changes" style="width:100%;"></textarea>
												</div>
											</div>
										</div>
										<?php if (count($allChange) > 0) { ?>
											<div class="row">
												<div class="col-md-12"><?php echo $rch; ?></div>
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
						<div class="box-footer">
							<input type="hidden" name="vlSampleId" id="vlSampleId" value="<?php echo $vlQueryInfo['vl_sample_id']; ?>" />
							<input type="hidden" name="isRemoteSample" value="<?php echo $vlQueryInfo['remote_sample']; ?>" />
							<input type="hidden" name="reasonForResultChangesHistory" id="reasonForResultChangesHistory" value="<?php echo base64_encode($vlQueryInfo['reason_for_vl_result_changes']); ?>" />
							<input type="hidden" name="oldStatus" value="<?php echo $vlQueryInfo['result_status']; ?>" />
							<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>&nbsp;
							<a href="vlRequest.php" class="btn btn-default"> Cancel</a>
						</div>
				</form>
			</div>
	</section>
</div>
<script>
	provinceName = true;
	facilityName = true;
	$(document).ready(function() {
		$('#testedBy').select2({
			width: '100%',
			placeholder: "Select Tested By"
		});

		$('#approvedBy').select2({
			width: '100%',
			placeholder: "Select Approved By"
		});
		$('#facilityId').select2({
            placeholder: "Select Clinic/Health Center"
        });
        $('#district').select2({
            placeholder: "District"
        });
        $('#province').select2({
            placeholder: "Province"
        });
		//getAge();

		getfacilityProvinceDetails($("#fName").val());

		__clone = $("#vlRequestFormRwd .labSection").clone();
		reason = ($("#reasonForResultChanges").length) ? $("#reasonForResultChanges").val() : '';
		result = ($("#vlResult").length) ? $("#vlResult").val() : '';
		checkPatientReceivesms('<?php echo $vlQueryInfo['consent_to_receive_sms']; ?>');
	});

	function showTesting(chosenClass) {
		$(".viralTestData").val('');
		$(".hideTestData").hide();
		$("." + chosenClass).show();
	}

	function getProvinceDistricts(obj) {
		$.blockUI();
		var cName = $("#fName").val();
		var pName = $("#province").val();
		if (pName != '' && provinceName && facilityName) {
			facilityName = false;
		}
		if ($.trim(pName) != '') {
			//if (provinceName) {
			$.post("/includes/siteInformationDropdownOptions.php", {
					pName: pName,
					testType: 'vl'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#fName").html(details[0]);
						$("#district").html(details[1]);
						$("#fCode").val('');
						$(".facilityDetails").hide();
						$(".facilityEmails").html('');
						$(".facilityMobileNumbers").html('');
						$(".facilityContactPerson").html('');
					}
				});
			//}
		} else if (pName == '' && cName == '') {
			provinceName = true;
			facilityName = true;
			$("#province").html("<?php echo $province; ?>");
			$("#fName").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>");
		}
		$.unblockUI();
	}

	function getFacilities(obj) {
		$.blockUI();
		var dName = $("#district").val();
		var cName = $("#fName").val();
		if (dName != '') {
			$.post("/includes/siteInformationDropdownOptions.php", {
					dName: dName,
					cliName: cName,
					testType: 'vl'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#fName").html(details[0]);
						$("#labId").html(details[1]);
						$(".facilityDetails").hide();
						$(".facilityEmails").html('');
						$(".facilityMobileNumbers").html('');
						$(".facilityContactPerson").html('');
					}
				});
		}
		$.unblockUI();
	}

	function getfacilityProvinceDetails(obj) {
		$.blockUI();
		//check facility name
		var cName = $("#fName").val();
		var pName = $("#province").val();
		if (cName != '' && provinceName && facilityName) {
			provinceName = false;
		}
		if (cName != '' && facilityName) {
			$.post("/includes/siteInformationDropdownOptions.php", {
					cName: cName,
					testType: 'vl'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#province").html(details[0]);
						$("#district").html(details[1]);
						$("#clinicianName").val(details[2]);
					}
				});
		} else if (pName == '' && cName == '') {
			provinceName = true;
			facilityName = true;
			$("#province").html("<?php echo $province; ?>");
			$("#facilityId").html("<?php echo $facility; ?>");
		}
		$.unblockUI();
	}

	function fillFacilityDetails(obj) {
		getfacilityProvinceDetails(obj)
		$("#fCode").val($('#fName').find(':selected').data('code'));
		var femails = $('#fName').find(':selected').data('emails');
		var fmobilenos = $('#fName').find(':selected').data('mobile-nos');
		var fContactPerson = $('#fName').find(':selected').data('contact-person');
		if ($.trim(femails) != '' || $.trim(fmobilenos) != '' || fContactPerson != '') {
			$(".facilityDetails").show();
		} else {
			$(".facilityDetails").hide();
		}
		($.trim(femails) != '') ? $(".femails").show(): $(".femails").hide();
		($.trim(femails) != '') ? $(".facilityEmails").html(femails): $(".facilityEmails").html('');
		($.trim(fmobilenos) != '') ? $(".fmobileNumbers").show(): $(".fmobileNumbers").hide();
		($.trim(fmobilenos) != '') ? $(".facilityMobileNumbers").html(fmobilenos): $(".facilityMobileNumbers").html('');
		($.trim(fContactPerson) != '') ? $(".fContactPerson").show(): $(".fContactPerson").hide();
		($.trim(fContactPerson) != '') ? $(".facilityContactPerson").html(fContactPerson): $(".facilityContactPerson").html('');
	}
	$("input:radio[name=gender]").click(function() {
		if ($(this).val() == 'male' || $(this).val() == 'not_recorded') {
			$('.femaleSection').hide();
			$('input[name="breastfeeding"]').prop('checked', false);
			$('input[name="patientPregnant"]').prop('checked', false);
		} else if ($(this).val() == 'female') {
			$('.femaleSection').show();
		}
	});
	$("input:radio[name=noResult]").click(function() {
		if ($(this).val() == 'yes') {
			$('.rejectionReason').show();
			$('.vlResult').css('display', 'none');
			$("#status").val(4);
			$('#rejectionReason').addClass('isRequired');
			$('#vlResult').removeClass('isRequired');
			$('.vlLog').css('display', 'none');
		} else {
			$('.vlResult').css('display', 'block');
			$('.rejectionReason').hide();
			$('#rejectionReason').removeClass('isRequired');
			$('#vlResult').addClass('isRequired');
			if ($('#tnd').is(':checked')) {
				$('#vlResult').removeClass('isRequired');
			}
			if ($('#bdl').is(':checked')) {
				$('#vlResult').removeClass('isRequired');
			}
			$('#rejectionReason').val('');
			$('.vlLog').css('display', 'block');
			$("#status").val('');
		}
	});
	$('#tnd').change(function() {
		if ($('#tnd').is(':checked')) {
			$('#vlResult,#vlLog').attr('readonly', true);
			$('#bdl').prop('checked', false).attr('disabled', true);
		} else {
			$('#vlResult,#vlLog').attr('readonly', false);
			$('#bdl').attr('disabled', false);
			if ($('#noResultNo').is(':checked')) {
				$('#vlResult').addClass('isRequired');
			}
		}
	});
	$('#bdl').change(function() {
		if ($('#bdl').is(':checked')) {
			$('#vlResult,#vlLog').attr('readonly', true);
			$('#tnd').prop('checked', false).attr('disabled', true);
		} else {
			$('#vlResult,#vlLog').attr('readonly', false);
			$('#tnd').attr('disabled', false);
			if ($('#noResultNo').is(':checked')) {
				$('#vlResult').addClass('isRequired');
			}
		}
	});
	$('#vlResult,#vlLog').on('input', function(e) {
		if (this.value != '') {
			$('#tnd,#bdl').attr('disabled', true);
		} else {
			$('#tnd,#bdl').attr('disabled', false);
		}
	});
	$("#vlRequestFormRwd .labSection").on("change", function() {
		if ($.trim(result) != '') {
			if ($("#vlRequestFormRwd .labSection").serialize() == $(__clone).serialize()) {
				$(".reasonForResultChanges").css("display", "block");
				$("#reasonForResultChanges").removeClass("isRequired");
			} else {
				$(".reasonForResultChanges").css("display", "block");
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

	function validateNow() {
		flag = deforayValidator.init({
			formId: 'vlRequestFormRwd'
		});

		$('.isRequired').each(function() {
			($(this).val() == '') ? $(this).css('background-color', '#FFFF99'): $(this).css('background-color', '#FFFFFF')
		});
		if (flag) {
			$.blockUI();
			document.getElementById('vlRequestFormRwd').submit();
		}
	}

	function checkPatientReceivesms(val) {
		if (val == 'yes') {
			$('#patientPhoneNumber').addClass('isRequired');
		} else {
			$('#patientPhoneNumber').removeClass('isRequired');
		}
	}

	function autoFillFocalDetails() {
		labId = $("#labId").val();
		if ($.trim(labId) != '') {
			$("#vlFocalPerson").val($('#labId option:selected').attr('data-focalperson'));
			$("#vlFocalPersonPhoneNumber").val($('#labId option:selected').attr('data-focalphone'));
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
</script>