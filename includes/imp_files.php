<?php

if(!isset($_SESSION['session_id'])) {
    session_start();
    $_SESSION['session_id'] = session_id();
}

require_once 'defines.php';
require_once 'config.php';
include_once 'autoload.php';
include_once 'functions.php';
include_once 'UsersFunc.php';

$db_connection = databaseConnection();

//if logged in store user DB details
$flo_id = null;
$user_id = null;
$log_fullName = null;
$user_email = null;

if (checkLoginStatus()) {
   
    if (isset($_SESSION['flo_id'],$_SESSION['user_id'])) {
        $flo_id = $_SESSION['flo_id'];
        $user_id = $_SESSION['user_id'];
        $log_fullName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
        $user_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    } else {
        redirect_to("logout.php");
    }

}

$UserClass = null;
$OrderClass = null;
$ApiClass = null;
$MailClass = null;

if (class_exists('Users') && class_exists('Orders') && class_exists('Api') && class_exists('SendMail')) {
    $UserClass = new Users();
    $OrderClass = new Orders();
    $ApiClass = new Api();
    $MailClass = new SendMail();
}
