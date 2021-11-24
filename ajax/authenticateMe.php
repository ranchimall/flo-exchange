<?php

require_once '../includes/imp_files.php';
session_start();

$cookie_life_days = COOKIE_LIFE_DAYS;

$std = new stdClass();
$std->message = array();
$std->error = true;

    if (isset($_POST['floID'], $_POST['floPubKey'], $_SESSION['session_id'], $_POST['signDataWithFlo'] )){
        
        ob_start();
        
            function callAPI($method, $url, $data){
            $curl = curl_init();
            switch ($method){
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);
                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                              
                    break;
                default:
                    if ($data)
                        $url = sprintf("%s?%s", $url, http_build_query($data));
            }
            // OPTIONS:
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'APIKEY: 111111111111111111111',
                'Content-Type: application/json',
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // EXECUTE:
            $result = curl_exec($curl);
            if(!$result){
                $std->message[] = "Authentication Service is not working ";
                $std->error = true;
                echo json_encode($std);
                return false;
                }
            curl_close($curl);
            return $result;
            }
    
            $floID = $_POST['floID'];
            $pubKey = $_POST['floPubKey'];
            $s_id = $_SESSION['session_id'];
            $signDataWithFlo = $_POST['signDataWithFlo'];
    
    
            $data_array =  array( "floID"        => $floID, "pubKey" => $pubKey, "message" => $s_id, "sign" => $signDataWithFlo  );
            $make_call = callAPI('POST', 'https://flo-sign-validator.duckdns.org', json_encode($data_array));
            $response = json_decode($make_call, true);
    
    
            if ($response['success'] == 1) {
                
                ob_end_clean();
                ob_start();
                
                setcookie("exchange[flo_id]", $floID, time () + (86400 * $cookie_life_days));
                setcookie("exchange[session_id]", $s_id, time () + (86400 * $cookie_life_days));
                setcookie("exchange[flo_pub_key]", $pubKey,time () + (86400 * $cookie_life_days)); 

                $_SESSION['authenticated'] = true;         
                $_SESSION['flo_id'] = $floID;

//Now  I need to enter public key and session ID in database .. but first I need to know the username 
// This check can be suspended here. If the user has correct FLO signature, but does not exist in database, he will fail the gate at acc_deact.php
                if (isset($UserClass)) { 
                    $permitted_status = $UserClass->checkIfFloIDPermitted($floID);
                    if ($permitted_status == true){
                    $UserClass->update_flo_details($floID, $s_id, $pubKey);
                    
                    $userDetails = $UserClass->getUserDetails($floID);
                    $_SESSION['user_id'] = $userDetails->CustomerId;
                    $_SESSION['user_name'] = $userDetails->Name;
                    $_SESSION['email'] = $userDetails->Email;


                    $std->message[] = "Authentication Succeeded.";
                    
                    $std->error = false;
                    echo json_encode($std);
                    return true;
                    } else { 
                        if (($UserClass->check_flo_id_registration_status($floID) == true) && ($UserClass->check_flo_id_active_status($floID) != true)) {
                            $std->message[] = "FLO ID is not active. Please contact RanchiMall Team. ";
                            $std->error = true;
                            echo json_encode($std);
                            return false; }
                        if ($UserClass->check_flo_id_registration_status($floID) != true ) {
                            $std->message[] = "Your FLO ID is not registered. Please contact RanchiMall Team and get yourself registered.";
                            $std->error = true;
                            echo json_encode($std);
                            return false; }    
                    }     
                }
            
            }
            
                
              else {
                $std->message[] = "Authentication has failed. ";
                $std->error = true;
                echo json_encode($std);
                return false;
            }
} else {
    $std->message[] = "All data fields not present. ";
    $std->error = true;
    echo json_encode($std);
    return false;
}
