<?php
$MOD_DIR = __DIR__;
$COD_DIR = dirname($MOD_DIR);
$VENDOR_DIR = dirname($COD_DIR);

$GLOBAL = new stdClass();
//GLOBAL function implementations
//
//TODO: populate typical start values from a permanent store.
function globals_get(string $key) : mixed
{
    return $GLOBAL->{$key} ?? null;
}

function globals_set(string $key, mixed $value)
{
    $GLOBAL->{$key} = $value;
}

function get_class_lower(object $instance) : string {
    return strtolower(get_class($instance));
}

require $VENDOR_DIR . "/autoload.php";
require $MOD_DIR . "/tests/_bootstrap.php";
require $COD_DIR . "/codeception/app.php";

