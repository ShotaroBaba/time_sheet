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
  $is_input_complete=false;
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
  // try {
    
    // Connect to the database first.
    $conn= new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", 
    $_SESSION['admin_user_name'], $_SESSION['admin_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Only a number(s) for 't' and 'n' inputs is allowed.
    if(
      (!empty($_REQUEST['t']) && !is_numeric($_REQUEST['t'])) ||
      (!empty($_REQUEST['n']) && !is_numeric($_REQUEST['n']))
    ){
      echo "Unknown error.";
      exit(1);
    }


    // ********************************
    // Calculate and output user's income.
    // ********************************
    if(preg_match('/^[1-9][0-9]*$/',$_REQUEST['user_id'])){
      $calculate_user_income_prepare=
      $conn->prepare("SELECT *,wage*diff_time/3600 AS income FROM (SELECT start_time, end_time,TIMESTAMPDIFF(SECOND,start_time,end_time) AS diff_time, start_employee_type_id AS employee_type_id,id_diff,next_row_id FROM 
      (SELECT `_tmp_result1`.time AS start_time, `_tmp_result2`.time AS end_time, `_tmp_result1`.employee_type_id AS start_employee_type_id, `_tmp_result2`.employee_type_id AS end_employee_type_id, next_row_id, `_tmp_result1`.curr_row_id, (_tmp_result2.`next_row_id` - _tmp_result1.`curr_row_id`) AS id_diff FROM (SELECT i.*, (@row_num:=@row_num+1) AS curr_row_id FROM 
      -- The below user id can be selected by :user_id.
      (SELECT * FROM `time_sheet`WHERE user_id = :user_id ORDER BY `time`) AS i, 
      (SELECT @row_num:=0) AS tmp_num, 
      (SELECT @next_row_num:=1) AS next_tmp_num) AS _tmp_result1 
      INNER JOIN (SELECT i.*, (@next_row_num:=@next_row_num+1) AS next_row_id FROM 
          (SELECT * FROM `time_sheet` WHERE user_id = :user_id ORDER BY `time` LIMIT 1,1000000) AS i, 
          (SELECT @row_num:=0) AS tmp_num, 
          (SELECT @next_row_num:=1) AS next_tmp_num) AS _tmp_result2 WHERE _tmp_result1.state='working' AND _tmp_result2.state='left_work' AND next_row_id - curr_row_id = 1) AS _tmp_result3 WHERE 
          -- Removing inconsistent record; An occuaption at the start and an occupation at the end is different.
          start_employee_type_id=end_employee_type_id) AS working_time JOIN occupation USING (employee_type_id) ORDER BY start_time;
          ");

      $calculate_user_income_prepare->bindValue(":user_id", htmlspecialchars($_REQUEST['user_id']), PDO::PARAM_INT);
      if($calculate_user_income_prepare->execute() < 1){
        echo "Unknown error.";
        exit(1);
      }

      $calculate_user_income_result=$calculate_user_income_prepare->fetchAll();
  
      $total_income_record=count($calculate_user_income_result);


      // Injection attack prevention measure.
      $select_num_output=$_REQUEST['t'] == '' || is_null($_REQUEST['t']) ? 10 : htmlspecialchars($_REQUEST['t']);
      $num_selection_output=$_REQUEST['n']== '' || is_null($_REQUEST['n']) ? 1 : htmlspecialchars($_REQUEST['n']);

      // Set the limit of selectiissue_dateon.
      $select_min=1;
      $select_max=intdiv($total_income_record,$select_num_output);
      if($total_income_record/($select_num_output*$select_max) > 1 || $select_max ==0){
        $select_max+=1;
      }

      $num_selection_output_tmp=$num_selection_output;

      // Prevent re-setting the number selection.
      if($num_selection_output>$select_max){
        $num_selection_output_tmp=$select_max;
      }
      
      $sliced_income_array=array_slice($calculate_user_income_result,$select_num_output*($num_selection_output_tmp-1),$select_num_output);

      
      // print_r($calculate_user_income_result);
    }
    
    
    $total_salary=array_sum(array_map(function($k){
      return $k['income'];
    },$calculate_user_income_result));

    // Finally reset cookie lifetime.
    $_SESSION['expireAfter']=time()+$user_login_expiration_time;

    
  // }

  // catch(Exception $e)  {
  //   
  //   echo "Unknown error.";
  //   exit(1); 
  // }

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
  <title> Admin Salary Management</title>
</head>

<link href='/css/bootstrap.min.css?v=1' rel='stylesheet'>
<link href='/css/index.css?v=<?php echo time(); ?>' rel='stylesheet'>
<script src="/script/jquery-3.6.0.min.js"></script>
<script src="/script/popper.js?v=1"></script>
<script src="/script/bootstrap.bundle.min.js?v=1"></script>
<script src="/script/admin_time_sheet_management.js?v=<?php echo time(); ?>"></script>

<body>

<form action="/admin/admin_salary_display.php" 
  id="adminForm" 
  method="POST"
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8">

  <input class="span2" id="t" name="t" type="hidden" 
  value='<?php echo $select_num_output; ?>'>
    
  <input class="span2" id="n" name="n" type="hidden"
  value='<?php echo $num_selection_output?>'>

  <input class="span2" id="user_id" name="user_id" type="hidden"
  value='<?php echo $_REQUEST['user_id']; ?>'>

    <!-- ------------------------------------------- -->
    <!-- Show user's time sheet -------------------- -->
    <!-- ------------------------------------------- -->

    <table class="table">
      <thead class="dark-head">
        <tr class="table-head">
          <th>Start Time</th>
          <th>End Time</th>
          <th>Occupation</th>
          <th>Working Time</th>
          <th>Income</th>  
        </tr>
      </thead>

      <tbody>
        <?php 
          $i=0;
          foreach ($sliced_income_array as $v) {
            $class_tag= $i%2==0 ? "class='grey-table-row'": "";
            ?>

            <tr <?php echo $class_tag; ?> >
            <th><?php echo $v['start_time'];?> </th>
            <th><?php echo $v['end_time'];?> </th>
            <th><?php echo $v['occupation_type'];?></th>
            <th><?php echo $v['time_diff']/3600;?> </th>
            <th><?php echo number_format($v['income'],2)." yen";?></th>
            
            </tr>
            
        <?php $i++; } ?>
      </tbody>
    </table>
    
    <div>
      <button type="button" onclick='changeToTimeSheetPage(<?php echo $_REQUEST["user_id"];?>)'>
        Return to time sheet
      </button>
    </div>

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
    
    <div>
    Total Income: <?php echo number_format($total_salary,2)." yen"; ?>
    </div>
    <div>
    Total Count: <?php echo number_format($total_income_record);?>
    </div>
  </form>

</body>