<?php
ob_start();
require_once 'includes/imp_files.php';
require_once VIEWS_DIR.'/header.php';

//include_once VIEWS_DIR.'/buy_sell_div.php';
//include_once VIEWS_DIR.'/buy_sell_list.php';
include_once VIEWS_DIR.'/view_all_recent_transactions.php';

include_once 'footer.php';

?>
<script src="<?=JS_DIR?>/load_more_recent_transactions.js"></script>
