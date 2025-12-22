<?php

require_once(__DIR__. "/Config/config.php");
require_once(__DIR__. "/Lib/Functions.php");
require_once(__DIR__. "/../vendor/autoload.php");



global $session;
$session = new \App\Lib\Session();