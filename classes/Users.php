<?php

class Users {

    protected $db_connection = null;
    private $orders_table = ORDERS_TABLE;
    private $customers_table = USERS_TABLE;
    private $top_buy_table = TOP_BUYS_TABLE;
    private $top_sell_table = TOP_SELL_TABLE;
    private $customer_balance_table = CREDITS_TABLE;
    private $transaction_table = TRANSACTIONS_TABLE;
    private $bal_history = CREDITS_HISTORY_TABLE;
    private $bank_acc = ACCOUNTS_TABLE;
    private $fund_trans = TRANSFER_INFO_TABLE;
    private $email = null;
    private $name = null;
    private $is_active = null;
    private $user_is_logged_in = false;
    private $errors = array();

    public function databaseConnection()
    {
        // if connection already exists
        if ($this->db_connection != null) {
            return true;
        } else {
            try {
                $this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
                return true;
            } catch (PDOException $e) {
                $this->errors[] = MESSAGE_DATABASE_ERROR . $e->getMessage();
            }
        }
        return false;
    }

    private function insert_balance($CustomerId, $AssetTypeId, $Balance, $FrozenBalance) {
        $now = $this->time_now();
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("INSERT INTO `$this->customer_balance_table`(`sr_no`, `CustomerId`, `AssetTypeId`, `Balance`, `FrozenBalance`, `UpdateDate`, `InsertDate`, `SaveDate`) VALUES ('', '$CustomerId',$AssetTypeId','$Balance','$FrozenBalance',NULL,'$now','$now')");
                      
                return true;
            
        }
        return false;
    }



    public function doInitialUserHandling($flo_id) {

// If username exists only then do FLO ID  insertion        
        if ($this->databaseConnection()) {
            $now = $this->time_now();
            
// I have added LIMIT 1 .. so only the first FLO ID is allowed. If there are two FLO IDs for same user, the second one will be discarded. MAYBE I SHOULD ADD AN EXPLICT CHECK LATER FOR DUPLICATE FLO ID WHILE BEING INSERTED
            $query = $this->db_connection->prepare("SELECT * FROM $this->customers_table WHERE `flo_id`=:flo_id LIMIT 1");
            $query->bindValue(':flo_id', $flo_id, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount) {
                //The case where FLO ID exists in database
                $user_obj = $query->fetchObject();

                $update_query = $this->db_connection->prepare("UPDATE $this->customers_table
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
               $this->user_name = $flo_id.time();
               $this->email = (isset($_SESSION['email'])) ? $_SESSION['email'] : "";
                $this->name = (isset($_SESSION['user_name'])) ? $_SESSION['user_name'] : "";

                $query = $this->db_connection->prepare("
                    INSERT INTO $this->customers_table (`CustomerId`, `flo_id`, `Username`, `Email`, `Name`, `UpdateDate`, `InsertDate`, `SaveDate`, `is_active`)
                    VALUES ('',:flo_id,:Username,:Email,:Name,NULL,'$now',NULL,0)
                ");

// Here we are setting name = FLO ID. Later we can ask for actual name and email when user logs in second time if both are same, and update
//Also email will be blank here for first time FLO user. That will give a signature to update the email later.                

                $query->bindValue(':flo_id', $flo_id, PDO::PARAM_INT);
                $query->bindValue(':Username', $this->user_name, PDO::PARAM_STR);
                $query->bindValue(':Email', $this->email, PDO::PARAM_STR);
                $query->bindValue(':Name', $this->name, PDO::PARAM_STR);
                if($query->execute()) {
                    $_SESSION['user_id'] = $this->db_connection->lastInsertId();
                    
                  
                    $AssetTypeId = 'btc';
                    $Balance = 0.00;
                    $FrozenBalance = 0.00;
                    $crypto = $this->insert_balance($_SESSION['user_id'], $AssetTypeId, $Balance, $FrozenBalance);

                    $AssetTypeId = 'traditional';
                    $Balance = 0.00;
                    $FrozenBalance = 0.00;
                    $cash = $this->insert_balance($_SESSION['user_id'], $AssetTypeId, $Balance, $FrozenBalance);

                    $user_exist = $this->check_user($_SESSION['user_id']);
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


public function check_flo_id_active_status($flo_id) {

        if ($this->databaseConnection()) {

            $query = $this->db_connection->query("SELECT * FROM $this->customers_table WHERE flo_id = '$flo_id' AND is_active = 1 LIMIT 1");
            
            
                $row_count = $query->rowCount();
                if ($row_count == 1) {
                    return true;
                }
                
             
        }
        return false;
    }

public function check_flo_id_registration_status($flo_id) {

        if ($this->databaseConnection()) {

            $query = $this->db_connection->query("SELECT * FROM $this->customers_table WHERE flo_id = '$flo_id' LIMIT 1");
            
                $row_count = $query->rowCount();
                if ($row_count == 1) {
                    return true;
                }
               
             
        }
        return false;
    }


public function checkIfFloIDPermitted($flo_id){
    if (($this->check_flo_id_registration_status($flo_id) == true) && ($this->check_flo_id_active_status($flo_id) == true)){
            return true;
    }

    return false;
}

public function update_flo_details($floID, $auth_random, $floPublicKey) {
        
        $now = $this->time_now();
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("UPDATE `$this->customers_table` SET `auth_random` = '$authRandom', `floPublicKey` = '$floPublicKey', `updateDate` = '$now' WHERE `flo_id` = '$floID' LIMIT 1");
                return true;
            
        }
        return false;
    }


public function update_newUser($flo_id, $name, $email) {
        
        $now = $this->time_now();
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("UPDATE `new_user` SET `name` = '$name', `email` = '$email', `insertDate` = '$now' WHERE `flo_id` = '$flo_id' LIMIT 1");
            
                return true;
            
        }
        return false;
    }

public function insert_flo_newUser($flo_id) {
        
        $now = $this->time_now();
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("INSERT INTO new_user (`flo_id`,`insertDate`) VALUES ('$flo_id', '$now')");
            return true;
            
        }
        return false;
    }    

public function check_duplicate_newUser($flo_id) {

        if ($this->databaseConnection()) {

            $query = $this->db_connection->query("SELECT `flo_id` FROM new_user WHERE flo_id = '$flo_id'");
            

            
                $row_count = $query->rowCount();
                if ($row_count >= 1) {
                    return true;
                }
                
             
        }
        return false;
    }



    public function check_user($customerId) {

        if ($this->databaseConnection()) {

            $query = $this->db_connection->query("SELECT * FROM $this->customers_table WHERE customerId = '$customerId' AND is_active = 1 LIMIT 1");
            

            
                $row_count = $query->rowCount();
                if ($row_count == 1) {
                    return $user_details = $query->fetchObject();
                } 
            }
        
        return false;
    }

    public function displayUserTransaction($user_id, $start=0, $limit=10) {
        if ($this->databaseConnection()) {
            $transactions = array();

            $query = $this->db_connection->query("
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

    public function list_messages_by_userId($user_id, $start=0, $limit=10) {
        if ($this->databaseConnection()) {
            $messages = array();

            $query = $this->db_connection->query("
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

    public function actions_user($u_id, $act=1) {
        if ($this->databaseConnection()) {
            if (!empty($u_id)) {

                $act = (int) $act;
                $u_id = (int) $u_id;

                $query = $this->db_connection->query("
                    UPDATE ".USERS_TABLE." SET `is_active`= '$act' 
                    WHERE CustomerId = '$u_id'
                    LIMIT 1
                ");
                

                
                    return true;
                
            }
        }
        return false;
    }
    
    public function get_total_users_count() {
        if ($this->databaseConnection()) {
            $total_users = 0;
            $query = $this->db_connection->query("SELECT COUNT(*) AS TOTAL_COUNT FROM ".USERS_TABLE." WHERE `is_active`=1");
            if ($query->rowCount()) {
                $total_users = $query->fetchObject()->TOTAL_COUNT;
            }
            return (int) $total_users;
        }
        return false;
    }

    public function time_now() {
        $n = new DateTime("now", new DateTimeZone("Asia/Kolkata"));
        $now = $n->format('Y-m-d H:i:s');
        return $now;
    }

    public function get_username($customerId=0) {

        if ($this->databaseConnection()) {
            $customerId = (int) $customerId;
            $query = $this->db_connection->query("SELECT Username FROM ".USERS_TABLE." WHERE customerId = '$customerId' LIMIT 1");
                        
            $row_count = $query->rowCount();
            if ($row_count == 1) {
                return $query->fetchObject()->Username;
            }
        }
        return false;
    }

    public function getUserDetails($flo_id) {

        if ($this->databaseConnection()) {
            $customerId = (int) $customerId;
            $query = $this->db_connection->query("SELECT CustomerId,Name,Email FROM ".USERS_TABLE." WHERE flo_id = '$flo_id' LIMIT 1");
                        
            $row_count = $query->rowCount();
            if ($row_count == 1) {
                return $query->fetchObject();
            }
        }
        return false;
    }

    public function input_user_email($email=null, $user_id=null) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("
            UPDATE ".USERS_TABLE." SET `Email`= '$email' WHERE CustomerId = '$user_id'
            ");
            

            
                return true;
            
        }
        return false;
    }

}
