<?php
ob_start();
include('../includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/General.php');
$general=new General($db);
$tableName="vl_request_form";
try {
    $id = $_POST['id'];
        $status=array(
            'contact_complete_status'=>$_POST['value']
        );
        $db=$db->where('vl_sample_id',$id);
        $db->update($tableName,$status);
        $result = $id;
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;