<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if(isset($_POST['job']) && $_POST['job'] == 'total_my_orders') {

    if (isset($user_id)) {
        $validate_user = check_user($user_id);

        if($validate_user == "" || empty($validate_user)) {
            return false;
        }
        echo $total_my_orders = (int) total_my_orders();
    }

}
