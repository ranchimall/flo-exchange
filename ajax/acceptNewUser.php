<?php

require_once '../includes/imp_files.php';
session_start();


//CRITICAL: THIS FILE NEEDS AUTHENTICATION -- ADD IT --ADDED
if (!checkLoginStatus()) {
    return false;
}

//AUTHORIZATION CHECK
if (isset($_SESSION['flo_id'], $_SESSION['user_id'])) {
    $root_flo = $_SESSION['flo_id'];
    $root_user_id = $_SESSION['user_id'];
    
    if ($root_flo != ADMIN_FLO_ID && $root_user_id != ADMIN_ID) {
        redirect_to("index.php");
    }


    if ((isset($_POST['flo_id']) && ($_POST['task'] == 'approve_user'))){
        
        ob_start();
        
                
            $floID = $_POST['flo_id'];
            $newUserDetails = findNewUserDetails($floID);

            $fullName = $newUserDetails->full_name;
            $emailID = $newUserDetails->email;

            acceptUser($floID,$fullName,$emailID);
            $result = "approved";
            deleteNewUser($floID);
            echo $result;
            exit();
    }

    if ((isset($_POST['flo_id']) && ($_POST['task'] == 'reject_user'))){
        
        ob_start();
        
                
            $floID = $_POST['flo_id'];
            deleteNewUser($floID);
            
            $result = "deleted";
            echo $result;
            exit();
    }
           
 

}