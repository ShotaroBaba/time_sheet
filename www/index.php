<?php 

header("Content-Type: text/html;charset=UTF-8");
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

include "/var/www/html/.secret/.config.php";

require_once("/var/www/html/plugin/strip_malicious_character.php");


// Get email & password for login.
$user_email=htmlspecialchars($_POST['employeeLoginIDInput']);
$password_char=htmlspecialchars($_POST['employeeLoginPasswordInput']);

try {
  
  // Check user input twice to prevent the attack
  // done by modifying javascript.

  // TODO: The admin will automatically move to the
  // webpage if it has his/her cookie.
  if(!empty($_SESSION['adminCookie'])){

  }

  if(!empty($_SESSION['employeeCookie'])){
    header('Location: /employee/employee_time_sheet.php');
  }

  
  // If a cookie does not exist, then set the cookie for a user.
  if(!empty($_POST['employeeLoginIDInput']) &&
     !empty($_POST['employeeLoginPasswordInput'])) {

    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check whether a user has logged in with his/her user account.
    $check_email_address_prepare=
    $conn->prepare("SELECT COUNT(`email`) as email_count
    FROM `user` WHERE
    `email` = :_email;");
  
    $check_email_address_prepare->bindValue(":_email", $user_email , PDO::PARAM_STR);
  
    if($check_email_address_prepare->execute() < 1){
      echo "Unknown error";
      
      $conn=NULL;
      $get_user_secret_prepare=NULL;
      $get_user_id_prepare=NULL;
      $check_email_address_prepare=NULL;
      exit(1);
    }
  
    if($check_email_address_prepare->fetch(PDO::FETCH_ASSOC)['email_count'] < 1) {
      echo "Unknown error";

      $conn=NULL;
      $get_user_secret_prepare=NULL;
      $get_user_id_prepare=NULL;
      $check_email_address_prepare=NULL;
      exit(1);
    }
  
    $get_user_id_prepare=
    $conn->prepare("SELECT user_id
    FROM `user` WHERE
    `email` = :_email;");
  
    $get_user_id_prepare->bindValue(":_email", $user_email, PDO::PARAM_STR);
  
    if($get_user_id_prepare->execute() < 1){
      echo "Unknown error";
      
      $conn=NULL;
      $get_user_secret_prepare=NULL;
      $get_user_id_prepare=NULL;
      $check_email_address_prepare=NULL;
      exit(1);
    }
  
    // Get user ID.
    $user_id=$get_user_id_prepare->fetch(PDO::FETCH_ASSOC)['user_id'];
   
    // Obtain salt & password hash using acqired user ID.
    $get_user_secret_prepare=
    $conn->prepare("SELECT `salt`, `password` 
    FROM `user_secret` WHERE
    `user_id` = :_user_id;");
  
    $get_user_secret_prepare->bindValue(":_user_id", $user_id, PDO::PARAM_INT);
    
    if($get_user_secret_prepare->execute() < 1){
      echo "Unknown error";
      
      $conn=NULL;
      $get_user_secret_prepare=NULL;
      $get_user_id_prepare=NULL;
      $check_email_address_prepare=NULL;
      exit(1);
    }
  
    $user_secret=$get_user_secret_prepare->fetch(PDO::FETCH_ASSOC);
  
    $salt=$user_secret['salt'];
    $password_hash=$user_secret['password'];

    if($password_hash == hash('sha256',$salt.$password_char.$pepper)){
      
      // Create a session.
      session_name('user_cookie');

      // Set a large session length.
      session_start([
        'sid_length' => 128
      ]);

      $_SESSION['ipaddress']=$_SERVER['REMOTE_ADDR'];
      $_SESSION['useragent']=$_SERVER['HTTP_USER_AGENT'];
      $_SESSION['employeeUserID']=$user_id['user_id'];
      $_SESSION['employeeCookie']=bin2hex(random_bytes(32));
      $_SESSION['expireAfter']= time()+$user_login_expiration_time;


      header('Location: /employee/employee_time_sheet.php');
    }
    else {
      echo "Unknown error.";
      exit(1);
    }
    $conn=NULL;
    $get_user_secret_prepare=NULL;
    $get_user_id_prepare=NULL;
    $check_email_address_prepare=NULL;
    exit(0);

  }
}

catch(PDOException $e)  {
  echo "Unknown error.";
  exit(1); 
}

?>

<!-- 
  Source: 
  Bootstrap: https://getbootstrap.com/
  Popper: https://popper.js.org/
  jQuery: https://jquery.com/
-->


<!-- The time sheet for employees will be done here... -->
<link href='/css/bootstrap.min.css?v=1' rel='stylesheet'>
<link href='/css/index.css?v=<?php echo time(); ?>' rel='stylesheet'>
<script src="/script/jquery-3.6.0.min.js"></script>

<script src="/script/popper.js"></script>
<script src="/script/bootstrap.bundle.min.js?v=1"></script>
<!-- Load script  -->
<script src="/script/check_employee_login.js?v=<?php echo time(); ?>"></script>
<script src="/script/check_admin_login.js?v=<?php echo time(); ?>"></script>


<div class="container">
<div class="row">
  <div class="col page-title">
    Employee Time Sheet Viewer
  </div>
</div>
</div>

<!-- enctype="multipart/form-data" accept-charset="UTF-8" -->

<div class="container ">
  <div class="page-title">
    Employee Login
  </div>
  <br>
  <form id="employeeLogin"
  method="POST"  
  action="/" 
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8"
  >
    <div class="form-group-sm row justify-content-center">
      <label for="employeeLoginIDInput" class="col-sm-1 col-form-label">Login ID: </label>
      <div class="col-sm-3">
        <input type="email" class="form-control" name="employeeLoginIDInput" id="employeeLoginIDInput" aria-describedby="emailHelp" placeholder="Enter LoginID">
      </div>
      <br>
    </div>
    <br>
    <div class="form-group-sm row justify-content-center">
      <label for="employeeLoginPasswordInput" class="col-sm-1 col-form-label">Password: </label>
      <div class="col-sm-3">
        <input type="password" autocomplete="on" class="form-control" name="employeeLoginPasswordInput" id="employeeLoginPasswordInput" aria-describedby="emailHelp" placeholder="Enter Password">
      </div>
      <br><br>
      <div class="d-flex justify-content-center">
        <button type="button" onclick="checkEmployeeInput();" class="btn btn-primary justify-content-center">Submit</button>
      </div>
    </div>
    <div  class="d-flex justify-content-center">
      <span id="employeeLoginErrorMessage" class='error-message'></span>
    </div>
    <br>
  </form>
</div>

<div class="container">
  <div class="submit-center">
    If you have not registered your account as an employee, press the button below:  
  </div>
  <br>
  <div class="d-flex justify-content-center">
    <button type="button" onclick="window.location='employee_registration/employee_account_registration.php'" class="btn btn-primary btn-lg btn-block">Register</button>
  </div>
</div>

<br><br><br>
<hr/>
<br>

<!-- The below is a Admin user login. Used for adding, deleting employees manually, or 
  checking all employees time sheets. -->
<div class="container">
  <div class="page-title">
    Administrator Login
  </div>
  <br>
  <form
  form id="adminLogin"
  method="POST"  
  action="/" 
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8"
  >
    <div class="form-group-sm row justify-content-center">
      <label for="adminLoginIDInput" class="col-sm-1 col-form-label">Login ID: </label>
      <div class="col-sm-3">
        <input type="email" class="form-control" id="adminLoginIDInput" aria-describedby="emailHelp" placeholder="Enter LoginID">
      </div>
      <br>
    </div>
    <br>
    <div class="form-group-sm row justify-content-center">
      <label for="adminPasswordInput" class="col-sm-1 col-form-label">Password: </label>
      <div class="col-sm-3">
        <input type="password" autocomplete="on" class="form-control" id="adminPasswordInput" aria-describedby="emailHelp" placeholder="Enter Password">
      </div>
      <br><br>
      <div class="d-flex justify-content-center">
        <button type="button" onclick="checkAdminInput();" class="btn btn-primary justify-content-center">Submit</button>
      </div>
      <div  class="d-flex justify-content-center">
        <span id="adminLoginErrorMessage" class='error-message'></span>
     </div>
    </div>
    <br>
  </form>
</div>
<script>
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>
