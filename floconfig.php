<?php
if(!session_id()) {
    session_start();
}
require_once 'includes/imp_files.php';

if (isset($UserClass)) {
    if (isset($fb_id)):
        // check if user already registered
        $validate_user = $UserClass->is_fb_registered($fb_id);
        if($validate_user == "" || $validate_user == false) {
            redirect_to('index.php');
        }
    endif;

    $tradersList = $OrderClass->UserBalanceList();
    $buy_list[] = $OrderClass->get_top_buy_sell_list(TOP_BUYS_TABLE, $asc_desc='DESC');  // buy
    $sell_list[] = $OrderClass->get_top_buy_sell_list(TOP_SELL_TABLE, $asc_desc='ASC');  // sell
}

$fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "";
$user_logged_in = false;
$action_class_market = 'fb_log_in';
$action_class_buy_sell = 'fb_log_in';


    // printing $profile array on the screen which holds the basic info about user

    $name = isset($profile['name']) ? $profile['name'] : null;
    $first_name = isset($profile['first_name']) ? $profile['first_name'] : null;
    $last_name = isset($profile['last_name']) ? $profile['last_name'] : null;
    $email = isset($profile['email']) ? $profile['email'] : null;
    //$gender = isset($profile['gender']) ? $profile['gender'] : null;
    $fb_id = isset($profile['id']) ? $profile['id'] : null;

    $_SESSION['first_name'] = $first_name;
    $_SESSION['full_name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['fb_id'] = $fb_id;

    // Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']

