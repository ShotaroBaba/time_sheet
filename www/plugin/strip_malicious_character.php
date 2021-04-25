<?php
function stripMaliciousChar ($str) {
    return htmlspecialchars(strip_tags($str));   
}
?>