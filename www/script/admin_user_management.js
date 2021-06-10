
function deleteUserDetail(userID) {
    
    if(confirm("Will you delete this user?\nFirst Name: "+
    $('#firstName_'+userID).text()+"\nLast Name: "+
    $('#lastName_'+userID).text())) {

        $('#delete_user_detail').val('t');
        $('#user_id').val(userID);
        
        $('#n').val('');
        $('#t').val('');
        
        submitValues('#t','#i','#n','#user_id',
        '#change_user_detail','#delete_user_detail');

        $('#adminForm').submit();
    };

}

function changeUserDetail(userID) {

    $('#change_user_detail').val('t');
    $('#n').val('');
    $('#t').val('');
    $('#user_id').val(userID);
    submitValues('#t','#i','#n','#insert_occupation','#user_id',
    '#change_user_detail','#delete_user_detail');
    $('#adminForm').submit();

}
