<?php

require_once '../includes/imp_files.php';

if (isset($_POST['task']) && trim($_POST['task'])=='loadTradersList') {

    $std = new stdClass();
    $std->traders_list = array();
    $std->error = true;

    $tradersList = UserBalanceList();
        if (is_array($tradersList) && !empty($tradersList)) {
            $std->traders_list = $tradersList;
            $std->error = false;
        }
    
    echo json_encode($std);

} else {
    return false;
}
