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

//List all the gatherings in the gatherings table
$gatherings = $model->getAllGatherings(GatheringsDataModel::GATHERINGS);
//If it fails
if ($gatherings === false) {
    //Then log the error and exit the script.
    logError(
        "There was an error when getting all gatherings from the GATHERINGS table. ",
        $model->getError(),
        $model->GetErrorString($model->getError()));
    return;
}

//Create a count for the number of records moved and a new array for events that need to recur.
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

        //If the event recurs
        if ($gather->doesRecur()) {
            //Then push it into the array and move onto the next gatherings. it will be dealt with later.
            array_push($recurringEvents, $gather);
        } else {
            //Switch the table to the past gatherings.
            $result = $model->switchTable($gather->getId(), GatheringsDataModel::GATHERINGS,
                GatheringsDataModel::PAST_GATHERINGS);

            //If that fails
            if ($result === false) {
                //Then log the error and move onto the next one.
                logError(
                    "There was an error when switching gathering marked by ID '" . $gather->getId() . "' from the
                    GATHERINGS table to PAST_GATHERINGS table. ",
                    $model->getError(),
                    $model->GetErrorString($model->getError()));
            } else {
                //Otherwise increment the counter for a successful move.
                $count++;
            }
        }
    }
}
//endregion

//region UPDATE RECURRING EVENTS

//Create a counter for the events that have been updated successfully.
$recurCount = 0;

foreach ($recurringEvents as $event) {
    /** @var $event Gathering */
    //If the value does not match the regex [0-6] (Number must be 0,1,2,3,4,5,6 [days of week]).
    if (!preg_match("/[0-6]/i", $event->getRecurring())) {
        //Then log the error and move onto the next one.
        logError(
            "There was an error when updating recurring events. ",
            "Unknown",
            "Recurring day '" . $event->getRecurring() . "' did not match the regex [0-6].");
    } else {
        //Otherwise calculate the new time that it will be occurring at.
        $newTime = getNewDate($event->getOccurring(), $event->getRecurring());
        //And try to update the gathering
        $update = $model->updateGathering(
            array(//Update occurring to the new time
                "occurring" => $newTime
            ),
            array(//When id is equal to the event id (Field is a primary key and unique so there can be no duplicates)
                "id" => $event->getId()
            ),
            GatheringsDataModel::GATHERINGS //In the table `gatherings`
        );

        //If the update fails.
        if ($update === false) {
            //Then log the error and move onto the next one.
            logError(
                "There was an error when updating the gathering marked by ID '" . $gather->getId() . "' from the
                GATHERINGS table with the new occurring date '" . $newTime . "'. ",
                $model->getError(),
                $model->GetErrorString($model->getError()));
        } else {
            //Otherwise increment the counter for a successful update.
            $recurCount++;
        }
    }
}
//endregion

//region OUTPUT COUNTS AND LOG DETAILS

//Log how many updates and moves occurred successfully.
echo timeFix() . "Successfully moved '" . $count . "' gathering records.";
echo timeFix() . "Successfully updated '" . $recurCount . "' recurring gathering records.";

//Then add a log end line so we can separate each execution if we need to see where something went wrong.
$tf = timeFix();
echo "\n------| LOG END " . substr($tf, 1, strlen($tf) - 1) . " |------";
//endregion

//region UTILITY FUNCTIONS
/**
 * This function will log an error to the standard output in the correct format.
 *
 * @param $message string The base error message to first print to the log file.
 * @param $code string The actual error code
 * @param $error string The real error message (Usually supplied from the model).
 */
function logError($message, $code, $error)
{
    echo timeFix() . $message . "Error below: ";
    echo timeFix() . "\tCode: " . $code;
    echo timeFix() . "\tMess: " . $error;
}

/**
 * This function will calculate a new date for a recurring event.
 * @param $occurs int The epoch timestamp for when the event originally occurs.
 * @param $recurs int The day of the week the event occurs on (Should already be validated with regex: /[0-6]/)
 * @return int The new timestamp for the event.
 */
function getNewDate($occurs, $recurs)
{
    //Separate the hour minute and second the event occurs at.
    $time = date("H:i:s", $occurs);
    //Create a blank day variable.
    $day = "";
    //And get the correct day string from the recurs variable.
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
    //Get the new year month and day for the next day we just got (So next monday or next tuesday etc...)
    $newDay = date("Y-m-d", strtotime("next " . $day));
    //Calculate the new timestamp from the Y-m-d and H:i:s we now have concatenated.
    $timestamp = strtotime($newDay . " " . $time);
    //And finally return it.
    return $timestamp;
}

/**
 * Function title stands for Time Prefix. This will return the current date and time in the format "\n[Y-m-d H:i:s]: ".
 *
 * @return string The prefix.
 */
function timeFix()
{
    $date = new DateTime();
    return "\n[" . $date->format('Y-m-d H:i:s') . "]:";
}
//endregion