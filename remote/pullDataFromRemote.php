<?php
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$globalConfigQuery ="SELECT * from system_config";
$configResult=$db->query($globalConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
if($arr['user_type']=='vluser'){
//sample type sync
$sTypeQuery = "select * from r_sample_type where data_sync=0";
$sTypeResult = $remotedb->query($sTypeQuery);
if($sTypeResult){
    foreach($sTypeResult as $type){
        $sTypeQuery = "select * from r_sample_type where sample_id=".$type['sample_id'];
        $sTypeLocalResult = $db->query($sTypeQuery);
        $sTypeData = array('sample_name'=>$type['sample_name'],'data_sync'=>1);
        $lastId = 0;
        if($sTypeLocalResult){
            $db = $db->where('sample_id',$type['sample_id']);
            $lastId = $db->update('r_sample_type',$sTypeData);
        }else{
            $db->insert('r_sample_type',$sTypeData);
            $lastId = $db->getInsertId();
        }
        if($lastId>0){
            $remotedb = $remotedb->where('sample_id',$type['sample_id']);
            $id = $remotedb->update('r_sample_type',array('data_sync'=>1));
        }
    }
}
//art code sync
//first get last updated time in local database
$artCodeLQuery = "select * from r_art_code_details order by updated_datetime DESC limit 1";
$artCodeLResult = $db->query($artCodeLQuery);
if($artCodeLResult){
    $artCodeQuery = "select * from r_art_code_details where updated_datetime >='".$artCodeLResult[0]['updated_datetime']."'";
    $artCodeResult = $remotedb->query($artCodeQuery);
    if($artCodeResult){
        foreach($artCodeResult as $artCode){
            $artCodeQuery = "select * from r_art_code_details where art_id=".$artCode['art_id'];
            $artCodeLocalResult = $db->query($artCodeQuery);
            $artCodeData = array('art_code'=>$artCode['art_code'],'parent_art'=>$artCode['parent_art'],
                                 'headings'=>$artCode['headings'],'nation_identifier'=>$artCode['nation_identifier'],
                                 'art_status'=>$artCode['art_status'],'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
            $lastId = 0;
            if($artCodeLocalResult){
                $db = $db->where('art_id',$artCode['art_id']);
                $lastId = $db->update('r_art_code_details',$artCodeData);
            }else{
                $db->insert('r_art_code_details',$artCodeData);
                $lastId = $db->getInsertId();
            }
        }
    }
}
//rejection reason sync
$rejectLQuery = "select * from r_sample_rejection_reasons order by updated_datetime DESC limit 1";
$rejectLResult = $db->query($rejectLQuery);
if($rejectLResult){
    $rejectQuery = "select * from r_sample_rejection_reasons where updated_datetime >='".$rejectLResult[0]['updated_datetime']."'";
    $rejectResult = $remotedb->query($rejectQuery);
    if($rejectResult){
        foreach($rejectResult as $reason){
            $rejectQuery = "select * from r_sample_rejection_reasons where rejection_reason_id=".$reason['rejection_reason_id'];
            $rejectLocalResult = $db->query($rejectQuery);
            $rejectResultData = array('rejection_reason_name'=>$reason['rejection_reason_name'],'rejection_type'=>$reason['rejection_type'],
                                 'rejection_reason_status'=>$reason['rejection_reason_status'],'rejection_reason_code'=>$reason['rejection_reason_code'],
                                 'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
            $lastId = 0;
            if($rejectLocalResult){
                $db = $db->where('rejection_reason_id',$reason['rejection_reason_id']);
                $lastId = $db->update('r_sample_rejection_reasons',$rejectResultData);
            }else{
                $db->insert('r_sample_rejection_reasons',$rejectResultData);
                $lastId = $db->getInsertId();
            }
        }
    }
}
//prvince data sync
$provinceLQuery = "select * from province_details order by updated_datetime DESC limit 1";
$provinceLResult = $db->query($provinceLQuery);
if($provinceLResult){
    $provinceQuery = "select * from province_details where updated_datetime >='".$provinceLResult[0]['updated_datetime']."'";
    $provinceResult = $remotedb->query($provinceQuery);
    if($provinceResult){
        foreach($provinceResult as $province){
            $provinceQuery = "select * from province_details where province_id=".$province['province_id'];
            $provinceLocalResult = $db->query($provinceQuery);
            $provinceData = array('province_name'=>$province['province_name'],'province_code'=>$province['province_code'],'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
            $lastId = 0;
            if($provinceLocalResult){
                $db = $db->where('province_id',$province['province_id']);
                $lastId = $db->update('province_details',$provinceData);
            }else{
                $db->insert('province_details',$provinceData);
                $lastId = $db->getInsertId();
            }
        }
    }
}
//facility data sync
$facilityLQuery = "select * from facility_details order by updated_datetime DESC limit 1";
$facilityLResult = $db->query($facilityLQuery);
if($facilityLResult){
    $facilityQuery = "select * from facility_details where updated_datetime >='".$facilityLResult[0]['updated_datetime']."'";
    $facilityResult = $remotedb->query($facilityQuery);
    //vlsm instance id
    $instanceQuery = "select vlsm_instance_id from s_vlsm_instance";
    $instanceResult = $remotedb->query($instanceQuery);
    if($facilityResult){
        foreach($facilityResult as $facility){
            $facilityQuery = "select * from facility_details where facility_id=".$facility['facility_id'];
            $facilityLocalResult = $db->query($facilityQuery);
            $facilityData = array('vlsm_instance_id'=>$instanceResult[0]['vlsm_instance_id'],'facility_name'=>$facility['facility_name'],'facility_code'=>$facility['facility_code'],
                                  'other_id'=>$facility['other_id'],'facility_emails'=>$facility['facility_emails'],
                                  'report_email'=>$facility['report_email'],'contact_person'=>$facility['contact_person'],
                                  'facility_mobile_numbers'=>$facility['facility_mobile_numbers'],'address'=>$facility['address'],
                                  'country'=>$facility['country'],'facility_state'=>$facility['facility_state'],
                                  'facility_district'=>$facility['facility_district'],'facility_hub_name'=>$facility['facility_hub_name'],
                                  'latitude'=>$facility['latitude'],'longitude'=>$facility['longitude'],'facility_type'=>$facility['facility_type'],
                                  'status'=>$facility['status'],'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
            $lastId = 0;
            if($facilityLocalResult){
                $db = $db->where('facility_id',$facility['facility_id']);
                $lastId = $db->update('facility_details',$facilityData);
            }else{
                $db->insert('facility_details',$facilityData);
                $lastId = $db->getInsertId();
            }
        }
    }
    }
}
?>