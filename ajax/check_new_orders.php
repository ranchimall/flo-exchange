<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

$last_trade_date = $_SESSION['last_trade_date'];

$lod = get_last_order_date($last_trade_date);

if ($lod) {
    $_SESSION['last_trade_date'] = time_now();
}
echo $lod;
