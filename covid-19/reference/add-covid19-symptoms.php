<?php
ob_start();
#require_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');
$rejQuery = "SELECT * from r_covid19_symptoms WHERE symptom_status ='active'";
$rejInfo = $db->query($rejQuery);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><i class="fa fa-gears"></i> Add Covid-19 Symptoms</h1>
    <ol class="breadcrumb">
      <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Covid-19 Symptoms</li>
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
        <form class="form-horizontal" method='post' name='addSympForm' id='addSympForm' autocomplete="off" enctype="multipart/form-data" action="add-symptoms-helper.php">
          <div class="box-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="symptomsName" class="col-lg-4 control-label">Symptom Name <span class="mandatory">*</span></label>
                  <div class="col-lg-7">
                    <input type="text" class="form-control isRequired" id="symptomsName" name="symptomsName" placeholder="Symptom Name" title="Please enter Symptom name" onblur="checkNameValidation('r_covid19_symptoms','symptom_name',this,null,'The Symptom name that you entered already exists.Enter another name',null)" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="parentSymptom" class="col-lg-4 control-label">Parent Symptom</label>
                  <div class="col-lg-7">
                    <select class="form-control" id="parentSymptom" name="parentSymptom" placeholder="Parent Symptom" title="Please enter Parent Symptom"  >
                        <option value=""> -- Select -- </option>
                        <?php
                        foreach ($rejInfo as $type) {
                        ?>
                            <option value="<?php echo $type['symptom_id']; ?>"><?php echo ucwords($type['symptom_name']); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="symptomsStatus" class="col-lg-4 control-label">Symptom Status</label>
                  <div class="col-lg-7">
                    <select class="form-control isRequired" id="symptomsStatus" name="symptomsStatus" placeholder="Symptom Status" title="Please enter Symptom Status"  >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
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
            <a href="covid19-symptoms.php" class="btn btn-default"> Cancel</a>
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
      formId: 'addSympForm'
    });

    if (flag) {
      $.blockUI();
      document.getElementById('addSympForm').submit();
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