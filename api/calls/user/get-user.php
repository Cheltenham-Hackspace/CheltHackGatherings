<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 16/06/15
 * Time: 20:04
 */
include_once("../../models/database/DatabaseModel.php");
include_once("../../models/user/UserDataModel.php");
include_once("../../JSONHelper.php");

if (isset($_POST["id"]) && !empty($_POST["id"])) { //Checks if action value exists
    getUser();
} else {
    echo json_encode(array(
        "success" => false,
        "error" => array(
            "code" => "ERR-CUSTOM-GETUSER-PARAMETER-MISSING",
            "message" => "The required field 'id' was not sent in the request."
        )
    ));
}

function getUser()
{
    $model = new UserDataModel("../../models/database/DatabaseModel.php");
    $model->initFromAuthFile("../../../lockedres/auth/database-details.txt");
    $model->connect();

    $id = $_POST['id'];

    $result = $model->getUserByID($id);
    if ($result === false) {
        echo JSONHelper::convertErrorToJSON($model);
    } else {
        echo json_encode(array("success" => true,
            "result" => $result));
    }
}