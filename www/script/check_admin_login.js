// Check user's input & send it to a php file dealing with them
// Check the info of account registration.


function _checkAdminInput() {

    return $.ajax(
        {
            data: {
                "adminLoginID": $("#adminLoginIDInput").val(),
                "adminPassword": $("#adminLoginPasswordInput").val()
            },
            dataType: "JSON",
            url: "/employee/check_user_input.php",
            method: "POST",
        }
    )
}

function checkAdminInput(){
    
    $.when(_checkEmployeeInput()).done(function(data) {

        if(data['login_success']){
            $('#adminLogin').submit();
        }
        else
        {
            $('#adminLoginErrorMessage').html("Password or user name incorrect.");
        }

    }).fail( function(data){

        $('#adminLoginErrorMessage').html("Password or user name incorrect.");
    });
};