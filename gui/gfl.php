<?php
    require_once 'include/config.php';
    // Guaranty for Login
    session_start();
    if (empty($_SESSION['SESSION_logged_in_user'])) {
        header('Location: login.php');
        exit;
    }

    $user_arr = [_USER=>'USER', _CUSTOMER=>'CUSTOMER', _RESELLER=>'RESELLER', _ADMIN=>'ADMIN'];

    define('USERT', (int) $_SESSION['SESSION_logged_in_user']);
    define('USERID', (int) $_SESSION['SESSION_logged_in_user_id']);
    define('USERNAME', $_SESSION['SESSION_logged_in_user_name']);

    foreach ($user_arr as $k => $elem) {
        define('is'.$elem, ($k == USERT));
    }

    function gfl($usertype = _ADMIN, $mode = false)
    {
        if ($mode) {
            if (USERT != $usertype) {
                die('Insufficient access-rights!');
            } elseif (USERT < $usertype) {
                die('Insufficient access-rights!');
            }
        }
        if (USERT < _USER || USERT > _ADMIN) {
            die('What the hell are you?!');
        }
    }
