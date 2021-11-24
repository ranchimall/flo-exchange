<?php 
session_start();
$_SESSION['authenticated'] = false;
ob_end_clean();
ob_start(); 
if (isset($_COOKIE['exchange'])) {
        unset($_COOKIE['exchange[flo_id]']); 
        setcookie('exchange[flo_id]', null, -1); 
        unset($_COOKIE['exchange[session_id]']); 
        setcookie('exchange[session_id]', null, -1); 
        unset($_COOKIE['exchange[flo_pub_key]']); 
        setcookie('exchange[flo_pub_key]', null, -1);
        unset($_COOKIE['exchange']); 
        setcookie('exchange', null, -1);
     }
session_destroy();
?>
