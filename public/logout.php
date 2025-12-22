<?php
use App\Exceptions\ClassException;
use App\Lib\Logger;
use App\Models\User;

require_once(__DIR__ . '/../app/bootstrap.php');

//Run the session logout method to logout the current user
// (B) USE THE CORRECT SESSION CLASS METHOD TO LOGOUT THE USER
$session->logout();

// (C) REDIRECT THE USER TO THE index.php PAGE
header('Location: index.php');

die();