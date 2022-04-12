<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['task'], $_POST['id']) && trim($_POST['task'])=="delOrder") {

    $del_id = extract_int($_POST['id']);

    if (isset($user_id)) {

        $validate_user = check_user($user_id);

        if($validate_user == "" || empty($validate_user)) {
            return false;
        }

        $del_order = del_order($del_id);

        if ($del_order) {
            echo true;
        }
    }
    return false;
}
