
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