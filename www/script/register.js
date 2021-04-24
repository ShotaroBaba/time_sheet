// Check user's input & send it to a php file dealing with them


// Check the account registration.

function registerUser() {

    htmlStr="";

    if (!$("#employeeFirstName").val()) {
       htmlStr+="Please input your first name.<br>"
    }
    
    if(!$("#employeeLastName").val()){
        htmlStr+="Please input your last name.<br>"
    }



    if(!$("#employeeAddress").val()){
        htmlStr+="Please input your address.<br>"
    }

    if(!$("#employeePhoneNumber").val()){
        htmlStr+="Please input your phone number.<br>"
    }

    if(!$("#employeeEmail").val()){
        htmlStr+="Please input your email address.<br>"
    }

    

    if(!htmlStr){
        userInputData=$("#userInputMain").serialize();
        console.log(userInputData);
        $.ajax({
            type: 'POST',
            data: userInputData,
            success: function(data) {
                window.location.href = "/employee/employee_account_registration_summary.php";
            }
        })
        return false;
    }
    else {
        $('#errorMessage').html(htmlStr);
    }

    

    
    // )
}