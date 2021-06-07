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