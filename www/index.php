<!-- The time sheet for employees will be done here... -->

<!-- Load bootstrap css. -->

<link href='/css/bootstrap.min.css' rel='stylesheet'>
<link href='/css/index.css' rel='stylesheet'>

<!-- Load script for downloading the  -->
<script src="/script/index.js"></script>

<div class="container">
<div class="row">
  <div class="col page-title">
    Employee Time Sheet Viewer
  </div>
</div>
</div>


<div class="container ">
  <div class="page-title">
    Employee Login
  </div>
  <br>
  <form id="">
    <div class="form-group-sm row justify-content-center">
      <label for="userLoginIDInput" class="col-sm-1 col-form-label">Login ID: </label>
      <div class="col-sm-3">
        <input type="email" class="form-control" id="userLoginIDInput" aria-describedby="emailHelp" placeholder="Enter LoginID">
      </div>
      <br>
    </div>
    <br>
    <div class="form-group-sm row justify-content-center">
      <label for="userPasswordInput" class="col-sm-1 col-form-label">Password: </label>
      <div class="col-sm-3">
        <input type="password" autocomplete="on" class="form-control" id="userPasswordInput" aria-describedby="emailHelp" placeholder="Enter Password">
      </div>
      <br><br>
      <div class="d-flex justify-content-center">
        <button type="submit" class="btn btn-primary justify-content-center">Submit</button>
      </div>
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
    <button type="button" onclick="window.location='employee/employee_account_registration.php'" class="btn btn-primary btn-lg btn-block">Register</button>
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