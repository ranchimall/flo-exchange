<?php
require_once '../includes/imp_files.php';

if(isset($_POST['req']) && $_POST['req'] == 'loadMoreRecentTransactions') {

        $std = new stdClass();
        $std->msg = array();
        $std->error = true;

        if (isset($_POST['records_per_page'], $_POST['start'])) {

            $start = (int) $_POST['start'];
            $records = (int) $_POST['records_per_page'];

            $megs = last_transaction_list($start, $records);

            if (is_array($megs) && !empty($megs)) {
                $std->trade_list = $megs;
                $std->error = false;
            }
        }
        echo json_encode($std);
}
