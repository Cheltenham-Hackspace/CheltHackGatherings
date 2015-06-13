<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 28/05/15
 * Time: 16:28
 */

session_start();

include_once("../mysql/GatheringMySQLConnection.php");
include_once("../variables/User.php");

if(!isset($_POST['Attending']) && !isset($_POST['NotAttending'])){
    redirect("", "../../pages/gathering/gathering.php");
    return;
}

if(!isset($_SESSION['user'])){
    redirect("You must be logged in to RSVP");
    return;
}

if(($_SESSION['count'] -= 1) == 0) {
    session_regenerate_id();
    $_SESSION['count'] = 5;
}

if(isset($_POST['Attending']) && isset($_POST['NotAttending'])){
    redirect("", "../../pages/gathering/gathering.php");
    return;
}

if(!isset($_POST['event-id'])){
    redirect("", "../../pages/gathering/gathering.php");
    return;
}

/** @var GatheringMySQLConnection $mysqlConnection */
$mysqlConnection = GatheringMySQLConnection::createDefault("../../");
$mysqlConnection->connect();

$userIdRequest = User::getUserID($_SESSION['user'], $mysqlConnection);
if(!$userIdRequest[0]){
    redirect("An unknown error occurred. Please try again later.", "../../pages/gathering/gathering.php");
    return;
}

if(isset($_POST['Attending'])){
    $mysqlConnection->addAttendees($_POST['event-id'], array($userIdRequest[3]));
}elseif(isset($_POST['NotAttending'])){
    $mysqlConnection->addNotAttendees($_POST['event-id'], array($userIdRequest[3]));
}

redirect("RSVPed successfully!", "../../pages/gathering/gathering.php");

function redirect($error, $address = "../../pages/login/login.php", $replace = true, $code = 303){
    $_SESSION['error'] = $error;
    header('Location: ' . $address, $replace, $code);
}