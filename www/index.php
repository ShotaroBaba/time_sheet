<!-- The time sheet for employees will be done here... -->
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<link href='/css/bootstrap.min.css' rel='stylesheet'>
<link href='/css/index.css' rel='stylesheet'>
<script src="/script/jquery-3.6.0.min.js"></script>

<!-- Load script  -->
<script src="/script/check_employee_login.js"></script>

<div class="container">
<div class="row">
  <div class="col page-title">
    Employee Time Sheet Viewer
  </div>
</div>
</div>

<!-- enctype="multipart/form-data" accept-charset="UTF-8" -->

<div class="container ">
  <div class="page-title">
    Employee Login
  </div>
  <br>
  <form id="employeeLogin"
  method="POST"  
  action="/employee/employee_time_sheet.php" 
  class="d-flex row justify-content-center"
  enctype="multipart/form-data" accept-charset="UTF-8"
  >
    <div class="form-group-sm row justify-content-center">
      <label for="employeeLoginIDInput" class="col-sm-1 col-form-label">Login ID: </label>
      <div class="col-sm-3">
        <input type="email" class="form-control" name="employeeLoginIDInput" id="employeeLoginIDInput" aria-describedby="emailHelp" placeholder="Enter LoginID">
      </div>
      <br>
    </div>
    <br>
    <div class="form-group-sm row justify-content-center">
      <label for="employeeLoginPasswordInput" class="col-sm-1 col-form-label">Password: </label>
      <div class="col-sm-3">
        <input type="password" autocomplete="on" class="form-control" name="employeeLoginPasswordInput" id="employeeLoginPasswordInput" aria-describedby="emailHelp" placeholder="Enter Password">
      </div>
      <br><br>
      <div class="d-flex justify-content-center">
        <button type="button" onclick="checkEmployeeInput();" class="btn btn-primary justify-content-center">Submit</button>
      </div>
    </div>
    <div  class="d-flex justify-content-center">
      <span id="employeeLoginErrorMessage" class='error-message'></span>
    </div>
    <br>
  </form>
</div>

<div class="container">
  <div class="submit-center">
    If you have not registered your account as an employee, press the button below:  
  </div>
  <br>
  <div class="d-flex justify-content-center">
    <button type="button" onclick="window.location='employee_registration/employee_account_registration.php'" class="btn btn-primary btn-lg btn-block">Register</button>
  </div>
</div>

<br><br><br>
<hr/>
<br>

<!-- The below is a Admin user login. Used for adding, deleting employees manually, or 
  checking all employees time sheets. -->
<div class="container">
  <div class="page-title">
    Administrator Login
  </div>
  <br>
  <form>
    <div class="form-group-sm row justify-content-center">
      <label for="adminLoginIDInput" class="col-sm-1 col-form-label">Login ID: </label>
      <div class="col-sm-3">
        <input type="email" class="form-control" id="adminLoginIDInput" aria-describedby="emailHelp" placeholder="Enter LoginID">
      </div>
      <br>
    </div>
    <br>
    <div class="form-group-sm row justify-content-center">
      <label for="adminPasswordInput" class="col-sm-1 col-form-label">Password: </label>
      <div class="col-sm-3">
        <input type="password" autocomplete="on" class="form-control" id="adminPasswordInput" aria-describedby="emailHelp" placeholder="Enter Password">
      </div>
      <br><br>
      <div class="d-flex justify-content-center">
        <button type="submit" class="btn btn-primary justify-content-center">Submit</button>
      </div>
    </div>
    <br>
  </form>
</div>

<script>
  // Get access to the website
  // console.log("Test");
</script>