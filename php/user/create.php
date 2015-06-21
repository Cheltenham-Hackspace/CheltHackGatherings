<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 30/05/15
 * Time: 23:50
 */

session_start();

include_once("../security/PasswordHashing.php");
include_once("../mysql/MySQLConnection.php");
include_once("../variables/User.php");
include_once("../security/SecurityUtils.php");
include_once("../../res/libraries/Michelf/Markdown.inc.php");
//region MySQL Connection creation and connection.
//Connect to the MySQL server.
$mysqlConnection = MySQLConnection::createDefault("../../");
$connectionResult = $mysqlConnection->connect();

//Check if the connection was a success
if(!$connectionResult[0]){
    //If not redirect with an error.
    redirect("Could not connect to the database. Error: [" . $connectionResult[1] . "] " . $connectionResult[2] . ".
    Please try again later.", true);
    return;
}
//endregion

//region Required field validation
//Username validation
if(!isset($_POST['userUsername']) || $_POST['userUsername'] == ''){
    redirect("Username is a required field.", true);
    return;
}
//Password validation
if(!isset($_POST['userPassword']) || $_POST['userPassword'] == ''){
    redirect("Password is a required field.", true);
    return;
}
if(!isset($_POST['userConfirmPass']) || $_POST['userConfirmPass'] == ''){
    redirect("Confirm Password is a required field.", true);
    return;
}
if($_POST['userPassword'] != $_POST['userConfirmPass']){
    redirect("The supplied passwords do not match.", true);
    return;
}
//Name validation
if(!isset($_POST['userFirstName']) || $_POST['userFirstName'] == ''){
    redirect("First Name is a required field.", true);
    return;
}
if(!isset($_POST['userLastName']) || $_POST['userLastName'] == ''){
    redirect("Last Name is a required field.", true);
    return;
}
//Email validation
if(!isset($_POST['userContactEmail']) || $_POST['userContactEmail'] == ''){
    redirect("Email is a required field.", true);
    return;
}else{
    $sql = "SELECT COUNT(*) FROM `users` WHERE `contact_email`=?;";
    $stmt = $mysqlConnection->getMysqli()->prepare($sql);
    $stmt->bind_param("s", SecurityUtils::obfuscateString(
        SecurityUtils::generateSaltFS(
            $_POST['userUsername'],
            $_POST['userFirstName'],
            $_POST['userLastName']
        ) . $_POST['userContactEmail']
    ));

    $result = $stmt->execute();
    if(!$result){
        redirect("There was an error checking for other users. Error: [" . $stmt->errno . "]: " . $stmt->error, true);
        return;
    }

    if($stmt->get_result()->fetch_array(MYSQLI_NUM)[0] > 0){
        redirect("A user with that email address already exists! If you have forgotten your password then try
        visiting the reset page. If you believe this is in error please speak to an admin.", true);
        return;
    }
}
//Image validation
if(!isset($_POST['userImageProfile']) || $_POST['userImageProfile'] == ''){
    redirect("Profile Image is a required field.", true);
    return;
}
if(!isset($_POST['userImageHeader']) || $_POST['userImageHeader'] == ''){
    redirect("Header Image is a required field.", true);
    return;
}
//endregion

//region Posted details to insert query array generation.
$detailsArray = array();

//Account active *
//region Active account and force flag checking
if(isset($_POST['force'])){
    if (!isset($_SESSION['user-id']) || !isset($_SESSION['agent']) || !isset($_SESSION['count']) ||
        !isset($_SESSION['user'])) {
        redirect("You cannot force a request without being logged in.", true);
        return;
    }
    $userRequest = User::createFromID($_SESSION['user-id'], $mysqlConnection);

    if(!$userRequest[0]){
        redirect("Error: [" . $userRequest[1] . "]: " . $userRequest[2] . ". Try again later. If this
                    problem persists please let one of the admin staff know.", "../../../pages/gathering/edit/create
                    .php");
        return;
    }
    /**@var User $user*/
    $user = $userRequest[3];

    $admin = Permission::getDefaultPermissions()['PERM_ALL_ADMIN'];
    $create = Permission::getDefaultPermissions()['PERM_USER_CREATE'];
    if(!in_array($admin, $user->getPermissionsArray()) &&
        !in_array($create, $user->getPermissionsArray())){
        redirect("Error: You don't have the necessary permissions to force the creation of a user.
                     If you feel this is in error contact an admin.", true);
        return;
    }
    array_push($detailsArray, 1);
}else{
    array_push($detailsArray, 2);
}
//endregion

//Users address contact info
if(!isset($_POST['userContactAddress']) || $_POST['userContactAddress'] == ''){
    array_push($detailsArray, null);
}else{
    array_push($detailsArray, SecurityUtils::obfuscateString(SecurityUtils::generateSaltFS($_POST['userUsername'],
            $_POST['userFirstName'], $_POST['userLastName']) . $_POST['userContactAddress']));
}
//Users email address *
array_push($detailsArray, SecurityUtils::obfuscateString(SecurityUtils::generateSaltFS($_POST['userUsername'],
        $_POST['userFirstName'], $_POST['userLastName']) . $_POST['userContactEmail']));
//Phone number contact info
if(!isset($_POST['userContactPhone']) || $_POST['userContactPhone'] == ''){
    array_push($detailsArray, null);
}else{
    array_push($detailsArray, SecurityUtils::obfuscateString(SecurityUtils::generateSaltFS($_POST['userUsername'],
            $_POST['userFirstName'], $_POST['userLastName']) . $_POST['userContactPhone']));
}
//Offer description
if(!isset($_POST['userDescriptionOffer']) || $_POST['userDescriptionOffer'] == ''){
    array_push($detailsArray, null);
}else{
    array_push($detailsArray, $_POST['userDescriptionOffer']);
}
//Personal Description
if(!isset($_POST['userDescriptionPersonal']) || $_POST['userDescriptionPersonal'] == ''){
    array_push($detailsArray, null);
}else{
    array_push($detailsArray, $_POST['userDescriptionPersonal']);
}
//Use description
if(!isset($_POST['userDescriptionUse']) || $_POST['userDescriptionUse'] == ''){
    array_push($detailsArray, null);
}else{
    array_push($detailsArray, $_POST['userDescriptionUse']);
}
//Password / Hash *
array_push($detailsArray, create_hash($_POST['userPassword']));
//Header image *
array_push($detailsArray, $_POST['userImageHeader']);
//Profile image *
array_push($detailsArray, $_POST['userImageProfile']);
//First name
array_push($detailsArray, $_POST['userFirstName']);
//Last name
array_push($detailsArray, $_POST['userLastName']);
//Middle name
if(!isset($_POST['userMiddleName']) || $_POST['userMiddleName'] == ''){
    array_push($detailsArray, null);
}else{
    array_push($detailsArray, $_POST['userMiddleName']);
}
//Permissions
array_push($detailsArray, "");
//Is address private
if(!isset($_POST['userContactAddressPrivate']) || $_POST['userContactAddressPrivate'] == ''){
    array_push($detailsArray, 0);
}else{
    array_push($detailsArray, 1);
}
//Is email private
if(!isset($_POST['userContactEmailPrivate']) || $_POST['userContactEmailPrivate'] == ''){
    array_push($detailsArray, 0);
}else{
    array_push($detailsArray, 1);
}
//Is full name private
if(!isset($_POST['userContactFullnamePrivate']) || $_POST['userContactFullnamePrivate'] == ''){
    array_push($detailsArray, 0);
}else{
    array_push($detailsArray, 1);
}
//Is phone number private
if(!isset($_POST['userContactPhonePrivate']) || $_POST['userContactPhonePrivate'] == ''){
    array_push($detailsArray, 0);
}else{
    array_push($detailsArray, 1);
}
//Username
array_push($detailsArray, $_POST['userUsername']);
//Unique ID
$uid = SecurityUtils::generateID(10);
array_push($detailsArray, $uid);
//endregion


//region SQL Generation
$sql = "INSERT INTO `users` ( `active`, `contact_address`, `contact_email`, `contact_telephone`, `description_offer`,
 `description_personal`, `description_use`, `hash`, `image_header`, `image_profile`, `name_first`, `name_last`, 
 `name_middle`, `permissions`, `private_address`, `private_email`, `private_full_name`, `private_phone`, `username`,
 `unique_id`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );";
$stmt = $mysqlConnection->getMysqli()->prepare($sql);
//endregion

//region SQL Parameter Binding
/* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
$a_param_type = array("i", "s", "s", "s", "s", "s", "s", "s", "s", "s", "s", "s", "s", "s", "i", "i", "i", "i", "s",
    "s");
$a_params = array();

$param_type = '';
$n = count($a_param_type);
for($i = 0; $i < $n; $i++) {
    $param_type .= $a_param_type[$i];
}

/* with call_user_func_array, array params must be passed by reference */
$a_params[] = & $param_type;

for($i = 0; $i < $n; $i++) {
    /* with call_user_func_array, array params must be passed by reference */
    $a_params[] = & $detailsArray[$i];
}

/* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
call_user_func_array(array($stmt, 'bind_param'), $a_params);
//endregion

//region SQL Execution
$result = $stmt->execute();
if(!$result){
    redirect("There was an error creating the user! Error: [" . $stmt->errno . "] " . $stmt->error, true);
    return;
}
//endregion

//region Success code
$_SESSION['success'] = "You have successfully registered. An email will be sent to you shortly with an confirmation
link. You will not be able to log in until you verify. If you can't find the email check your spam folder.";

$sql = "SELECT * FROM `users` WHERE `unique_id`=?;";
$stmt = $mysqlConnection->getMysqli()->prepare($sql);
$stmt->bind_param("s", $uid);

$result = $stmt->execute();
if (!$result) {
    redirect("here was an error sending the confirmation email. Please contact an admin to get your account
    activated and inform them that there was an error executing the select query with the following details: Error:
    [" . $stmt->errno . "] " . $stmt->error . ". Please try again later.", true);
    return;
}

$records = $stmt->get_result();
if ($records->num_rows > 1) {
    redirect("There was an error sending the confirmation email. Please contact an admin to get your account
    activated and inform them that the amount of rows for the UNIQUE-ID was greater than 1");
    return;
}

if ($records->num_rows == 0) {
    redirect("There was an error sending the confirmation email. Please contact an admin to get your account
    activated and inform them that the amount of rows for the UNIQUE-ID was 0.");
    return;
}

$row = $records->fetch_array(MYSQLI_NUM)[0];
$name = $row['name_first'];
$server = $_SERVER['SERVER_NAME'];
$email = SecurityUtils::deobfuscateString($row['contact-email']);

//TODO(ryan <vitineth@gmail.com>):Update details to Cheltenham Hackspace Details
require '../../res/libraries/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

$path = "../../lockedres/auth/stmp-details.txt";
$fileData = fread(fopen($path, "r"), filesize($path));
$data = explode("\n", $fileData);

$mail = new PHPMailer();
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = $data[0];  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = $data[1];               // SMTP username
$mail->Password = $data[2];                     // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = $data[3];                                    // TCP port to connect to

$mail->From = 'gathering@cheltenhamhackspace.org';
$mail->FromName = 'Ryan';
$mail->addAddress('vitineth@gmail.com', 'Ryan Delaney');     // Add a recipient
$mail->addReplyTo('hello@cheltenhamhackspace.org', 'Cheltenham Hackspace');

$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Gathering Registration';
$mail->Body = 'Hello ' . $name . ', <br>&nbsp;&nbsp;&nbsp;&nbsp;Thank you for your registration to Cheltenham
Hackspace Gatherings. Please click the link below to complete your registration:<br><br>' . $server .
    '/CHG/php/mail/finalize .php?id=' . $uid . '<br>If you didn\'t request this application then please disregard
this message. ';
$mail->AltBody = 'Hello ' . $name . ', \n\tThank you for your registration to Cheltenham Hackspace Gatherings.
Please click the link below to complete your registration:\n\n' . $server . '/CHG/php/mail/finalize
.php?id=' . $uid . '\n\nIf you didn\'t request this application then please disregard this message. ';

if (!$mail->send()) {
    redirect("There was an error when trying to send the email. Please contact an admin to get your account activated
     and tell them the following information: Mailer Error: " . $mail->ErrorInfo);
    return;
} else {
    $_SESSION['success'] = "An email has been sent to " . $email . ". Please click the link in that email to confirm
    your account.";
    redirect(null);
    return;
}
//endregion

function redirect($error, $refer = false, $address = "../../pages/user/edit/create.php", $replace = true, $code = 303)
{
    if($refer){
        $address = isset($_SERVER["HTTP_REFERER"]) ? htmlspecialchars($_SERVER["HTTP_REFERER"]) : "../.
        ./pages/user/edit/create.php";
    }
    if ($error != null) $_SESSION['error'] = $error;
    header('Location: ' . $address, $replace, $code);
}