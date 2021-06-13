
// Go to user login management screen.
function chageUserTimeDetail(userID,timeID)
{
    $('#time_id').val(timeID);
    $('#user_id').val(userID);

    $('#chageUserTimeTable').val('t');

    $('#adminForm').submit();

}

// Submit change.
function changeUserTimeDetailSubmit(){  
    
    $('#i').val('t')
    $('#adminForm').submit();
}

// Only a user id is required.
function changeToSalaryPage(userID){

    $('#user_id').val(userID);
    $('#n').val(null);
    $('#t').val(null);

    $('#adminForm').attr('action','/admin/admin_salary_display.php');
    $('#adminForm').submit();
     
}

function changeToTimeSheetPage(UserID){
    
    $('#user_id').val(UserID);
    $('#n').val(null);
    $('#t').val(null);

    $('#adminForm').attr('action','/admin/admin_time_sheet_management.php');
    $('#adminForm').submit();
}