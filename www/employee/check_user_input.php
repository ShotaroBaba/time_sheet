<?php

include "/var/www/html/.secret/.config.php";


// *************************************/
// **** Check login status *************/
// This will check the 
try {

  // Connect to the database.
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

  $check_email_address_prepare->bindValue(":_email", htmlspecialchars($_POST['employeeEmail']), PDO::PARAM_STR);


  // Email already exists.
  if($check_email_address_prepare->execute() < 1){
    echo "Unknown error";
    goto disconnect_database;
  }
  
  if($check_email_address_prepare->fetch(PDO::FETCH_ASSOC)['email_count'] > 0) {
    $is_email_exist=true;
  }

  $check_user_name_phone_prepare=
  $conn->prepare("SELECT COUNT(*) as user_name_phone_count
  FROM `user` WHERE
  `first_name` = :_first_name AND
  `middle_name` = :_middle_name AND
  `last_name` = :_last_name AND 
  `phone_number` = :_phone_number ;");

  $check_user_name_phone_prepare->bindValue(":_first_name", htmlspecialchars($_POST['employeeFirstName']), PDO::PARAM_STR);
  $check_user_name_phone_prepare->bindValue(":_middle_name", htmlspecialchars($_POST['employeeMiddleName']), PDO::PARAM_STR);
  $check_user_name_phone_prepare->bindValue(":_last_name", htmlspecialchars($_POST['employeeLastName']), PDO::PARAM_STR);
  $check_user_name_phone_prepare->bindValue(":_phone_number", htmlspecialchars($_POST['employeePhoneNumber']), PDO::PARAM_STR);

  if($check_user_name_phone_prepare->execute() < 1) {
    echo "Unknown error.";
    goto disconnect_database;
  }

  if($check_user_name_phone_prepare->fetch(PDO::FETCH_ASSOC)['user_name_phone_count'] > 0) {
    $is_name_phone_exist=true;
  }

  $output=array();
  $output['is_email_exist']=$is_email_exist;
  $output['is_name_phone_exist']=$is_name_phone_exist;
  echo json_encode($output);

  // Jump to this code if the SQL omits an error when being executed.
  disconnect_database:
  $check_email_address_prepare=NULL;
  $check_user_name_phone_prepare=NULL;
  $conn=NULL;
  exit(0);

}

catch(PDOException $e)  {
  echo "Unknown error.";
  die; 
}


?>