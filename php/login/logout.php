<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 13/06/15
 * Time: 17:36
 */

session_start();

unset($_SESSION['user']);
unset($_SESSION['user-id']);
unset($_SESSION['agent']);
unset($_SESSION['count']);
unset($_SESSION['admin']);
header('Location: ' . $_SERVER['HTTP_REFERER'], true, 303);