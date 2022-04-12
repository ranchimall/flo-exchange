<?php
require_once '../includes/imp_files.php';

if(isset($_POST['job']) && $_POST['job'] == 'total_recent_transactions') {
        echo $total_my_orders = (int) total_recent_transactions();
}
