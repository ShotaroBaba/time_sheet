<?php 

  header("Content-Type: text/html;charset=UTF-8");
  error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

  include "/var/www/html/.secret/.config.php";
  include "/var/www/html/plugin/create_next_previous_button.php";

  require_once("/var/www/html/plugin/strip_malicious_character.php");

  session_name('admin_cookie');
  session_start([
    'sid_length' => 128
  ]);
  session_regenerate_id(true);

  $is_valid_input=NULL;
  $is_delete_complete=false;

  // Change session ID to prevent session hijacking.
  session_regenerate_id(true);

  
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

  if(isset($_SESSION['expireAfter']) & time() > $_SESSION['expireAfter']){
    session_unset();
    session_destroy();    
    header('Location: /');
    exit(0);
  }

  // If a user remains inactive for a certain time, then
  // a user will automatically be logged out.
  try {
    
    $conn= new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", 
    $_SESSION['admin_user_name'], $_SESSION['admin_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if(empty($_REQUEST['chageUserTimeTable'])) {
        // Only a number for 't' and 'n' inputs is allowed.
      if(
        (!is_null($_REQUEST['t']) && !is_numeric($_REQUEST['t'])) ||
        (!is_null($_REQUEST['n']) && !is_numeric($_REQUEST['n']))
      ){
        echo "Unknown error.";
        exit(1);
      }



      // ********************************
      // Select all user records
      // ********************************
      
      $select_user_info_prepare=
      $conn->prepare("SELECT
      `first_name`,
      `middle_name`,
      `last_name` ,
      `employee_type_id`,
      `state`
      FROM `user` WHERE `user_id` = :_user_id;");

      $select_user_info_prepare->bindValue(":_user_id", htmlspecialchars($_REQUEST['user_id']), PDO::PARAM_INT);
      if($select_user_info_prepare->execute() < 1){
        echo "Unknown error.";
        exit(1);
      }

      $user_info=$select_user_info_prepare->fetch(PDO::FETCH_ASSOC);

      // ***********************************
      // *** End of taking all user info ***
      // ***********************************
      
      $user_state_input=NULL;

      // Avoid user invalid input just in case.
      if($_REQUEST['i']=='working' && $user_info['state']=='left_work'){
        $user_state_input='working';
        $user_info['state']='working';
      }

      else if($_REQUEST['i']=='left_work' && $user_info['state']=='working'){
        $user_state_input='left_work';
        $user_info['state']='left_work';
      }

      //Update user's info.
      if(!is_null($user_state_input)) {
        
        // Update user state.
        $update_user_state_prepare=$conn->prepare("UPDATE
        `user`
        SET `state` = :_state WHERE `user_id` = :_user_id;");
        
        $update_user_state_prepare->bindValue(":_user_id", 
        htmlspecialchars($_REQUEST['user_id']), 
        PDO::PARAM_INT);

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

        $insert_user_status_record_prepare->bindValue(":_user_id", $_REQUEST['user_id'], PDO::PARAM_INT);
        $insert_user_status_record_prepare->bindValue(":_employee_type_id", $user_info['employee_type_id'], PDO::PARAM_INT);
        $insert_user_status_record_prepare->bindValue(":_state", $user_state_input, PDO::PARAM_STR);

        if($insert_user_status_record_prepare->execute() < 1){
          echo "Unknown error.";
          exit(1);
        }

        }

        $total_user_attendance_num=$conn->prepare("SELECT COUNT(*) AS total_attend_num FROM (SELECT `time`,`state`,`occupation_type` FROM `time_sheet` JOIN 
        `occupation` USING (`employee_type_id`) WHERE `user_id` = :_user_id) AS t;");

        $total_user_attendance_num->bindValue(":_user_id", htmlspecialchars($_REQUEST['user_id']), PDO::PARAM_INT);

        if($total_user_attendance_num->execute() < 1){
          echo "Unknown error.";
          exit(1);
        }

        $total_result=$total_user_attendance_num->fetch();


        // Injection attack prevention measure.
        $select_num_output=$_REQUEST['t'] == '' || is_null($_REQUEST['t']) ? 10 : htmlspecialchars($_REQUEST['t']);
        $num_selection_output=$_REQUEST['n']== '' || is_null($_REQUEST['n']) ? 1 : htmlspecialchars($_REQUEST['n']);

        // Set the limit of selection.
        $select_min=1;
        $select_max=intdiv($total_result['total_attend_num'],$select_num_output);
        if($total_result['total_attend_num']/($select_num_output*$select_max) > 1 || $select_max ==0){
          $select_max+=1;
        }

        $num_selection_output_tmp=$num_selection_output;

        // Prevent re-setting the number selection.
        if($num_selection_output>$select_max){
          $num_selection_output_tmp=$select_max;
        }

        $select_user_attendance_record=
        $conn->prepare("SELECT * FROM (SELECT @row_num := @row_num + 1 AS sheet_no, i.* FROM (SELECT `time_id`,`time`,`state`,`occupation_type` FROM `time_sheet` JOIN 

        `occupation` USING (`employee_type_id`) WHERE `user_id` = :_user_id) i, (SELECT @row_num := 0) t) AS total 
        LIMIT :_total OFFSET :_n_total;");

        $select_user_attendance_record->bindValue(":_user_id", htmlspecialchars($_REQUEST['user_id']), PDO::PARAM_INT);
        $select_user_attendance_record->bindValue(":_total", $select_num_output, PDO::PARAM_INT);
        $select_user_attendance_record->bindValue(":_n_total", $select_num_output*($num_selection_output_tmp-1), PDO::PARAM_INT);

        if($select_user_attendance_record->execute() < 1){
          echo "Unknown error.";
          exit(1);
        }

        $select_result=$select_user_attendance_record->fetchAll();
    }

    if(!empty($_REQUEST['chageUserTimeTable'])){
    
      if(preg_match('/^[1-9][0-9]*$/',$_REQUEST['user_id']) && preg_match('/^[1-9][0-9]*$/',$_REQUEST['time_id'] ))
      {


        if(!empty($_REQUEST['i'])){
          
          $is_valid_input=false;
          $input_time=trim($_REQUEST['time']);
          // Check if a time format is valid.
          if(preg_match('/^[1-9][0-9]{3}-[0-1][0-9]-[0-3][0-9]\s[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/',$input_time) &&
             preg_match('/^[1-9][0-9]*$/',$_REQUEST['time_id']) && preg_match('/^[1-9][0-9]*$/',$_REQUEST['user_id'])&& preg_match('/^(left_work|working)$/',$_REQUEST['state']))
          {
       
          }
        }

        // After the update, a time sheet is displayed.
        $display_user_time_sheet_input_prepare=
        $conn->prepare("SELECT * FROM (SELECT @row_num := @row_num + 1 AS sheet_no, i.* FROM (SELECT `time_id`,`time`,`state`,`occupation_type` FROM `time_sheet` JOIN `occupation` USING (`employee_type_id`) WHERE `user_id` = :user_id AND `time_id`= :time_id) AS i, (SELECT @row_num := 0) AS t) AS total;");

        $display_user_time_sheet_input_prepare->bindValue(':user_id',htmlspecialchars($_REQUEST['user_id'], PDO::PARAM_INT));

        $display_user_time_sheet_input_prepare->bindValue(':time_id',htmlspecialchars($_REQUEST['time_id'], PDO::PARAM_INT));

        if($display_user_time_sheet_input_prepare->execute() < 1)
        {
          echo 'Unknown error';
          exit(1);
        }

        $display_user_time_sheet_input_result=$display_user_time_sheet_input_prepare->fetch();

        // Select all the type of employment to allow users to select in the dropdown menu.
        $select_all_employment_type_prepare=$conn->prepare("SELECT * FROM occupation WHERE NOT employee_type_id = 1;");

        if($select_all_employment_type_prepare->execute() < 1){
          echo 'Unknown error';
          exit(1);
        }

        $select_all_employment_type_result=$select_all_employment_type_prepare->fetchAll();
       
      }


    }
    
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
  <title> Admin Time Management (<?php echo $user_info['first_name']; ?>)</title>
</head>

<link href='/css/bootstrap.min.css?v=1' rel='stylesheet'>
<link href='/css/index.css?v=<?php echo time(); ?>' rel='stylesheet'>
<script src="/script/jquery-3.6.0.min.js"></script>
<script src="/script/popper.js?v=1"></script>
<script src="/script/bootstrap.bundle.min.js?v=1"></script>
<script src="/script/admin_time_sheet_management.js?v=<?php echo time(); ?>"></script>


<?php if(empty($_REQUEST['chageUserTimeTable'])) { ?>
<body>
  <!-- ------------------------------------------- -->
  <!-- Show user's time sheet -------------------- -->
  <!-- ------------------------------------------- -->
  Name: <?php echo $user_info['first_name']." ".$user_info['middle_name']." ".$user_info['last_name'];?>

  <table class="table">
    <thead class="dark-head">
      <tr class="table-head">
        <th>No. </th>
        <th>Time</th>
        <th>Attendance Status</th>
        <th>Occupation</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $i=0;
        foreach ($select_result as $v) {
          $class_tag= $i%2==0 ? "class='grey-table-row'": "";
          $work_status=$v['state']=="working"?"At work":"Already left";
          ?>

          <tr <?php echo $class_tag; ?> >
          <th><?php echo $v['sheet_no'];?></th>
          <th><?php echo $v['time'];?> </th>
          <th><?php echo $work_status;?> </th>
          <th><?php echo $v['occupation_type'];?></th>
          
          <th>
            <button type="button" class="btn btn-success"
            onclick="chageUserTimeDetail(<?php echo $_REQUEST['user_id'].','.$v['time_id'];?>);"
            >Change detail
            </button>
            &nbsp;&nbsp;&nbsp;
            
            <button type="button" class="btn btn-success"
            onclick="deleteUserTimeDetail(<?php echo $_REQUEST['user_id'].','.$v['time_id'];?>)">
            Delete
            </button>

          </th>
          </tr>
          
       <?php $i++; } ?>
    </tbody>
  </table>

  <form action="/admin/admin_time_sheet_management.php" id="adminForm" method="POST">
    <button type="submit" name="i" value="logout">Admin logout</button>

    <button type="submit" 
    name="i" num_selection_output
    value=<?php echo($user_info['state']=='left_work' ? '"working"' : '"left_work"'); ?>
    
    >Insert new record</button>
    
    <input class="span2" id="t" name="t" type="hidden" 
    value='<?php echo $select_num_output; ?>'>
    
    <input class="span2" id="n" name="n" type="hidden"
    value='<?php echo $num_selection_output?>'>
    
    <input class="span2" id="user_id" name="user_id" type="hidden"
    value='<?php echo $_REQUEST['user_id'];?>'>
    
    <input class="span2" id="chageUserTimeTable" name="chageUserTimeTable" type="hidden" value='<?php echo $_REQUEST['chageUserTimeTable'];?>'>
    
    <input class="span2" id="time_id" name="time_id" type="hidden" type="hidden"
    value='<?php echo $_REQUEST['time_id']?>'>

    <!-- Pull down menu -->
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php
          echo $select_num_output;
        ?>
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <li class="dropdown-item"  onclick="$('#t').val(10);$('#adminForm').submit();" value='10'>10</a>
        <li class="dropdown-item"  onclick="$('#t').val(25);$('#adminForm').submit();"value='25'>25</a>
        <li class="dropdown-item"  onclick="$('#t').val(50);$('#adminForm').submit();"value='50'>50</a>
        <li class="dropdown-item"  onclick="$('#t').val(100);$('#adminForm').submit();"value='100'>100</a>
      </ul>
    </div>

    <nav aria-label="Page navigation example">
      <ul class="pagination">
      <?php generate_previous_next_button($select_min,$select_max,$num_selection_output_tmp,'#adminForm');?>
      </ul>
    </nav>
  </form>

</body>

<?php } ?>

<?php if(!empty($_REQUEST['chageUserTimeTable'])) { ?>
  
<!-- ------------------------------------------- -->
<!-- Adjust user's time sheet -------------------- -->
<!-- ------------------------------------------- -->
<div class="container align-items-center">
  <form id ="adminForm" method="POST"  
  action="/admin/admin_time_sheet_management.php" 
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8">

    <input class="span2" id="user_id" name="user_id" type="hidden"
    value='<?php echo $_REQUEST['user_id'];?>'>

    <input class="span2" id="chageUserTimeTable" name="chageUserTimeTable" type="hidden" 
    value='<?php echo $_REQUEST['chageUserTimeTable'];?>'>

    <input class="span2" id="time_id" name="time_id" type="hidden"
    value='<?php echo $_REQUEST['time_id'];?>'>

    <input class="span2" id="state" name="state" type="hidden" 
    value='<?php echo $display_user_time_sheet_input_result['state'];?>'>

    <input class="span2" id="i" name="i" type="hidden"
    value=''>

    <div class="form-group  col-sm-10">
      <span class='time-display'>Time ID: <?php echo $display_user_time_sheet_input_result['time_id'];?></span> 
      <br><br>

      <label for="time">Time</label>
      <input type="text" id="time" name="time" class="form-control" 
      value="<?php echo $display_user_time_sheet_input_result['time'];?>" 
      placeholder="*Required (Current recorded time: <?php echo $display_user_time_sheet_input_result['time'] ?>)">

      <hr/>
      <!-- Pull down menu -->
      Employee Status:&nbsp; <span class="dropdown">
        <button id="stateSelect"class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?php
            echo $display_user_time_sheet_input_result['state']=='left_work' ? 'Left Work' : 'Working';
          ?>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li class="dropdown-item"  
          onclick="$('#state').val('left_work');$('#stateSelect').text('Left Work');">Left Work</li>
          <li class="dropdown-item"  
          onclick="$('#state').val('working');$('#stateSelect').text('Working');">Working</li>
        </ul>
      </span>

      <hr/>
    </div>

    

    <div class="form-group  col-sm-10">
    Types of employment:&nbsp; <span class="dropdown">
        <button id="stateSelect"class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?php
            echo $display_user_time_sheet_input_result['state']=='left_work' ? 'Left Work' : 'Working';
          ?>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li class="dropdown-item"  
          onclick="$('#state').val('left_work');$('#stateSelect').text('Left Work');">Left Work</li>
          <li class="dropdown-item"  
          onclick="$('#state').val('working');$('#stateSelect').text('Working');">Working</li>
        </ul>
      </span>
    </div>    
    
    <div class="col-sm-10">
      <hr/>
    </div>
    <br><br><br>

    <div  class="d-flex justify-content-center">
      <button type="button" onclick="changeUserTimeDetailSubmit()" class="btn btn-success">Change Time & Detail</button>
    </div>
    <div  class="d-flex justify-content-center">
      <button type="button" onclick="$('#chageUserTimeTable').val('');$('#adminForm').submit();">Return to previous screen</button>
    </div>
    <div  class="d-flex justify-content-center">
      <span id="errorMessage" class='error-message'></span>
    </div>

  </form>
</div>


<?php } ?>

