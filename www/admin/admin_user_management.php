<?php 
  header("Content-Type: text/html;charset=UTF-8");
  error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
  
  include "/var/www/html/.secret/.config.php";

  session_name('admin_cookie');
  session_start([
    'sid_length' => 128
  ]);
  
  $is_valid_input=NULL;
  $is_delete_complete=false;

  // Change session ID to prevent session hijacking.
  session_regenerate_id(true);

  try {
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
      session_destroy();    // ******************************
      // Display User table
      // ******************************
      header('Location: /');
      exit(0);
    }

    $conn= new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", 
    $_SESSION['admin_user_name'], $_SESSION['admin_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    


    // *******************************
    // Delete User Part
    // *******************************

    if(!empty($_REQUEST['delete_user_detail'])){

      if(preg_match('/^[1-9][0-9]*$/',$_REQUEST['user_id'])){
        $delete_user_prepare=$conn->prepare("DELETE FROM user WHERE user_id=:user_id;");
        
        $delete_user_prepare->bindValue(":user_id",
        htmlspecialchars($_REQUEST['user_id']),
        PDO::PARAM_INT);

        if($delete_user_prepare->execute() < 1){
          echo "Unknown error";
          exit(1);
        }
        $is_delete_complete=true;

      }
    }

    // ******************************
    // Delete User Part End
    // *******************************
    

    // ******************************
    // Display User table
    // ******************************
    
    if(empty($_REQUEST['change_user_detail'])){

      $total_user_num=$conn->prepare("SELECT COUNT(*) FROM user;");

      if($total_user_num->execute() < 1){
        echo "Unknown error.";
        exit(1);
      }

      $total_result=$total_user_num->fetch();

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


      $user_selection_prepare=$conn->prepare('SELECT * FROM user JOIN occupation USING (employee_type_id);');

      if($user_selection_prepare->execute()<1){
          echo "Unknown error";
          exit(1);
      }


      $user_selection_result=$user_selection_prepare->fetchAll();
      
    }
    
    // ******************************
    // Display User table End
    // ******************************


    // ********************************************
    // Employee Detail Change 
    // ********************************************

    if(!empty($_REQUEST['change_user_detail']) && !empty($_REQUEST['user_id'])){
      
      if(!empty($_REQUEST['i'])){
        
        if(preg_match('/^[1-9][0-9]*$/',$_REQUEST['user_id']) &&
        preg_match('/^.+$/',$_REQUEST['firstName']) &&
        preg_match('/^.*$/',$_REQUEST['middleName']) &&
        preg_match('/^.+$/',$_REQUEST['lastName']) &&
        preg_match('/^[0-9\-]+$/',$_REQUEST['phoneNumber']) &&
        preg_match('/^.+$/',$_REQUEST['address']) &&
        preg_match('/^.+$/',$_REQUEST['email']) && 
        preg_match('/^[1-9][0-9]+$/',$_REQUEST['employee_type_id']))
        {
        
          $is_valid_input=true;
        
          $change_user_detail_prepare=$conn->prepare("UPDATE user 
          SET `first_name` = :first_name,
          `middle_name` = :middle_name,
          `last_name` = :last_name,
          `phone_number`=:phone_number,
          `address` = :_address,
          `email` = :email,
          `employee_type_id`= :employee_type_id WHERE
          user_id=:user_id");
          
          ////////////////////////////////////
          // Bind values 
          ////////////////////////////////////
          $change_user_detail_prepare->bindValue(":first_name",
          htmlspecialchars($_REQUEST['firstName']),
          PDO::PARAM_STR);

          $change_user_detail_prepare->bindValue(":middle_name",
          htmlspecialchars($_REQUEST['middleName']),
          PDO::PARAM_STR);
          
          $change_user_detail_prepare->bindValue(":last_name",
          htmlspecialchars($_REQUEST['lastName']),
          PDO::PARAM_STR);
          
          $change_user_detail_prepare->bindValue(":phone_number",
          htmlspecialchars($_REQUEST['phoneNumber']),
          PDO::PARAM_STR);

          $change_user_detail_prepare->bindValue(":_address",
          htmlspecialchars($_REQUEST['address']),
          PDO::PARAM_STR);

          $change_user_detail_prepare->bindValue(":email", 
          htmlspecialchars($_REQUEST['email']), 
          PDO::PARAM_STR);

          $change_user_detail_prepare->bindValue(":user_id",
          htmlspecialchars($_REQUEST['user_id']), 
          PDO::PARAM_INT);
          
          $change_user_detail_prepare->bindValue(":employee_type_id",
          htmlspecialchars($_REQUEST['employee_type_id']),
          PDO::PARAM_INT);
          
          ////////////////////////////////////
          // End of substitution
          ////////////////////////////////////

          if($change_user_detail_prepare->execute()<1){
            echo 'Unknown error';
            exit(1);
          }

          $is_input_complete=true;

        }
        
        else {
          $is_valid_input=false;
        }
        
      }

      if(preg_match('/^[1-9][0-9]*$/',$_REQUEST['user_id'])
      ){
        
        // Select all oocupations and its ids to display
        $select_all_occupations_prepare=$conn->prepare("SELECT * FROM occupation WHERE NOT employee_type_id = 1;");

        if($select_all_occupations_prepare->execute() < 1){
          echo 'Unknown error';
          exit(1);
        }

        $select_all_occupations_result=$select_all_occupations_prepare->fetchAll();

        // Select all users with their occupations.
        $select_user_prepare=$conn->prepare('SELECT * FROM user JOIN occupation USING  (`employee_type_id`)  WHERE user_id = :user_id');
        $select_user_prepare->bindValue(':user_id',htmlspecialchars($_REQUEST['user_id']),PDO::PARAM_INT);
      
        if($select_user_prepare->execute()<1) {
          echo "Unknown error";
          exit(1);
        }

        $select_user_result=$select_user_prepare->fetch();

      }

    }
    

    // *********************************************
    // Employee Detail Change End
    // *********************************************
    

    $_SESSION['expireAfter']=time()+$user_login_expiration_time;  

  }
  catch (PDOException $e) {
    echo 'Unknown error';
    exit(0);
  }

?>

<!-- Admin can check all time sheets of employees. -->
<!-- TODO: Create an actual user list that is shown on this web page. -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Employee Manager</title>
  <link href='/css/bootstrap.min.css?v=1' rel='stylesheet'>
  <link href='/css/index.css?v=<?php echo time(); ?>' rel='stylesheet'>
  <script src="/script/jquery-3.6.0.min.js"></script>
  <script src="/script/popper.js?v=<?php echo time(); ?>"></script>
  <script src="/script/bootstrap.bundle.min.js?v=<?php echo time(); ?>"></script>
  <script src="/script/submit_func.js?v=<?php echo time(); ?>"></script>
  <script src="/script/admin_user_management.js?v=<?php echo time(); ?>"></script>
</head>


<?php if(empty($_REQUEST['change_user_detail'])) { ?>
<body>
  <form id="adminForm"
  method="POST"
  enctype="multipart/form-data"
  accept-charset="UTF-8">

    <input type="hidden" 
    id="change_user_detail"
    name="change_user_detail"
    value="<?php echo $_REQUEST['change_user_detail'];?>">

    <input type="hidden"
    id="delete_user_detail"
    name="delete_user_detail"
    value="<?php echo $_REQUEST['delete_user_detail'];?>">

    <input type="hidden"
    id='user_id'
    name=user_id
    value=''>


    <table class="table">
      <thead>
        <th>User ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Phone Number</th>
        <th>Email</th>
        <th>Address</th>
        <th>State</th>
        <th>Occupation</th>
        <th></th>
        <th></th>
        <th></th>
        
      </thead>
      <tbody>
      <?php 
        $i=0;
        foreach ($user_selection_result as $v) {
          $class_tag=$i%2==0?" class='grey-table-row'":""; ?>
           <tr <?php echo $class_tag; ?>>
          <th><?php echo $v['user_id']?></th>
           <th id="firstName_<?php  $v['user_id']?>"><?php echo $v['first_name']?></th>
           <th id="lastName_<?php echo $v['user_id']; ?>"><?php echo $v['last_name'];?></th>
           <th><?php echo($v['phone_number']);?></th>
           <th><?php echo($v['email']);?></th>
           <th><?php echo($v['address']);?></th>
           <th><?php echo($v['state']);?></th>
           <th><?php echo($v['occupation_type']);?></th>


          <th><button type='button' 
          class='btn btn-success button-size-small'
          id='user_<?php echo $v['user_id'];?>' 
          onclick='changeUserDetail(<?php echo $v["user_id"]; ?>)'
          >Change Detail</button></th>
          

          <th><button type='button' 
          class='btn btn-success'
          id='user_<?php echo $v['user_id'];?>' 
          onclick='deleteUserDetail(<?php echo $v["user_id"];?>)'
          >Delete</button></th>
        
          <th>

          <button type='button'
          class='btn btn-success'
          id='user_.<?php echo $v["user_id"];?>'
          onclick='showUserTimeTable(<?php echo $v["user_id"];?>)'
          >Time Sheet
          </button>
              
          </th>
          </tr>

          
       <?php $i++; } ?>


      </tbody> 
    </table>   

    <button type="button" class="btn btn-success" onclick="window.location='/admin/occupation_management.php'">
      Change to occupation manager
    </button>
    
    <?php if($is_delete_complete){ ?>
    <span id="errorMessage" class='complete-message'> 
      User input deletion completed.
    </span>
    <?php } ?>

  </form>
  
  <?php if($is_delete_complete){ ?>
    <script type='text/javascript'>    
      window.history.replaceState(NULL, 'Occupation Manager', '/admin/admin_user_management.php');
    </script>";
  <?php } ?>

</body>
<?php } ?>

<?php if(!empty($_REQUEST['change_user_detail'])){ ?>
<body>
<!-- ------------------------------------------ -->
<!-- Change User Detail ---------------------- -->
<!-- ----------------------------------------- -->

<!-- Change GET method to POSt method to stop the leak of private info -->
  <form id ="adminForm" method="POST"  
  action="/admin/admin_user_management.php" 
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8">

    <input type="hidden" 
    id="change_user_detail" 
    name ="change_user_detail" 
    value="<?php echo $_REQUEST['change_user_detail'];?>">
    
    <input type="hidden" 
    id="delete_user_detail"
    name="delete_user_detail" 
    value="<?php echo $_REQUEST['delete_user_detail'];?>">

    <input type="hidden" 
    id="i"
    name="i"
    value="<?php echo $_REQUEST['i'];?>">

    <input type="hidden" 
    id="user_id"
    name="user_id"
    value="<?php echo $_REQUEST['user_id'];?>">

    <input type="hidden" 
    id="employee_type_id"
    name="employee_type_id"
    value="<?php echo $select_user_result['employee_type_id'];?>">

    <div class="form-group  col-sm-10">
      <label for="firstName">First Name</label>
      <input type="text" id="firstName" 
      name="firstName" 
      class="form-control" 
      placeholder="*Required (Current first name: <?php echo($select_user_result['first_name'])?>)"
      value="<?php echo($select_user_result['first_name']); ?>"
      data-defaut-value="<?php echo($select_user_result['first_name']); ?>">
    </div>
    <br><br><br>

    <div class="form-group  col-sm-10">
      <label for="middleName">Middle Name</label>
      <input type="text" id="middleName" 
      name="middleName" 
      class="form-control" 
      placeholder="*Required (Current middle name: <?php echo($select_user_result['middle_name'])?>)"
      value="<?php echo($select_user_result['middle_name']); ?>"
      data-default-value="<?php echo($select_user_result['middle_name']); ?>">
    </div>
    
    <br><br><br>
    <div class="form-group  col-sm-10">
      <label for="lastName">Last Name</label>
      <input type="text" id="lastName" 
      name="lastName" 
      class="form-control" 
      placeholder="*Required (Current last name: <?php echo($select_user_result['last_name'])?>)"
      value="<?php echo($select_user_result['last_name']); ?>"
      data-default-value="<?php echo($select_user_result['last_name']); ?>">
      <br>
      <hr/>
    </div>

    <div class="form-group  col-sm-10">
      <label for="address">Address</label>
      <input type="text" id="address" name="address" 
      class="form-control" 
      placeholder="*Required (Current address: <?php echo($select_user_result['address'])?>)"
      value="<?php echo($select_user_result['address']);?>"
      data-default-value="<?php echo($select_user_result['address']);?>">
    </div>

    <div class="form-group  col-sm-10">
      <label for="phoneNumber">Phone Number</label>
      <input type="text" id="phoneNumber" name="phoneNumber" 
      class="form-control" 
      placeholder="*Required (Current address: <?php echo($select_user_result['phone_number'])?>)"
      value="<?php echo($select_user_result['phone_number']);?>"
      data-default-value="<?php echo($select_user_result['phone_number']);?>">
    </div>
    
    <div class="form-group  col-sm-10">
      <label for="email">Email</label>
      <input type="text" id="email" name="email" 
      class="form-control" 
      placeholder="*Required (Current email: <?php echo($select_user_result['email'])?>)"
      value="<?php echo($select_user_result['email']);?>"
      data-default-value="<?php echo($select_user_result['email']);?>">
    </div>


    <br><br><br>
    <div class="form-group col-sm-10">
      <hr/>
    </div>
    <br><br><br>
    <div class="d-flex justify-content-center">
      <button type="submit" 
      class="btn btn-success" 
      onclick="
      $('#i').val('t');
      $('#adminForm').submit();">
      Change User Detail
      </button>  
      &nbsp;&nbsp;&nbsp;
      <button type="button" class="btn btn-success" 
      onclick="window.location='/admin/admin_user_management.php'">Return to occupation view</button>
    

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

        else if($is_input_complete) {
          echo "Occupation info has successfully been input.";
          echo "<script type='text/javascript'>
          
          // Removing user's input
          
          removeUserInput();
          // Change browser's URI display.

          window.history.replaceState(NULL, 'Occupation Manager', '/admin/admin_user_management.php');
          </script>";
        }

      ?>
      </span>
    </div>

    <!-- List of all occupations and select to decide which occupation can be selected. -->
    <span class="dropdown">
      <button id="occupationSelectDropdown" type=button class="btn btn-secondary dropdown-toggle" type="button"  data-toggle="dropdown" aria-haspopup="true" 
      value="<?php echo $select_user_result['employee_type_id'];?>"
      aria-expanded="false">
        <?php
          echo $select_user_result['occupation_type'];
        ?>
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <?php foreach($select_all_occupations_result as $o) { ?>
          <li class="dropdown-item" 
          onclick="$('#occupationSelectDropdown').text(
            '<?php echo $o['occupation_type'];?>'
            )
            $('#employee_type_id').val(

              <?php echo $o['employee_type_id'];?>
            );" 
          ><?php echo $o['occupation_type']; ?></li>
        <?php } ?>
      </ul>
    </span>
  </form>

</body>
<?php }?>


</html>