<?php
// imported in eid-add-request.php based on country in global config

ob_start();

//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);


// $configQuery = "SELECT * from global_config";
// $configResult = $db->query($configQuery);
// $arr = array();
// $prefix = $arr['sample_code_prefix'];

// Getting the list of Provinces, Districts and Facilities

$rKey = '';
$pdQuery = "SELECT * from province_details";
if ($sarr['user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "Select user_id from vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $chkUserFcMapResult = $db->query($chkUserFcMapQry);
    if ($chkUserFcMapResult) {
        $pdQuery = "SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
    }
    $rKey = 'R';
} else {
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
}
$pdResult = $db->query($pdQuery);
$province = "";
$province .= "<option value=''> -- Sélectionner -- </option>";
foreach ($pdResult as $provinceName) {
    $province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}
//$facility = "";
$facility = "<option value=''> -- Sélectionner -- </option>";
foreach ($fResult as $fDetails) {
    $facility .= "<option value='" . $fDetails['facility_id'] . "'>" . ucwords(addslashes($fDetails['facility_name'])) . "</option>";
}

?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> EARLY INFANT DIAGNOSIS (EID) LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add EID Request</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method="post" name="addEIDRequestForm" id="addEIDRequestForm" autocomplete="off" action="eid-add-request-helper.php">
              <div class="box-body">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="box-header with-border">
                          <h3 class="box-title">A. Réservé à la structure de soins</h3>
                        </div>
                        <div class="box-header with-border">
                            <h3 class="box-title">Information sur la structure de soins</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                              <?php if ($sarr['user_type'] == 'remoteuser') {?>
                                <td><label for="sampleCode">Échantillon ID </label></td>
                                <td>
                                  <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
                                  <input type="hidden" id="sampleCode" name="sampleCode"/>
                                </td>
                              <?php } else {?>
                                <td><label for="sampleCode">Échantillon ID </label><span class="mandatory">*</span></td>
                                <td>
                                  <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Échantillon ID" title="Please enter échantillon id" style="width:100%;" onchange="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode; ?>',this.id,null,'The échantillon id that you entered already exists. Please try another échantillon id',null)"/>
                                </td>
                              <?php }?>
                                <td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                        <?php echo $province; ?>
                                    </select>
                                </td>
                                <td><label for="district">Zone de Santé </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                      <option value=""> -- Sélectionner -- </option>
                                    </select>
                                </td>
                                <td><label for="facilityId">Nom de l'installation </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                      <?php echo $facility; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="supportPartner">Partnaire d'appui </label></td>
                                <td>
                                  <!-- <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire dappui" title="Please enter partenaire dappui" style="width:100%;"/> -->
                                  <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php
foreach ($implementingPartnerList as $implementingPartner) {
    ?>
                                        <option value="<?php echo ($implementingPartner['i_partner_id']); ?>"><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                      <?php }?>
                                   </select>
                                </td>
                                <td><label for="fundingSource">Source de Financement</label></td>
                                <td>
                                    <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose source de financement" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                        <?php
foreach ($fundingSourceList as $fundingSource) {
    ?>
                                        <option value="<?php echo ($fundingSource['funding_source_id']); ?>"><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
                                        <?php }?>
                                    </select>
                                </td>
                                <?php if ($sarr['user_type'] == 'remoteuser') {?>
                              <!-- <tr> -->
                                  <td><label for="labId">Nom du Laboratoire <span class="mandatory">*</span></label> </td>
                                  <td>
                                      <select name="labId" id="labId" class="form-control isRequired" title="Nom du Laboratoire" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach ($lResult as $labName) {?>
                                        <option value="<?php echo $labName['facility_id']; ?>" ><?php echo ucwords($labName['facility_name']); ?></option>
                                        <?php }?>
                                    </select>
                                  </td>
                              <!-- </tr> -->
                            <?php }?>
                            </tr>
                        </table>
                        <br><br>
                        <table class="table" style="width:100%">
                            <tr>
                               <th colspan=8><h4>1. Données démographiques mère / enfant</h4></th>
                            </tr>
                            <tr>
                            <th colspan=8><h5 style="font-weight:bold;font-size:1.1em;">ID de la mère</h5></th>
                            </tr>
                            <tr>
                               <th><label for="mothersId">Code (si applicable) </label></th>
                               <td>
                                    <input type="text" class="form-control " id="mothersId" name="mothersId" placeholder="Code du mère" title="Please enter code du mère" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="mothersName">Nom </label></th>
                               <td>
                                    <input type="text" class="form-control " id="mothersName" name="mothersName" placeholder="Nom du mère" title="Please enter nom du mère" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="mothersDob">Date de naissance </label></th>
                               <td>
                                    <input type="text" class="form-control date" id="mothersDob" name="mothersDob" placeholder="Date de naissance" title="Please enter Date de naissance" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="mothersMaritalStatus">Etat civil </label></th>
                               <td>
                                    <select class="form-control " name="mothersMaritalStatus" id="mothersMaritalStatus">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value='single'> Single </option>
                                    <option value='married'> Married </option>
                                    <option value='cohabitating'> Cohabitating </option>

                                    </select>
                               </td>
                            </tr>

                            <tr>
                                <th colspan=8><h5 style="font-weight:bold;font-size:1.1em;">ID de l'enfant</h5></th>
                            </tr>
                            <tr>
                               <th><label for="childId">Code de l’enfant (Patient) </label></th>
                               <td>
                                    <input type="text" class="form-control " id="childId" name="childId" placeholder="Code (Patient)" title="Please enter code" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="childName">Nom </label></th>
                               <td>
                                    <input type="text" class="form-control " id="childName" name="childName" placeholder="Nom" title="Please enter nom" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="childDob">Date de naissance </label></th>
                               <td>
                                    <input type="text" class="form-control date" id="childDob" name="childDob" placeholder="Date de naissance" title="Please enter Date de naissance" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="childGender">Gender </label></th>
                               <td>
                                <select class="form-control " name="childGender" id="childGender">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value='male'> Male </option>
                                    <option value='female'> Female </option>

                                    </select>
                               </td>
                            </tr>
                            <tr>
                                        <th>Age</th>
                                        <td><input type="number" max=9 maxlength="1" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="childAge" name="childAge" placeholder="Age" title="Age" style="width:100%;"  onchange=""/></td>
                                        <th></th>
                                        <td></td>
                                        <th></th>
                                        <td></td>
                                        <th></th>
                                        <td></td>
                                        <th></th>
                                        <td></td>
                            </tr>

                        </table>



                        <br><br>
                        <table class="table" style="width:100%">
                            <tr>
                               <th colspan=6><h4>2. Management de la mère</h4></th>
                            </tr>
                            <tr>
                              <th colspan=2>ARV donnés à la maman pendant la grossesse:</th>
                              <td colspan=4>
                                  <input type="checkbox" name="motherTreatment[]" value="Nothing" /> Rien <br>
                                  <input type="checkbox" name="motherTreatment[]" value="ARV Initiated during Pregnancy" /> ARV débutés durant la grossesse <br>
                                  <input type="checkbox" name="motherTreatment[]" value="ARV Initiated prior to Pregnancy"  /> ARV débutés avant la grossesse <br>
                                  <input type="checkbox" name="motherTreatment[]" value="ARV at Child Birth"  /> ARV à l’accouchement <br>
                                  <input type="checkbox" name="motherTreatment[]" value="Option B plus"  /> Option B plus <br>
                                  <input type="checkbox" name="motherTreatment[]" value="AZT/3TC/NVP" /> AZT/3TC/NVP <br>
                                  <input type="checkbox" name="motherTreatment[]" value="TDF/3TC/EFV" /> TDF/3TC/EFV <br>
                                  <input type="checkbox" name="motherTreatment[]" value="Other" onclick="$('#motherTreatmentOther').prop('disabled', function(i, v) { return !v; });"  /> Autres (à préciser): <input class="form-control" style="max-width:200px;display:inline;" disabled="disabled" placeholder="Autres" type="text" name="motherTreatmentOther" id="motherTreatmentOther" /> <br>
                                  <input type="checkbox" name="motherTreatment[]"  value="Unknown" /> Inconnu
                              </td>
                            </tr>
                            <tr>
                              <th style="vertical-align:middle;">CD4</th>
                              <td style="vertical-align:middle;">
                                <div class="input-group">
                                  <input type="text" class="form-control " id="mothercd4" name="mothercd4" placeholder="CD4" title="CD4" style="width:100%;"  onchange=""/>
                                  <div class="input-group-addon">/mm3</div>
                                </div>
                              </td>
                              <th style="vertical-align:middle;">Viral Load</th>
                              <td style="vertical-align:middle;">
                                <div class="input-group">
                                  <input type="number" class="form-control " id="motherViralLoadCopiesPerMl" name="motherViralLoadCopiesPerMl" placeholder="Viral Load in copies/mL" title="Viral Load" style="width:100%;"  onchange=""/>
                                  <div class="input-group-addon">copies/mL</div>
                                </div>
                              </td>
                              <td style="vertical-align:middle;">- OR -</td>
                              <td style="vertical-align:middle;">
                              <select class="form-control " name="motherViralLoadText" id="motherViralLoadText" onchange="updateMotherViralLoad()">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value='tnd'> Target Not Detected </option>
                                    <option value='bdl'> Below Detection Limit </option>
                                    <option value='< 20'> < 20 </option>
                                    <option value='< 40'> < 40 </option>
                                    <option value='invalid'> Invalid </option>

                                    </select>
                              </td>
                            </tr>

                        </table>






                        <br><br>
                        <table class="table" style="width:70%">
                            <tr>
                               <th colspan=2><h4>3. Mangement de l’enfant</h4></th>
                            </tr>
                            <tr>
                              <th>Bébé a reçu:<br>(Cocher tout ce qui est reçu, Rien, ou inconnu)</th>
                              <td>
                                        <input type="checkbox" name="childTreatment" value="Nothing" />&nbsp;Rien &nbsp; &nbsp;&nbsp;&nbsp;
                                        <input type="checkbox" name="childTreatment" value="AZT"  />&nbsp;AZT &nbsp; &nbsp;&nbsp;&nbsp;
                                        <input type="checkbox" name="childTreatment" value="NVP"  />&nbsp;NVP &nbsp; &nbsp;&nbsp;&nbsp;
                                        <input type="checkbox" name="childTreatment" value="Unknown"  />&nbsp;Inconnu &nbsp; &nbsp;&nbsp;&nbsp;
                              </td>
                            </tr>
                            <tr>
                              <th>Bébé a arrêté allaitement maternel ?</th>
                              <td>
                                  <select class="form-control" name="hasInfantStoppedBreastfeeding" id="hasInfantStoppedBreastfeeding">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value="yes"> Oui </option>
                                    <option value="no" /> Non </option>
                                    <option value="unknown" /> Inconnu </option>
                                  </select>
                              </td>
                            </tr>
                            <tr>
                              <th>Age (mois) arrêt allaitement :</th>
                              <td colspan="4">
                              <input type="number" class="form-control" style="max-width:200px;display:inline;" placeholder="Age (mois) arrêt allaitement" type="text" name="ageBreastfeedingStopped" id="ageBreastfeedingStopped" />
                              </td>
                            </tr>
                            <!-- <tr>
                              <th>Bébé encore allaité?</th>
                              <td>
                                  <select class="form-control" name="isInfantStillBeingBreastfed" id="isInfantStillBeingBreastfed">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value="yes"> Oui </option>
                                    <option value="no" /> Non </option>
                                    <option value="unknown" /> Inconnu </option>
                                  </select>
                              </td>
                            </tr> -->
                            <tr>
                              <th>Choix d’allaitement de bébé :</th>
                              <td>
                                        <select class="form-control" name="choiceOfFeeding" id="choiceOfFeeding">
                                          <option value=''> -- Sélectionner -- </option>
                                          <option value="Breastfeeding only"> Allaitement seul </option>
                                          <option value="Milk substitute" /> Substitut de lait </option>
                                          <option value="Combination" /> Mixte </option>
                                          <option value="Other" /> Autre </option>
                                        </select>
                              </td>
                            </tr>
                            <tr>
                              <th>Cotrimoxazole donné au bébé?</th>
                              <td>
                                      <select class="form-control" name="isCotrimoxazoleBeingAdministered" id="choiceOfFeeding">
                                            <option value=''> -- Sélectionner -- </option>
                                            <option value="no"> Non </option>
                                            <option value="Yes, takes CTX everyday" /> Oui, prend CTX chaque jour </option>
                                            <option value="Starting on CTX today" /> Commence CTX aujourd’hui </option>
                                          </select>

                              </td>
                            </tr>
                        </table>






                        <br><br>
                        <table class="table" style="width:70%">
                            <tr>
                               <th colspan=2><h4>4. Information sur l’échantillon</h4></th>
                            </tr>
                            <tr>
                              <th>Date de collecte <span class="mandatory">*</span> </th>
                              <td>
                                  <input class="form-control dateTime isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Date de collecte" onchange="sampleCodeGeneration();"/>
                              </td>
                            </tr>
                            <tr>
                              <th>Tel. du préleveur</th>
                              <td>
                                  <input class="form-control" type="text" name="sampleRequestorPhone" id="sampleRequestorPhone" placeholder="Tel. du préleveur" />
                              </td>
                            </tr>
                            </tr>
                            <tr>
                              <th>Nom du demandeur</th>
                              <td>
                                  <input class="form-control" type="text" name="sampleRequestorName" id="sampleRequestorName" placeholder="Nom du demandeur" />
                              </td>
                            </tr>
                            <tr>
                              <th>Raison de la PCR (cocher une):</th>
                              <td>
                                  <select class="form-control" name="reasonForPCR" id="reasonForPCR">
                                        <option value=''> -- Sélectionner -- </option>
                                        <option value="Nothing"> Rien</option>
                                        <option value="First Test for exposed baby"> 1st test pour bébé exposé</option>
                                        <option value="First test for sick baby"> 1st test pour bébé malade</option>
                                        <option value="Repeat due to problem with first test"> Répéter car problème avec 1er test</option>
                                        <option value="Repeat to confirm the first result"> Répéter pour confirmer 1er résultat</option>
                                        <option value="Repeat test once breastfeeding is stopped"> Répéter test après arrêt allaitement maternel (6 semaines au moins après arrêt allaitement)</option>
                                    </select>
                              </td>
                            </tr>
                            <tr>
                                        <th colspan=2><strong>Pour enfant de 9 mois ou plus</strong></th>
                            </tr>
                            <tr>
                              <th>Test rapide effectué?</th>
                              <td>
                                  <select class="form-control" name="rapidTestPerformed" id="rapidTestPerformed">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value="yes"> Oui </option>
                                    <option value="no" /> Non </option>
                                  </select>
                              </td>
                            </tr>
                            <tr>
                              <th>Si oui, date :</th>
                              <td>
                                <input class="form-control" type="text" name="rapidtestDate" id="rapidtestDate" placeholder="Si oui, date" />
                              </td>
                            </tr>
                            <tr>
                              <th>Résultat test rapide</th>
                              <td>
                              <select class="form-control" name="rapidTestResult" id="rapidTestResult">
                                <option value=''> -- Sélectionner -- </option>
                                <option value="positive"> Positif </option>
                                <option value="negative" /> Négatif </option>
                                <option value="indeterminate" /> Indéterminé </option>
                              </select>
                              </td>
                            </tr>
                        </table>


                    </div>
                </div>
                <?php if ($sarr['user_type'] != 'remoteuser') {?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title">B. Réservé au laboratoire d’analyse </h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <th><label for="">Date de réception de l'échantillon </label></th>
                                <td>
                                    <input type="text" class="form-control dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réception de léchantillon" <?php echo $labFieldDisabled; ?> onchange="" style="width:100%;"/>
                                </td>
                                <td></td>
                              <td></td>
                            <tr>
                              <th>Is Sample Rejected ?</th>
                              <td>
                              <select class="form-control" name="isSampleRejected" id="isSampleRejected">
                                <option value=''> -- Sélectionner -- </option>
                                <option value="yes"> Oui </option>
                                <option value="no" /> Non </option>
                              </select>
                              </td>

                              <th>Reason for Rejection</th>
                              <td>
                              <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason">
                                <option value=''> -- Sélectionner -- </option>
                                <option value="Technical Problem"> Problème technique </option>
                                <option value="Poor numbering" /> Mauvaise numérotation </option>
                                <option value="Insufficient sample" /> Echantillon insuffisant </option>
                                <option value="Degraded sample or clot" /> Echantillon dégradé ou caillot </option>
                                <option value="Poor packaging" /> Mauvais empaquetage </option>
                              </select>
                              </td>
                              </tr>
                            <tr>
                                <td style="width:25%;"><label for="">Test effectué le </label></td>
                                <td style="width:25%;">
                                    <input type="text" class="form-control dateTime" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="e.g 09-Jan-1992 05:30" title="Test effectué le" <?php echo $labFieldDisabled; ?> onchange="" style="width:100%;"/>
                                </td>


                              <th>Résultat</th>
                              <td>
                              <select class="form-control" name="result" id="result">
                                <option value=''> -- Sélectionner -- </option>
                                <option value="positive"> Positif </option>
                                <option value="negative" /> Négatif </option>
                                <option value="indeterminate" /> Indéterminé </option>
                              </select>
                              </td>
                            </tr>

                        </table>
                    </div>
                </div>
                <?php }?>

              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') {?>
                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>"/>
                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>"/>
                <?php }?>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="formId" id="formId" value="3"/>
                <input type="hidden" name="eidSampleId" id="eidSampleId" value=""/>
                <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code']; ?>"/>
                <a href="/eid/requests/eid-add-request.php" class="btn btn-default"> Cancel</a>
              </div>
              <!-- /.box-footer -->
            </form>
          <!-- /.row -->
        </div>
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>



<script type="text/javascript">
  changeProvince = true;
  changeFacility = true;
  provinceName = true;
  facilityName = true;
  machineName = true;
  function getfacilityDetails(obj){
    $.blockUI();
    var cName = $("#facilityId").val();
    var pName = $("#province").val();
    if(pName!='' && provinceName && facilityName){
      facilityName = false;
    }
    if($.trim(pName)!=''){
      if(provinceName){
          $.post("/includes/getFacilityForClinic.php", { pName : pName},
          function(data){
              if(data!= ""){
                details = data.split("###");
                $("#facilityId").html(details[0]);
                $("#district").html(details[1]);
                $("#clinicianName").val(details[2]);
              }
          });
      }
      sampleCodeGeneration();
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province; ?>");
      $("#facilityId").html("<?php echo $facility; ?>");
    }else{
      $("#district").html("<option value=''> -- Sélectionner -- </option>");
    }
    $.unblockUI();
  }

  function sampleCodeGeneration() {
    var pName = $("#province").val();
    var sDate = $("#sampleCollectionDate").val();
    if(pName!='' && sDate!=''){
      $.post("/eid/requests/generateSampleCode.php", { sDate : sDate, pName : pName},
      function(data){
        var sCodeKey = JSON.parse(data);
        <?php if ($arr['sample_code'] == 'auto') { ?>
              pNameVal = pName.split("##");
              sCode = sCodeKey.auto;
              $("#sampleCode").val('<?php echo $rKey; ?>'+pNameVal[1]+sCode+sCodeKey.maxId);
              $("#sampleCodeInText").html('<?php echo $rKey; ?>'+pNameVal[1]+sCode+sCodeKey.maxId);
              $("#sampleCodeFormat").val('<?php echo $rKey; ?>'+pNameVal[1]+sCode);
              $("#sampleCodeKey").val(sCodeKey.maxId);
              //checkSampleNameValidation('eid_form','<?php echo $sampleCode; ?>','sampleCode',null,'The sample number that you entered already exists. Please try another number',null);
          <?php } else if ($arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') {?>
              $("#sampleCode").val('<?php echo $rKey . $arr['sample_code_prefix']; ?>'+sCodeKey.mnthYr+sCodeKey.maxId);
              $("#sampleCodeInText").html('<?php echo $rKey . $arr['sample_code_prefix']; ?>'+sCodeKey.mnthYr+sCodeKey.maxId);
              //$("#sampleCodeValue").html('exemple de code:'+'<?php echo $rKey . $arr['sample_code_prefix']; ?>'+sCodeKey.mnthYr+sCodeKey.maxId).css('display','block');
              $("#sampleCodeFormat").val('<?php echo $rKey . $arr['sample_code_prefix']; ?>'+sCodeKey.mnthYr);
              $("#sampleCodeKey").val(sCodeKey.maxId);
              //checkSampleNameValidation('eid_form','<?php echo $sampleCode; ?>','sampleCode',null,'The sample number that you entered already exists. Please try another number',null)
        <?php }?>
      });
    }
  }

  function getfacilityDistrictwise(obj){
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#facilityId").val();
    if(dName!=''){
      $.post("/includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
          if(data != ""){
            details = data.split("###");
            $("#facilityId").html(details[0]);
          }
      });
    }else{
       $("#facilityId").html("<option value=''> -- Sélectionner -- </option>");
    }
    $.unblockUI();
  }
  function getfacilityProvinceDetails(obj){
    $.blockUI();
     //check facility name
      var cName = $("#facilityId").val();
      var pName = $("#province").val();
      if(cName!='' && provinceName && facilityName){
        provinceName = false;
      }
    if(cName!='' && facilityName){
      $.post("/includes/getFacilityForClinic.php", { cName : cName},
      function(data){
          if(data != ""){
            details = data.split("###");
            $("#province").html(details[0]);
            $("#district").html(details[1]);
            $("#clinicianName").val(details[2]);
          }
      });
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province; ?>");
      $("#facilityId").html("<?php echo $facility; ?>");
    }
    $.unblockUI();
  }
  

  function validateNow(){
    flag = deforayValidator.init({
      formId: 'addEIDRequestForm'
    });
    if(flag){
      //$.blockUI();
      <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') {?>
              insertSampleCode('addEIDRequestForm','eidSampleId','sampleCode','sampleCodeKey','sampleCodeFormat',3,'sampleCollectionDate');
      <?php } else {?>
              document.getElementById('addEIDRequestForm').submit();
      <?php }?>
    }
  }

  function updateMotherViralLoad(){
    //var motherVl = $("#motherViralLoadCopiesPerMl").val();
    var motherVlText = $("#motherViralLoadText").val();
    if(motherVlText != ''){
      $("#motherViralLoadCopiesPerMl").val(''); 
    }
  }

  $(document).ready(function(){

    $('#facilityId').select2({placeholder:"Select Clinic/Health Center"});
    $('#district').select2({placeholder:"District"});
    $('#province').select2({placeholder:"Province"});
    $("#motherViralLoadCopiesPerMl").on("change keyup paste", function(){
      var motherVl = $("#motherViralLoadCopiesPerMl").val();
      //var motherVlText = $("#motherViralLoadText").val();
      if(motherVl != ''){
        $("#motherViralLoadText").val(''); 
      }      
    });


  });

  </script>