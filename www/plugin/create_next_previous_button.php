<?php

function _create_onclick_attribute($value_id,$click_val,$form_id){
    return "onclick=\"$('".$value_id."').val(".$click_val.");$('".$form_id."').submit()\"";
}

function _generate_li_tag($str, $tag_val=null){
    $set_pointer_style=is_null($tag_val) ? ' style="color: black" ' : ' style="cursor: pointer" ';
    $set_tags=is_null($tag_val) ? $set_pointer_style : $set_pointer_style.
    _create_onclick_attribute('#n',$tag_val,'#userForm');
    return "<li class='page-item'><a class='page-link'".$set_tags.">".$str."</a></li>\n";
}

function generate_previous_next_button($min,$max,$current_pos){
    if(is_numeric($min) && is_numeric($max) && is_numeric($current_pos)){
        
        $min=(int)$min;
        $max=(int)$max;
        $current_pos=(int)$current_pos;
        if($current_pos<$min || $max<$current_pos){
            echo "Unknown error";
            exit(1);
        }
        
        // If min & max is the same, then only a number is printed.
        if($min==$max){
            echo _generate_li_tag($min);
        }
        
        else if($min < $max){
            echo $min == $current_pos ? '' :_generate_li_tag(htmlspecialchars("<<"),$min);
            echo $min == $current_pos ? '' :_generate_li_tag(htmlspecialchars("<"),$current_pos-1);
            echo $current_pos == $min ?  _generate_li_tag($min) : _generate_li_tag($min,$min);
            echo $min < $current_pos - 2 ? _generate_li_tag('...'): '';
            echo $min < $current_pos - 1 ? _generate_li_tag($current_pos-1,$current_pos-1): '';
            echo $current_pos == $min || $current_pos == $max ? '' : _generate_li_tag($current_pos) ;
            echo $current_pos + 1 < $max ? _generate_li_tag($current_pos+1,$current_pos+1): '';
            echo $current_pos + 2 < $max ? _generate_li_tag('...'): '';
            echo $current_pos == $max ?  _generate_li_tag($max) : _generate_li_tag($max,$max);
            echo $max == $current_pos ? '' :_generate_li_tag(htmlspecialchars(">"),$current_pos+1);
            echo $max == $current_pos ? '' :_generate_li_tag(htmlspecialchars(">>"),$max);
        }
    }

    else{
        echo "Unknown error.";
        exit(1);
    }
}

?>