<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 11/06/15
 * Time: 17:56
 */

//TODO CRONTAB: 0 * * * * /usr/bin/php /var/www/html/CHG/lockedres/php/RecurringEvents.php >> event-log.log

include_once("../../php/variables/Gathering.php");

include("../../api/models/database/DatabaseModel.php");
include("../../api/models/gatherings/GatheringsDataModel.php");
include("../../api/JSONHelper.php");

$model = new GatheringsDataModel();
$init = $model->initFromAuthFile("../../lockedres/auth/database-details.txt");
if ($init === false) {
    echo timeFix() . "There was an error when initializing from the auth file. Error below: ";
    echo timeFix() . "\tCode: " . $model->getError();
    echo timeFix() . "\tMess: " . $model->GetErrorString($model->getError());
    return;
}

$connect = $model->connect();
if ($connect === false) {
    echo timeFix() . "There was an error when initializing from the auth file. Error below: ";
    echo timeFix() . "\tCode: " . $model->getError();
    echo timeFix() . "\tMess: " . $model->GetErrorString($model->getError());
    return;
}

$gatherings = $model->getAllGatherings(GatheringsDataModel::GATHERINGS);
if ($gatherings === false) {
    echo timeFix() . "There was an error when getting all gatherings from the GATHERINGS table. Error below: ";
    echo timeFix() . "\tCode: " . $model->getError();
    echo timeFix() . "\tMess: " . $model->GetErrorString($model->getError());
    return;
}

$count = 0;

foreach ($gatherings as $gather) {
    /** @var $gather Gathering */

    //Get the time it will conclude at for easy reference
    $concluding = $gather->getConcluding();
    //Get the modification value. If the accept timeout is greater than 0 (It occurs after the event) then return
    // that value otherwise return 0 then times by 60 twice to get seconds in said hours.
    $modVal = ($gather->getAcceptTimeout() > 0 ? $gather->getAcceptTimeout() : 0) * 60 * 60;
    //Add the mod value to the concluding time (Will either be 0 or the seconds until accept timeouts finish.)
    if ($concluding != 0) $concluding += $modVal;
    //If the concluding time does not equal 0 (no specified end time.) and the concluding time has passed
    var_dump($concluding);
    var_dump($modVal);
    var_dump(time());
    var_dump("----");
    if ($concluding != 0 && $concluding < time()) {
        //Switch the table to the past gatherings.
        $result = $model->switchTable($gather->getId(), GatheringsDataModel::GATHERINGS,
            GatheringsDataModel::PAST_GATHERINGS);

        if ($result === false) {
            echo timeFix() . "There was an error when switching gathering marked by ID '" . $gather->getId() . "'
            from the GATHERINGS table to PAST_GATHERINGS table. Error below: ";
            echo timeFix() . "\tCode: " . $model->getError();
            echo timeFix() . "\tMess: " . $model->GetErrorString($model->getError());
        } else {
            $count++;
        }
    }
}

echo timeFix() . "Successfully moved '" . $count . "' gathering records.";

function timeFix()
{
    $date = new DateTime();
    return "\n[" . $date->format('Y-m-d H:i:s') . "]:";
}
