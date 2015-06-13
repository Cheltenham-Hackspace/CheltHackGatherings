<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 13/06/15
 * Time: 16:15
 */

include_once("../mysql/MySQLConnection.php");
include_once("../variables/User.php");
include_once("../security/SecurityUtils.php");
include_once("../../res/libraries/Michelf/Markdown.inc.php");

//Start the session so we can access the users details
session_start();

//Verify that all the necessary field are there.
//This is a really annoying way to do it but I want to do it like this so the user can see which field was not filled
// in. I might change it later but for not it is okay albeit ugly. Like _really_ ugly
if(!isset($_POST['pm-frm-name']) || $_POST['pm-frm-name'] == ''){
    redirect("Name not set");
    return;
}

if(!isset($_POST['pm-frm-description']) || $_POST['pm-frm-description'] == ''){
    redirect("Name not set");
    return;
}

//Verify user details.

//Check that the user is logged in.
if(!isset($_SESSION['user-id']) && !isset($_SESSION['agent']) && !isset($_SESSION['count']) && !isset
    ($_SESSION['user'])){

    //If not redirect them back and tell them they need to be logged in.
    redirect("You must be logged in to add a timeline event", "../../pages/login/login.php");
    return;
}

//If the value of count when we remove one is equal to zero
if(($_SESSION['count'] -= 1) == 0) {
    //Then regenerate their ID
    session_regenerate_id();
    //And set count back to 5
    $_SESSION['count'] = 5;
    //We do this to try and prevent hijacking. If the attacker does get their session ID then they can only do 5
    // actions on the site that require logged in access. After that they will need to retrieve a new user ID to do
    // more things.
}

//If their current user agent does not match the user agent they logged in with
if($_SESSION['agent'] != $_SERVER['HTTP_USER_AGENT']){
    //Then set the login details to null (Unset them)
    $_SESSION['agent'] = null;
    $_SESSION['user'] = null;
    $_SESSION['count'] = null;
    //And redirect them to the login page.
    redirect("There is a chance your session has been hijacked. This could be a false positive (Sometimes triggered
    from new extensions being installed). Please re-login just to be safe.", "../../pages/login/login.php");
    return;

    //We do this to prevent hijacking. If the attacker gets their session ID and tries to use it on a different
    // browser etc... then it will not work. However this comes with the downside that if the user installs a new
    // extension they will have to re-login.
}

//Connect to the database
$mysqlConnection = MySQLConnection::createDefault("../../");
$connectionResult = $mysqlConnection->connect();

//Verify that we connected successfully.
if (!$connectionResult[0]) {
    redirect("Could not connect to the MySQL database. Error: [" . $connectionResult[1] . "]: " . $connectionResult[2]);
    return;
}

//Create the user from their ID
$userRequest = User::createFromID($_SESSION['user-id'], $mysqlConnection);
//If it doesn't work
if(!$userRequest[0]){
    //Then redirect them.
    redirect("There was an error retrieving your user. Please try again later.");
    return;
}

//Get the user object and store it so we can use it in a minute.
$user = $userRequest[3];

//If the user does not have the permission of an admin
if(!SecurityUtils::userHavePermission($user, Permission::getDefaultPermissions()["PERM_ALL_ADMIN"])){
    //Then redirect them and inform them of the error.
    redirect("You are not an admin! Please contact an authorized admin if you feel this is in error..", "../.
    ./pages/gathering/gathering.php");
    return;
}

$sql = "INSERT INTO `permissions` ( `description`, `name`) VALUES ( ?, ? );";
$stmt = $mysqlConnection->getMysqli()->prepare($sql);

$stmt->bind_param("ss", $_POST['pm-frm-description'], $_POST['pm-frm-description']);

$result = $stmt->execute();

if(!$result){
    $stmt->close();
    $mysqlConnection->disconnect();
    redirect("There was an error executing the statement. Error: [" . $stmt->errno . "]: " . $stmt->error);
    return;
}else{
    $stmt->close();
    $mysqlConnection->disconnect();
    $_SESSION['success'] = "The permission was added successfully";
    redirect(null);
}

function redirect($error, $address = "../../admin/pages/permissions.php", $replace = true, $code = 303){
    if($error != null) $_SESSION['error'] = $error;
    header('Location: ' . $address, $replace, $code);
}