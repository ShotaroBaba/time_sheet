<?php

include "/var/www/html/.secret/.config.php";


// *************************************/
// **** Check login status *************/
// This will check the 
try {


  $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  $is_email_exist=false;
  $is_name_phone_exist=false;
  // Toggle an error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Check a number of email before inserting a
  // user's info.

  $check_email_address_prepare=
  $conn->prepare("SELECT COUNT(`email`) as email_count
  FROM `user` WHERE
  `email` = :_email;");

  // $check_email_address_prepare->bindValue(":_email", htmlspecialchars($_POST['employeeEmail']), PDO::PARAM_STR);
  $check_email_address_prepare->bindValue(":_email", $_POST['employeeEmail'], PDO::PARAM_STR);

  $check_email_address_result=$check_email_address_prepare->execute();

  // Email already exists.
  if($check_email_address_result > 0){
    $is_email_exist=true;
    $check_email_address_prepare=NULL;
  }
  
  $check_user_name_phone_prepare=
  $conn->prepare("SELECT COUNT(*) as user_name_phone_count
  FROM `user` WHERE
  `first_name` = :_first_name AND
  `middle_name` = :_middle_name AND
  `last_name` = :_last_name AND 
  `phone_number` = :_phone_number ;");

  $check_user_name_phone_prepare->bindValue(":_first_name", $_POST['employeeFirstName'], PDO::PARAM_STR);
  $check_user_name_phone_prepare->bindValue(":_middle_name", $_POST['employeeMiddleName'], PDO::PARAM_STR);
  $check_user_name_phone_prepare->bindValue(":_last_name", $_POST['employeeLastName'], PDO::PARAM_STR);
  $check_user_name_phone_prepare->bindValue(":_phone_number", $_POST['employeePhoneNumber'], PDO::PARAM_STR);

  $check_user_name_phone_result=$check_user_name_phone_prepare->execute();

   // Email already exists.
   if($check_email_address_result > 0){
    $is_name_phone_exist=true;
    $check_user_name_phone_prepare=NULL;
  }

  $check_user_name_phone_prepare=NULL;
  echo "{'email':$is_email_exist,
  'account':$is_name_phone_exist}";

}

catch(PDOException $e)  {
  echo "Unknown error.";
  die; 
}


?>