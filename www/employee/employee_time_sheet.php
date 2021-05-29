<?php 

header("Content-Type: text/html;charset=UTF-8");
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

include "/var/www/html/.secret/.config.php";
include "/var/www/html/plugin/create_next_previous_button.php";

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

if(isset($_SESSION['expireAfter']) & time() > $_SESSION['expireAfter'] ){
  session_unset();
  session_destroy();
  header('Location: /');
  exit(0);
}

// If a user remains inactive for a certain time, then
// a user will automatically be logged out.
try {

  if($_GET['i']=='logout'){
    session_unset();
    session_destroy();
    header('Location: /');
    exit(0);
  }

  // Only a number for 't' and 'n' inputs is allowed.
  if(
    (!is_null($_GET['t']) && !is_numeric($_GET['t'])) ||
    (!is_null($_GET['n']) && !is_numeric($_GET['n']))
  ){
    echo "Unknown error.";
    exit(1);
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

  $total_user_attendance_num=$conn->prepare("SELECT COUNT(*) AS total_attend_num FROM (SELECT `time`,`state`,`occupation_type` FROM `time_sheet` JOIN 
  `occupation` USING (`employee_type_id`) WHERE `user_id` = :_user_id) AS t;");

  $total_user_attendance_num->bindValue(":_user_id", htmlspecialchars($_SESSION['employeeUserID']), PDO::PARAM_INT);

  if($total_user_attendance_num->execute() < 1){
    echo "Unknown error.";
    exit(1);
  }



  $total_result=$total_user_attendance_num->fetch();

  
  // Injection attack prevention
  $select_num_output=$_GET['t'] == '' || is_null($_GET['t']) ? 10 : htmlspecialchars($_GET['t']);
  $num_selection_output=$_GET['n']== '' || is_null($_GET['n']) ? 1 : htmlspecialchars($_GET['n']);

  // Set the limit of selection.
  $select_min=1;
  $select_max=intdiv($total_result['total_attend_num'],$select_num_output);
  if($total_result['total_attend_num']/($select_num_output*$select_max) > 1 || $select_max ==0){
    $select_max+=1;
  }
  echo $total_result['total_attend_num']/$select_num_output;
  $num_selection_output_tmp=$num_selection_output;
  // Prevent re-setting the number selection.
  if($num_selection_output>$select_max){
    $num_selection_output_tmp=$select_max;
  }


  // TODO: Put LIMIT and OFFSET.
  $select_user_attendance_record=
  $conn->prepare("SELECT * FROM (SELECT @row_num := @row_num + 1 AS sheet_no, i.* FROM (SELECT `time`,`state`,`occupation_type` FROM `time_sheet` JOIN 

  `occupation` USING (`employee_type_id`) WHERE `user_id` = :_user_id) i, (SELECT @row_num := 0) t) AS total 
  LIMIT :_total OFFSET :_n_total;");

  $select_user_attendance_record->bindValue(":_user_id", htmlspecialchars($_SESSION['employeeUserID']), PDO::PARAM_INT);
  $select_user_attendance_record->bindValue(":_total", $select_num_output, PDO::PARAM_INT);
  $select_user_attendance_record->bindValue(":_n_total", $select_num_output*($num_selection_output_tmp-1), PDO::PARAM_INT);

  if($select_user_attendance_record->execute() < 1){
    echo "Unknown error.";
    exit(1);
  }

  $select_result=$select_user_attendance_record->fetchAll();

  // Finally reset cookie lifetime.
  $_SESSION['expireAfter']=time()+$user_login_expiration_time;

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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">   
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Time Sheet (<?php echo $user_info['first_name']; ?>)</title>
</head>

<link href='/css/bootstrap.min.css?v=1' rel='stylesheet'>
<link href='/css/index.css?v=<?php echo time(); ?>' rel='stylesheet'>
<script src="/script/jquery-3.6.0.min.js"></script>
<script src="/script/popper.js?v=1"></script>
<script src="/script/bootstrap.bundle.min.js?v=1"></script>
<script src="/script/register.js?v=<?php echo time(); ?>"></script>

<body>
  

  Name: <?php echo $user_info['first_name']." ".$user_info['middle_name']." ".$user_info['last_name'];?>

  <table class="table">
    <thead class="dark-head">
      <tr class="table-head">
        <th>No. </th>
        <th>Time</th>
        <th>Attendance Status</th>
        <th>Occupation</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $i=0;
        foreach ($select_result as $v) {
          $class_tag=$i%2==0?" class='grey-table-row'":"";
          $work_status=$v['state']=="working"?"At work":"Already left";
          echo "<tr".$class_tag.">";
          echo "<th>".$v['sheet_no']."</th>";
          echo "<th>".$v['time']."</th>";
          echo "<th>".$work_status."</th>";
          echo "<th>".$v['occupation_type']."</th>";
          echo "</tr>";
          $i++;
        }
      ?>
    </tbody>
  </table>

  <form action="/employee/employee_time_sheet.php" id="userForm" method="GET">
    <button type="submit" name="i" value="logout">Logout</button>
    <button type="submit" 
    name="i" num_selection_output
    value=<?php echo($user_info['state']=='left_work' ? '"working"' : '"left_work"'); ?>
    ><?php echo( $user_info['state']=='left_work' ? "Attend" : "Leave");   ?></button>
    <input class="span2" id="t" name="t" type="hidden" 
    value='<?php echo $select_num_output; ?>'>
    <input class="span2" id="n" name="n" type="hidden"
    value='<?php echo $num_selection_output?>'>

    <!-- Pull down menu -->
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php
          echo $select_num_output;
        ?>
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <li class="dropdown-item"  onclick="$('#t').val(10);$('#userForm').submit();" value='10'>10</a>
        <li class="dropdown-item"  onclick="$('#t').val(25);$('#userForm').submit();"value='25'>25</a>
        <li class="dropdown-item"  onclick="$('#t').val(50);$('#userForm').submit();"value='50'>50</a>
        <li class="dropdown-item"  onclick="$('#t').val(100);$('#userForm').submit();"value='100'>100</a>
      </ul>
    </div>

    <nav aria-label="Page navigation example">
      <ul class="pagination">
      <?php generate_previous_next_button($select_min,$select_max,$num_selection_output_tmp);?>
      </ul>
    </nav>
  </form>
</body>


