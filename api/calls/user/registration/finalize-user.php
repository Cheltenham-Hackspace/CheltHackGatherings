<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 17/06/15
 * Time: 19:15
 */

include_once("../../../../php/security/SecurityUtils.php");
include_once("../../../../php/variables/User.php");
include_once("../../../models/database/DatabaseModel.php");
include_once("../../../models/user/UserDataModel.php");

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    echo json_encode(array(
        "success" => false,
        "error" => array(
            "code" => "ERR-WRONG-REQUEST-TYPE",
            "message" => "The wrong request type was used. This must be a GET request.."
        )
    ));
    return;
}

if (isset($_GET["uid"]) && !empty($_GET["uid"])) { //Checks if action value exists
    mailUser();
} else {
    echo json_encode(array(
        "success" => false,
        "error" => array(
            "code" => "ERR-CUSTOM-FINALIZEUSER-PARAMETER-MISSING",
            "message" => "The required field 'uid' was not sent in the request."
        )
    ));
}


function mailUser()
{
    $uid = $_GET['uid'];

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
                "code" => "ERR-CUSTOM-FINALIZEUSER-NO-USER-FOUND",
                "message" => "The specified user cannot be obtained. Are you already registered?"
            )
        ));
        return;
    }

    /** @var User $user */
    $user = $users[0];
    $result = $model->updateUser(array(
        "active" => 1
    ), array(
        "id" => $user->getId(),
        "unique_id" => $user->getUniqueID(),
        "username" => $user->getUsername()
    ));

    if (!$result) {
        echo JSONHelper::convertErrorToJSON($model);
    } else {
        echo json_encode(array(
            "success" => "true"
        ));
    }
}