<?php 

header("Content-Type: text/html;charset=UTF-8");
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

include "/var/www/html/.secret/.config.php";

require_once("/var/www/html/plugin/strip_malicious_character.php");

try {
  
  // Check user input twice to prevent the attack
  // done by modifying javascript.

  // If a cookie does not exist, then set the cookie for a user.
  $admin_user_name=htmlspecialchars($_POST['adminLoginIDInput']);
  $admin_pass=htmlspecialchars($_POST['adminLoginPasswordInput']);

  // The connection will return false if a value is false.
  $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $admin_user_name, $admin_pass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  
  $data=array();
  $data['login_success']=true;
  echo(json_encode($data));
  exit(0);

}
catch(PDOException $e)  {
  $data=array();
  $data['login_success']=false;
  echo (json_encode($data));
  exit(1); 
}

?>