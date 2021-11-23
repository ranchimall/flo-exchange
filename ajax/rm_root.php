<?php
require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_SESSION['flo_id'], $_SESSION['user_id'])) {
    $root_flo = $_SESSION['flo_id'];
    $root_user_id = $_SESSION['user_id'];
    
    if ($root_flo != ADMIN_FLO_ID && $root_user_id != ADMIN_ID) {
        redirect_to("index.php");
    }

    if (isset($_POST['task'], $_POST['btn_id']) && trim($_POST['task']=="act_user")) {

        $u_id = explode('_', trim($_POST['btn_id']));
        $u_id_int = extract_int($u_id[1]);
        $u_id_str = (string) trim($u_id[0]);
        $act = "";

        if ($u_id_str == "off") {
            $act = "0";
        } else if($u_id_str == "on") {
            $act = "1";
        } else {
            return false;
        }
        if (isset($OrderClass, $UserClass)) {

            if ($u_id_str == "off") {
                $del_ord = $OrderClass->delete_orders_of_user($u_id_int);
            }
            $act_user = $UserClass->actions_user($u_id_int, $act);

            if ($act_user) {
                echo $u_id_str;
            }
        }
        return false;
    }

}
