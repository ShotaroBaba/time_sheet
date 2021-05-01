<?php

include ".secret/header.php";


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

  $check_email_address_prepare->bindValue(":_email", htmlspecialchars($_POST['employeeEmail']), PDO::PARAM_STR));
  
  $check_email_address_result=$check_email_address_prepare->execute();
  $check_email_address_result=$check_email_address_result->fetch(PDO::FETCH_ASSOC);
  $check_email_address_prepare=NULL;

  // Email already exists.
  if($check_email_address_result['email_count'] > 0){
    echo "Error. Registered email already exists.";
    header("Location: employee_account_registration.php");
    die;
  }
  echo "okay";
}

catch(PDOException $e)  {
  echo "Unknown error."
  die; 
}

echo '{"a": "b"}';

?>