<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 28/05/15
 * Time: 00:08
 */
//Start the session so we can set the $_SESSION values.
session_start();

//Include all the php files we need. We are using once which is slower but I feel like it would be better. I can replace
//it with include("") but for now this is fine.
include_once("../security/PasswordHashing.php");
include_once("../mysql/MySQLConnection.php");
include_once("../variables/User.php");
include_once("../../res/libraries/Michelf/Markdown.inc.php");
include_once("../security/SecurityUtils.php");

//If either username or password is not set (either the form is having a heart attack or someone has navigated straight
//here for some reason.
if(!isset($_POST['username']) || !isset($_POST['password'])){
    //If so then redirect them with this error to the login page.
    redirect("Username or password was not set!");
    return;
}

//Otherwise get the username and password
$username = $_POST['username'];
$password = $_POST['password'];

//Connect to the MySQL server.
$mysqlConnection = MySQLConnection::createDefault("../../");
$connectionResult = $mysqlConnection->connect();

if(!$connectionResult[0]){
    redirect("Could not connect to the database. Error: [" . $connectionResult[1] . "] " . $connectionResult[2] . ".
    Please try again later.");
    return;
}

//And get the users id from the supplied username
$userIdRequestResult = User::getUserID($username, $mysqlConnection);

//If the response from the ID request return a false result (No user found)
if(!$userIdRequestResult[0]){
    //Then redirect and return out of this file (Otherwise we keep going. Redirects aren't instant. Go figure.)
    redirect("Invalid username or password.");
    return;
}

//Get the user ID from the request result now that we know it was a success.
$userId = $userIdRequestResult[3];

$userRequest = User::createFromID($userId, $mysqlConnection);
if(!$userRequest[0]){
    redirect("An unknown error occurred");//TODO(ryan <vitineth@gmail.com>): Fix this. This is REALLY bad for bugs!
    return;
}

/** @var User $user */
$user = $userRequest[3];
//Get the user hash from the request result now that we know it was a success.
$hash = $user->getHash();

//Check if the password the user supplied was valid with the newly obtained hash
$isValidPassword = validate_password($password, $hash);

//If it was
if($isValidPassword){
    if ($user->getActive() == 0) {
        redirect("Your account has been deactivated. If you have just registered check your email to finish the
        registration. Otherwise contact an admin about getting your account reinstated.");
        return;
    }
    if ($user->getActive() == 2) {
        redirect("Your account is currently deactivated. To enable your account click the link in the email that
        should have been sent to you when you signed up. If you didn't recieve an email check your spam folder or
        contact a site admin.");
        return;
    }

    //Then set the user of the session to their username
    $_SESSION['user'] = $username;
    //Store the user ID as we use that more often as well
    $_SESSION['user-id'] = $userId;
    //Store their user agent for checks later to stop spoofing
    $_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT'];
    //And store a count that will refresh their session ID every 5 requests so reduce the damage an attacher can do.
    $_SESSION['count'] = 5;
    //Store whether they are an admin for the basic navigation stuff
    $_SESSION['admin'] = in_array(Permission::getDefaultPermissions()['PERM_ALL_ADMIN'], $user->getPermissionsArray());
    //Finally redirect to the testing page
    redirect(null, "../../pages/gathering/gathering.php");
    //And return
    return;
}else{
    redirect("Invalid username or password.");
    return;
}

function redirect($error, $address = "../../pages/login/login.php", $replace = true, $code = 303){
    if($error != null) $_SESSION['error'] = $error;
    header('Location: ' . $address, $replace, $code);
}