<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- iCheck -->
 
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
</head>
<body class="">
    <div class="container">    
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
            <div class="panel panel-info" >
                    <div class="panel-heading">
                        <div class="panel-title">Sign In</div>
                        
                    </div>     

                    <div style="padding-top:30px" class="panel-body" >

                        <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
                            
                        <form id="loginForm" name="loginForm" class="form-horizontal" role="form" method="post" action="loginProcess.php">
                                    
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input id="login-username" type="text" class="form-control isRequired" name="username" value="" placeholder="User Name" title="Please enter the user name">
                            </div>
                                
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input id="login-password" type="password" class="form-control isRequired" name="password" placeholder="Password" title="Please enter the password">
                            </div>
                                
                            <div class="input-group">
                                      <div class="checkbox">
                                        <label>
                                          <input id="login-remember" type="checkbox" name="remember" value="1"> Remember me
                                        </label>
                                      </div>
                            </div>

                            <div style="margin-top:10px" class="form-group">
                                <!-- Button -->
                                <div class="col-sm-12 controls">
                                    <a class="btn btn-lg btn-success btn-block" href="javascript:void(0);" onclick="validateNow();return false;">Login</a>
                                </div>
                            </div>
                            </form>
                        </div>                     
                    </div>  
        </div>
    </div>
    <script src="assets/js/deforayValidation.js"></script>
    <script type="text/javascript">

    function validateNow(){
      flag = deforayValidator.init({
          formId: 'loginForm'
      });
      
      if(flag){
        document.getElementById('loginForm').submit();
      }
    }
    $(document).ready(function(){
    <?php
    if(isset($_SESSION['alertMsg']) && trim($_SESSION['alertMsg'])!=""){
    ?>
    alert('<?php echo $_SESSION['alertMsg']; ?>');
    <?php
    $_SESSION['alertMsg']='';
    unset($_SESSION['alertMsg']);
    }
    ?>
    });
    </script>
</body>
</html>
