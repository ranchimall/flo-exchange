<?php
require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if(isset($_POST['req']) && $_POST['req'] == 'loadMoreMyTransactions') {

    if (isset($user_id)) {
        $validate_user = check_user($user_id);

        if($validate_user == "" || empty($validate_user)) {
            return false;
        }

        $std = new stdClass();
        $std->msg = array();
        $std->error = true;

        if (isset($_POST['records_per_page'], $_POST['start'])) {

            $start = (int) $_POST['start'];
            $records = (int) $_POST['records_per_page'];

            $megs = displayUserTransaction($user_id, $start, $records);

            if (is_array($megs) && !empty($megs)) {
                $std->trade_list = $megs;
                $std->error = false;
            }
        }
        echo json_encode($std);
    }
}
