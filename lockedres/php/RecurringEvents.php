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

//region CONNECT TO DATABASE
$model = new GatheringsDataModel();
$init = $model->initFromAuthFile("../../lockedres/auth/database-details.txt");
if ($init === false) {
    logError(
        "There was an error when initializing from the auth file. ",
        $model->getError(),
        $model->GetErrorString($model->getError()));
    return;
}

$connect = $model->connect();
if ($connect === false) {
    logError(
        "There was an error when connecting to the database.. ",
        $model->getError(),
        $model->GetErrorString($model->getError()));
    return;
}

//endregion

//region MOVE CONCLUDED EVENTS (if they don't recur)
$gatherings = $model->getAllGatherings(GatheringsDataModel::GATHERINGS);
if ($gatherings === false) {
    logError(
        "There was an error when getting all gatherings from the GATHERINGS table. ",
        $model->getError(),
        $model->GetErrorString($model->getError()));
    return;
}

$count = 0;
$recurringEvents = array();

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
    if ($concluding != 0 && $concluding < time()) {
        var_dump($gather->getRecurring());
        if ($gather->doesRecur()) {
            array_push($recurringEvents, $gather);
        } else {
            //Switch the table to the past gatherings.
            $result = $model->switchTable($gather->getId(), GatheringsDataModel::GATHERINGS,
                GatheringsDataModel::PAST_GATHERINGS);

            if ($result === false) {
                logError(
                    "There was an error when switching gathering marked by ID '" . $gather->getId() . "' from the
                    GATHERINGS table to PAST_GATHERINGS table. ",
                    $model->getError(),
                    $model->GetErrorString($model->getError()));
            } else {
                $count++;
            }
        }
    }
}
//endregion

//region UPDATE RECURRING EVENTS
$recurCount = 0;

foreach ($recurringEvents as $event) {
    /** @var $event Gathering */
    if (!preg_match("/[0-6]/i", $event->getRecurring())) {
        logError(
            "There was an error when updating recurring events. ",
            "Unknown",
            "Recurring day '" . $event->getRecurring() . "' did not match the regex [0-6].");
    } else {
        $newTime = getNewDate($event->getOccurring(), $event->getRecurring());
        $update = $model->updateGathering(
            array(//Update occurring to the new time
                "occurring" => $newTime
            ),
            array(//When id is equal to the event id (Field is a primary key and unique so there can be no duplicates)
                "id" => $event->getId()
            ),
            GatheringsDataModel::GATHERINGS //In the table `gatherings`
        );

        if ($update === false) {
            logError(
                "There was an error when updating the gathering marked by ID '" . $gather->getId() . "' from the
                GATHERINGS table with the new occurring date '" . $newTime . "'. ",
                $model->getError(),
                $model->GetErrorString($model->getError()));
        } else {
            $recurCount++;
        }
    }
}
//endregion

//region OUTPUT COUNTS AND LOG DETAILS
echo timeFix() . "Successfully moved '" . $count . "' gathering records.";
echo timeFix() . "Successfully updated '" . $recurCount . "' recurring gathering records.";

$tf = timeFix();
echo "\n------| LOG END " . substr($tf, 1, strlen($tf) - 1) . " |------";
//endregion

//region UTILITY FUNCTIONS
function logError($message, $code, $error)
{
    echo timeFix() . $message . "Error below: ";
    echo timeFix() . "\tCode: " . $code;
    echo timeFix() . "\tMess: " . $error;
}

function getNewDate($occurs, $recurs)
{
    $time = date("H:i:s", $occurs);
    $day = "";
    switch ($recurs) {
        case 0: //Monday
            $day = "monday";
            break;
        case 1: //Tuesday
            $day = "tuesday";
            break;
        case 2: //Wednesday
            $day = "wednesday";
            break;
        case 3: //Thursday
            $day = "thursday";
            break;
        case 4: //Friday
            $day = "friday";
            break;
        case 5: //Saturday
            $day = "saturday";
            break;
        case 6: //Sunday
            $day = "sunday";
            break;
    }
    $newDay = date("Y-m-d", strtotime("next " . $day));
    $timestamp = strtotime($newDay . " " . $time);
    return $timestamp;
}

function timeFix()
{
    $date = new DateTime();
    return "\n[" . $date->format('Y-m-d H:i:s') . "]:";
}
//endregion