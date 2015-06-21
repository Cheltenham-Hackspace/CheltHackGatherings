<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 17/06/15
 * Time: 19:35
 */

include_once("../../../../php/security/SecurityUtils.php");
include_once("../../../../php/variables/User.php");
include_once("../../../models/database/DatabaseModel.php");
include_once("../../../models/user/UserDataModel.php");

if (isset($_POST["uid"]) && !empty($_POST["uid"])) { //Checks if action value exists
    mailUser();
} else {
    echo json_encode(array(
        "success" => false,
        "error" => array(
            "code" => "ERR-CUSTOM-MAILUSER-PARAMETER-MISSING",
            "message" => "The required field 'uid' was not sent in the request."
        )
    ));
}

function mailUser()
{
    $uid = $_POST['uid'];

    $model = new UserDataModel();
    $initStatus = $model->initFromAuthFile("../../../../lockedres/auth/database-details.txt");

    if (!$initStatus) {
        echo JSONHelper::convertErrorToJSON($model);
        return;
    }

    $connectStatus = $model->connect();

    if (!$connectStatus) {
        echo JSONHelper::convertErrorToJSON($model);
        return;
    }

    $users = $model->getUsers(array(
        "unique_id" => $uid,
        "active" => 2
    ));

    if ($users === false) {
        echo JSONHelper::convertErrorToJSON($model);
        return;
    }

    if (count($users) == 0) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" => "ERR-CUSTOM-MAILUSER-NO-USER-FOUND",
                "message" => "The specified user cannot be obtained. Are you already registered?"
            )
        ));
        return;
    }

    /** @var User $user */
    $user = $users[0];
    $email = $user->getDecryptedEmail();
    if (!empty($email)) {
        require '../../../../res/libraries/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

        $stmpDetails = correctlyLoadFile("../../../../lockedres/auth/stmp-details.txt");
        if ($stmpDetails === false) return;

        $data = explode("\n", $stmpDetails);

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
        $mail->addAddress($email, $user->getNameFirst() . " " . $user->getNameLast());     // Add a recipient
        $mail->addReplyTo('hello@cheltenhamhackspace.org', 'Cheltenham Hackspace');

        $mail->isHTML(true);                                  // Set email format to HTML

        $emailTemplate = correctlyLoadFile("../../../../res/templates/email/em2.html");
        if ($emailTemplate === false) return;

        $emailTemplate = str_replace("{{NAME}}", $user->getNameFirst() . " " . $user->getNameLast(), $emailTemplate);
        $emailTemplate = str_replace("{{LINK}}", 'http://' . urlencode($_SERVER['HTTP_HOST']) . '/CHG/php/mail/finalize.php?id=' .
            $uid, $emailTemplate);

        $mail->Subject = '[Registration]: Activate your gatherings account.';
        $mail->Body = $emailTemplate;

        $mail->AltBody = 'Hello ' . $user->getUsername() . ', \n\tThank you for your registration to Cheltenham
        Hackspace Gatherings. Please click the link below to complete your registration:\n\nhttp://' . urlencode
            ($_SERVER['HTTP_HOST']) . '/CHG/api/calls/registration/finalize-user.php?id=' . $uid . '\n\nIf you
            didn\'t request this application then please disregard this message. ';

        if (!$mail->send()) {
            echo json_encode(array(
                "success" => false,
                "error" => array(
                    "code" => "ERR-CUSOM-MAILUSER-MAIL-ERROR",
                    "message" => "When sending the email the server encountered this error: " . $mail->ErrorInfo
                )
            ));
            return;
        } else {
            echo json_encode(array(
                "success" => true
            ));
            return;
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" => "ERR-CUSTOM-MAILUSER-NO-EMAIL",
                "message" => "The specified user does not have an email address on file. We don't know how this
                happened!"
            )
        ));
    }
}

/**
 * This function will open, get the size of, read and close a file checking every time whether it worked and output
 * an error if it did not. It will echo the JSON encoded error in the correct style before returning 'false' on failure.
 *
 * @param $path string The path to the file
 * @return bool|string The files data on success or false on error.
 */
function correctlyLoadFile($path)
{
    $handle = fopen($path, "r");
    if ($handle === false) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" => "ERR-CUSTOM-MAILUSER-TEMP-OPEN-FAILED",
                "message" => "The system could not open the email template. Please contact an admin to have your
                account enabled and report this error to them."
            )
        ));
        return false;
    }

    $size = filesize($path);
    if ($handle === false) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" => "ERR-CUSTOM-MAILUSER-TEMP-SIZE-FAILED",
                "message" => "The system could not read the size of the email template. Please contact an admin to have
                your account enabled and report this error to them."
            )
        ));
        return false;
    }

    $data = fread($handle, $size);
    if ($handle === false) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" => "ERR-CUSTOM-MAILUSER-TEMP-READ-FAILED",
                "message" => "The system could not read the email template. Please contact an admin to have your
                account enabled and report this error to them."
            )
        ));
        return false;
    }

    $result = fclose($handle);
    if ($result === false) {
        echo json_encode(array(
            "success" => false,
            "error" => array(
                "code" => "ERR-CUSTOM-MAILUSER-TEMP-CLOSE-FAILED",
                "message" => "The system could not close the email template. Please contact an admin to have your
                account enabled and report this error to them."
            )
        ));
        return false;
    }

    return $data;
}