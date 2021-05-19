<?php 

header("Content-Type: text/html;charset=UTF-8");
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

include "/var/www/html/.secret/.config.php";

require_once("/var/www/html/plugin/strip_malicious_character.php");

session_name('user_cookie');
session_start();

// If this session value becomes empty.
// then a user will move on to the top page.
if(empty($_SESSION)){
  session_unset();
  session_destroy();
}

// For preventing session hijacking.
if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ipaddress'])
{
  session_unset();
  session_destroy();
}

if ($_SERVER['HTTP_USER_AGENT'] != $_SESSION['useragent'])
{
  session_unset();
  session_destroy();
}

// Get email & password for login.
$user_email=htmlspecialchars($_POST['employeeLoginIDInput']);
$password_char=htmlspecialchars($_POST['employeeLoginPasswordInput']);


// If a user remains inactive for a certain time, then
// a user will automatically be logged out.
try {
  // Get user info from a user_id;
  $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $insert_user_log_prepare=
  $conn->prepare("INSERT INTO `user_log` (`session_id`,
  `user_id`,
  `login_time`,
  `url`)
  VALUES
  (:_session_id, :_user_id, SYSDATE(6), :_url);");

  $insert_user_log_prepare->bindValue(":_session_id", htmlspecialchars($_SESSION['employeeCookie']), PDO::PARAM_STR);
  $insert_user_log_prepare->bindValue(":_user_id", htmlspecialchars($_SESSION['employeeUserID']), PDO::PARAM_INT);
  $insert_user_log_prepare->bindValue(":_url", $_SERVER['PHP_SELF'], PDO::PARAM_STR);

  if($insert_user_log_prepare->execute() < 1){
    echo "Unknown error.";
  }

  echo "Recorded...";
}

catch(PDOException $e)  {
  print $e;
  echo "Unknown error.";
  exit(1); 
}

?>