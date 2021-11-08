<?php 
session_start();
$_SESSION['authenticated'] = false;
session_destroy();
header("Location: index.php");        // you can enter home page here ( Eg : header("Location: " ."http://www.krizna.com"); 
?>
