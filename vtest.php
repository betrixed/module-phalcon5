<?php
$MOD_DIR = __DIR__;
$COD_DIR = dirname($MOD_DIR);
$VENDOR_DIR = dirname($COD_DIR);

require $VENDOR_DIR . "/autoload.php";
require $MOD_DIR . "/tests/_bootstrap.php";
require $COD_DIR . "/codeception/app.php";

