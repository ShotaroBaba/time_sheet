<!-- Load css files. -->
<link href='/css/bootstrap.min.css' rel='stylesheet'>
<link href='/css/index.css' rel='stylesheet'>
<script src="/script/jquery-3.6.0.min.js"></script>
<!-- Load script for registration -->
<script src="/script/register.js"></script>

<div class="container align-items-center">

  <form id ="userInputMain" method="POST" onsubmit="return registerUser()" action="/employee/employee_account_registration_summary.php" class="d-flex row justify-content-center">
    <div class="form-group  col-sm-10">
      <label for="employeeFirstName">First Name</label>
      <input type="text" id="employeeFirstName" name="employeeFirstName" class="form-control" placeholder="*Required">
    </div>
    <div class="form-group col-sm-10">
      <label for="employeeMiddleName">Middle Name</label>
      <input type="text" id="employeeMiddleName" name="employeeMiddleName" class="form-control" placeholder="">
    </div>
    <div class="form-group col-sm-10">
      <label for="employeeLastName">Last Name</label>
      <input type="text" id="employeeLastName" name="employeeLastName" class="form-control" placeholder="*Required">
    </div>
    <br><br><br>
    <div class="col-sm-10">
      <hr/>
    </div>
    <div class="form-group  col-sm-10">
      <label for="employeeAddress">Address</label>
      <input type="text" id="employeeAddress" name="employeeAddress" class="form-control" placeholder="*Required">
    </div>
    <div class="form-group  col-sm-10">
      <label for="employeePhoneNumber">Phone Number</label>
      <input type="text" id="employeePhoneNumber" name="employeePhoneNumber" class="form-control" value="fffff" placeholder="*Required">
    </div>
    <div class="form-group  col-sm-10">
      <label for="employeeEmail">Email</label>
      <input type="text" id="employeeEmail" name="employeeEmail" class="form-control" placeholder="*Required">
    </div>
    <br><br><br>
    <div class="col-sm-10">
      <hr/>
    </div>
    <br><br><br>

    <div class="form-group  col-sm-10">
      <label for="employeePassword">Password</label>
      <input type="password" id="employeePassword" name="employeePassword" class="form-control" placeholder="*Password (Only alpha-numerical characters are accepted)">
    </div>
    <div class="form-group  col-sm-10">
      <label for="employeePasswordRetype">Re-type Password</label>
      <input type="password" id="employeePasswordRetype" name="employeePasswordRetype" class="form-control" placeholder="Retype Password">
    </div>

    <div  class="d-flex justify-content-center">
      <button class="btn btn-primary">Submit</button>
    </div>
    <div  class="d-flex justify-content-center">
      <span id="errorMessage" class='error-message'></span>
    </div>
  </form>
</div>