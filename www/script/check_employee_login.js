// Check user's input & send it to a php file dealing with them
// Check the info of account registration.


function _checkEmployeeInput() {

    return $.ajax(
        {
            data: {
                "employeeEmail": $("#employeeLoginIDInput").val(),
                "employeePassword": $("#employeeLoginPasswordInput").val()
            },
            dataType: "JSON",
            url: "/employee/check_user_input.php",
            method: "POST",
        }
    )
}

function checkEmployeeInput(){
    
    $.when(_checkEmployeeInput()).done(function(data) {

        if(data['login_success']){
            $('#employeeLogin').submit();
        }
        else
        {
            $('#employeeLoginErrorMessage').html("Password or user name incorrect.");
        }

    }).fail( function(data){
            $('#employeeLoginErrorMessage').html("Password or user name incorrect.");
    });
};