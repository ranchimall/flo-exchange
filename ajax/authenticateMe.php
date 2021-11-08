<?php

$std = new stdClass();
$std->users = null;
$std->cash = null;
$std->bit = null;
$std->message = array();
$std->error = false;

    if (isset($_POST['flo_id'], $_POST['flo_pub_key'], $_SESSION['session_id'], $_POST['signDataWithFlo'] )){
        
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
            if(!$result){die("Connection Failure");}
            curl_close($curl);
            return $result;
            }
    
            $floID = $_POST['flo_id'];
            $pubKey = $_POST['flo_pub_key'];
            $s_id = $_SESSION['session_id'];
            $signDataWithFlo = $_POST['signDataWithFlo'];
    
    
            $data_array =  array( "floID"        => $floID, "pubKey" => $pubKey, "message" => $s_id, "sign" => $signDataWithFlo  );
            $make_call = callAPI('POST', 'https://flo-sign-validator.duckdns.org', json_encode($data_array));
            $response = json_decode($make_call, true);
    
    
            if ($response['success'] == 1) {
                
                //store $s_id (session ID) in SQl
                ob_end_clean();
                ob_start();
                $std->error = false;
                setcookie("exchange[flo_id]", $floID, time () + (86400 * $cookie_life_days));
                setcookie("exchange[session_id]", $s_id, time () + (86400 * $cookie_life_days));
                setcookie("exchange[flo_pub_key]", $pubKey,time () + (86400 * $cookie_life_days)); 
                
                $_SESSION['authenticated'] = true;
                
                //TEMPORARILY ASSIGNING ALL THESE VARIABLES TO $floID
                $_SESSION['full_name'] = $floID;
                $_SESSION['email'] = $floID;
                $_SESSION['fb_id'] = $floID;


                echo json_encode($std);
                return true;
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



