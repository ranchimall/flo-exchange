<?php ob_start(); date_default_timezone_set('Asia/Kolkata'); ?>
<?php $user_id = 0; ?>
    <!--Bootstrap-->
<?php 
require_once 'includes/imp_files.php';
require_once 'views/header.php';?>

<?php

    

    if (!checkLoginStatus()) {
        redirect_to("index.php");
    }

    if (isset($_SESSION['flo_id'], $_SESSION['user_id'])) {
        $root_flo = (string) $_SESSION['flo_id'];
        $root_user_id = (int) $_SESSION['user_id'];
        

        /*This should match ajax/rm_root.php too*/
        if ($root_flo != ADMIN_FLO_ID && $root_user_id != ADMIN_ID) {
            redirect_to("index.php");
        }

        
        $traders = newUserList();

         ?>

            <div class="container mt--2">
                <h2>Actions table</h2>
                
                <input type="text" id="search_traders" onkeyup="search_traders()" placeholder="Search for names..">

                <div class="table-responsive" id="traders_table">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>FLO ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>InsertDate</th>
                            <th>Approve</th>
                            <th>Reject</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        
                        $btn_name = null;$btn_name2 = null;
                        if (is_array($traders) && !empty($traders)) {
                            foreach ($traders as $index=>$trader) {
                                if (isset($trader->flo_id)) {
                    
                                    $btn_name = "Approve Account";
                                    $btn_name2 = "Reject Account";
                                } 
                        ?>
                                <tr>
                                    <td><?=$trader->flo_id?></td>
                                    <td><a href="http://facebook.com/flo_id" target="_blank"><?=$trader->full_name?></a></td>
                                    <td><?=$trader->email?></td>
                                    <td><?=$trader->insertDate?></td>
                                    <td><input type="button" class="btn-ra" id="<?='a_'.$trader->flo_id?>" value="<?=$btn_name?>"></td>
                                    <td><input type="button" class="btn-da" id="<?='d_'.$trader->flo_id?>" value="<?=$btn_name2?>"></td>
                                </tr>
                            <?php }
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>


            
            
            
            
        <?php
    }
?>

<!--footer-->
<?php include_once 'footer.php'; ?>

<script>
    $(document).on('click', '.btn-ra', function (e) {
        e.preventDefault();
        var btn = $(this);
        var btn_id = $(this).attr('id').slice(2);
        $.ajax({
            method:'post',
            url:'ajax/acceptNewUser.php',
            data: { task : 'approve_user', flo_id:btn_id}
        }).error(function(xhr, status, error) {
            console.log(error);
        }).success(function(data) {
            data = $.trim(data);
            if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                if (data == 'approved') {
                    btn.prop("value", "Account Accepted");
                    $.notify({
                        title: "<strong>Success!:</strong> ",
                        message: "User activated successfully."
                    },{
                        type: 'info'
                    });
                } else if (data == 'off') {
                    btn.prop("value", "Activate Account");
                    $.notify({
                        title: "<strong>Success!:</strong> ",
                        message: "User de-activated successfully."
                    },{
                        type: 'info'
                    });
                } else {
                    $.notify({
                        title: "<strong>Process Failed!:</strong> ",
                        message: "Process could not be completed."
                    },{
                        type: 'warning'
                    });
                }

            } else {
                displayNotice("Process could not be completed. Try again later.", "failure");
            }
            run_all();
        });
    });


    $(document).on('click', '.btn-da', function (e) {
        e.preventDefault();
        var btn = $(this);
        var btn_id = $(this).attr('id').slice(2);
        $.ajax({
            method:'post',
            url:'ajax/acceptNewUser.php',
            data: { task : 'reject_user', flo_id:btn_id}
        }).error(function(xhr, status, error) {
            console.log(error);
        }).success(function(data) {
            data = $.trim(data);
            if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                if (data == 'deleted') {
                    btn.prop("value", "Account Deleted");
                    $.notify({
                        title: "<strong>Success!:</strong> ",
                        message: "User deleted."
                    },{
                        type: 'info'
                    });
                } else if (data == 'off') {
                    btn.prop("value", "Activate Account");
                    $.notify({
                        title: "<strong>Success!:</strong> ",
                        message: "User de-activated successfully."
                    },{
                        type: 'info'
                    });
                } else {
                    $.notify({
                        title: "<strong>Process Failed!:</strong> ",
                        message: "Process could not be completed."
                    },{
                        type: 'warning'
                    });
                }

            } else {
                displayNotice("Process could not be completed. Try again later.", "failure");
            }
            run_all();
        });
    });
    
    function search_traders() {
        // Declare variables
        var input, filter, table, tr, td, i;
        input = document.getElementById("search_traders");
        filter = input.value.toUpperCase();
        table = document.getElementById("traders_table");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[1];

            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
        

    
</script>
