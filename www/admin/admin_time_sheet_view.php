<!-- Admin can check all time sheets of employees. -->


<?php 
    session_name('admin_cookie');
    session_start();

    // if(empty($_SESSION)){
    //     session_unset();
    //     session_destroy();
    //     header('Location: /');
    //     exit(0);
    // }
    
    // // For preventing session hijacking.
    // if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ipaddress'])
    // {
    //     session_unset();
    //     session_destroy();
    //     header('Location: /');
    //     exit(0);
    // }
    
    // if ($_SERVER['HTTP_USER_AGENT'] != $_SESSION['useragent'])
    // {
    //     session_unset();
    //     session_destroy();
    //     header('Location: /');
    //     exit(0);
    // }
    

    // Select all user info
    // try{


    //     $conn= new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", 
    //     $_SESSION['admin_user'], $_SESSION['admin_pass']);
    //     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //     $user_selection_prepare=$conn->prepare('SELECT * FROM user;')
    
    //     if($user_selection_prepare->execute()<1){
    //         echo "Unknown error";
    //         exit(1);
    //     }
    
    //     $user_selection_result=$user_selection_prepare->fetchAll();
    // }
    // catch(PDOException $e)  {
    //     echo "Unknown error";
    //     exit(1);
    // }

?>

<!-- TODO: Create an actual user list that is shown on this web page. -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Employee Manager</title>
</head>
<body>
    Success!!
</body>
</html>