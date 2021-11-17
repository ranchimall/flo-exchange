<?php

// Turn off error reporting
error_reporting(0);
@ini_set('display_errors', 0);
session_start();

$tradersList = array();
$buy_list = array();
$sell_list = array();

$validate_user = null;
if (isset($UserClass)) {
    if (isset($_SESSION['flo_id'])):
        // check if user already registered
        $validate_user = $UserClass->doInitialUserHandling($_SESSION['flo_id']);  
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
$action_class_market = 'flo_log_in';
$action_class_buy_sell = 'flo_log_in';
if(checkLoginStatus()) {
    $user_logged_in = true;
    $action_class_market = 'market_submit_btn';
    $action_class_buy_sell = 'process';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?=STYLE_DIR?>/bootstrap.css">
    <link rel="stylesheet" href="<?=STYLE_DIR?>/custom.css">
    <link rel="stylesheet" href="<?=STYLE_DIR?>/mate.css">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300" rel="stylesheet">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://sairajzero.github.io/Standard_Operations/cdn/floCrypto.js"></script>

    <script src="<?=JS_DIR?>/notify.js"></script>

    <script src="<?=JS_DIR?>/main.js"></script>

</head>
<?php
if(isset($_GET['msg']) && $_GET['msg'] !== '') {
$err_msg = (string)$_GET['msg']; 
$type = isset($_GET['type']) ? trim($_GET['type']) : 'danger';
?>
<div id="error_msg">
    <script>
        $.notify({
            title: "<strong>Notice:</strong> ",
            message: "<?=$err_msg?>"
        },{
            type: '<?=$type?>'
        });
    </script>
</div>
<?php }?>
<body class="text--default">
<div class="container-fluid background--primary p--3">
    <div class="container">
        <div class="col-sm-6">
            <a href="http://ranchimall.net/exchange"><div class="logo mt--1"></div></a>
        </div>
        <div class="col-sm-6 text-right mt--1-m">
            <?php if(isset($_SESSION['authenticated'])&&($_SESSION['authenticated'] == true)) { ?>
                <a href="logout.php">
                    <div class="btn btn--facebook ">
                        Log Out
                    </div>
                </a>
            <?php } else {?>
                
        <div class="row" id="to_login_fields">
        <h2 class="text-center">Login using FLO Private Key </h2>
        <hr>
        <div class="col-lg-12 lazy-form">
            <label for="key_flo">Enter your FLO Private Key</label>
            <input type="text" name="key_flo" id="key_flo" style="color:initial">
        </div>

        <div class="col-lg-12">
            <input type="submit" id="key_flo_submit" class="btn btn--block btn--facebook flo_log_in" style="width: 100%;" value="Enter Key">
        </div>
        <a href="logout.php" id="to_logout_fields" style="visibility: hidden;">
            <div class="btn btn--facebook ">
                Log Out
            </div>
        </a>

    </div>  
            
    <script>

// Need to handle authentication fail case .. its easy 

function ajax_authentication(floID1,floPubKey1,message1,signDataWithFlo1) {
    $.ajax({
        method:'post',
        url:'ajax/authenticateMe.php',
        data: { floID: floID1,floPubKey:floPubKey1,message:message1,signDataWithFlo:signDataWithFlo1},
        success: function(data) {
            // console.log(data);
        }
    }).error(function(xhr, status, error) {
        console.log(xhr.responseText);
    }).success(function(data) {
        var IS_JSON = true;
        try {
            var d = jQuery.parseJSON(data);
            console.log(d.message);
        }
        catch(err) {
            IS_JSON = false;
        }

        if(IS_JSON) {
            if(d.error == false) {
             document.getElementById("to_login_fields").style.visibility = "hidden";
             document.getElementById("to_logout_fields").style.visibility = "visible";  
             
            }
        } 

    });
}

   document.getElementById("key_flo_submit").onclick = function(evt) {

            let floPrivKey = document.getElementById("key_flo").value;
            let floPubKey = floCrypto.getPubKeyHex(floPrivKey);
            let floID = floCrypto.getFloID(floPubKey);
            let message = <?php $_SESSION['session_id'] ?>;
            let signDataWithFlo = floCrypto.signData(message, floPrivKey);
            delete floPrivKey;
            ajax_authentication(floID,floPubKey,message,signDataWithFlo);            

        }

    </script>


            <?php } ?>
        </div>
    </div>

</div>
<div class="container-fluid  background--primary-1 p--1">
    <div class="container">
        <div class="col-sm-6">
            <?php if (isset($OrderClass)) {
                $LastTradedPrice = $OrderClass->LastTradedPrice();
                $LastTradedPrice = ($LastTradedPrice !=Null) ? '$ '. $LastTradedPrice->B_Amount : 'No Data';?>
                <h5 class="font-20 mt--2 text--uppercase text--bold text--center--mobile">Last Traded Price: <span id="_ltp"><?=$LastTradedPrice;?></span></h5>
            <?php } ?>
        </div>
        <?php if($user_logged_in) { ?>
            <div class="col-sm-6 text-right text--uppercase text--center--mobile ">
                <h2 class="text--uppercase"><?=$fullName?></h2>
                <h6 class="text--bold">Token Balance: <span id="my_bit_balance">loading...</span> </h6>
                <h6 class="text--bold">Cash Balance: $ <span id="my_cash_balance">loading...</span> </h6>
            </div>
        <?php } ?>
    </div>
</div>

<?php if ($user_logged_in) {include_once 'req_user_info.php';} ?>
