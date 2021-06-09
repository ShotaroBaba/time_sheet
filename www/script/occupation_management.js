function removeUserInput() {
    $('#occupationName').val('');
    $('#wage').val('');
}

function deleteOccupation(occupationID) {
    
    if(confirm("Will you delete this occupation?\nOccupation Name: "+
    $('#occupation_type_'+occupationID).text()+"\nWage: "+
    $('#wage_'+occupationID).text())) {
        $('#delete_occupation').val('t');
        $('#n').val('');
        $('#t').val('');
        submitValues('#t','#i','#n','#insert_occupation','#employee_type_id',
        '#alter_occupation','#delete_occupation');
        
        // Send ajax call to delete a the line
        $.ajax({
            type: 'POST',
            url: '/admin/occupation_management.php',
            

            // if success, then delete the line
        })
    };

}

function changeOccupation(occupationID) {

    $('#alter_occupation').val('t');
    $('#n').val('');
    $('#t').val('');
    $('#employee_type_id').val(occupationID);
    submitValues('#t','#i','#n','#insert_occupation','#employee_type_id',
    '#alter_occupation','#delete_occupation');
    $('#adminForm').submit();

}