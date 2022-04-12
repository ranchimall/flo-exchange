<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['task']) && $_POST['task']=='loadMyMessagesList') {
    if (isset($user_id)) {

        $std = new stdClass();
        $std->msg = null;
        $std->error = true;

        $my_messages = list_messages_by_userId($user_id, 0, 10);

        if (is_array($my_messages) && !empty($my_messages)) {
            $std->msg = $my_messages;
            $std->error = false;
        }

        echo json_encode($std);

    }
}
