<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if(isset($_POST['job']) && $_POST['job'] == 'total_my_transactions') {

        echo $total_my_orders = (int) total_my_transactions();
    
    }
