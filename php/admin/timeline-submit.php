<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 13/06/15
 * Time: 15:11
 */

include_once("../mysql/MySQLConnection.php");
include_once("../variables/User.php");

//Start the session so we can access the users details
session_start();

//Verify that all the necessary field are there.
//This is a really annoying way to do it but I want to do it like this so the user can see which field was not filled
// in. I might change it later but for not it is okay albeit ugly. Like _really_ ugly
if(!isset($_POST['tln-frm-timestamp'])){
    redirect("Timestamp not set!", "../../admin/pages/index.php");
    return;
}
if(!isset($_POST['tln-frm-name']) || $_POST['tln-frm-name'] == ''){
    redirect("Name not set!", "../../admin/pages/index.php");
    return;
}
if(!isset($_POST['tln-frm-description']) || $_POST['tln-frm-description'] == ''){
    redirect("Description not set!", "../../admin/pages/index.php");
    return;
}
if(!isset($_POST['tln-frm-icon']) || $_POST['tln-frm-icon'] == ''){
    redirect("Icon not set!", "../../admin/pages/index.php");
    return;
}
if(!isset($_POST['tln-frm-color'])){
    redirect("Color not set!", "../../admin/pages/index.php");
    return;
}
if(!isset($_POST['tln-frm-author'])){
    redirect("Author not set!", "../../admin/pages/index.php");
    return;
}
if(!isset($_POST['tln-frm-side'])){
    redirect("Side not set!");
    return;
}

//Verify and fix send details.

//Verify HTML Color Code

//If it doesn't match the format #xxxxxx or xxxxxx then redirect them back with an error
if (!preg_match('/^#[a-f0-9]{6}$/i', $_POST['tln-frm-color']) && !preg_match('/^[a-f0-9]{6}$/i',
        $_POST['tln-frm-color'])) {
    redirect("Invalid HTML color code. Please use the format #xxxxxx");
    return;
}

//If it matches xxxxxx then add a # to the start.
if (preg_match('/^[a-f0-9]{6}$/i', $_POST['tln-frm-color'])) {
    $_POST['tln-frm-color'] = '#' . $_POST['tln-frm-color'];
}

//Fix the side value by setting it to lower case so it will be added to the database.
$_POST['tln-frm-side'] = strtolower($_POST['tln-frm-side']);

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
$mysqlConnection = new MySQLConnection("vit-mysql.ddns.net", "chelthacktesting",
    "remote_admin", "S*@qEnl6k2HpoVvyRqYeNA@4Tp8TXm");
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

//Create the insert query
$sql = "INSERT INTO `timeline` ( `author`, `color`, `description`, `icon`, `name`, `side`, `timestamp`) VALUES ( ?,
?, ?, ?, ?, ?, ? );";

//And prepare it
$stmt = $mysqlConnection->getMysqli()->prepare($sql);
//Bind all the supplied parameters to the statement
$stmt->bind_param("isssssi", $_POST['tln-frm-author'], $_POST['tln-frm-color'], $_POST['tln-frm-description'],
    $_POST['tln-frm-icon'], $_POST['tln-frm-name'], $_POST['tln-frm-side'], $_POST['tln-frm-timestamp']);

//And then execute it.
$result = $stmt->execute();

//If the result is false (It failed for some reason)
if(!$result){
    //Then redirect the user and inform them of the error.
    redirect("An error occurred executing the statement. Error: [" . $stmt->errno . "]: " .$stmt->error);
    return;
}else{
    //Otherwise set the success value
    $_SESSION['success'] = "The timeline event has been added successfully!";
    //And redirect them so they get the correct banner
    header('Location: ' . "../../admin/pages/index.php", true, 303);
    return;
}

function redirect($error, $address = "../../admin/pages/index.php", $replace = true, $code = 303){
    if($error != null) $_SESSION['error'] = $error;
    header('Location: ' . $address, $replace, $code);
}