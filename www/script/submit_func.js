function submitValues(...args){

    for (var i = 0;i<args.length;i++) {
        if(!typeof(args[i])=='string'){
            break;
        }
        else{
            if(!$(args[i]).val()){
                $(args[i]).remove();
            }
        }
    }

}

function submitAlterButton(){
    $('#alter_occupation').val('t');
    $('#n').val('');
    $('#t').val('');
    submitValues('#t','#i','#n','#insert_occupation','#alter_occupation','#employee_type_id');
}

function submitChangeButton(){
    $('#change_occupation').val('t');
    $('#n').val('');
    $('#t').val('');
    submitValues('#t','#i','#n','#insert_occupation','#change_occupation','#employee_type_id');
}
