<?php

//Database Configuration
define("DB_HOST", "localhost");
define("DB_USER", "zzhang");
define("DB_PASSWORD", "tn4e0tn4e0");
define("DB_NAME", "zzhangauction");



// Add your name below
define("CONFIG_ADMIN", "Joshua Z");
define("CONFIG_ADMINEMAIL", "w0863366@myscc.ca");
// Add the location of your forums below
define("CONFIG_URL", "https://zzhang.scweb.ca/auction/");
// Add your blog name below
define("CONFIG_AUCTIONNAME", "Joshua's Online Auction");
// The currency used on the auction
define("CONFIG_CURRENCY", "$");

//Set Timezone
date_default_timezone_set("America/Toronto");

//Log Location
define('LOG_LOCATION', __DIR__ . '/../logs/app.log');

//File Upload Location
define("FILE_UPLOADLOC", "imgs/");

define("CLIENT_ID", "AfbfcZmj8_99nAUsLUG6XUlcdQvB8Tk-3W2Jv73UHmm2MDCdMqhxkg1DvIYbY5unFORSffKkoszDQQQr");
define("CLIENT_SECRET", "ECGqp_UGmtpbmmgnKVl-wIDsmAB-ZpNzCPp80zHMjIrWSv4pc_Q-rnsm3YgH5jGpCNoGEQY2vHNGZrQU");
define("WEBHOOK_ID", "64P84279G73765303");
define("PAYPAL_CURRENCY", "CAD");
define("PAYPAL_RETURNURL", CONFIG_URL . "/payment-successful.php");
define("PAYPAL_CANCELURL", CONFIG_URL . '/payment-cancelled.php');

?>