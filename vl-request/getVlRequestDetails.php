<?php
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$formConfigQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($formConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$primaryKey="vl_sample_id";

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        $aColumns = array('vl.serial_no',"DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')",'b.batch_code','vl.art_no','vl.patient_name','f.facility_name','f.state','f.district','s.sample_name','vl.result','ts.status_name');
        $orderColumns = array('vl.serial_no','vl.sample_collection_date','b.batch_code','vl.art_no','vl.patient_name','f.facility_name','f.state','f.district','s.sample_name','vl.result','ts.status_name');
        
        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = $primaryKey;
        
        $sTable = $tableName;
        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sOffset = $_POST['iDisplayStart'];
            $sLimit = $_POST['iDisplayLength'];
        }
        
        /*
         * Ordering
        */
        
        $sOrder = "";
        if (isset($_POST['iSortCol_0'])) {
            $sOrder = "";
            for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {
                if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
                    $sOrder .= $orderColumns[intval($_POST['iSortCol_' . $i])] . "
				 	" . ( $_POST['sSortDir_' . $i] ) . ", ";
                }
            }
            $sOrder = substr_replace($sOrder, "", -2);
        }
        
        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
        */
        
        $sWhere = "";
        if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
            $searchArray = explode(" ", $_POST['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);
                
                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }
        
        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_POST['bSearchable_' . $i]) && $_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
                }
            }
        }
        
        /*
         * SQL queries
         * Get data to display
        */
	$aWhere = '';
        //$sQuery="SELECT vl.vl_sample_id,vl.facility_id,vl.patient_name,f.facility_name,f.facility_code,art.art_code,s.sample_name FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id  INNER JOIN r_art_code_details as art ON vl.current_regimen=art.art_id INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id";
	$sQuery="SELECT * FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_art_code_details as art ON vl.current_regimen=art.art_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id";
	
        //echo $sQuery;die;
	$start_date = '';
	$end_date = '';
	if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
	   $s_c_date = explode("to", $_POST['sampleCollectionDate']);
	   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
	     $start_date = $general->dateFormat(trim($s_c_date[0]));
	   }
	   if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
	     $end_date = $general->dateFormat(trim($s_c_date[1]));
	   }
	}
	  
	if (isset($sWhere) && $sWhere != "") {
           $sWhere=' where '.$sWhere;
	    //$sQuery = $sQuery.' '.$sWhere;
	    if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
	        $sWhere = $sWhere.' AND b.batch_code LIKE "%'.$_POST['batchCode'].'%"';
	    }
	    if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
		if (trim($start_date) == trim($end_date)) {
		    $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
		}else{
		   $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
		}
           }
	   if(isset($_POST['sampleType']) && $_POST['sampleType']!=''){
	    $sWhere = $sWhere.' AND s.sample_id = "'.$_POST['sampleType'].'"';
	   }
	   if(isset($_POST['facilityName']) && $_POST['facilityName']!=''){
	    $sWhere = $sWhere.' AND f.facility_id = "'.$_POST['facilityName'].'"';
	   }
	   if(isset($_POST['district']) && trim($_POST['district'])!= ''){
		$sWhere = $sWhere." AND f.district LIKE '%" . $_POST['district'] . "%' ";
	    }if(isset($_POST['state']) && trim($_POST['state'])!= ''){
		$sWhere = $sWhere." AND f.state LIKE '%" . $_POST['state'] . "%' ";
	    }
	}else{
	    if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
		$setWhr = 'where';
		$sWhere=' where '.$sWhere;
	        $sWhere = $sWhere.' b.batch_code = "'.$_POST['batchCode'].'"';
	    }
	    if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
		if(isset($setWhr)){
		    if (trim($start_date) == trim($end_date)) {
		     if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
		        $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
		     }else{
			$sWhere=' where '.$sWhere;
			$sWhere = $sWhere.' DATE(vl.sample_collection_date) = "'.$start_date.'"';
		     }
		    }
		}else{
		    $setWhr = 'where';
		    $sWhere=' where '.$sWhere;
		    $sWhere = $sWhere.' DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
		}
	    }
	    if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
		if(isset($setWhr)){
		    $sWhere = $sWhere.' AND s.sample_id = "'.$_POST['sampleType'].'"';
		}else{
		$setWhr = 'where';
		$sWhere=' where '.$sWhere;
	        $sWhere = $sWhere.' s.sample_id = "'.$_POST['sampleType'].'"';
		}
	    }
	    if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!= ''){
		if(isset($setWhr)){
		    $sWhere = $sWhere.' AND f.facility_id = "'.$_POST['facilityName'].'"';
		}else{
		$setWhr = 'where';
		$sWhere=' where '.$sWhere;
	        $sWhere = $sWhere.' f.facility_id = "'.$_POST['facilityName'].'"';
		}
	    }
	    if(isset($_POST['district']) && trim($_POST['district'])!= ''){
		if(isset($setWhr)){
		$sWhere = $sWhere." AND f.district LIKE '%" . $_POST['district'] . "%' ";
		}else{
		  $setWhr = 'where';
		  $sWhere=' where '.$sWhere;
		  $sWhere = $sWhere." f.district LIKE '%" . $_POST['district'] . "%' ";
		}
	    }if(isset($_POST['state']) && trim($_POST['state'])!= ''){
	      if(isset($setWhr)){
		$sWhere = $sWhere." AND f.state LIKE '%" . $_POST['state'] . "%' ";
	      }else{
		$sWhere=' where '.$sWhere;
		$sWhere = $sWhere." f.state LIKE '%" . $_POST['state'] . "%' ";
	      }
	    }
	}
	if($sWhere!=''){
	    $sWhere = $sWhere.' AND vl.form_id="'.$arr['vl_form'].'"';
	}else{
	    $sWhere = $sWhere.' where vl.form_id="'.$arr['vl_form'].'"';
	}
	$sQuery = $sQuery.' '.$sWhere;
	$sQuery = $sQuery." ORDER BY vl.modified_on DESC";
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.",".$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
        $_SESSION['vlRequestSearchResultQuery'] = $sQuery;
        $rResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        $aResultFilterTotal =$db->rawQuery("SELECT vl.vl_sample_id,vl.facility_id,vl.patient_name,vl.result,f.facility_name,f.facility_code,vl.art_no,s.sample_name,b.batch_code,vl.batch_id,ts.status_name FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id $sWhere ORDER BY vl.modified_on DESC, $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(vl_sample_id) as total FROM vl_request_form where form_id='".$arr['vl_form']."'");
       // $aResultTotal = $countResult->fetch_row();
       //print_r($aResultTotal);
        $iTotal = $aResultTotal[0]['total'];

        /*
         * Output
        */
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
	$vlRequest = false;
	$vlView = false;
	if(isset($_SESSION['privileges']) && (in_array("editVlRequest.php", $_SESSION['privileges']))){
	    $vlRequest = true;
	}
	if(isset($_SESSION['privileges']) && (in_array("viewVlRequest.php", $_SESSION['privileges']))){
	    $vlView = true;
	}
        
        foreach ($rResult as $aRow) {
	    $vlResult='';
	    $edit='';
	    if(isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date'])!= '' && $aRow['sample_collection_date']!= '0000-00-00 00:00:00'){
	       $xplodDate = explode(" ",$aRow['sample_collection_date']);
	       $aRow['sample_collection_date'] = $general->humanDateFormat($xplodDate[0]);
	    }else{
	       $aRow['sample_collection_date'] = '';
	    }
            $row = array();
	    //$row[]='<input type="checkbox" name="chk[]" class="checkTests" id="chk' . $aRow['vl_sample_id'] . '"  value="' . $aRow['vl_sample_id'] . '" onclick="toggleTest(this);"  />';
	    $row[] = $aRow['serial_no'];
	    $row[] = $aRow['sample_collection_date'];
	    $row[] = $aRow['batch_code'];
	    $row[] = $aRow['art_no'];
            $row[] = ucwords($aRow['patient_name']).' '.ucwords($aRow['surname']);
	    $row[] = ucwords($aRow['facility_name']);
	    $row[] = ucwords($aRow['state']);
	    $row[] = ucwords($aRow['district']);
            $row[] = ucwords($aRow['sample_name']);
            $row[] = $aRow['result'];
            $row[] = ucwords($aRow['status_name']);
	    //$printBarcode='<a href="javascript:void(0);" class="btn btn-info btn-xs" style="margin-right: 2px;" title="View" onclick="printBarcode(\''.base64_encode($aRow['vl_sample_id']).'\');"><i class="fa fa-barcode"> Print Barcode</i></a>';
	    //$enterResult='<a href="javascript:void(0);" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result" onclick="showModal(\'updateVlResult.php?id=' . base64_encode($aRow['vl_sample_id']) . '\',900,520);"> Result</a>';
		if($aRow['form_id']==2){
			if($vlRequest){
				$edit='<a href="editVlRequest.php?id=' . base64_encode($aRow['vl_sample_id']) . '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
			}
			$pdf = '<a href="javascript:void(0);" class="btn btn-success btn-xs" style="margin-right: 2px;" title="View" onclick="convertZmbPdf('.$aRow['vl_sample_id'].');"><i class="fa fa-file-text"> PDF</i></a>';
			if($vlView){
			    $view = '<a href="viewVlRequestZm.php?id=' . base64_encode($aRow['vl_sample_id']) . '" class="btn btn-default btn-xs" style="margin-right: 2px;" title="View"><i class="fa fa-eye"> View</i></a>';
			}
		}else{
			if($vlRequest){
			    $edit='<a href="editVlRequest.php?id=' . base64_encode($aRow['vl_sample_id']) . '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
			}
			$pdf = '<a href="javascript:void(0);" class="btn btn-success btn-xs" style="margin-right: 2px;" title="View" onclick="convertPdf('.$aRow['vl_sample_id'].');"><i class="fa fa-file-text"> PDF</i></a>';
			if($vlView){
			    $view = '<a href="viewVlRequest.php?id=' . base64_encode($aRow['vl_sample_id']) . '" class="btn btn-default btn-xs" style="margin-right: 2px;" title="View"><i class="fa fa-eye"> View</i></a>';
			}
		}
		
		
	    if($vlView){
			$row[] = $edit;//.$pdf.$view;
	    }else if($vlRequest || $editVlRequestZm){
		$row[] = $edit;//.$pdf;
	    }else if($vlView){
		$row[] = "";//$pdf.$view;
	    }
            $output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>