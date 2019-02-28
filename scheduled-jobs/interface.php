<?php

require __DIR__ . "/../includes/MysqliDb.php";
require __DIR__ . "/../General.php";

$iHOST = '127.0.0.1';
$iUSER = 'root';
$iPASSWORD = 'zaq12345';
$iDBNAME = 'interfacing';
$iPORT = 3306;

$interfacedb = new MysqliDb($iHOST, $iUSER, $iPASSWORD, $iDBNAME, $iPORT);

$general = new General($db);
//get the value from interfacing DB

$interfaceQuery = "SELECT * from orders where result_status = 1 and lims_sync_status=0";

$interfaceInfo = $interfacedb->query($interfaceQuery);

if (count($interfaceInfo) > 0) {
    foreach ($interfaceInfo as $key => $result) {
        $vlQuery = "SELECT vl_sample_id from vl_request_form where sample_code = '" . $result['test_id'] . "'";
        
        $vlInfo = $db->rawQueryOne($vlQuery);

        if (isset($vlInfo['vl_sample_id'])) {
            $absDecimalVal = null;
            $absVal = null;
            $logVal = null;
            $txtVal = null;
            //set result in result fields
            if (trim($result['results']) != "") {

                $vlResult = $result['results'];
                $unit = trim($result['test_unit']);

                if (strpos($unit, '10') !== false) {
                    $unitArray = explode(".", $unit);
                    $exponentArray = explode("*", $unitArray[0]);
                    $multiplier = pow($exponentArray[0], $exponentArray[1]);
                    $vlResult = $vlResult * $multiplier;
                    $unit = $unitArray[1];
                }                

                if (is_numeric($vlResult)) {
                    $absVal = (float) trim($vlResult);
                    $absDecimalVal = (float) trim($vlResult);
                    $logVal = round(log10($absDecimalVal),2);
                } else {
                    if (strpos("<", $vlResult) !== false) {
                        $vlResult = str_replace("<", "", $vlResult);
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal),2);
                        $absVal = "< " . (float) trim($vlResult);
                    } else if (strpos(">", $vlResult) !== false) {
                        $vlResult = str_replace(">", "", $vlResult);
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal),2);
                        $absVal = "> " . (float) trim($vlResult);
                    } else {
                        $txtVal = trim($result['results']);
                    }
                }
            }

            $data = array(
                'result_approved_by' => $result['tested_by'],
                'result_approved_datetime' => $result['authorised_date_time'],
                'sample_tested_datetime' => $result['result_accepted_date_time'],
                'result_value_log' => $logVal,
                'result_value_absolute' => $absVal,
                'result_value_absolute_decimal' => $absDecimalVal,
                'result_value_text' => $txtVal,
                'result' => $vlResult,
                'result_status' => 8
            );

            $db = $db->where('vl_sample_id', $vlInfo['vl_sample_id']);
            $vlUpdateId = $db->update('vl_request_form', $data);
            if ($vlUpdateId) {
                $interfaceData = array(
                    'lims_sync_status' => 1,
                    'lims_sync_date_time' => date('Y-m-d H:i:s'),
                );
                $interfacedb = $interfacedb->where('id', $result['id']);
                $interfaceUpdateId = $interfacedb->update('orders', $interfaceData);
            }
        }
    }
}