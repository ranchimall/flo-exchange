<?php

// Turn off error reporting
error_reporting(0);
@ini_set('display_errors', 0);

$tradersList = array();
$buy_list = array();
$sell_list = array();

$validate_user = null;
if (isset($UserClass)) {
    if (isset($_SESSION['flo_id'])):
        // check if user already registered
        $validate_user = $UserClass->doInitialUserHandling($flo_id); 
        
        if($validate_user == "" || $validate_user == false) {
            redirect_to('index.php');
        }
    endif;

    $tradersList = $OrderClass->UserBalanceList();
    $buy_list[] = $OrderClass->get_top_buy_sell_list(TOP_BUYS_TABLE, $asc_desc='DESC');  // buy
    $sell_list[] = $OrderClass->get_top_buy_sell_list(TOP_SELL_TABLE, $asc_desc='ASC');  // sell
}

$fullName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "";
$user_logged_in = false;
$action_class_market = 'flo_log_in';
$action_class_buy_sell = 'flo_log_in';
if(checkLoginStatus()) {
    $user_logged_in = true;
    $action_class_market = 'market_submit_btn';
    $action_class_buy_sell = 'process';
}

$_SESSION['rand'] = rand();
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
    <script src="https://ranchimall.github.io/Standard_Operations/floCrypto.js"></script>

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
                
<div class="row" >
    <div id="to_login_fields">    
        <h2 class="text-center">Login using FLO Private Key </h2>
        <hr>
        <div class="col-lg-12 lazy-form">
            <label for="key_flo" id="loginMessage">Enter your FLO Private Key</label>
            <input type="password" name="key_flo" id="key_flo" style="color:initial">
            
        </div>
        
        <div class="col-lg-12">
            <input type="submit" id="key_flo_submit" class="btn btn--block btn--facebook flo_log_in" style="width: 100%;" value="Enter Key">
        </div>
    </div>
        <a href="logout.php" id="to_logout_fields" style="display: none">
            <div class="btn btn--facebook ">
                Log Out
            </div>
        </a>

    </div> 
            
    <script>

// Refresh needed every 12 minutes to fetch new time challenge to be digitally signed
 var delayInMilliseconds = 700000; 
 setInterval(function() {window.location.reload();}, delayInMilliseconds);

function ajax_authentication(floID,floPubKey,message,signDataWithFlo) {
    $.ajax({
        method:'post',
        url:'ajax/authenticateMe.php',
        data: { floID: floID,floPubKey:floPubKey,message:message,signDataWithFlo:signDataWithFlo},
        success: function(data) {
        //     console.log(data);
        }
    }).error(function(xhr, status, error) {
        console.log(xhr.responseText);
        document.getElementById("loginMessage").innerHTML = xhr.responseText;
        document.getElementById("loginMessage").style.color = "red";
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
             document.getElementById("loginMessage").style.color = "#FFFFFF";    
             document.getElementById("loginMessage").innerHTML = "Successful Login. Digital Signature is verified. Getting user details. <span id='myBlinkingDiv'>Wait a moment<span>";
             
             var blink_speed = 500; 
             var t = setInterval(function () {
                var ele = document.getElementById('myBlinkingDiv');
                ele.style.visibility = (ele.style.visibility == 'hidden' ? '' : 'hidden');
                }, blink_speed);
                
             window.location.reload();
                         } else {
                document.getElementById("loginMessage").innerHTML = d.message;
                document.getElementById("loginMessage").style.color = "red";   
            }
            
        } 

    });
}

   document.getElementById("key_flo_submit").onclick = function(evt) {

            let floPrivKey = document.getElementById("key_flo").value;
            let floPubKey = floCrypto.getPubKeyHex(floPrivKey);
            let floID = floCrypto.getFloID(floPubKey);
            
            if (floID != null) {
            //Generating a tough challenge for user to sigitally sign    
            let message ="<?php echo($_SESSION['session_id'] . round(time()/1000)*1000 . $_SESSION['rand']);?>";
            let signDataWithFlo = floCrypto.signData(message, floPrivKey);
            delete floPrivKey;

            document.getElementById("loginMessage").style.color = "#FFFFFF";    
            document.getElementById("loginMessage").innerHTML = "Digital signature provided for server generated message. Waiting for signature verification.";

            ajax_authentication(floID,floPubKey,message,signDataWithFlo);            
            } else {
                var displayMessage = "Private Key is invalid";
                console.log(displayMessage);
                document.getElementById("loginMessage").innerHTML = displayMessage;
                document.getElementById("loginMessage").style.color = "red";
            }
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
            <div class="col-sm-6 text-right text--center--mobile ">
                <h2 class="text--uppercase"><?=$fullName?></h2>
                <h5 class="text-bold"><?=$flo_id?></h2>
                <h6 class="text--bold text--uppercase">Token Balance: <span id="my_bit_balance">loading...</span> </h6>
                <h6 class="text--bold text--uppercase">Cash Balance: $ <span id="my_cash_balance">loading...</span> </h6>
            </div>
            <script>get_my_balance();</script>
        <?php } ?> 
    
    </div>
</div>

<?php if ($user_logged_in) {include_once 'req_user_info.php';} ?>
