<?php 


  // ******************************
  // Set initial values
  // ******************************

  $is_valid_input=NULL;
  $is_empty_input=NULL;
  $is_input_complete=false;
  $is_delete_complete=false;
  $is_input_duplicate=false;
  $a_select_result=NULL;

  header("Content-Type: text/html;charset=UTF-8");
  error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
  
  include "/var/www/html/.secret/.config.php";
  include "/var/www/html/plugin/create_next_previous_button.php";
  session_name('admin_cookie');
  session_start([
    'sid_length' => 128
  ]);
  session_regenerate_id(true);

  if((!empty($_GET['alter_occupation']) && strlen($_GET['alter_occupation']) > 1) || 
  (!empty($_GET['insert_occupation']) && strlen($_GET['insert_occupation']) > 1) ||
  (!empty($_GET['delete_occupation'])) && strlen($_GET['delete_occupation']) > 1){
    echo "Illegal URI variable(s) detected. Aborting.";
    session_unset();
    session_destroy();
    header("Location: /");
    exit(0);
  }

  try {
    if(isset($_SESSION['expireAfter']) & time() > $_SESSION['expireAfter']){
      session_unset();
      session_destroy();
      header('Location: /');
      exit(0);
    }
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

    // Make a connection first.
    $conn= new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", 
    $_SESSION['admin_user_name'], $_SESSION['admin_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // *************************************
    // Occupation View
    //**************************************

    // Delete the element first if a element is not empty.
    if(!empty($_GET['delete_occupation'])){
      
      // Validate employee_type_id input
      if(preg_match('/^[1-9][0-9]*$/',$_GET['employee_type_id'])) {

        $delete_occupation_prepare=$conn->
        prepare('DELETE FROM `occupation` WHERE `employee_type_id` = :employee_type_id');

        $delete_occupation_prepare->bindValue(':employee_type_id',
        htmlspecialchars($_GET['employee_type_id']),
        PDO::PARAM_INT);
        
        if($delete_occupation_prepare->execute()<1) {
          echo "Unkonwn error";
          exit(1);
        } 
        
        // Set true once all deletion progress has completed.
        $is_delete_complete=true;

      }

    }

    if(empty($_GET['insert_occupation']) && 
    empty($_GET['alter_occupation'])){
      
      $total_occupation_num=$conn->prepare("SELECT COUNT(*) AS total_occupation_num FROM occupation WHERE NOT employee_type_id = 1;");

      $total_occupation_num->bindValue(":_user_id", htmlspecialchars($_SESSION['employeeUserID']), PDO::PARAM_INT);

      if($total_occupation_num->execute() < 1){
        echo "Unknown error.";
        exit(1);
      }

      $total_result=$total_occupation_num->fetch();

      // Injection attack prevention measure.
      $select_num_output=$_GET['t'] == '' || is_null($_GET['t']) ? 10 : htmlspecialchars($_GET['t']);
      $num_selection_output=$_GET['n']== '' || is_null($_GET['n']) ? 1 : htmlspecialchars($_GET['n']);

      // Set the limit of selection.
      $select_min=1;
      $select_max=intdiv($total_result['total_occupation_num'],$select_num_output);
      if($total_result['total_occupation_num']/($select_num_output*$select_max) > 1 || $select_max ==0){
        $select_max+=1;
      }
      
      $num_selection_output_tmp=$num_selection_output;
      // Prevent re-setting the number selection.
      if($num_selection_output>$select_max){
        $num_selection_output_tmp=$select_max;
      }

      $ocupation_selection_prepare=
      $conn->prepare('SELECT * FROM occupation WHERE NOT employee_type_id = 1;');

      if($ocupation_selection_prepare->execute()<1){
          echo "Unknown error";
          exit(1);
      }
      
      $occupation_selection_result=$ocupation_selection_prepare->fetchAll(); 
    
    }
    
    // *******************************
    // End of Occupation
    // *******************************
    



    // *************************************
    // Occpation Insert Process
    //**************************************
    if(!empty($_GET['i']) && !empty($_GET['insert_occupation'])) {
      
      // Check if a value is empty.
      if(!empty($_GET['occupationName']) && !empty($_GET['wage'])) {
        
        $is_empty_input=false;
        if(preg_match('/^[a-zA-Z0-9,\.\-\s\(\)]{1,}$/',$_GET['occupationName']) &&
        preg_match('/^[1-9][0-9]*$/',$_GET['wage'])) {
          
          $is_valid_input=true;
          
          // If both inputs are valid and non-empty, then insert data into occupation table.
          $insert_new_occupation_prepare=
          $conn->prepare('INSERT INTO `occupation` (`occupation_type`,`issue_time`,`wage`) 
          VALUES (:occupation_type,SYSDATE(6),:wage)');
          
          $insert_new_occupation_prepare->bindValue(':occupation_type',htmlspecialchars($_GET['occupationName']),PDO::PARAM_STR);
          $insert_new_occupation_prepare->bindValue(':wage',htmlspecialchars($_GET['wage']),PDO::PARAM_INT);
          
          if($insert_new_occupation_prepare->execute() < 1){
            echo "Unknown error";
            exit(1);
          }

          $is_input_complete=true;
          
        }
        else{ 
          $is_valid_input=false;
        }
      }
      else {
        $is_empty_input=true;
      }
    }
    // ******************************
    // End of Occupation Insert *****
    // ******************************





    // **************************************
    // Occupation Alteration View ***********
    // **************************************
    if(!empty($_GET['alter_occupation'])) {

        
      // Check if a value is empty.
      if(!empty($_GET['i']) && 
      !empty($_GET['occupationName']) && 
      !empty($_GET['wage'])) {
              
        $is_empty_input=false;
        if(preg_match('/^[a-zA-Z0-9,\.\-\s\(\)]{1,}$/',$_GET['occupationName']) &&
        preg_match('/^[1-9][0-9]*$/',$_GET['wage']) &&
        preg_match('/^[1-9][0-9]*$/',$_GET['employee_type_id'])) {
          
          $is_valid_input=true;
          
          // If both inputs are valid and non-empty, then insert data into occupation table.
          $update_occupation_prepare=
          $conn->prepare('UPDATE `occupation`
          SET `occupation_type` = :occupation_type,
          `wage` = :wage
          WHERE `employee_type_id` = :employee_type_id;');
          
          $update_occupation_prepare->bindValue(':occupation_type',htmlspecialchars($_GET['occupationName']),PDO::PARAM_STR);

          $update_occupation_prepare->bindValue(':wage',htmlspecialchars($_GET['wage']),PDO::PARAM_INT);
          
          $update_occupation_prepare->bindValue(':employee_type_id',htmlspecialchars($_GET['employee_type_id']),PDO::PARAM_INT);

          if($update_occupation_prepare->execute() < 1){
            echo "Unknown error";
            exit(1);
          }

          $is_input_complete=true;
          
        }
        else{ 
          $is_valid_input=false;
        }
      }

      if(preg_match('/^[1-9][0-9]*$/',$_GET['employee_type_id'])){

        $select_occupation_prepare=
        $conn->prepare('SELECT `occupation_type`,`wage` 
        FROM occupation WHERE `employee_type_id` = :employee_type_id');
        
        $select_occupation_prepare->bindValue(':employee_type_id',
        htmlspecialchars($_GET['employee_type_id']),
        PDO::PARAM_INT);

        if($select_occupation_prepare->execute() < 1){
          echo "Unknown error";
          exit(1);
        }

        $a_select_result=$select_occupation_prepare->fetch();
      }  
      
    }

    // *******************************************
    // End of Occupation Alteration
    // *******************************************
    
    // Add time to session cookie if everything finishes.
    $_SESSION['expireAfter']=time()+$user_login_expiration_time;

  }

  catch (PDOException $e) {
    // Dupclication error.
    if($e->getCode() == 23000 ){
      $is_input_duplicate=true;
    }
    else {
      echo 'Unknown error';
      exit(1);
    }
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Occupation Manager</title>

  <link href='/css/bootstrap.min.css?v=1' rel='stylesheet'>
  <link href='/css/index.css?v=<?php echo time(); ?>' rel='stylesheet'>
  <script src="/script/jquery-3.6.0.min.js"></script>
  <script src="/script/popper.js?v=1"></script>
  <script src="/script/bootstrap.bundle.min.js?v=1"></script>
  <script src="/script/admin_occupation_management.js?v=<?php echo time(); ?>"></script>
  <script src="/script/submit_func.js?v=<?php echo time(); ?>"></script>

</head>



<?php if(empty($_GET['alter_occupation']) &&
        empty($_GET['insert_occupation'])){ ?>
<!-- Occupation View. -->
<body>
  <form id='adminForm'
  method="GET" 
  enctype="multipart/form-data" 
  accept-charset="UTF-8">

    <input type="hidden" 
    name="t" 
    id="t" 
    value="<?php echo $select_num_output;?>">
    
    <input 
    type="hidden" 
    name="n" 
    id="n" 
    value="<?php echo $num_selection_output;?>">

    <input type="hidden" 
    id="insert_occupation" 
    name="insert_occupation"
    value="<?php echo htmlspecialchars($_GET['insert_occupation']); ?>"
    >
    
    <input type="hidden" 
    id="employee_type_id"
    name="employee_type_id"echo
    value="<?php echo htmlspecialchars($_GET['employee_type_id']); ?>";>

    <input 
    type="hidden" 
    id="alter_occupation" 
    name="alter_occupation"
    value="<?php echo htmlspecialchars($_GET['alter_occupation']);?>">

    <input type="hidden" 
    id="delete_occupation"
    name="delete_occupation"
    value="<?php echo htmlspecialchars($_GET['delete_occupation']);?>">

  <table class="table">
    <thead>
      <tr>
        <th>Occupation ID</th>
        <th>Occupation Type</th>
        <th>Occupation Issued Time</th>
        <th>Wage Per Hour</th>
        <th></th>
        <th></th>   
      </tr>
    </thead>
    <tbody>
      <!-- ******************************** -->
      <!-- Start table contents************ -->
      <!-- ******************************** -->
      <?php 
        $i=0;
        foreach($occupation_selection_result as $v) {
          $class_tag=$i%2==0?" class='grey-table-row'":"";
          echo "<tr".$class_tag.">";
          echo "<th>".$v['employee_type_id']."</th>";
          echo "<th id='occupation_type_".$v['employee_type_id']."'>".$v['occupation_type']."</th>";
          echo "<th>".$v['issue_time']."</th>";
          echo "<th id='wage_".$v['employee_type_id']."'>".$v['wage']."</th>";

          echo "<th><button type='button' 
          class='btn btn-primary'
          onclick='changeOccupation(".$v['employee_type_id'].");'>
          Change Detail
          </button></th>";
          
          echo "<th><button 
          type='button'
          class='btn btn-primary'
          onclick='deleteOccupation(".$v['employee_type_id'].");'>
          Delete</button></th>";
          
          echo "</tr>";
          $i++;
          }
      ?>
      <!-- ******************************** -->
      <!-- ********* End of table ********* -->
      <!-- ******************************** -->
    </tbody>
  </table>




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
  
  <button type="submit" class="btn btn-primary" onclick="
    $('#insert_occupation').val('t');
    $('#n').val('');$('#t').val('');
    submitValues('#t','#i','#n','#insert_occupation','#alter_occupation','#delete_occupation');">Add occupation</button>
  <button type="button" class="btn btn-primary" onclick="window.location='/admin/admin_user_management.php'">Return to user management</button>
  
  <nav aria-label="Page navigation example">
    <ul class="pagination">
    <?php 
    generate_previous_next_button($select_min,$select_max,$num_selection_output_tmp);?>
    </ul>
  </nav>
  <?php if($is_delete_complete){ ?>

    <span class="complete-message">
      The occupation deletion completed.
    </span>
  
    <script type='text/javascript'>
      
      // Change URI content.
      window.history.replaceState(NULL, 
      'Occupation Manager', 
      '/admin/occupation_management.php');
    </script>
  <?php }  ?>
  
  
  </form>

</body>
</html>

<?php } ?>



<?php if(!empty($_GET['insert_occupation'])) {?>
<!-- --------------------------------------------- -->
<!-- Insert occupation  view --------------------- -->
<!-- --------------------------------------------- -->
<body>

<div class="container align-items-center">
  <form id ="userInputMain" method="GET"  
  action="/admin/occupation_management.php" 
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8">
    
    <input type="hidden" 
    id="insert_occupation" 
    name ="insert_occupation" 
    value="<?php echo $_GET['insert_occupation'];?>">
    
    <input type="hidden" 
    id="i"
    name="i"
    value="<?php echo $_GET['i'];?>">

    <div class="form-group  col-sm-10">
      <label for="occupationName">Occupation Name</label>
      <input type="text" id="occupationName" 
      name="occupationName" 
      class="form-control" 
      placeholder="*Required (Cannot use the same occupation name)"
      value="<?php echo htmlspecialchars($_GET['occupationName']);?>">
    </div>
    <br><br><br>
    <div class="col-sm-10">
    </div>
    <div class="form-group  col-sm-10">
      <label for="wage">Wage</label>
      <input type="text" id="wage" name="wage" 
      class="form-control" 
      placeholder="*Required"
      value="<?php echo $_GET['wage'];?>">
    </div>
    <br><br><br>
    <div class="form-group col-sm-10">
      <hr/>
    </div>
    <br><br><br>

  <div class="d-flex justify-content-center">
    <button type="submit" 
    class="btn btn-primary" 
    onclick="$('#i').val('t');submitValues('#wage','#occupationName','#i','#insert_occupation');
    $('#userInputMain').submit();">
    Submit
    </button>  
    <button type="button" class="btn btn-primary" onclick="window.location='/admin/occupation_management.php'">Return to occupation view</button>
    
    <div id="errorMessage" class='<?php
    if($is_input_complete){
      echo "complete-message";
    }
    else{
      echo "error-message";
    }
    ?>'>

    <?php if(!$is_valid_input && !is_null($is_valid_input)){ 
      echo "Invalid occupation information input.";
    }
    else if($is_empty_input && !is_null($is_empty_input)){
      echo "Either form is empty input.";
    }
    else if($is_input_duplicate && !is_null($is_input_duplicate)){
      echo "The input occupation name already exists.";
    }
    else if($is_input_complete) {
      echo "Occupation info has successfully been input.";
      echo "<script type='text/javascript'>
      
      // Removing user's input
      
      removeUserInput();
      // Change browser's URI display.

      window.history.replaceState(NULL, 'Occupation Manager', '/admin/occupation_management.php?insert_occupation=t');
      </script>";
    }
    ?>
    
    </div>
  </div>
  </form>
</div>
</body>
<?php } ?>



<?php if((!empty($_GET['alter_occupation']))) {?>
<!-- --------------------------------------------- -->
<!-- Alter occupation  view --------------------- -->
<!-- --------------------------------------------- -->
<body>
  <form id ="userInputMain" method="GET"  
  action="/admin/occupation_management.php" 
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8">
  
    <input type="hidden" 
    id="alter_occupation" 
    name ="alter_occupation" 
    value="<?php echo $_GET['alter_occupation'];?>">
    
    <input type="hidden" 
    id="i"
    name="i"
    value="<?php echo $_GET['i'];?>">

    <input type="hidden" 
    id="employee_type_id"
    name="employee_type_id"
    value="<?php echo $_GET['employee_type_id'];?>">

    <div class="form-group  col-sm-10">
      <label for="occupationName">Occupation Name</label>
      <input type="text" id="occupationName" 
      name="occupationName" 
      class="form-control" 
      placeholder="*Required (Current occupation name: <?php echo($a_select_result['occupation_type'])?>)"
      value="">
    </div>
    <br><br><br>
    <div class="col-sm-10">
    </div>
    <div class="form-group  col-sm-10">
      <label for="wage">wage</label>
      <input type="text" id="wage" name="wage" 
      class="form-control" 
      placeholder="*Required (Current wage: <?php echo($a_select_result['wage'])?>)"
      value="">
    </div>
    <br><br><br>
    <div class="form-group col-sm-10">
      <hr/>
    </div>
    <br><br><br>

    <div class="d-flex justify-content-center">
      <button type="submit" 
      class="btn btn-primary" 
      onclick="
      $('#i').val('t');
      submitValues('#i','#alter_occupation', '#employee_type_id');
      $('#userInputMain').submit();">
      Change Occupation Detail
      </button>  
      &nbsp;&nbsp;&nbsp;
      <button type="button" class="btn btn-primary" 
      onclick="window.location='/admin/occupation_management.php'">Return to occupation view</button>
    

      <span id="errorMessage" class='<?php
      if($is_input_complete){
        echo "complete-message";
      }
      else{
        echo "error-message";
      }
      ?>'>

      <?php 
      
        if(!$is_valid_input && !is_null($is_valid_input)){ 
          echo "Invalid occupation information input.";
        }
        else if($is_empty_input && !is_null($is_empty_input)){
          echo "Either form is empty input.";
        }
        else if($is_input_complete) {
          echo "Occupation info has successfully been input.";
          echo "<script type='text/javascript'>
          
          // Removing user's input
          
          removeUserInput();
          // Change browser's URI display.

          window.history.replaceState(NULL, 'Occupation Manager', '/admin/occupation_management.php?alter_occupation=t&employee_type_id=".htmlspecialchars($_GET['employee_type_id']).");
          </script>";
        }

      ?>
      </span>
    </div>
  </form>

</body>
<?php } ?>