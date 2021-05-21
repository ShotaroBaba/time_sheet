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
  header('Location: /');
  exit(0);
}

// For preventing session hijacking.
if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ipaddress'])
{
  session_unset();
  session_destroy();
  header('Location: /');
  exit(0);
}

if ($_SERVER['HTTP_USER_AGENT'] != $_SESSION['useragent'])
{
  session_unset();
  session_destroy();
  header('Location: /');
  exit(0);
}

// Get email & password for login.
$user_email=htmlspecialchars($_POST['employeeLoginIDInput']);
$password_char=htmlspecialchars($_POST['employeeLoginPasswordInput']);


// If a user remains inactive for a certain time, then
// a user will automatically be logged out.
try {

  if($_GET['i']=='logout'){
    session_unset();
    session_destroy();
    header('Location: /');
    exit(0);
  }

  // Get user info from a user_id;
  $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $select_user_info_prepare=
  $conn->prepare("SELECT
  `first_name`,
  `middle_name`,
  `last_name` ,
  `employee_type_id`,
  `state`
  FROM `user` WHERE `user_id` = :_user_id;");

  $select_user_info_prepare->bindValue(":_user_id", $_SESSION['employeeUserID'], PDO::PARAM_INT);
  if($select_user_info_prepare->execute() < 1){
    echo "Unknown error.";
    exit(1);
  }

  $user_info=$select_user_info_prepare->fetch(PDO::FETCH_ASSOC);
  $user_state_input=NULL;

  // Avoid user invalid input just in case.
  if($_GET['i']=='working' && $user_info['state']=='left_work'){
    $user_state_input='working';
    $user_info['state']='working';
  }

  else if($_GET['i']=='left_work' && $user_info['state']=='working'){
    $user_state_input='left_work';
    $user_info['state']='left_work';
  }

  //Update user's info.
  if(!is_null($user_state_input)) {
    
    // Update user state.
    $update_user_state_prepare=$conn->prepare("UPDATE
    `user`
    SET `state` = :_state WHERE `user_id` = :_user_id;");
    
    $update_user_state_prepare->bindValue(":_user_id", $_SESSION['employeeUserID'], PDO::PARAM_INT);
    $update_user_state_prepare->bindValue(":_state", $user_state_input, PDO::PARAM_STR);

    if($update_user_state_prepare->execute() < 1){
      echo "Unknown error.";
      exit(1);
    }

    $insert_user_status_record_prepare=$conn->prepare(
    "INSERT INTO `time_sheet` (`user_id`,
    `employee_type_id`,
    `time`,
    `state`) VALUES (:_user_id, :_employee_type_id, SYSDATE(6),:_state);");

    $insert_user_status_record_prepare->bindValue(":_user_id", $_SESSION['employeeUserID'], PDO::PARAM_INT);
    $insert_user_status_record_prepare->bindValue(":_employee_type_id", $user_info['employee_type_id'], PDO::PARAM_INT);
    $insert_user_status_record_prepare->bindValue(":_state", $user_state_input, PDO::PARAM_STR);

    if($insert_user_status_record_prepare->execute() < 1){
      echo "Unknown error.";
      exit(1);
    }

  }

  // Record user's login history.
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
    exit(1);
  }

}

catch(PDOException $e)  {
  echo "Unknown error.";
  exit(1); 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">   
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<link href='/css/bootstrap.min.css' rel='stylesheet'>
<link href='/css/index.css' rel='stylesheet'>
<script src="/script/jquery-3.6.0.min.js"></script>
<script src="/script/register.js"></script>

<body>
  

  Name: <?php echo $user_info['first_name']." ".$user_info['middle_name']." ".$user_info['last_name'];?>

  <form action="/employee/employee_time_sheet.php" method="GET">
    <button type="submit" name="i" value="logout">Logout</button>
    <button type="submit" 
    name="i" 
    value=<?php echo($user_info['state']=='left_work' ? '"working"' : '"left_work"'); ?>
    ><?php echo( $user_info['state']=='left_work' ? "Attend" : "Leave");   ?></button>
  </form>

</body>
</html>


