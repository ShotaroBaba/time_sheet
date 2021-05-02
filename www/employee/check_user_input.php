<?php

include "/var/www/html/.secret/.config.php";


// *************************************/
// **** Check login status *************/
// This will check the 
try {

  $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  
  // Toggle an error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Check a number of email before inserting a
  // user's info.

  $check_email_address_prepare=
  $conn->prepare("SELECT COUNT(`email`) as email_count
  FROM `user` WHERE
  `email` = :_email;");

  $check_email_address_prepare->bindValue(":_email", htmlspecialchars($_POST['employeeEmail']), PDO::PARAM_STR);
  
  $check_email_address_result=$check_email_address_prepare->execute();
  // Email already exists.
  if($check_email_address_result > 0){
    echo "Error. Registered email already exists.";
    $check_email_address_prepare=NULL;
    die;
  }
  $check_email_address_prepare=NULL;
  echo "okay";
}

catch(PDOException $e)  {
  echo "Unknown error.";
  die; 
}


?>