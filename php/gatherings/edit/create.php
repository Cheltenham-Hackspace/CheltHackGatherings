<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 28/05/15
 * Time: 21:00
 */

session_start();

include_once("../../mysql/GatheringMySQLConnection.php");
include_once("../../security/SecurityUtils.php");
include_once("../../variables/User.php");
include_once("../../variables/Permission.php");

if(!isset($_SESSION['user'])){
    redirect("You must be logged in to create a gathering!");
    return;
}

if(($_SESSION['count'] -= 1) == 0) {
    session_regenerate_id();
    $_SESSION['count'] = 5;
}

if($_SESSION['agent'] != $_SERVER['HTTP_USER_AGENT']){
    $_SESSION['agent'] = null;
    $_SESSION['user'] = null;
    $_SESSION['count'] = null;
    redirect("There is a chance your session has been hijacked. This could be a false positive (Sometimes triggered from new extensions being installed). Please re-login just to be safe.");
    return;
}

//Confirm all required values are here

if(
    !isset($_POST['gatheringName']) ||
    !isset($_POST['gatheringDescription']) ||
    !isset($_POST['gatheringCreatedReal']) ||
    !isset($_POST['gatheringOccurring']) ||
    !isset($_POST['gatheringTime']) ||
    !isset($_POST['gatheringTimeout']) ||
    !isset($_POST['gatheringLocationAddress'])
){
    redirect("Required details not filled in. Please confirm all fields marked with a * are filled in.", "../../../pages/gathering/edit/create.php");
    return;
}

$mysqlConnection = GatheringMySQLConnection::createDefault("../../../");
$mysqlConnection->connect();

$userIdRequest = User::getUserID($_SESSION['user'], $mysqlConnection);
if(!$userIdRequest[0]){
    redirect("Error: [" . $userIdRequest[1] . "]: " . $userIdRequest[2] . ". Try again later. If this problem persists please let one of the admin staff know.", "../../../pages/gathering/edit/create.php");
    return;
}

$userID = $userIdRequest[3];
$userRequest = User::createFromID($userID, $mysqlConnection);

if(!$userRequest[0]){
    redirect("Error: [" . $userRequest[1] . "]: " . $userRequest[2] . ". Try again later. If this problem persists please let one of the admin staff know.", "../../../pages/gathering/edit/create.php");
    return;
}
/**@var User $user*/
$user = $userRequest[3];
if(!in_array(Permission::getDefaultPermissions()['PERM_ALL_ADMIN'], $user->getPermissionsArray()) || !in_array(Permission::getDefaultPermissions()['PERM_GATHERING_CREATE'], $user->getPermissionsArray())){
    redirect("Error: You don't have the necessary permissions to create a gathering. If you feel this is in error contact an admin.", "../../../pages/gathering/edit/create.php");
}

$dt = DateTime::createFromFormat("d/m/Y H:i", $_POST['gatheringOccurring'] . " " . $_POST['gatheringTime']);
$time = $dt->getTimestamp();

$response = $mysqlConnection->addGathering($_POST['gatheringDescription'], $_POST['gatheringName'], $_POST['gatheringLocationAddress'], $time, $userID, (isset($_POST['gatheringLocationLatitude']) ? $_POST['gatheringLocationLatitude'] : null), (isset($_POST['gatheringLocationLongitude']) ? $_POST['gatheringLocationLongitude'] : null), $_POST['gatheringTimeout'], true, array($userID));

if(!$response[0]){
    redirect("Error: [" . $response[1] . "]: " . $response[2], "../../../pages/gathering/gathering.php");
    return;
}

redirect("Gathering submitted successfully!", "../../../pages/gathering/gathering.php");

function redirect($error, $address = "../../../pages/login/login.php", $replace = true, $code = 303){
    $_SESSION['error'] = $error;
    header('Location: ' . $address, $replace, $code);
}