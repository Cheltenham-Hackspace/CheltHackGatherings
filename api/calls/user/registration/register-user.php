<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 16/06/15
 * Time: 20:40
 */
include_once("../../../models/database/DatabaseModel.php");
include_once("../../../models/user/UserDataModel.php");
include_once("../../../JSONHelper.php");
include_once("../../../../php/security/PasswordHashing.php");
include_once("../../../../php/security/SecurityUtils.php");
//region Required field validation
//Username validation
if (!isset($_POST['userUsername']) || $_POST['userUsername'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "Username is a required field.")));
    return;

}
//Password validation
if (!isset($_POST['userPassword']) || $_POST['userPassword'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "Password is a required field.")));
    return;

}
if (!isset($_POST['userConfirmPass']) || $_POST['userConfirmPass'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "Confirm Password is a required field.")));
    return;

}
if ($_POST['userPassword'] != $_POST['userConfirmPass']) {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "The supplied passwords do not match.")));
    return;

}
//Name validation
if (!isset($_POST['userFirstName']) || $_POST['userFirstName'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "First Name is a required field.")));
    return;

}
if (!isset($_POST['userLastName']) || $_POST['userLastName'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "Last Name is a required field.")));
    return;

}
//Email validation
if (!isset($_POST['userContactEmail']) || $_POST['userContactEmail'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "Email is a required field.")));
    return;

}//TODO Add email duplicate validation.
//Image validation
if (!isset($_POST['userImageProfile']) || $_POST['userImageProfile'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "Profile Image is a required field.")));
    return;

}
if (!isset($_POST['userImageHeader']) || $_POST['userImageHeader'] == '') {
    echo json_encode(array("success" => false, "error" => array("code" => "ERR-CUSTOM-REGISTER-REQUIRED-FIELD-MISSING", "message" => "Header Image is a required field.")));
    return;

}
getUser();
//endregion

function getUser()
{
    $model = new UserDataModel("../../models/database/DatabaseModel.php");
    $model->initFromAuthFile("../../../../lockedres/auth/database-details.txt");
    $model->connect();

    //region Posted details to insert query array generation.
    $detailsArray = array();

    //Account active *
    $detailsArray = array_merge($detailsArray, array("active" => 2));

    //Users address contact info
    if (!isset($_POST['userContactAddress']) || $_POST['userContactAddress'] == '') {
        $detailsArray = array_merge($detailsArray, array("contact_address" => null));
    } else {
        $detailsArray = array_merge($detailsArray, array("contact_address" => SecurityUtils::obfuscateString
        (SecurityUtils::generateSaltFS($_POST['userUsername'], $_POST['userFirstName'], $_POST['userLastName']) .
            $_POST['userContactAddress'])));
    }
    //Users email address *
    $detailsArray = array_merge($detailsArray, array("contact_email" => SecurityUtils::obfuscateString
    ($_POST['userContactEmail'])));
    //Phone number contact info
    if (!isset($_POST['userContactPhone']) || $_POST['userContactPhone'] == '') {
        $detailsArray = array_merge($detailsArray, array("contact_telephone" => null));
    } else {
        $detailsArray = array_merge($detailsArray, array("contact_telephone" => SecurityUtils::obfuscateString
        (SecurityUtils::generateSaltFS($_POST['userUsername'], $_POST['userFirstName'], $_POST['userLastName']) .
            $_POST['userContactPhone'])));
    }
    //Offer description
    if (!isset($_POST['userDescriptionOffer']) || $_POST['userDescriptionOffer'] == '') {
        $detailsArray = array_merge($detailsArray, array("description_offer" => null));
    } else {
        $detailsArray = array_merge($detailsArray, array("description_offer" => $_POST['userDescriptionOffer']));
    }
    //Personal Description
    if (!isset($_POST['userDescriptionPersonal']) || $_POST['userDescriptionPersonal'] == '') {
        $detailsArray = array_merge($detailsArray, array("description_personal" => null));
    } else {
        $detailsArray = array_merge($detailsArray, array("description_personal" => $_POST['userDescriptionPersonal']));
    }
    //Use description
    if (!isset($_POST['userDescriptionUse']) || $_POST['userDescriptionUse'] == '') {
        $detailsArray = array_merge($detailsArray, array("description_use" => null));
    } else {
        $detailsArray = array_merge($detailsArray, array("description_use" => $_POST['userDescriptionUse']));
    }
    //Password / Hash *
    $detailsArray = array_merge($detailsArray, array("hash" => create_hash($_POST['userPassword'])));
    //Header image *
    $detailsArray = array_merge($detailsArray, array("image_header" => $_POST['userImageHeader']));
    //Profile image *
    $detailsArray = array_merge($detailsArray, array("image_profile" => $_POST['userImageProfile']));
    //First name
    $detailsArray = array_merge($detailsArray, array("name_first" => $_POST['userFirstName']));
    //Last name
    $detailsArray = array_merge($detailsArray, array("name_last" => $_POST['userLastName']));
    //Middle name
    if (!isset($_POST['userMiddleName']) || $_POST['userMiddleName'] == '') {
        $detailsArray = array_merge($detailsArray, array("name_middle" => null));
    } else {
        $detailsArray = array_merge($detailsArray, array("name_middle" => $_POST['userMiddleName']));
    }
    //Permissions
    $detailsArray = array_merge($detailsArray, array("permissions" => ""));
    //Is address private
    if (!isset($_POST['userContactAddressPrivate']) || $_POST['userContactAddressPrivate'] == '') {
        $detailsArray = array_merge($detailsArray, array("private_address" => 0));
    } else {
        $detailsArray = array_merge($detailsArray, array("private_address" => 1));
    }
    //Is email private
    if (!isset($_POST['userContactEmailPrivate']) || $_POST['userContactEmailPrivate'] == '') {
        $detailsArray = array_merge($detailsArray, array("private_email" => 0));
    } else {
        $detailsArray = array_merge($detailsArray, array("private_email" => 1));
    }
    //Is full name private
    if (!isset($_POST['userContactFullnamePrivate']) || $_POST['userContactFullnamePrivate'] == '') {
        $detailsArray = array_merge($detailsArray, array("private_full_name" => 0));
    } else {
        $detailsArray = array_merge($detailsArray, array("private_full_name" => 1));
    }
    //Is phone number private
    if (!isset($_POST['userContactPhonePrivate']) || $_POST['userContactPhonePrivate'] == '') {
        $detailsArray = array_merge($detailsArray, array("private_phone" => 0));
    } else {
        $detailsArray = array_merge($detailsArray, array("private_phone" => 1));
    }
    //Username
    $detailsArray = array_merge($detailsArray, array("username" => $_POST['userUsername']));
    //Unique ID
    $uid = SecurityUtils::generateID(10);
    $detailsArray = array_merge($detailsArray, array("unique_id" => $uid));
    //endregion

    $result = $model->createUserFromDetails($detailsArray);
    if ($result === false) {
        echo JSONHelper::convertErrorToJSON($model);
    } else {
        //TODO This todo is only here as a marker for the following comment:
        //Due to the fact that this returns the users unique id, it could be
        //used for spamming accounts. If a bot where to call this script they
        //could then activate the account at the same time. It is unlikely that
        //this would happen but I may change it at another point. If you think
        //that this should be changed contact Vitineth and let him know.
        echo json_encode(array("success" => true, "mail" => true, "id" => $uid));
    }
}

