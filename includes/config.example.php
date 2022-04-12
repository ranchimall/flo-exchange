<?php

/** NOTE: The session values must match DB values of users table */

/*Change these values according to your configurations*/

define("DB_HOST", "localhost");
define("DB_NAME", "test");
define("DB_USER", "User");
define("DB_PASS", "password");
define("MESSAGE_DATABASE_ERROR", "Failed to connect to database.");

define("EMAIL_USE_SMTP", true);
define("EMAIL_SMTP_HOST", "secure207.inmotionhosting.com");
define("EMAIL_SMTP_AUTH", true);
define("EMAIL_SMTP_USERNAME", "abc@abc.net");
define("EMAIL_SMTP_PASSWORD", "smtp_pass");
define("EMAIL_SMTP_PORT", 587);  //587
define("EMAIL_SMTP_ENCRYPTION", "ssl");

define("RT", "rt@gmail.com");
define("RM", "dv@xyz.net");
define("PI", "am@xyz.com");
define("AB", "av@xyz.com");
define("RMGM", "rm@xyz.com");
define("FINANCE", "finance@xyz.com");

define("EMAIL_SENDER_NAME", "Ranchi Mall");
define("EMAIL_SUBJECT", "Ranchi Mall Fund Transfer Request.");
define("EMAIL_SUBJECT_RTM_TRANSFER", "Ranchi Mall RMT Transfer Request.");
define("EMAIL_SUBJECT_BTC_TO_CASH", "Ranchi Mall BTC To CASH exchange Request.");

define("TOP_BUYS_TABLE", "active_buy_list");
define("TOP_SELL_TABLE", "active_selling_list");
define("CREDITS_TABLE", "assetbalance");
define("CREDITS_HISTORY_TABLE", "bal_history");
define("ACCOUNTS_TABLE", "bank_accounts");
define("USERS_TABLE", "customer");
define("TRANSFER_INFO_TABLE", "fund_transfer");
define("MSG_TABLE", "messages");
define("ORDERS_TABLE", "orderbook");
define("TRANSACTIONS_TABLE", "transaction");
define("ADMIN_BAL_RECORDS", "root_bal_updates");

define("APP_ID", '371829233156037');
define("APP_SECRET", '439475897850541dcd7d4b11f9ef654c');

define("ADMIN_FB_ID", "107349923344472");
define("ADMIN_ID", "1");
define("ADMIN_UNAME", "Ranchi1507555608");

define("COOKIE_LIFE_DAYS", 60);
