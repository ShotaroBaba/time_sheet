// Check user's input & send it to a php file dealing with them
// Check the info of account registration.

function registerUser() {

    htmlStr="";

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

    if($("#employeePassword").val() && !$("#employeePasswordRetype").val()){
        htmlStr+="Please re-type the same password for confirmation.<br>"
    }

    if($("#employeePassword").val() !== $("#employeePasswordRetype").val()){
        htmlStr+="Password does not match"
    }
    // ********* User's validation end. ********


    if(!htmlStr){
       return true;
    }
    else {
        $('#errorMessage').html(htmlStr);
        return false;
    }

}