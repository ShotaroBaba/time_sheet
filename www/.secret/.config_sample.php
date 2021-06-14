<?php
$host = 'mysql_server';
$db   = 'time_sheet';
$user = 'time_sheet_client';

// This part is replaced using a supplied 
// password, which will be created
// by executing the create_server.sh script.
$pass   = '_____time_sheet_pass_____';
$pepper = '_____pepper_string_____';
$error_message = "Unknown error.";

$user_login_expiration_time=1800;

$charset = 'utf8mb4';
?>