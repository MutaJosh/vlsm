<?php

$title = "VLSM | Dashboard";

include_once('../startup.php'); 
include_once(APPLICATION_PATH.'/header.php');


/* Total data set length */
  $vlFormTotal =  $db->rawQuery("select COUNT(vl_sample_id) as total FROM vl_request_form");
 // $aResultTotal = $countResult->fetch_row();
 //print_r($aResultTotal);
  $labCount = $vlFormTotal[0]['total'];
  
  $facilityTotal =  $db->rawQuery("select COUNT(facility_id) as total FROM facility_details");
  $facilityCount = $facilityTotal[0]['total'];

?>
<link rel="stylesheet" href="../assets/css/components-rounded.min.css">
<style>
    .bluebox, .dashboard-stat2{
        border:1px solid #3598DC;
    }
    .input-mini{width:100% !important;}
    .labAverageTatDiv{display:none;}
</style>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
      </h1>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row" style="padding-top:10px;padding-bottom:20px;">
	<div class="col-lg-7">
  <form autocomplete="off">
	  <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
		<tr>
		    <td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
		    <td>
		      <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;"/>
		    </td>
		    <td colspan="3">&nbsp;<input type="button" onclick="searchVlRequestData();" value="Search" class="btn btn-success btn-sm">
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="resetSearchVlRequestData();"><span>Reset</span></button>
		    </td>
		</tr>
	  </table>
    </form>
	  </div>
      </div>
      <div class="row">
	<div id="sampleResultDetails"></div>
	
        <!--<div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3>< ?php echo $facilityCount;?></h3>
              <p>Facilities</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="facilities.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green">
            <div class="inner">
              <h3>< ?php echo $labCount; ?></h3>

              <p>Lab Requests</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="vlRequest.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>-->
        <div class="box-body" id="noOfSampleCount">
            </div>
            <div id="pieChartDiv">
            </div>

            
      </div>
      
      <!-- /.row -->
      <!-- Main row -->
      <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
  </div>
  <script type="text/javascript" src="../assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script src="../assets/js/highcharts.js"></script>
  <script>
    $(function () {
    
    
    $('#sampleCollectionDate').daterangepicker({
            format: 'DD-MMM-YYYY',
	          separator: ' to ',
            startDate: moment().subtract(6,'days'),
            endDate: moment(),
            maxDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
                'Last 7 Days': [moment().subtract(6,'days'), moment()],
                'Last 30 Days': [moment().subtract(29,'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')]
            }
        },
        function(start, end) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');
      });
      searchVlRequestData();
    });
    
    function searchVlRequestData(){
      $.blockUI();
      $.post("getSampleResult.php",{sampleCollectionDate:$("#sampleCollectionDate").val()},
      function(data){
        if(data!=''){
          $("#sampleResultDetails").html(data);
        }
      });
      $.unblockUI();
      getNoOfSampleCount();
      getSamplesOverview();
    }

    function getNoOfSampleCount(){
      $.blockUI();
      $.post("getSampleCount.php",{sampleCollectionDate:$("#sampleCollectionDate").val()},
      function(data){
        if(data!=''){
          $("#noOfSampleCount").html(data);
        }
      });
      $.unblockUI();
    }

function getSamplesOverview(){
      $.blockUI();
      $.post("/program-management/getSampleStatus.php",{sampleCollectionDate:$("#sampleCollectionDate").val(),batchCode:'',facilityName:'',sampleType:''},
      function(data){
        if($.trim(data)!=''){
          $("#pieChartDiv").html(data);
          $(".labAverageTatDiv").css("display","none");
        }
      });
      $.unblockUI();
    }    
    
    function resetSearchVlRequestData(){
      $('#sampleCollectionDate').daterangepicker({
            format: 'DD-MMM-YYYY',
	          separator: ' to ',
            startDate: moment().subtract(6,'days'),
            endDate: moment(),
            maxDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
                'Last 7 Days': [moment().subtract(6,'days'), moment()],
                'Last 30 Days': [moment().subtract(29,'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')]
            }
        },
        function(start, end) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');
      });
      searchVlRequestData();
    }
  </script>
 <?php
 include(APPLICATION_PATH.'/footer.php');
 ?>
