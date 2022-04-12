<?
ob_start();
session_start(); 
$_SESSION['id'] = session_id();


echo <<<END
    <head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/mate.css">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://ranchimall.github.io/Standard_Operations/lib.js"></script>
    <script src="https://ranchimall.github.io/Standard_Operations/floCrypto.js"></script>


    </head>

        <body class="text--default">
        <div class="container-fluid background--primary p--3">
            <div class="container">
                <div class="col-sm-6">
                    <a href="http://ranchimall.net/exchange"><div class="logo mt--1"></div></a>
                </div>

            <div class="col-sm-4 ">
                <div class="box p--2 mt--4">
                    <div class="row">
                        <h2 class="text-center" id="loginMessage" style="font-size:14px">All fields are mandatory.<br> Name and Email must be correct. <br>Make sure you have the private key for FLO ID.</h2>
                        
                       
                        <h2 class="text-center">Register Me Please </h2>
                        <hr>
                        <div class="col-lg-12 lazy-form">
                            <label for="id_flo">My FLO ID. I confirm I have the private key</label>
                            <input type="text" name="id_flo" id="id_flo" style="color:black;">
                        </div>
                        
                        <hr>
                        <div class="col-lg-12 lazy-form">
                            <label for="full_name">My Full Name. I promise it is correct</label>
                            <input type="text" name="full_name" id="full_name" style="color:black;">
                        </div>
                        
                         <hr>
                        <div class="col-lg-12 lazy-form">
                            <label for="email_id">My Email ID. I am sure I get emails on it</label>
                            <input type="text" name="email_id" id="email_id" style="color:black;">
                        </div>
    
                        <div class="col-lg-12">
                            <input type="submit" id="data_submit" class="btn btn--primary-1 btn-white fb_log_in" value="Register Me">
                        </div>
    
                    </div>
    
                </div>
    
            </div>

END;




 ?>

<script>

function ajax_authentication(floID,fullName,emailID) {
    $.ajax({
        method:'post',
        url:'ajax/registerMe.php',
        data: { floID: floID,fullName:fullName,emailID:emailID},
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
             document.getElementById("loginMessage").style.color = "blue";    
             document.getElementById("loginMessage").innerHTML = d.message;
             
                         } else {
                document.getElementById("loginMessage").innerHTML = d.message;
                document.getElementById("loginMessage").style.color = "red";   
            }
            
        } 

    });
}

   document.getElementById("data_submit").onclick = function(evt) {

            document.getElementById("loginMessage").style.color = "#CCCCCC";    
            document.getElementById("loginMessage").innerHTML = "Information provided. Waiting for response.";
            var floID,fullName, emailID;
            floID = document.getElementById("id_flo").value;
            fullName = document.getElementById("full_name").value;
            emailID = document.getElementById("email_id").value;
            console.log(floID);console.log(fullName);console.log(emailID);
            ajax_authentication(floID,fullName,emailID);            
            
        }

    </script>

