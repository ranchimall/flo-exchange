<?php
    $db_connection;
    //Variables flattened from User 
    $orders_table = ORDERS_TABLE;
    $customers_table = USERS_TABLE;
    $top_buy_table = TOP_BUYS_TABLE;
    $top_sell_table = TOP_SELL_TABLE;
    $customer_balance_table = CREDITS_TABLE;
    $transaction_table = TRANSACTIONS_TABLE;
    $bal_history = CREDITS_HISTORY_TABLE;
    $bank_acc = ACCOUNTS_TABLE;
    $fund_trans = TRANSFER_INFO_TABLE;
    $email = null;
    $name = null;
    $is_active = null;
    $user_is_logged_in = false;
    $errors = array();

    //Variables flattened from Order
    $customerId = 0;
    $orderTypeId = 0;
    $quantity = 0;
    $demanded_qty = 0;
    $price = 0;
    $orderStatusId = 2; //pending
    $max_top_bids = 20;
    $customer_balance = null; // Don't make it 0
    $customer_frozen_balance = null; // Don't make it 0

function databaseConnection(){
    global $db_connection;
            try {
                $db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
                return $db_connection;
            } catch (PDOException $e) {
               // errors[] = $e->getMessage();
            }
        
        return false;
    }

function sanitise_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}  


function insert_balance($CustomerId, $AssetTypeId, $Balance, $FrozenBalance) {
        global $db_connection,$customer_balance_table;
        $now = time_now();
        if ($db_connection) {
            $query = $db_connection->query("INSERT INTO `$customer_balance_table` (`sr_no`, `CustomerId`, `AssetTypeId`, `Balance`, `FrozenBalance`, `UpdateDate`, `InsertDate`, `SaveDate`) VALUES ('', '$CustomerId',$AssetTypeId','$Balance','$FrozenBalance',NULL,'$now','$now')");
                      
                return true;
            
        }
        return false;
    }


function get_total_users_count() {
    global $db_connection;
        if ($db_connection) {
            $total_users = 0;
            $query = $db_connection->query("SELECT COUNT(*) AS TOTAL_COUNT FROM ".USERS_TABLE." WHERE `is_active`=1");
            if ($query->rowCount()) {
                $total_users = $query->fetchObject()->TOTAL_COUNT;
            }
            return (int) $total_users;
        }
        return false;
    }

function time_now() {
        $n = new DateTime("now", new DateTimeZone("Asia/Kolkata"));
        $now = $n->format('Y-m-d H:i:s');
        return $now;
    }

function check_flo_id_active_status($flo_id) {
        global $db_connection,$customers_table;
        if ($db_connection) {

            $query = $db_connection->query("SELECT * FROM $customers_table WHERE flo_id = '$flo_id' AND is_active = 1 LIMIT 1");
            
            
                $row_count = $query->rowCount();
                if ($row_count == 1) {
                    return true;
                }
                
             
        }
        return false;
    }

function check_flo_id_registration_status($flo_id) {
    global $db_connection,$customers_table;
        if ($db_connection) {

            $query = $db_connection->query("SELECT * FROM $customers_table WHERE flo_id = '$flo_id' LIMIT 1");
            
                $row_count = $query->rowCount();
                if ($row_count == 1) {
                    return true;
                }
               
             
        }
        return false;
    }

function checkIfFloIDPermitted($flo_id){
    global $db_connection,$customers_table;
    if ((check_flo_id_registration_status($flo_id) == true) && (check_flo_id_active_status($flo_id) == true)){
            return true;
    }

    return false;
}    

function update_flo_details($floID, $auth_random, $floPublicKey) {
        global $db_connection,$customers_table;
        $now = time_now();
        if ($db_connection) {
            $query = $db_connection->query("UPDATE `$customers_table` SET `auth_random` = '$authRandom', `floPublicKey` = '$floPublicKey', `updateDate` = '$now' WHERE `flo_id` = '$floID' LIMIT 1");
                return true;
            
        }
        return false;
    }

function update_newUser($flo_id, $name, $email) {
        global $db_connection;
        $now = time_now();
        if ($db_connection) {
            $query = $db_connection->query("UPDATE `new_user` SET `full_name` = '$name', `email` = '$email', `insertDate` = '$now' WHERE `flo_id` = '$flo_id' LIMIT 1");
            
                return true;
            
        }
        return false;
    }

function insert_flo_newUser($flo_id) {
        global $db_connection;
        $now = time_now();
        if ($db_connection) {
            $query = $db_connection->query("INSERT INTO new_user (`flo_id`,`insertDate`) VALUES ('$flo_id', '$now')");
            return true;
            
        }
        return false;
    }    

function check_duplicate_newUser($flo_id) {
        global $db_connection;
        if ($db_connection) {

            $query = $db_connection->query("SELECT `flo_id` FROM new_user WHERE flo_id = '$flo_id'");
            

            
                $row_count = $query->rowCount();
                if ($row_count >= 1) {
                    return true;
                }
                
             
        }
        return false;
    }

function newUserList() {
        global $db_connection;
        if ($db_connection) {

            $list = array();
            $query = $db_connection->query("
                SELECT * FROM new_user 
            ");

            if ($query->rowCount() > 0) {
                while ($ls = $query->fetchObject()) {
                    $list[] = $ls;
                }
                return $list;
            }
            return false;
        }
        return false;
    }
    
function findNewUserDetails($flo_id) {
  global $db_connection;
        if ($db_connection) {
            $list = array();
            $query = $db_connection->query("
                SELECT * FROM new_user WHERE `flo_id` = '$flo_id'
            ");

            if ($query->rowCount() == 1) {
                $list = $query->fetchObject(); 
                return $list;
            }
            return false;
        }
        return false;
}    

function deleteNewUser($flo_id) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("DELETE FROM `new_user` WHERE `flo_id`='$flo_id' LIMIT 1");
            return true;
        }
        return false;
}

function acceptUser($flo_id,$name, $email) {
        global $db_connection,$customers_table;
// If username exists only then do FLO ID  insertion        
        if ($db_connection) {
            $now = time_now();
            
// I have added LIMIT 1 .. so only the first FLO ID is allowed. If there are two FLO IDs for same user, the second one will be discarded. MAYBE I SHOULD ADD AN EXPLICT CHECK LATER FOR DUPLICATE FLO ID WHILE BEING INSERTED
            $query = $db_connection->query("SELECT * FROM $customers_table WHERE `flo_id`='$flo_id' LIMIT 1");
            
            

            $rowCount = $query->rowCount();

            if($rowCount) {
                //The case where FLO ID exists in database
                $user_obj = $query->fetchObject();

                $update_query = $db_connection->query("UPDATE $customers_table
                                                            SET `SaveDate`='$now'
                                                            WHERE `flo_id`='flo_id'
                                                            LIMIT 1");
                return true;

            } else {

                //The case when FLO ID does not exist in database
                //NOT NEEDED .. These session variables are set in authenticateMe.php
               $user_name = $name.time();
               
                $query = $db_connection->query("
                    INSERT INTO $customers_table (`CustomerId`, `flo_id`, `Username`, `Email`, `Name`, `UpdateDate`, `InsertDate`, `SaveDate`, `is_active`)
                    VALUES ('','$flo_id','$user_name','$email','$name',NULL,'$now',NULL,1)
                ");

// Here we are setting name = FLO ID. Later we can ask for actual name and email when user logs in second time if both are same, and update
//Also email will be blank here for first time FLO user. That will give a signature to update the email later.                

                
                
                    
//Transfer User: Change it from last InsertID to userid for FLO ID just inserted
                    $user_id = getUserDetails($flo_id)->CustomerId;
                    
                  
                    //$AssetTypeId = 'btc';
                    //$Balance = 0.00;
                    //$FrozenBalance = 0.00;
                    //$crypto = insert_balance($user_id, $AssetTypeId, $Balance, $FrozenBalance);
                    
                     $query1 = $db_connection->query("
                    INSERT INTO `assetbalance` (`sr_no`, `CustomerId`, `AssetTypeId`, `Balance`, `FrozenBalance`, `UpdateDate`, `InsertDate`, `SaveDate`) VALUES (NULL, '$user_id', 'btc', '0.00', '0.00', '$now', '$now', '$now')
                ");

                   // $AssetTypeId = 'traditional';
                //    $Balance = 0.00;
                  //  $FrozenBalance = 0.00;
                   // $cash = insert_balance($user_id, $AssetTypeId, $Balance, $FrozenBalance);

                    $query2 = $db_connection->query("
                    INSERT INTO `assetbalance` (`sr_no`, `CustomerId`, `AssetTypeId`, `Balance`, `FrozenBalance`, `UpdateDate`, `InsertDate`, `SaveDate`) VALUES (NULL, '$user_id', 'traditional', '0.00', '0.00', '$now', '$now', '$now')
                ");
                    return true;
                
            }
        } 
    }


function check_user($customerId) {
        global $db_connection,$customers_table;
        if ($db_connection) {

            $query = $db_connection->query("SELECT * FROM $customers_table WHERE customerId = '$customerId' AND is_active = 1 LIMIT 1");
            

            
                $row_count = $query->rowCount();
                if ($row_count == 1) {
                    return $user_details = $query->fetchObject();
                } 
            }
        
        return false;
    }

    function displayUserTransaction($user_id, $start=0, $limit=10) {
        global $db_connection;
        if ($db_connection) {
            $transactions = array();

            $query = $db_connection->query("
                SELECT TransactionId AS T_ID, a_buyer AS BUYER_ID, b_seller AS SELLER_ID, (SELECT ".USERS_TABLE.".Name FROM ".USERS_TABLE." WHERE ".USERS_TABLE.".CustomerId=BUYER_ID) AS BUYER, (SELECT ".USERS_TABLE.".Name FROM ".USERS_TABLE." WHERE ".USERS_TABLE.".CustomerId=SELLER_ID) AS SELLER, B_AMOUNT AS TRADE_PRICE, ".TRANSACTIONS_TABLE.".InsertDate, ".TRANSACTIONS_TABLE.".qty_traded AS TRADED_QTY
                FROM ".TRANSACTIONS_TABLE.", ".USERS_TABLE."
                WHERE `a_buyer`= '$user_id' OR `b_seller`= '$user_id'
                GROUP BY T_ID
                ORDER BY T_ID DESC
                LIMIT $start, $limit
            ");
            
            
                $rowCount = $query->rowCount();
                if ($rowCount > 0) {
                    while ($tr = $query->fetchObject()) {
                        $transactions[] = $tr;
                    }
                }
            
            return $transactions;
        }
        return false;
    }

    function list_messages_by_userId($user_id, $start=0, $limit=10) {
        global $db_connection;
        if ($db_connection) {
            $messages = array();

            $query = $db_connection->query("
                SELECT * FROM ".MSG_TABLE." WHERE `username_key`= '$user_id'
                ORDER BY datetime DESC 
                LIMIT $start, $limit
             ");
            
            
                $rowCount = $query->rowCount();
                if ($rowCount > 0) {
                    while ($tr = $query->fetchObject()) {
                        $messages[] = $tr;
                    }
                }
            
            return $messages;
        }
        return false;
    }

    function actions_user($u_id, $act=1) {
        global $db_connection;
        if ($db_connection) {
            if (!empty($u_id)) {

                $act = (int) $act;
                $u_id = (int) $u_id;

                $query = $db_connection->query("
                    UPDATE ".USERS_TABLE." SET `is_active`= '$act' 
                    WHERE CustomerId = '$u_id'
                    LIMIT 1
                ");
                

                
                    return true;
                
            }
        }
        return false;
    }
    
    


    function get_username($customerId=0) {
        global $db_connection;
        if ($db_connection) {
            $customerId = (int) $customerId;
            $query = $db_connection->query("SELECT Username FROM ".USERS_TABLE." WHERE customerId = '$customerId' LIMIT 1");
                        
            $row_count = $query->rowCount();
            if ($row_count == 1) {
                return $query->fetchObject()->Username;
            }
        }
        return false;
    }

    function getUserDetails($flo_id) {
        global $db_connection;
        if ($db_connection) {
            $customerId = (int) $customerId;
            $query = $db_connection->query("SELECT CustomerId,Name,Email FROM ".USERS_TABLE." WHERE flo_id = '$flo_id' LIMIT 1");
                        
            $row_count = $query->rowCount();
            if ($row_count == 1) {
                return $query->fetchObject();
            }
        }
        return false;
    }

    function input_user_email($email=null, $user_id=null) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("
            UPDATE ".USERS_TABLE." SET `Email`= '$email' WHERE CustomerId = '$user_id'
            ");
            

            
                return true;
            
        }
        return false;
    }




    function doInitialUserHandling($flo_id) {
        global $db_connection,$customers_table;
// If username exists only then do FLO ID  insertion        
        if ($db_connection) {
            $now = time_now();
            
// I have added LIMIT 1 .. so only the first FLO ID is allowed. If there are two FLO IDs for same user, the second one will be discarded. MAYBE I SHOULD ADD AN EXPLICT CHECK LATER FOR DUPLICATE FLO ID WHILE BEING INSERTED
            $query = $db_connection->prepare("SELECT * FROM $customers_table WHERE `flo_id`=:flo_id LIMIT 1");
            $query->bindValue(':flo_id', $flo_id, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount) {
                //The case where FLO ID exists in database
                $user_obj = $query->fetchObject();

                $update_query = $db_connection->prepare("UPDATE $customers_table
                                                            SET `SaveDate`='$now'
                                                            WHERE `flo_id`=:flo_id
                                                            LIMIT 1");
                $update_query->bindValue(':flo_id', $flo_id, PDO::PARAM_STR);
                $update_query->execute();

                if (!isset($_SESSION['last_trade_date'])) {
                    $_SESSION['last_trade_date'] = $user_obj->SaveDate;
                }
                return true;

            } else {

                //The case when FLO ID does not exist in database
                //NOT NEEDED .. These session variables are set in authenticateMe.php
               $user_name = $flo_id.time();
               $email = (isset($_SESSION['email'])) ? $_SESSION['email'] : "";
                $name = (isset($_SESSION['user_name'])) ? $_SESSION['user_name'] : "";

                $query = $db_connection->prepare("
                    INSERT INTO $customers_table (`CustomerId`, `flo_id`, `Username`, `Email`, `Name`, `UpdateDate`, `InsertDate`, `SaveDate`, `is_active`)
                    VALUES ('',:flo_id,:Username,:Email,:Name,NULL,'$now',NULL,0)
                ");

// Here we are setting name = FLO ID. Later we can ask for actual name and email when user logs in second time if both are same, and update
//Also email will be blank here for first time FLO user. That will give a signature to update the email later.                

                $query->bindValue(':flo_id', $flo_id, PDO::PARAM_INT);
                $query->bindValue(':Username', $user_name, PDO::PARAM_STR);
                $query->bindValue(':Email', $email, PDO::PARAM_STR);
                $query->bindValue(':Name', $name, PDO::PARAM_STR);
                if($query->execute()) {
                    $_SESSION['user_id'] = $db_connection->lastInsertId();
                    
                  
                    $AssetTypeId = 'btc';
                    $Balance = 0.00;
                    $FrozenBalance = 0.00;
                    $crypto = insert_balance($_SESSION['user_id'], $AssetTypeId, $Balance, $FrozenBalance);

                    $AssetTypeId = 'traditional';
                    $Balance = 0.00;
                    $FrozenBalance = 0.00;
                    $cash = insert_balance($_SESSION['user_id'], $AssetTypeId, $Balance, $FrozenBalance);

                    $user_exist = check_user($_SESSION['user_id']);
                    if($user_exist && $crypto && $cash) {
                        return true;
                    }
                    return false;
                }
                return false;
            }
        } else {
            return false;
        }
    }



//Order flattening starts here
    function insert_order_in_active_table($top_table, $orderId, $price, $quantity) {
        global $db_connection;
        if ($db_connection) {

            $n = new DateTime("now", new DateTimeZone("Asia/Kolkata"));
            $now = $n->format('Y-m-d H:i:s');
            $user_id = $_SESSION['user_id'];

            $query = $db_connection->query("INSERT INTO $top_table(`price`, `orderId`, `quantity`, `customerId`, `insertDate`)
                      VALUES ('$price', '$orderId', '$quantity','$user_id' , '$now')");
          

            
                updateOrderStatus($orderId, 3);
                return true;
            
            
        }
        return false;
    }



    function check_customer_balance($assetType, $user_id) {
        global $db_connection,$customer_balance_table;
        if ($db_connection) {

            $customer_balance = null;
            $query = $db_connection->query("SELECT `Balance`, FrozenBalance
                                    FROM $customer_balance_table
                                    WHERE `CustomerId`= '$user_id' AND `AssetTypeId`='$assetType'");
            
            
                if ($query->rowCount()) {
                    $customer_balance = $query->fetchObject();
                }
            
            return $customer_balance;
        }
        return false;
    }

    function record_bal_history($user_id, $balance, $type) {
        global $db_connection,$bal_history;
        if ($db_connection) {

            $now = time_now();

            $query = $db_connection->query("INSERT INTO $bal_history (`id`, `user_id`, `balance`, `AssetType`, `datetime`) 
                      VALUES ('', '$user_id', '$balance', '$type', '$now')");
            
            
                return true;
            
        }
        return false;
    }

    function update_user_balance($assetType, $balance=null, $user_id) {
        global $db_connection,$customer_balance_table;
        if ($db_connection) {
            $now = time_now();
            $sql = "";
            if ($balance >= 0) {
                $sql .= "UPDATE $customer_balance_table ";
                $sql .= " SET `Balance`= '$balance', ";
                $sql .= " `UpdateDate`= '$now' ";
                $sql .= " WHERE `CustomerId`= '$user_id' ";
                $sql .= " AND `AssetTypeId`= '$assetType' ";
                $sql .= "LIMIT 1";

                $query = $db_connection->query($sql);
                record_bal_history($user_id, $balance, $assetType);
                return true;
                
            }
          }
        return false;
    }

    function insert_pending_order($orderTypeId1, $qty, $price1, $orderStatusId1, $OfferAssetTypeId=null, $WantAssetTypeId=null) {
        global $db_connection,$customerId,$orderTypeId,$price,$quantity,$orderStatusId,$customer_balance,$orders_table,$top_buy_table,$top_sell_table;
        if ($db_connection) {
            $now = time_now();
            $messages = null;

            $customerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $orderTypeId = $orderTypeId1;  // 0-> buy; 1 -> sell;
            $quantity = $qty;
            $price = $price1;
            $orderStatusId = $orderStatusId1;  // 0 -> cancelled; 1 -> complete; 2 -> pending; 3 ->order active

            $std = new stdClass();
            $std->insertedrowid = null;
            $std->orderTypeId = null; // 0 -> buy; 1 -> sell
            $std->item_qty = null;
            $std->item_price = null;
            $std->orderStatusId = null;
            $std->insert_date = null;
            $std->error = true;
            $std->message = null;

            // check user balance
            $assetType = null;
            $total_trade_val = null;
            if ($orderTypeId == 0) {
                $assetType = 'traditional';
                $total_trade_val = $quantity * $price;
            } else if ($orderTypeId == 1) {
                $assetType = 'btc';
                $total_trade_val = $quantity;
            }
            $cBalance = check_customer_balance($assetType, $customerId);
            $customer_bal = (float)$cBalance->Balance;

            $customer_balance = $customer_bal;

            if ($customer_balance == '' || $customer_balance == null || !is_float($customer_balance)) {
                $messages = "0 balance: Your account balance is nil.";
                $std->message = $messages;
                storeMessages($order_id=null, $customerId, $messages);
                return $std;
            }

            if ($total_trade_val > $customer_balance) {
                $messages = "Insufficient balance: You have insufficient balance to continue this trade. Please recharge your wallet or lower the quantity.";
                $std->message = $messages;
                storeMessages($order_id=null, $customerId, $messages);
                return $std;
            }

            $query = $db_connection->query("INSERT INTO $orders_table (`OrderId`, `CustomerId`, `OrderTypeId`, `OfferAssetTypeId`, `WantAssetTypeId`, `Quantity`, `Price`, `OrderStatusId`, `UpdateDate`, `InsertDate`, `SaveDate`)
                                    VALUES ('', " . $customerId . ", " . $orderTypeId . ", '$OfferAssetTypeId', '$WantAssetTypeId', " . $quantity . "," . $price . ", " . $orderStatusId . ", NULL, '$now', NULL)");

            
            

                $insertedrowid = $db_connection->lastInsertId();

                // Check if $price is eligible to be inserted into top_buy or top_sell table
                $top_table = null;
                $asc_desc = null;
                $new_balance = null;
                $new_frozenbalance = null;

                if ($orderTypeId == '0') {
                    $top_table = $top_buy_table;
                } else if ($orderTypeId == '1') {
                    $top_table = $top_sell_table;
                }

                $trade_type = ($orderTypeId==1) ? "sell" : "buy";
                $messages = "You entered a $trade_type order for $qty token at $ $price per token for $ ".$qty*$price;

                storeMessages($insertedrowid, $customerId, $messages);

                // Change the order status to active and insert in active table in DB
                $insert_in_active_table = insert_order_in_active_table($top_table, $insertedrowid, $price, $quantity);

                $orderStatusId = 3; // order activated

                $std = new stdClass();
                $std->insertedrowid = $insertedrowid;
                $std->made_to_active_list = $insert_in_active_table;
                $std->orderTypeId = $orderTypeId; // 0 -> buy; 1 -> sell
                $std->item_qty = $qty;
                $std->item_price = $price1;
                $std->orderStatusId = $orderStatusId;
                $std->insert_date = date('Y-m-d H:i:s');
                $std->error = false;
                $std->message = "Order moved to active table.";

                return $std;
            
        }
        return false;
    }

    function get_top_buy_sell_list($top_table, $asc_desc) {
        global $db_connection,$max_top_bids;
        if ($db_connection) {

            $top_list = array();

            $query = $db_connection->query("SELECT $top_table.OrderId, $top_table.customerId, $top_table.Quantity, $top_table.Price, ".USERS_TABLE.".Name
                                    FROM $top_table, ".USERS_TABLE."
                                    WHERE $top_table.customerId = ".USERS_TABLE.".CustomerId
                                    ORDER BY price $asc_desc
                                    LIMIT $max_top_bids
                                    ");

            if ($query) {

                $rowCount = $query->rowCount();

                if ($rowCount > 0) {

                    while ($orders = $query->fetchObject()) {

                        $top_list[] = $orders;

                    }
                }

            } else {
                return false;
            }
            return $top_list;
        }
        return false;
    }









    function get_all_buy_sell_list($buy_or_sell_id, $AscDesc) {
        global $db_connection,$orders_table,$customers_table;
        if ($db_connection) {

            $buy_or_sell_list = array();

            $query = $db_connection->query("SELECT $orders_table.OrderId, $customers_table.CustomerId, $customers_table.Name, $orders_table.Quantity, $orders_table.Price, ($orders_table.Quantity * $orders_table.Price) AS TOTAL_COST,  $orders_table.OrderStatusid, $orders_table.InsertDate
                                    FROM $orders_table, $customers_table
                                    WHERE $orders_table.OrderTypeId = '$buy_or_sell_id'
                                    GROUP BY $orders_table.Price $AscDesc");

                $rowCount = $query->rowCount();
                if ($rowCount > 0) {
                    while ($orders = $query->fetchObject()) {
                        $buy_or_sell_list[] = $orders;
                    }
                }
            return $buy_or_sell_list;
        }
        return false;
    }

        function get_active_order_of_user($user_id, $top_table) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("
                SELECT * FROM $top_table WHERE `customerId`= '$user_id' ORDER BY `insertDate` DESC 
            ");
            

            $arr = array();
            while ($qr = $query->fetchObject()) {
                $arr[] = $qr;
            }
            return $arr;
        }
        return false;
    }

        function OrderMatchingQuery() {
            global $db_connection,$top_sell_table,$top_buy_table,$orders_table;
        if ($db_connection) {

            $query = $db_connection->query("
                SELECT $top_sell_table.orderId, $top_sell_table.price, $top_sell_table.Quantity, $top_sell_table.orderId, (SELECT `CustomerId` FROM $top_buy_table ORDER BY price DESC LIMIT 1) AS BUYER_ID, $top_sell_table.CustomerId AS SELLER_ID
                FROM $top_sell_table, $orders_table
                WHERE (
                 ($top_sell_table.price <= (SELECT `price` FROM `$top_buy_table` ORDER BY price DESC LIMIT 1))
                 AND ($orders_table.OrderId = $top_sell_table.orderId)
                 AND ($orders_table.OrderStatusId = '3')
                 AND ($orders_table.OrderTypeId= '1') )
                ORDER BY $top_sell_table.price ASC
            ");

            if($rowCount = $query->rowCount() > 0) { // Transaction is possible
                $matched_orders = array();
                while ($obj = $query->fetchObject()) {
                    $matched_orders[] = $obj;
                }
                return $matched_orders;
            }
            return false;
        }
        return false;
    }

        function get_highest_demand() {
        global $db_connection,$top_buy_table,$orders_table;
        if ($db_connection) {

            $query = $db_connection->query("SELECT $top_buy_table.OrderId, $top_buy_table.Price, $top_buy_table.Quantity FROM `$orders_table`, `$top_buy_table` WHERE $orders_table.OrderId = $top_buy_table.orderId ORDER BY $top_buy_table.price DESC LIMIT 1");
            $rowCount_Qty = $query->rowCount();
            if (!$rowCount_Qty) {
                return false;
            }
            return $highest_demanded = $query->fetchObject();
        }
        return false;
    }

        function updateOrderStatus($orderId=null, $status=null) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("UPDATE ".ORDERS_TABLE." SET `OrderStatusId`= '$status' WHERE `OrderId` = '$orderId' LIMIT 1");
                return true;
       }
        return false;
    }

        function OrderMatchingService() {
            global $db_connection,$top_buy_table,$top_sell_table;
        if ($db_connection) {

            $message = array();
            $trade_qty = 0;

            $demanded_qty = (get_highest_demand() != false) ? get_highest_demand()->Quantity : 0;
            $buy_order_id = (get_highest_demand() != false) ? get_highest_demand()->OrderId : 0;
            $buy_amount = (get_highest_demand() != false) ? get_highest_demand()->Price : 0;

            $supply_available = OrderMatchingQuery();

            if ($demanded_qty == false || $demanded_qty == '' || $demanded_qty == '0' || $supply_available == false || $supply_available == '' || !is_array($supply_available) || empty($supply_available)) {
                return false;
            }

            $demanded_qty = (float)$demanded_qty;

            if(is_array($supply_available) && !empty($supply_available) ) {

                foreach ($supply_available as $available) {

                    if ($demanded_qty > 0) {
                        $supply_available = OrderMatchingQuery();
                        $seller_order_id = (int)$available->orderId;
                        $available->Quantity = (float)$available->Quantity;
                        $seller_id = $available->SELLER_ID;
                        $seller_balance_btc = check_customer_balance($assetType = 'btc', $seller_id)->Balance;
                        $seller_balance_cash = check_customer_balance($assetType = 'traditional', $seller_id)->Balance;

                        $buyer_id = $available->BUYER_ID;
                        $buyer_balance_btc = check_customer_balance($assetType = 'btc', $buyer_id)->Balance;
                        $buyer_balance_cash = check_customer_balance($assetType = 'traditional', $buyer_id)->Balance;

                        if ($demanded_qty > $available->Quantity) {

                            $trade_qty = (float) $available->Quantity;

                            $cost_of_total_supply = $available->Quantity * $available->price; // traditional or cash
                            $cost_of_total_supply = (float) $cost_of_total_supply;

                            if ($buyer_balance_cash < $cost_of_total_supply) {
                                //Record the message
                                storeMessages($buy_order_id, $buyer_id, $msg="Transaction failed: You have insufficient cash balance.");
                                if ($_SESSION['user_id'] == $buyer_id) {
                                    $message[] = "Transaction failed: You have insufficient cash balance.";
                                }
                                // Delete the culprit order
                                del_order($buy_order_id, $buyer_id);
                                break;
                            }
                            if (($seller_balance_btc == 0) || ($seller_balance_btc < $available->Quantity)) {
                                //Record the message
                                storeMessages($seller_order_id, $seller_id, $msg="Transaction failed: You had insufficient RMT balance");
                                if ($_SESSION['user_id'] == $seller_id) {
                                    $message[] = "Transaction failed: You had insufficient RMT balance";
                                }
                                del_order($seller_order_id, $seller_id);
                                break;
                            }

                            if ($buyer_id != $seller_id) {
                                $new_seller_balance_cash = $seller_balance_cash + $cost_of_total_supply;  // traditional or cash
                                $new_seller_balance_btc = $seller_balance_btc - $available->Quantity; // deduct the btc sold

                                $new_buyer_balance_btc = $buyer_balance_btc + $available->Quantity; // btc
                                $new_buyer_balance_cash = $buyer_balance_cash - $cost_of_total_supply; // traditional or cash

                                // subtract the debit access (customers balance of $ or BTC)
                                update_user_balance($assetType = 'btc', $balance = $new_buyer_balance_btc, $buyer_id);

                                // increment the credit asset (customers balance of $ or BTC)
                                update_user_balance($assetType = 'traditional', $balance = $new_seller_balance_cash, $seller_id);

                                // record the commission in the commission account
                                // decrease respective balances
                                update_user_balance($assetType = 'btc', $balance = $new_seller_balance_btc, $seller_id);
                                update_user_balance($assetType = 'traditional', $balance = $new_buyer_balance_cash, $buyer_id);
                            }

                            $demanded_qty = $demanded_qty - $available->Quantity;

                            // update the quantity field for demand
                            update_quantity($top_table = $top_buy_table, $demanded_qty, $buy_order_id);

                            // Delete the row from Sell list
                            delete_order($top_sell_table, $available->orderId);

                            // Mark this order status 1 i.e transaction successful
                            updateOrderStatus($available->orderId, $status='1');

                            // Record messages
                            $buyer_msg = "Transaction successful: You bought $available->Quantity RMT for $ $cost_of_total_supply at the rate of $ $available->price per token.";
                            $seller_msg = "Transaction successful: You sold $available->Quantity RMT for $ $cost_of_total_supply at the rate of $ $available->price per token.";
                            storeMessages($buy_order_id, $buyer_id, $msg=$buyer_msg);
                            storeMessages($seller_order_id, $seller_id, $msg=$seller_msg);

                            if(isset($_SESSION['user_id'])) {
                                $logged_in_user = (int) $_SESSION['user_id'];
                                if (check_user($logged_in_user) != false) {
                                    if ($logged_in_user == $buyer_id) {
                                        $message[] = $buyer_msg;
                                    } else if($logged_in_user == $seller_id) {
                                        $message[] = $seller_msg;
                                    }
                                }
                            }

                            $available->Quantity = 0;

                        } elseif ($demanded_qty == $available->Quantity) {

                            $trade_qty = (float) $available->Quantity;

                            $cost_of_total_supply = $available->Quantity * $available->price; // traditional or cash
                            $cost_of_total_supply = (float) $cost_of_total_supply;

                            if ($buyer_balance_cash < $cost_of_total_supply) {
                                //Record the message
                                storeMessages($buy_order_id, $buyer_id, $msg="Transaction failed: You had insufficient cash balance.");
                                if ($_SESSION['user_id'] == $buyer_id) {
                                    $message[] = "Transaction failed: You have insufficient cash balance.";
                                }
                                del_order($buy_order_id, $buyer_id);
                                break;
                            }
                            if (($seller_balance_btc == 0) || ($seller_balance_btc < $available->Quantity)) {
                                //Record the message
                                storeMessages($seller_order_id, $seller_id, $msg="Transaction failed: You had insufficient RMT balance.");
                                if ($_SESSION['user_id'] == $seller_id) {
                                    $message[] = "Transaction failed: You had insufficient RMT balance";
                                }
                                del_order($seller_order_id, $seller_id);
                                break;
                            }

                            if ($buyer_id != $seller_id) {
                                $new_seller_balance_cash = $seller_balance_cash + $cost_of_total_supply;  // traditional or cash
                                $new_seller_balance_btc = $seller_balance_btc - $available->Quantity; // deduct the btc sold

                                $new_buyer_balance_btc = $buyer_balance_btc + $available->Quantity; // btc
                                $new_buyer_balance_cash = $buyer_balance_cash - $cost_of_total_supply; // traditional or cash

                                // subtract the debit access (customers balance of $ or BTC)
                                update_user_balance($assetType = 'btc', $balance = $new_buyer_balance_btc, $user_id = $buyer_id);

                                // increment the credit asset (customers balance of $ or BTC)
                                update_user_balance($assetType = 'traditional', $balance = $new_seller_balance_cash, $user_id = $seller_id);

                                // record the commission in the commission account
                                // decrease respective balances
                                update_user_balance($assetType = 'btc', $balance = $new_seller_balance_btc, $user_id = $seller_id);
                                update_user_balance($assetType = 'traditional', $balance = $new_buyer_balance_cash, $user_id = $buyer_id);
                            }

                            // Delete the row from Sell list And Buy list
                            delete_order($top_sell_table, $available->orderId);
                            delete_order($top_buy_table, $buy_order_id);

                            // Mark trades of buyer & seller 1 i.e 'successful'
                            updateOrderStatus($available->orderId, $status='1');
                            updateOrderStatus($buy_order_id, $status='1');

                            // Record messages
                            $buyer_msg = "Transaction successful: You bought $available->Quantity RMT for $ $cost_of_total_supply at the rate of $ $available->price per token.";
                            $seller_msg = "Transaction successful: You sold $available->Quantity RMT for $ $cost_of_total_supply at the rate of $ $available->price per token.";
                            storeMessages($buy_order_id, $buyer_id, $msg=$buyer_msg);
                            storeMessages($seller_order_id, $seller_id, $msg=$seller_msg);

                            if(isset($_SESSION['user_id'])) {
                                $logged_in_user = (int) $_SESSION['user_id'];
                                if (check_user($logged_in_user) != false) {
                                    if ($logged_in_user == $buyer_id) {
                                        $message[] = $buyer_msg;
                                    } else if($logged_in_user == $seller_id) {
                                        $message[] = $seller_msg;
                                    }
                                }
                            }

                            // save changes
                            $demanded_qty = 0;
                            $available->Quantity = 0;

                        } elseif ($demanded_qty < $available->Quantity) {

                            $trade_qty = (float) $demanded_qty;

                            $cost_of_total_supply = $demanded_qty * $available->price; // traditional or cash
                            $cost_of_total_supply = (float) $cost_of_total_supply;

                            if ($buyer_balance_cash < $cost_of_total_supply) {
                                //Record the message
                                storeMessages($buy_order_id, $buyer_id, $msg="Transaction failed: You had insufficient cash balance.");
                                if ($_SESSION['user_id'] == $buyer_id) {
                                    $message[] = "Transaction failed: You have insufficient cash balance.";
                                }
                                del_order($buy_order_id, $buyer_id);
                                break;
                            }
                            if (($seller_balance_btc == 0) || ($seller_balance_btc < $demanded_qty)) {
                                //Record the message
                                storeMessages($seller_order_id, $seller_id, $msg="Transaction failed: You had insufficient RMT balance.");
                                if ($_SESSION['user_id'] == $seller_id) {
                                    $message[] = "Transaction failed: You had insufficient RMT balance";
                                }
                                del_order($seller_order_id, $seller_id);
                                break;
                            }

                            if ($buyer_id != $seller_id) {
                                $new_seller_balance_cash = $seller_balance_cash + $cost_of_total_supply;  // traditional or cash
                                $new_seller_balance_btc = $seller_balance_btc - $demanded_qty; // deduct the btc sold

                                $new_buyer_balance_btc = $buyer_balance_btc + $demanded_qty; // btc
                                $new_buyer_balance_cash = $buyer_balance_cash - $cost_of_total_supply; // traditional or cash

                                // subtract the debit access (customers balance of $ or BTC)
                                update_user_balance($assetType = 'btc', $balance = $new_buyer_balance_btc, $user_id = $buyer_id);

                                // increment the credit asset (customers balance of $ or BTC)
                                update_user_balance($assetType = 'traditional', $balance = $new_seller_balance_cash, $user_id = $seller_id);

                                // record the commission in the commission account
                                // decrease respective balances
                                update_user_balance($assetType = 'btc', $balance = $new_seller_balance_btc, $user_id = $seller_id);

                                update_user_balance($assetType = 'traditional', $balance = $new_buyer_balance_cash, $user_id = $buyer_id);

                            }

                            // update the quantity field for $availableQuantity
                            $availableQuantity = $available->Quantity - $demanded_qty;
                            update_quantity($top_table = $top_sell_table, $availableQuantity, $available->orderId);

                            // Delete the row from Buy list
                            delete_order($top_buy_table, $buy_order_id);

                            // Mark this order status 1 i.e transaction successful
                            updateOrderStatus($buy_order_id, $status='1');

                            // Record messages
                            $buyer_msg = "Transaction successful: You bought $demanded_qty RMT for $ $cost_of_total_supply at the rate of $ $available->price per token.";
                            $seller_msg = "Transaction successful: You sold $demanded_qty RMT for $ $cost_of_total_supply at the rate of $ $available->price per token.";
                            storeMessages($buy_order_id, $buyer_id, $msg=$buyer_msg);
                            storeMessages($seller_order_id, $seller_id, $msg=$seller_msg);

                            if(isset($_SESSION['user_id'])) {
                                $logged_in_user = (int) $_SESSION['user_id'];
                                if (check_user($logged_in_user) != false) {
                                    if ($logged_in_user == $buyer_id) {
                                        $message[] = $buyer_msg;
                                    } else if($logged_in_user == $seller_id) {
                                        $message[] = $seller_msg;
                                    }
                                }
                            }

                            // save changes
                            $demanded_qty = 0;
                        }

                        // Record the transaction
                        record_transaction($buyer_id,$buy_order_id, $buy_amount, $buy_commission='0', $seller_id, $available->orderId, $available->price, $sell_commission = '0', $trade_qty);
                    } else {
                        return false;
                    }
                    OrderMatchingQuery();
                }
                return $message;
            }
            return false;
        }
        return false;
    }

    function record_transaction($buyer, $buy_order_id, $buy_amount, $buy_commission, $seller, $sell_order_id, $sell_amount, $sell_commission, $trade_qty) {
        global $db_connection,$transaction_table;
        if ($db_connection) {
            $now = time_now();
            $query = $db_connection->query("
            INSERT INTO $transaction_table (`TransactionId`, `a_buyer`, `A_OrderId`, `A_Amount`, `A_Commission`, `b_seller`, `B_OrderId`, `B_Amount`, `B_Commission`, `qty_traded`, `UpdateDate`, `InsertDate`, `SaveDate`)
            VALUES ('', '$buyer','$buy_order_id', '$buy_amount', '$buy_commission', '$seller', '$sell_order_id', '$sell_amount', 'sell_commission', '$trade_qty', NULL, '$now', '$now')
            ");
                 return true;
            
        }
        return false;
    }

    function delete_order($top_table, $orderId) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("DELETE FROM `$top_table` WHERE `orderId`='$orderId' LIMIT 1");
            return true;
        }
        return false;
    }

    function update_quantity($top_table, $qty, $orderId) {
        global $db_connection;
        if ($db_connection) {
            $now = time_now();
            $query = $db_connection->query("
                UPDATE $top_table
                SET `quantity`= '$qty', `insertDate`='$now'
                WHERE orderId = '$$orderId'
                LIMIT 1
            ");
            
                return true;
            }
        return false;
    }

    function insert_market_order($customerId, $orderTypeId, $OfferAssetTypeId=null, $WantAssetTypeId=null, $qty, $price) {
        global $db_connection,$orders_table;
        if ($db_connection) {
            $now = time_now();
            $query = $db_connection->query("INSERT INTO $orders_table (`OrderId`, `CustomerId`, `OrderTypeId`, `OfferAssetTypeId`, `WantAssetTypeId`, `Quantity`, `Price`, `OrderStatusId`, `MarketOrder`, `UpdateDate`, `InsertDate`, `SaveDate`)
                                VALUES ('', '$customerId', '$orderTypeId', '$OfferAssetTypeId', '$WantAssetTypeId', '$qty', '$price', 1, 1, NULL, '$now', NULL)
                              ");
            
            
                $insertedrowid = $db_connection->lastInsertId();

                $trade_type = ($orderTypeId=='1') ? "sell" : "buy";
                $messages = "You entered an instant $trade_type order for $qty token at $ $price per token for $ ".$qty*$price;
                storeMessages($insertedrowid, $customerId, $messages);

                return (int) $insertedrowid;
            }
        return false;
    }


    
    function market_order($order_type, $qty) {
        global $db_connection,$top_sell_table,$top_buy_table;
        if ($db_connection) {

            $message = array();

            // Check if it is a buy or sell
            if ($order_type == 'buy') {

                if(is_float($qty) || is_int($qty)) {
                    if ($qty > 0) {

                        $sell_list = get_top_buy_sell_list($top_table=TOP_SELL_TABLE, $asc_desc='ASC');

                        if (!empty($sell_list)) {

                            //Code to find the last iteration of loop. Required to print shortage of token supply.
                            end($sell_list);
                            $last_iter = key($sell_list);

                            foreach ($sell_list as $key=>$available) {

                                $trade_qty = 0;
                                $sell_order_id = (int)$available->OrderId;
                                $available->Quantity = (float)$available->Quantity;

                                if ($available->Quantity <= 0 || $qty <= 0) {
                                    if (isset($message)) {
                                        if ($available->Quantity <= 0) {
                                            $message[] = "Oops! There's shortage of the availability of tokens.";
                                        } else if($qty <= 0) {
                                            //$message[] = "The demanded asset is nill.";
                                        }
                                    } else {
                                        exit;
                                    }
                                    return $message;
                                }

                                $seller_id = $available->customerId;
                                $seller_cash_balance = (float) check_customer_balance($assetType = 'traditional', $seller_id)->Balance;
                                $seller_bit_balance = (float) check_customer_balance($assetType = 'btc', $seller_id)->Balance;
                                $buyer_id = $_SESSION['user_id'];
                                $buyer_cash_balance = (float) check_customer_balance($assetType = 'traditional', $buyer_id)->Balance;
                                $buyer_bit_balance = (float) check_customer_balance($assetType = 'btc', $buyer_id)->Balance;

                                switch ($qty) {
                                    case ($qty > $available->Quantity):

                                        $trade_qty = (float)$available->Quantity;
                                        $cost_of_total_supply = $available->Quantity * $available->Price; // traditional or cash

                                        if ($buyer_cash_balance < $cost_of_total_supply) {
                                            $message[] = "Transaction failed: You have insufficient cash balance.";
                                            storeMessages($buy_order_id=null, $buyer_id, $msg="Transaction failed: You had insufficient cash balance.");
                                            return $message;
                                        }
                                        if (($seller_bit_balance == 0) || ($seller_bit_balance < $available->Quantity)) {
                                            $message[] = "Transaction failed: The seller has insufficient token balance.";
                                            storeMessages($sell_order_id, $seller_id, $msg="Transaction failed: The seller has insufficient token balance.");
                                            return $message;
                                        }

                                        $new_seller_cash_balance = $seller_cash_balance + $cost_of_total_supply;  // traditional or cash
                                        $new_seller_bit_balance = $seller_bit_balance - $available->Quantity;
                                        $new_buyer_bit_balance = $buyer_bit_balance + $available->Quantity; // traditional or cash
                                        $new_buyer_cash_balance = $buyer_cash_balance - $cost_of_total_supply; // traditional

                                        $insert_market_order = insert_market_order($_SESSION['user_id'], $orderTypeId='0', $OfferAssetTypeId='USD', $WantAssetTypeId='RMT', $available->Quantity, $available->Price);

                                        $buy_order_id = 0;
                                        if($insert_market_order == false) {
                                            return false;
                                        } else if(is_int($insert_market_order)) {
                                            $buy_order_id = (int)$insert_market_order;
                                        } else {
                                            return false;
                                        }

                                        if ($buyer_id != $seller_id) {
                                            // increment the bits of buyer
                                            update_user_balance($assetType = 'btc', $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

                                            // deduct cash of buyer
                                            update_user_balance($assetType = 'traditional', $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

                                            // increase the cash of seller
                                            update_user_balance($assetType = 'traditional', $balance = $new_seller_cash_balance, $user_id = $seller_id);

                                            // deduct bits of seller
                                            update_user_balance($assetType = 'btc', $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                        }

                                        // Record the transaction
                                        record_transaction($buyer_id, $buy_order_id, $available->Price, $buy_commission='0', $seller_id, $available->OrderId, $available->Price, $sell_commission = '0', $trade_qty);

                                        // Delete the row from Sell list
                                        delete_order($top_sell_table, $available->OrderId);

                                        // Update Order Status in Order table
                                        UpdateOrderStatus($available->OrderId, '1');

                                        $message[] = "Instant Transaction Successful: You bought $available->Quantity RMT at $ $available->Price per token for $ $cost_of_total_supply.";

                                        // Record message in DB
                                        storeMessages($buy_order_id, $buyer_id, $msg="Instant Transaction Successful: You bought $available->Quantity RMT at $ $available->Price per token for $ $cost_of_total_supply.");
                                        storeMessages($sell_order_id, $seller_id, $msg="Transaction Successful: You sold $available->Quantity RMT at $ $available->Price per token for $ $cost_of_total_supply.");

                                        $qty = $qty - $available->Quantity;
                                        $available->Quantity = 0;

                                        // save changes
                                        break;
                                    case ($qty == $available->Quantity):

                                        $trade_qty = (float)$available->Quantity;
                                        $cost_of_total_supply = $available->Quantity * $available->Price; // traditional or cash

                                        if ($buyer_cash_balance < $cost_of_total_supply) {
                                            $message[] = "Instant Transaction failed: You have insufficient cash balance.";
                                            storeMessages($buy_order_id=null, $buyer_id, $msg="Transaction failed: You had insufficient cash balance.");
                                            return $message;
                                        }
                                        if (($seller_bit_balance == 0) || ($seller_bit_balance < $available->Quantity)) {
                                            $message[] = "Instant Transaction failed: The seller has insufficient token balance.";
                                            storeMessages($sell_order_id, $seller_id, $msg="Transaction failed: The seller has insufficient token balance.");
                                            return $message;
                                        }

                                        $new_seller_cash_balance = $seller_cash_balance + $cost_of_total_supply;  // traditional or cash
                                        $new_seller_bit_balance = $seller_bit_balance - $qty;
                                        $new_buyer_cash_balance = $buyer_cash_balance - $cost_of_total_supply; // traditional
                                        $new_buyer_bit_balance = $buyer_bit_balance + $available->Quantity; // traditional or cash

                                        $insert_market_order = insert_market_order($_SESSION['user_id'], $orderTypeId='0', $OfferAssetTypeId='USD', $WantAssetTypeId='RMT', $available->Quantity, $available->Price);

                                        $buy_order_id = 0;
                                        if($insert_market_order == false) {
                                            return false;
                                        } else if(is_int($insert_market_order)) {
                                            $buy_order_id = (int)$insert_market_order;
                                        } else {
                                            return false;
                                        }

                                        if ($buyer_id != $seller_id) {
                                            // increment the bits of buyer
                                            update_user_balance($assetType = 'btc', $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

                                            // deduct cash of buyer
                                            update_user_balance($assetType = 'traditional', $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

                                            // increase the cash of seller
                                            update_user_balance($assetType = 'traditional', $balance = $new_seller_cash_balance, $user_id = $seller_id);

                                            // deduct bits of seller
                                            update_user_balance($assetType = 'btc', $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                        }

                                        $message[] = "Instant Transaction Successful: You bought $qty tokens at $ $available->Price per token for $ $cost_of_total_supply.";

                                        // Record message in DB
                                        storeMessages($buy_order_id, $buyer_id, $msg="Instant Transaction Successful: You bought $available->Quantity RMT at $ $available->Price per token for $ $cost_of_total_supply.");
                                        storeMessages($sell_order_id, $seller_id, $msg="Transaction Successful: You sold $available->Quantity RMT at $ $available->Price per token for $ $cost_of_total_supply.");

                                        $qty = $qty - $available->Quantity; // should be equal to 0
                                        $available->Quantity = 0;

                                        // Record the transaction
                                        record_transaction($buyer_id, $buy_order_id, $available->Price, $buy_commission='0', $seller_id, $available->OrderId, $available->Price, $sell_commission = '0', $trade_qty);

                                        // Delete the row from Sell list
                                        delete_order($top_sell_table, $available->OrderId);

                                        // Update Order Status in Order table
                                        UpdateOrderStatus($buy_order_id, '1');
                                        UpdateOrderStatus($available->OrderId, '1');

                                        break;
                                    case ($qty < $available->Quantity):

                                        $trade_qty = (float) $qty;
                                        $cost_of_total_supply = $qty * $available->Price; // traditional or cash

                                        if ($buyer_cash_balance < $cost_of_total_supply) {
                                            $message[] = "Instant Transaction failed: You have insufficient cash balance.";
                                            storeMessages($buy_order_id=null, $buyer_id, $msg="Transaction failed: You had insufficient cash balance.");
                                            return $message;
                                        }
                                        if (($seller_bit_balance == 0) || ($seller_bit_balance < $qty)) {
                                            $message[] = "Instant Transaction failed: The seller has insufficient token balance.";
                                            storeMessages($sell_order_id, $seller_id, $msg="Transaction failed: The seller has insufficient token balance.");
                                            return $message;
                                        }

                                        $new_seller_cash_balance = $seller_cash_balance + $cost_of_total_supply;  // traditional or cash
                                        $new_seller_bit_balance = $seller_bit_balance - $qty;
                                        $new_buyer_cash_balance = $buyer_cash_balance - $cost_of_total_supply; // traditional
                                        $new_buyer_bit_balance = $buyer_bit_balance + $qty; // traditional or cash

                                        $insert_market_order = insert_market_order($_SESSION['user_id'], $orderTypeId='0', $OfferAssetTypeId='USD', $WantAssetTypeId='RMT', $qty, $available->Price);

                                        $buy_order_id = 0;
                                        if($insert_market_order == false) {
                                            return false;
                                        } else if(is_int($insert_market_order)) {
                                            $buy_order_id = (int)$insert_market_order;
                                        } else {
                                            return false;
                                        }

                                        if ($buyer_id != $seller_id) {
                                            // increment the bits of buyer
                                            update_user_balance($assetType = 'btc', $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

                                            // deduct cash of buyer
                                            update_user_balance($assetType = 'traditional', $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

                                            // increase the cash of seller
                                            update_user_balance($assetType = 'traditional', $balance = $new_seller_cash_balance, $user_id = $seller_id);

                                            // deduct bits of seller
                                            update_user_balance($assetType = 'btc', $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                        }

                                        // Record the transaction
                                        record_transaction($buyer_id, $buy_order_id, $available->Price, $buy_commission='0', $seller_id, $available->OrderId, $available->Price, $sell_commission = '0', $trade_qty);

                                        $available->Quantity = $available->Quantity - $qty;

                                        // update the quantity field for supply
                                        update_quantity($top_table = $top_sell_table, $available->Quantity, $available->OrderId);

                                        // Update Order Status in Order table
                                        UpdateOrderStatus($buy_order_id, '1');

                                        $message[] = "Instant Transaction Successful: You bought $qty tokens at $ $available->Price per token for $ $cost_of_total_supply.";

                                        // Record message in DB
                                        storeMessages($buy_order_id, $buyer_id, $msg="Instant Transaction Successful: You bought $qty RMT at $ $available->Price per token for $ $cost_of_total_supply.");
                                        storeMessages($sell_order_id, $seller_id, $msg="Transaction Successful: You sold $qty RMT at $ $available->Price per token for $ $cost_of_total_supply.");

                                        // update the quantity field for demand
                                        $qty = 0;

                                        break;
                                }
                                if (($available->Quantity <= 0) && ($qty > 0) && ($key === $last_iter)) {
                                    //The supply of token is 0. Stop further transaction. 
                                    $message[] = "Instant Transaction failure: There's no token left to be sold any more. $qty tokens could not be bought.";
                                    storeMessages($buy_order_id=null, $buyer_id, $msg="There's no token left to be sold any more. $qty tokens could not be bought.");
                                }
                            }
                            return $message;
                        } else {
                            $message[] = "empty_sell_list";
                            return $message;
                        }
                    }
                }

            } elseif ($order_type == 'sell') {
                if(is_float($qty) || is_int($qty)) {
                    if ($qty > 0) {

                        $buy_list = get_top_buy_sell_list($top_table=TOP_BUYS_TABLE, $asc_desc='DESC');

                        if (!empty($buy_list)) {
                            foreach ($buy_list as $available) {

                                $trade_qty = 0;
                                $buy_order_id = (int) $available->OrderId;
                                $available->Quantity = (float)$available->Quantity;

                                if ($available->Quantity <= 0 || $qty <= 0) {
                                    if (isset($message)) {
                                        if ($available->Quantity <= 0) {
                                            $message[] = "Instant Transaction Failure: The available asset is nill.";
                                        } else if($qty <= 0) {
                                            //$message[] = "The demanded asset is nill.";
                                        }
                                        return $message;
                                    } else {
                                        exit;
                                    }
                                }

                                $seller_id = $_SESSION['user_id'];
                                $seller_cash_balance = check_customer_balance($assetType = 'traditional', $seller_id)->Balance;
                                $seller_bit_balance = check_customer_balance($assetType = 'btc', $seller_id)->Balance;
                                $buyer_id = $available->customerId;
                                $buyer_cash_balance = check_customer_balance($assetType = 'traditional', $buyer_id)->Balance;
                                $buyer_bit_balance = check_customer_balance($assetType = 'btc', $buyer_id)->Balance;

                                switch ($qty) {
                                    case ($qty > $available->Quantity):

                                        $trade_qty = (float) $available->Quantity;
                                        $cost_of_total_supply = $available->Quantity * $available->Price; // traditional or cash

                                        if ($buyer_cash_balance < $cost_of_total_supply) {
                                            $message[] = "Instant Transaction failed: The buyer has insufficient cash balance.";
                                            storeMessages($buy_order_id, $buyer_id, $msg="Your order no $buy_order_id was unprocessed due to insufficient balance.");
                                            storeMessages($sell_order_id=null, $seller_id, $msg="A transaction was unprocessed due to insufficient balance of buyer with id $buyer_id.");
                                            return $message;
                                        }
                                        if (($seller_bit_balance == 0) || ($seller_bit_balance < $available->Quantity)) {
                                            $message[] = "Instant Transaction failed: You have insufficient token balance.";
                                            storeMessages($buy_order_id, $buyer_id, $msg="Your order no $buy_order_id was unprocessed due to insufficient token balance of seller with id $seller_id.");
                                            storeMessages($sell_order_id=null, $seller_id, $msg="Transaction failed: You have insufficient token balance.");
                                            return $message;
                                        }

                                        $new_seller_cash_balance = $seller_cash_balance + $cost_of_total_supply;  // traditional or cash
                                        $new_seller_bit_balance = $seller_bit_balance - $available->Quantity; // deduct the btc sold
                                        $new_buyer_cash_balance = $buyer_cash_balance - $cost_of_total_supply; // traditional
                                        $new_buyer_bit_balance = $buyer_bit_balance + $available->Quantity; // btc

                                        $insert_market_order = insert_market_order($_SESSION['user_id'], $orderTypeId='1', $OfferAssetTypeId='RMT', $WantAssetTypeId='USD', $available->Quantity, $available->Price);

                                        $sell_order_id = 0;
                                        if($insert_market_order == false) {
                                            return false;
                                        } else if(is_int($insert_market_order)) {
                                            $sell_order_id = (int)$insert_market_order;
                                        } else {
                                            return false;
                                        }

                                        if ($buyer_id != $seller_id) {
                                            // increment the bits of buyer
                                            update_user_balance($assetType = 'btc', $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

                                            // deduct cash of buyer
                                            update_user_balance($assetType = 'traditional', $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

                                            // increase the cash of seller
                                            update_user_balance($assetType = 'traditional', $balance = $new_seller_cash_balance, $user_id = $seller_id);

                                            // deduct bits of seller
                                            update_user_balance($assetType = 'btc', $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                        }

                                        // Delete the row from buy list
                                        delete_order(top_buy_table, $available->OrderId);

                                        // Update Order Status in Order table
                                        UpdateOrderStatus($available->OrderId, '1');

                                        // Record the transaction
                                        record_transaction($buyer_id, $available->OrderId, $available->Price, $buy_commission='0', $seller_id, $sell_order_id, $available->Price, $sell_commission = '0', $trade_qty);

                                        $message[] = "Instant Transaction Successful: You sold $available->Quantity tokens for $ $cost_of_total_supply.";
                                        storeMessages($sell_order_id, $seller_id, $msg="Instant Transaction Successful: You sold $available->Quantity RMT for $ $cost_of_total_supply.");
                                        storeMessages($buy_order_id, $buyer_id, $msg="Transaction Successful: You bought $available->Quantity RMT for $ $cost_of_total_supply.");

                                        $qty = $qty - $available->Quantity;

                                        break;
                                    case ($qty == $available->Quantity):

                                        $trade_qty = (float) $available->Quantity;
                                        $cost_of_total_supply = $available->Quantity * $available->Price; // traditional or cash

                                        if ($buyer_cash_balance < $cost_of_total_supply) {
                                            $message[] = "Instant Transaction failed: The buyer has insufficient cash balance.";
                                            storeMessages($buy_order_id, $buyer_id, $msg="Your order no $buy_order_id was unprocessed due to insufficient balance.");
                                            storeMessages($sell_order_id=null, $seller_id, $msg="A transaction was unprocessed due to insufficient balance of buyer with id $buyer_id.");
                                            return $message;
                                        }
                                        if (($seller_bit_balance == 0) || ($seller_bit_balance < $available->Quantity)) {
                                            $message[] = "Instant Transaction failed: You have insufficient token balance.";
                                            storeMessages($buy_order_id, $buyer_id, $msg="Your order no $buy_order_id was unprocessed due to insufficient token balance of seller with id $seller_id.");
                                            storeMessages($sell_order_id=null, $seller_id, $msg="Transaction failed: You have insufficient token balance.");
                                            return $message;
                                        }

                                        $new_seller_cash_balance = $seller_cash_balance + $cost_of_total_supply;  // traditional or cash
                                        $new_seller_bit_balance = $seller_bit_balance - $available->Quantity; // deduct the btc sold
                                        $new_buyer_cash_balance = $buyer_cash_balance - $cost_of_total_supply; // traditional
                                        $new_buyer_bit_balance = $buyer_bit_balance + $available->Quantity; // traditional or cash

                                        $insert_market_order = insert_market_order($_SESSION['user_id'], $orderTypeId='1', $OfferAssetTypeId='RMT', $WantAssetTypeId='USD', $available->Quantity, $available->Price);

                                        $sell_order_id = 0;
                                        if($insert_market_order == false) {
                                            return false;
                                        } else if(is_int($insert_market_order)) {
                                            $sell_order_id = (int)$insert_market_order;
                                        } else {
                                            return false;
                                        }

                                        if ($buyer_id != $seller_id) {
                                            // increment the bits of buyer
                                            update_user_balance($assetType = 'btc', $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

                                            // deduct cash of buyer
                                            update_user_balance($assetType = 'traditional', $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

                                            // subtract the cash of seller
                                            update_user_balance($assetType = 'traditional', $balance = $new_seller_cash_balance, $user_id = $seller_id);

                                            // deduct bits of seller
                                            update_user_balance($assetType = 'btc', $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                        }

                                        $message[] = "Instant Transaction Successful: You sold $qty tokens for $ $cost_of_total_supply.";
                                        storeMessages($sell_order_id, $seller_id, $msg="Instant Transaction Successful: You sold $available->Quantity RMT for $ $cost_of_total_supply.");
                                        storeMessages($buy_order_id, $buyer_id, $msg="Transaction Successful: You bought $available->Quantity RMT for $ $cost_of_total_supply.");

                                        $qty = $qty - $available->Quantity;

                                        // Update Order Status in Order table
                                        UpdateOrderStatus($sell_order_id, '1');
                                        UpdateOrderStatus($available->OrderId, '1');

                                        // Record the transaction
                                        record_transaction($buyer_id, $available->OrderId, $available->Price, $buy_commission='0', $seller_id, $sell_order_id, $available->Price, $sell_commission = '0', $trade_qty);

                                        // Delete the row from buy list
                                        delete_order($top_buy_table, $available->OrderId);

                                        break;
                                    case ($qty < $available->Quantity):

                                        $trade_qty = (float) $qty;
                                        $available->Quantity = $available->Quantity - $qty;
                                        $cost_of_total_supply = $qty * $available->Price; // traditional or cash

                                        if ($buyer_cash_balance < $cost_of_total_supply) {
                                            $message[] = "Instant Transaction failed: The buyer has insufficient cash balance.";
                                            storeMessages($buy_order_id, $buyer_id, $msg="Your order no $buy_order_id was unprocessed due to insufficient balance.");
                                            storeMessages($sell_order_id=null, $seller_id, $msg="A transaction was unprocessed due to insufficient balance of buyer with id $buyer_id.");
                                            return $message;
                                        }
                                        if (($seller_bit_balance == 0) || ($seller_bit_balance < $qty)) {
                                            $message[] = "Instant Transaction failed: You have insufficient token balance.";
                                            storeMessages($buy_order_id, $buyer_id, $msg="Your order no $buy_order_id was unprocessed due to insufficient token balance of seller with id $seller_id.");
                                            storeMessages($sell_order_id=null, $seller_id, $msg="Transaction failed: You have insufficient token balance.");
                                            return $message;
                                        }

                                        $new_seller_cash_balance = $seller_cash_balance + $cost_of_total_supply;  // traditional or cash
                                        $new_seller_bit_balance = $seller_bit_balance - $qty; // deduct the btc sold
                                        $new_buyer_cash_balance = $buyer_cash_balance - $cost_of_total_supply; // traditional
                                        $new_buyer_bit_balance = $buyer_bit_balance + $qty; // traditional or cash

                                        $insert_market_order = insert_market_order($_SESSION['user_id'], $orderTypeId='1', $OfferAssetTypeId='RMT', $WantAssetTypeId='USD', $qty, $available->Price);

                                        $sell_order_id = 0;
                                        if($insert_market_order == false) {
                                            return false;
                                        } else if(is_int($insert_market_order)) {
                                            $sell_order_id = (int)$insert_market_order;
                                        } else {
                                            return false;
                                        }

                                        if ($buyer_id != $seller_id) {
                                            // increment the bits of buyer
                                            update_user_balance($assetType = 'btc', $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

                                            // deduct cash of buyer
                                            update_user_balance($assetType = 'traditional', $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

                                            // subtract the cash of seller
                                            update_user_balance($assetType = 'traditional', $balance = $new_seller_cash_balance, $user_id = $seller_id);

                                            // deduct bits of seller
                                            update_user_balance($assetType = 'btc', $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                        }

                                        // Record the transaction
                                        record_transaction($buyer_id, $available->OrderId, $available->Price, $buy_commission='0', $seller_id, $sell_order_id, $available->Price, $sell_commission = '0', $trade_qty);

                                        // update the quantity field for supply
                                        update_quantity($top_table = $top_buy_table, $available->Quantity, $available->OrderId);

                                        // Update Order Status in Order table
                                        UpdateOrderStatus($sell_order_id, '1');

                                        $message[] = "Instant Transaction Successful: You sold $qty tokens for $ $cost_of_total_supply.";
                                        storeMessages($sell_order_id, $seller_id, $msg="Instant Transaction Successful: You sold $qty RMT for $ $cost_of_total_supply.");
                                        storeMessages($buy_order_id, $buyer_id, $msg="Transaction Successful: You bought $qty RMT for $ $cost_of_total_supply.");

                                        // update the quantity field for demand
                                        $qty = 0;

                                        break;
                                }
                            }
                            return $message;
                        } else {
                            $message[] = "empty_buy_list";
                            return $message;
                        }
                    }
                }
            }
        }
        return false;
    }

    function last_transaction_list($start=0, $limit = 10) {
        global $db_connection;
        if ($db_connection) {

            $list = array();

            $query = $db_connection->query("
                SELECT TransactionId AS T_ID, a_buyer AS BUYER_ID, b_seller AS SELLER_ID, (SELECT ".USERS_TABLE.".Name FROM ".USERS_TABLE." WHERE ".USERS_TABLE.".CustomerId=BUYER_ID) AS BUYER, (SELECT ".USERS_TABLE.".Name FROM ".USERS_TABLE." WHERE ".USERS_TABLE.".CustomerId=SELLER_ID) AS SELLER, B_AMOUNT AS TRADE_PRICE, ".TRANSACTIONS_TABLE.".InsertDate, ".TRANSACTIONS_TABLE.".qty_traded AS TRADED_QTY
                FROM ".TRANSACTIONS_TABLE.", ".USERS_TABLE."
                GROUP BY T_ID
                ORDER BY T_ID DESC
                LIMIT $start, $limit
            ");

            if ($query->rowCount() > 0) {
                while ($ls = $query->fetchObject()) {
                    $list[] = $ls;
                }
                return $list;
            }
            return false;
        }
        return false;
    }

        function UserBalanceList($is_active=null) {
        global $db_connection;
        if ($db_connection) {

            $list = array();

            $extraQuery = "";

            if ($is_active != null) {
                $extraQuery = "WHERE ".USERS_TABLE.".is_active = 0 OR ".USERS_TABLE.".is_active = 1";
            } else {
                $extraQuery = "WHERE ".USERS_TABLE.".is_active = 1";
            }

            $query = $db_connection->query("
                SELECT ".USERS_TABLE.".CustomerId AS UID, ".USERS_TABLE.".Name, ".USERS_TABLE.".is_active, ".USERS_TABLE.".fb_id AS FACEBOOK_ID,
                (SELECT ".CREDITS_TABLE.".Balance FROM ".CREDITS_TABLE." WHERE ".CREDITS_TABLE.".AssetTypeId='btc' AND ".CREDITS_TABLE.".CustomerId=UID) AS BTC, 
                (SELECT ".CREDITS_TABLE.".Balance FROM ".CREDITS_TABLE." WHERE ".CREDITS_TABLE.".AssetTypeId='traditional' AND ".CREDITS_TABLE.".CustomerId=UID) AS CASH 
                FROM ".USERS_TABLE.", ".CREDITS_TABLE." 
                $extraQuery
                GROUP BY UID ORDER BY MAX(BTC) DESC
            ");

            if ($query->rowCount() > 0) {
                while ($ls = $query->fetchObject()) {
                    $list[] = $ls;
                }
                return $list;
            }
            return false;
        }
        return false;
    }

    function LastTradedPrice() {
        global $db_connection;
        if ($db_connection) {

            $query = $db_connection->query("
                SELECT `B_Amount`,InsertDate FROM ".TRANSACTIONS_TABLE." ORDER BY `InsertDate` DESC LIMIT 1
            ");

            if ($query->rowCount() == 1) {
                $ls = $query->fetchObject();
                return $ls;
            }
            return false;
        }
        return false;
    }

    function UserOrdersList($user_id, $start=0, $limit=10) {
        global $db_connection;
        if ($db_connection) {

            $list = array();
            $query = $db_connection->query("
            SELECT `OrderId`, `CustomerId`, `OrderTypeId`, `OfferAssetTypeId`, `WantAssetTypeId`, `Quantity`, `Price`, `OrderStatusId`, `MarketOrder`, `InsertDate`
            FROM ".ORDERS_TABLE."
            WHERE `CustomerId`='$user_id'
            ORDER  BY InsertDate DESC
            LIMIT $start, $limit
            ");
            
            
                if ($query->rowCount() > 0) {
                    while ($ls = $query->fetchObject()) {
                        $list[] = $ls;
                    }
                    return $list;
                }
            
        }
        return false;
    }

    function cancel_order($order_id=null, $user_id=null) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("
                DELETE FROM ".TOP_BUYS_TABLE." WHERE `orderId`=:'$order_id' AND customerId = '$user_id';
                DELETE FROM ".TOP_SELL_TABLE." WHERE `orderId`=:'$order_id' AND customerId = '$user_id'
            ");

            unset($query); // Unset the query

            $q = $db_connection->query("
                    UPDATE ".ORDERS_TABLE." SET `OrderStatusId`= 0 
                    WHERE `OrderId` = '$order_id'
                    AND CustomerId = '$user_id'
                  ");
            unset($q);

            $query2 = $db_connection->query("
                        SELECT * FROM ".TOP_BUYS_TABLE." WHERE `orderId`='$order_id';
                        SELECT * FROM ".TOP_SELL_TABLE." WHERE `orderId`='$order_id'
                    ");
            

            
                if ($query2->rowCount() == 0) {
                    if ($_SESSION['user_id']==ADMIN_ID) {
                        storeMessages($order_id, ADMIN_ID, $msg="Order number $order_id was deleted by user id ".ADMIN_ID);
                        storeMessages($order_id, $user_id, $msg="Order number $order_id was deleted by Admin.");
                    } else {
                        storeMessages($order_id, $user_id, $msg="Order number $order_id was deleted by you.");
                    }
                    return true; // This means row was actually deleted
                }
            
        }
        return false;
    }

    function del_order($order_id, $usid=null) {
        global $db_connection;
        if ($db_connection) {

            $user_id = 0;
            if (!isset($_SESSION['user_id'])) {
                return false;
            }
            $user_id = (int) $_SESSION['user_id'];
            // Allow Admin to delete order, if its not admin check owner of order
            if ($usid == null) {
                $is_owner = isUserOrderOwner($order_id, $user_id);

                if (!$is_owner) {
                    return false;
                }
            } else if(($usid != null) && ($user_id == ADMIN_ID)) {   // This else part to be used by admin in delete_orders_of_user()
                $user_id = $usid;
            } else {
                return false;
            }

            // Finally cancel the order
            return cancel_order($order_id, $user_id);
        }
        return false;
    }

    function storeMessages($order_id=null, $user_id=null, $msg=null) {
        global $db_connection;
        if($db_connection) {
            $now = time_now();
            if ($user_id == false) {
                return false;
            }
            $username = get_username($user_id);

            $query = $db_connection->query("
                INSERT INTO ".MSG_TABLE."(`id`, `order_id`, `username_key`, `username`, `messages`, `datetime`) 
                VALUES ('', '$order_id', '$user_id', '$username', '$msg', '$now')
            ");
            
            return true;
        }
        return false;
    }



    //Messages Load More
    function total_my_messages() {
        global $db_connection;
        if ($db_connection) {
            $my_total_messages = 0;
            if (isset($_SESSION['user_id'])) {
                $user_id = (int) $_SESSION['user_id'];
            } else {
                return $my_total_messages;
            }
            $query = $db_connection->query("
                SELECT COUNT(*) AS MY_TOTAL_MESSAGES
                FROM ".MSG_TABLE."
                WHERE `username_key`=:'$user_id'
            ");
            
            
                $fetch = $query->fetchObject();
                $my_total_messages = (int) $fetch->MY_TOTAL_MESSAGES;
            
            return $my_total_messages;
        }
        return false;
    }

    function total_my_orders(){
    global $db_connection;
    
        if ($db_connection) {
            $my_total_orders = 0;
            if (isset($_SESSION['user_id'])) {
                $user_id = (int)$_SESSION['user_id'];
            } else {
                return $my_total_orders;
            }
            $query = $db_connection->query("
                SELECT COUNT(*) AS MY_TOTAL_ORDERS
                FROM ".ORDERS_TABLE."
                WHERE `CustomerId`='$user_id'
            ");
            
            
                $fetch = $query->fetchObject();
                $my_total_orders = (int)$fetch->MY_TOTAL_ORDERS;
            
            return $my_total_orders;
        }
        return false;
    }

    function total_my_transactions(){
    global $db_connection;
    
        if ($db_connection) {
            $my_total_orders = 0;
            if (isset($_SESSION['user_id'])) {
                $user_id = (int)$_SESSION['user_id'];
            } else {
                return $my_total_orders;
            }
            $query = $db_connection->query("
                SELECT COUNT(*) AS MY_TOTAL_ORDERS
                FROM ".TRANSACTIONS_TABLE."
                WHERE `a_buyer`= '$user_id' OR `b_seller`= '$user_id'
            ");
            
            
                $fetch = $query->fetchObject();
                $my_total_orders = (int)$fetch->MY_TOTAL_ORDERS;
            
            return $my_total_orders;
        }
        return false;
    }

    function total_recent_transactions()
    {
    global $db_connection;
        if ($db_connection) {
            $total_orders = 0;

            $query = $db_connection->query("
                SELECT COUNT(*) AS TOTAL_ORDERS
                FROM ".TRANSACTIONS_TABLE."
            ");
            
                $fetch = $query->fetchObject();
                $total_orders = (int)$fetch->TOTAL_ORDERS;
            
            return $total_orders;
        }
        return false;
    }

    function isUserOrderOwner($order_id=0, $user_id=0) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("
                SELECT `OrderId` FROM ".ORDERS_TABLE." 
                WHERE `OrderId`='$order_id' 
                AND `CustomerId`='$user_id'
                LIMIT 1
            ");
            
                if ($query->rowCount()==1) {
                    return true;
                }
            
        }
        return false;
    }

    function storeMessagesPublic($order_id=null, $user_id=null, $msg=null) {
        global $db_connection;
        if ($db_connection) {
            storeMessages($order_id, $user_id, $msg);
        }
    }

    //Add bank account
    function add_bank_account($user_id, $holder, $bank_name, $account_num, $branch_name, $bank_addr, $bk_ctry) {
        global $db_connection,$bank_acc;
        if ($db_connection) {
            $now = time_now();
            $query = $db_connection->query(
                "INSERT INTO $bank_acc(`id`, `user_id`, `acc_holder`, `bank_name`, `acc_num`, `branch_name`, `bank_addr`, `bank_ctry`, `date_added`) 
                 VALUES ('', '$user_id', '$holder', '$bank_name', '$account_num', '$branch_name', '$bank_addr', '$bk_ctry', '$now')"
            );
            
            
                storeMessages(null, $user_id, $msg="You added a new bank account number $account_num.");
                return true;
            
        }
        return false;
    }

    function send_notice_mail($reciever_email, $email_from, $email_sender, $email_subject, $email_body) {
        global $db_connection;
        $mail = new SendMail();
        $do_mail = $mail->do_email($reciever_email, $email_from, $email_sender, $email_subject, $email_body);
        if ($do_mail==true) {
            return $do_mail;
        }
        return false;
    }

    function get_bank_details($user_id, $acc=null) {
        global $db_connection,$bank_acc;
        $acc_details = null;
        if ($db_connection) {

            $ex = "";
            if ($acc != null) {
                $ex = "AND `acc_num`='$acc'";
            }

            $query = $db_connection->query(
                "SELECT * FROM $bank_acc WHERE `user_id`='$user_id' $ex"
            );
            
            if ($query->rowCount() > 0) {
                while ($acc_info = $query->fetchObject()) {
                    $acc_details[] = $acc_info;
                }
            }
        }
        return $acc_details;
    }

    //Fund transfer
    function fund_transfer($fund_type, $from, $to, $amount, $remarks, $assetType) {
        global $db_connection;
        $user_id = (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] != 0) ? $_SESSION['user_id'] : 0;
        $now = time_now();
        if ($db_connection && $user_id != 0) {
            $cBalance = check_customer_balance($assetType, $user_id);
            $user_bal_currently = (float)$cBalance->Balance;

            $new_bal = (float)$user_bal_currently - $amount;

            $ft = update_user_balance($assetType, $new_bal, $user_id);

            if ($user_bal_currently == null || $ft == null) {
                return false;
            }

            $sign = ($assetType == 'btc') ? 'RTM':'$';

            $query = $db_connection->query(
                "INSERT INTO ".TRANSFER_INFO_TABLE."(`id`, `user_id`, `fund_type`, `tr_from`, `tr_to`, `fund_amount`, `remarks`, `datetime`) 
                 VALUES('', '$user_id', '$fund_type', '$from', '$to', '$amount', '$remarks', '$now')"
            );
                       
                storeMessages(null, $user_id, $msg="You have requested to transfer $sign $amount to bank account number $to.");
                return true;
            
        }
        return false;
    }
    
    function record_root_bal_update($uid, $bal_prev, $bal_now, $bal_type) {
        global $db_connection;
        if ($db_connection) {
            $now = time_now();
            $root = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $query = $db_connection->query("
                INSERT INTO ".ADMIN_BAL_RECORDS."(`BalStatusHistoryId`, `user_id`, `bal_prev`, `bal_now`, `type`, `root_id`, `UpdateDate`) 
                VALUES ('', '$uid', '$bal_prev', '$bal_now', '$bal_type', '$root', '$now')
            ");
            
            
                return true;
            
        }
        return false;
    }

    function list_root_bal_changes() {
        global $db_connection;
        if ($db_connection) {
            $list_details = array();
            $query = $db_connection->query(" 
                SELECT ".ADMIN_BAL_RECORDS.".*, ".USERS_TABLE.".Name, ".USERS_TABLE.".Email 
                FROM ".ADMIN_BAL_RECORDS.", ".USERS_TABLE." 
                WHERE ".ADMIN_BAL_RECORDS.".user_id=".USERS_TABLE.".CustomerId
                ORDER BY UpdateDate DESC
                LIMIT 200
            ");
            

            if ($query->rowCount() > 0) {
                while ($list = $query->fetchObject()) {
                    $list_details[] = $list;
                }
            }
            return $list_details;
        }
        return false;
    }

    function get_last_order_date($date=null) {
        global $db_connection;
        if ($db_connection) {
            $query = $db_connection->query("SELECT * FROM `orderbook` WHERE `InsertDate`> '$date'");
            if ($query->rowCount()) {
                return true;
            }
        }
        return false;
    }

    function delete_orders_of_user($user_id=null) {
        global $db_connection;
        if ($db_connection) {
            $order_ids = array();
            $query = $db_connection->query("
            SELECT orderId FROM ".TOP_BUYS_TABLE." WHERE `customerId`='$user_id'
            UNION
            SELECT orderId FROM ".TOP_SELL_TABLE." WHERE `customerId`='$user_id'
            ");
            
            
            if ($query->rowCount() > 0) {
                while ($rr = $query->fetchObject()) {
                    $order_ids[] = $rr;
                }
                foreach ($order_ids as $oid) {
                    del_order($oid->orderId, $user_id);
                }
                return true;
            }
        }
        return false;
    }
    
