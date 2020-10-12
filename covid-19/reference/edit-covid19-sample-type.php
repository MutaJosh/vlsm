<?php
ob_start();
#require_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');
$id = base64_decode($_GET['id']);
$sampleQuery = "SELECT * from r_covid19_sample_type where sample_id=$id";
$sampleInfo = $db->query($sampleQuery);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><i class="fa fa-gears"></i> Edit Covid-19 Sample Type</h1>
    <ol class="breadcrumb">
      <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Covid-19 Sample Type</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    
    <div class="box box-default">
      <div class="box-header with-border">
        <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
      </div>
      <!-- /.box-header -->
      <div class="box-body">
        <!-- form start -->
        <form class="form-horizontal" method='post' name='editSampleForm' id='editSampleForm' autocomplete="off" enctype="multipart/form-data" action="edit-sample-type-helper.php">
          <div class="box-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sampleName" class="col-lg-4 control-label">Sample Name <span class="mandatory">*</span></label>
                  <div class="col-lg-7">
                    <input type="text" class="form-control isRequired" id="sampleName" name="sampleName" placeholder="sample Name" title="Please enter Sample name" value="<?php echo $sampleInfo[0]['sample_name']; ?>" onblur="checkNameValidation('sample_details','sample_name',this,'<?php echo "sample_id##" . $sampleInfo[0]['sample_id']; ?>','The sample name that you entered already exists.Enter another name',null)" />
                    <input type="hidden" class="form-control isRequired" id="sampleId" name="sampleId" value="<?php echo base64_encode($sampleInfo[0]['sample_id']); ?>" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sampleStatus" class="col-lg-4 control-label">Sample Status</label>
                  <div class="col-lg-7">
                    <select class="form-control isRequired" id="sampleStatus" name="sampleStatus" placeholder="Sample Status" title="Please enter Sample Status"  >
                      <option value="active" <?php echo($sampleInfo[0]['sample_status']=="active" ? 'selected':''); ?> >Active</option>
                      <option value="inactive" <?php echo($sampleInfo[0]['sample_status']=="inactive" ? 'selected':''); ?> >Inactive</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <br>

          </div>
          <!-- /.box-body -->
          <div class="box-footer">
            <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
            <a href="covid19-sample-type.php" class="btn btn-default"> Cancel</a>
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
  function validateNow() {
   
    flag = deforayValidator.init({
      formId: 'editSampleForm'
    });

    if (flag) {
      $.blockUI();
      document.getElementById('editSampleForm').submit();
    }
  }

  function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
    var removeDots = obj.value.replace(/\./g, "");
    var removeDots = removeDots.replace(/\,/g, "");
    //str=obj.value;
    removeDots = removeDots.replace(/\s{2,}/g, ' ');

    $.post("/includes/checkDuplicate.php", {
        tableName: tableName,
        fieldName: fieldName,
        value: removeDots.trim(),
        fnct: fnct,
        format: "html"
      },
      function(data) {
        if (data === '1') {
          alert(alrt);
          document.getElementById(obj.id).value = "";
        }
      });
  }

</script>

<?php
include(APPLICATION_PATH . '/footer.php');
?>