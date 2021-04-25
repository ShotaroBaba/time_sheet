<!-- Load css files. -->
<link href='/css/bootstrap.min.css' rel='stylesheet'>
<link href='/css/index.css' rel='stylesheet'>


<!-- Show summary before  -->
<?php

require_once("/var/www/html/plugin/strip_malicious_character.php");

?>



<div class="container align-items-center" id="formReview">
  <h1 style="text-align: center">Account Registration Review</h1>
  <div class="Review-window">
    First  Name <br>
    <?php echo stripMaliciousChar($_POST["employeeFirstName"]) ?><br><br>
    Middle Name <br>
    <?php echo stripMaliciousChar($_POST["employeeMiddleName"]) ?><br><br>
    Last Name<br>
     <?php echo stripMaliciousChar($_POST["employeeLastName"]) ?><br><br>
    Address<br>
     <?php echo stripMaliciousChar($_POST["employeeAddress"]) ?><br><br>
    Phone Number<br>
     <?php echo stripMaliciousChar($_POST["employeePhoneNumber"]) ?><br><br>
    Email<br>
    <?php echo stripMaliciousChar($_POST["employeeEmail"]) ?><br><br>
    Password: ****
  </div>
  <div>
  <div  class="d-flex justify-content-center">
    <div class="col-auto mx-3">
      <button class="btn btn-primary">Confirm </button>
    </div>    
    <div class="col-auto mx-3">
      <button class="btn btn-primary">Back </button>
    </div>
  </div>
</div>
