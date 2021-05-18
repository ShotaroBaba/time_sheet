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

  // If a cookie does not exist, then set the cookie for a user.
  if(!$_SESSION 
  || !$_SESSION['employee_cookie']
  || ($_SERVER['REMOTE_ADDR'] != $_SESSION['ipaddress'])
  || ($_SERVER['HTTP_USER_AGENT'] != $_SESSION['useragent'])) {

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
    $user_id=
    $get_user_id_prepare->fetch(PDO::FETCH_ASSOC);
   
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
      session_name('employee_cookie');
      session_start([
        'cookie_lifetime' => 600
      ]);
      session_regenerate_id(true);
      
      $_SESSION['ipaddress']=$_SERVER['REMOTE_ADDR'];
      $_SESSION['useragent']=$_SERVER['HTTP_USER_AGENT'];
      echo "Success!";
      print_r($_SESSION);
    }
    else {
      echo "Unknown error.";
      exit(1);
    }
    $conn=NULL;
    $get_user_secret_prepare=NULL;
    $get_user_id_prepare=NULL;
    $check_email_address_prepare=NULL;

  }

  else {
    // If a user is inactive for 30 minutes, then a user 
    // cannot login with the same cookie that a user previously used.
    echo "Success!!~~";
  }
}

catch(PDOException $e)  {
  echo "Unknown error.";
  exit(1); 
}

?>