// Check user's input & send it to a php file dealing with them
// Check the info of account registration.

passwordFormat=/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d]{8,}$/
illegalPasswordFormat=/[^A-Za-z0-9]+/

function _registerUser() {

    return $.ajax({
            data: {
                "employeeFirstName": $("#employeeFirstName").val(),
                "employeeMiddleName": $("#employeeMiddleName").val(),
                "employeeLastName": $("#employeeLastName").val(),
                "employeePhoneNumber": $("#employeePhoneNumber").val(),
                "employeeEmail": $("#employeeEmail").val()
            },
            dataType: "JSON",
            url: "check_user_input.php",
            method: "POST",
            // success: function(data) {
               
            // }
        }
    )


}

function registerUser(){
    
    htmlStr="";
    $.when(_registerUser()).done(function(data) {
        
        // If data already exists in database.
        if (data['is_email_exist']) {
            htmlStr+="Email has already been registered.";
        }
        
        if (data['is_name_phone_exist']) {
            htmlStr+="Name and phone has already been registerd";
        }

        // ********* Validate user's info ********
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
    
        if(!$("#employeePassword").val()){
            htmlStr+="Please input your password.<br>"
        }
    
        else if($("#employeePassword").val() && !$("#employeePasswordRetype").val()){
            htmlStr+="Please re-type the same password for confirmation.<br>"
        }
    
        else if($("#employeePassword").val() !== $("#employeePasswordRetype").val()){
            htmlStr+="Password does not match."
        }
        
        // Check if password satisfies the specific formats.
        else if(!passwordFormat.test($("#employeePassword").val())){
            htmlStr+="Password must contains at least one lower-case alphabet, one uppacase-alphabet and one numeric number, and needs to be 8 character length.<br>"
        }
        else if(illegalPasswordFormat.test($("#employeePassword").val())){
            htmlStr+="Password contains special characters. It needs to have only lower-case alphabet, uppacase-alphabet and one numeric number.<br>"
        }
        
        if(!htmlStr){
            $('#userInputMain').submit();
        }
        else
        {
            $('#errorMessage').html(htmlStr);
        }

    })
};