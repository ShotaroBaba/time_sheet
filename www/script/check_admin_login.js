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
            url: "/admin/check_user_input.php",
            method: "POST"
        }
    )
}

function checkAdminInput(){
    
    $.when(_checkAdminInput()).done(function(data) {

        if(data['login_success']){
            console.log(data);
            $('#adminLogin').submit();
        }
        else
        {
            console.log(data);
            $('#adminLoginErrorMessage').html("Password or user name incorrect.");
        }

    }).fail( function(data){
        console.log(data);
        console.log('failed');
        $('#adminLoginErrorMessage').html("Password or user name incorrect.");
    });
};