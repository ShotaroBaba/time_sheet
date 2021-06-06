<?php 
  header("Content-Type: text/html;charset=UTF-8");
  error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
  
  include "/var/www/html/.secret/.config.php";

  session_name('admin_cookie');
  session_start([
    'sid_length' => 128
  ]);
  
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
      session_destroy();
      header('Location: /');
      exit(0);
    }

    $conn= new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", 
    $_SESSION['admin_user_name'], $_SESSION['admin_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $total_user_num=$conn->prepare("SELECT COUNT(*) FROM user;");

    $total_user_num->bindValue(":_user_id", htmlspecialchars($_SESSION['employeeUserID']), PDO::PARAM_INT);

    if($total_user_num->execute() < 1){
      echo "Unknown error.";
      exit(1);
    }

    $total_result=$total_user_num->fetch();

    // Injection attack prevention measure.
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


    $user_selection_prepare=$conn->prepare('SELECT * FROM user;');

    if($user_selection_prepare->execute()<1){
        echo "Unknown error";
        exit(1);
    }

    $user_selection_result=$user_selection_prepare->fetchAll();
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
  <script src="/script/popper.js?v=1"></script>
  <script src="/script/bootstrap.bundle.min.js?v=1"></script>
</head>


<body>
  <table class="table">
    <thead>
      <th>User ID</th>
      <th>First Name</th>
      <th>Middle Name</th>
      <th>Last Name</th>
      <th>Phone Number</th>
      <th>Address</th>
      <th>Email</th>
      <th>State</th>
    </thead>
    <tbody>
    <?php 
      $i=0;
      foreach ($user_selection_result as $v) {
        $class_tag=$i%2==0?" class='grey-table-row'":"";
        echo "<tr".$class_tag.">";
        echo "<th>".$v['user_id']."</th>";
        echo "<th>".$v['first_name']."</th>";
        echo "<th>".$v['middle_name']."</th>";
        echo "<th>".$v['last_name']."</th>";
        echo "<th>".$v['phone_number']."</th>";
        echo "<th>".$v['email']."</th>";
        echo "<th>".$v['address']."</th>";
        echo "<th>".$v['state']."</th>";
        echo "</tr>";
        $i++;
      }
    ?>
    </tbody> 
  </table>   

<button class="btn btn-success" onclick="window.location='/admin/occupation_management.php'">
  Change to occupation manager
</button>

</body>
</html>