<?php

require_once '../includes/imp_files.php';
session_start();

$std = new stdClass();
$std->message = array();
$std->error = true;

if (empty($_POST["floID"])) {
                $std->message[] = "FLO ID is missing.";
                $std->error = true;
                echo json_encode($std);   
                exit();
  }

  if (empty($_POST["fullName"])) {
                $std->message[] = "Full Name is missing.";
                $std->error = true;
                echo json_encode($std);   
                exit();
  }

  if (empty($_POST["emailID"])) {
                $std->message[] = "E-mail is missing.";
                $std->error = true;
                echo json_encode($std);   
                exit();
  }

            $floID = sanitise_input($_POST['floID']);
            $fullName = sanitise_input($_POST['fullName']);
            $emailID = sanitise_input($_POST['emailID']);
            
    if (preg_match('/[^1-9A-HJ-NP-Za-km-z]/', $floID)) {
                $std->message[] = "FLO ID has unacceptable characters.";
                $std->error = true;
                echo json_encode($std);   
                exit();;
        }        


     if (strlen($floID) != 34) {
            $std->message[] = "Length of FLO ID should be 34.";
                $std->error = true;
                echo json_encode($std);   
                exit();
        }
        
    if ($floID[0] != "F"){
            $std->message[] = "FLO ID must begin with F";
                $std->error = true;
                echo json_encode($std);   
                exit();
    }    
            
            
    if (!preg_match("/^[0-9a-zA-Z-' .]*$/",$fullName)) {
                $std->message[] = "Only letters, numbers, period and white space allowed in Full Name";
                $std->error = true;
                echo json_encode($std);   
                exit();
    }
    
    if (!filter_var($emailID, FILTER_VALIDATE_EMAIL)) {
                $std->message[] = "Invalid email format";
                $std->error = true;
                echo json_encode($std);   
                exit();
    }
            

    if (isset($_POST['floID'], $_POST['fullName'], $_POST['emailID'] )){
        
        ob_start();
            
            $check_duplicate_status = true;
            $check_already_exists_status = true; 
            $insert_status = false;

                    $check_duplicate_status = check_duplicate_newUser($floID);
                    $check_already_exists_status = check_flo_id_registration_status($floID);

                    if (($check_duplicate_status == false) && ($check_already_exists_status == false)){ 
                     $insert_status = insert_flo_newUser($floID);   
                    
                     
                    } else {
                      $std->message[] = "New User could not be registered as FLO ID attempted to register earlier. ";
                $std->error = true;
                echo json_encode($std);   
                exit();
                    }
                    
                    if ($insert_status == true){
                    $update_status = update_newUser($floID, $fullName, $emailID);
                    
                    //$std->message[] = "New User Request accepted ". $floID ." ". $fullName ." ". $emailID;
                    
                    //$std->error = false;
                    //echo json_encode($std);
                    // exit();
                    
                    $std->message[] = "New User Request accepted for ". $floID . " You can login after System Admin enables your account, usually within 24 hours.";
                    
                    $std->error = false;
                    echo json_encode($std);
                    exit();     
                
            
            }
        }    
                
              else {
                $std->message[] = "New User could not be registered as either data fields are missing, FLO ID is wrong, or already attempted to register earlier. ";
                $std->error = true;
                echo json_encode($std);
                
        }


