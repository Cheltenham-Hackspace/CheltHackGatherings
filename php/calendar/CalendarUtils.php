<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 05/06/15
 * Time: 21:11
 */

include_once("../../res/libraries/vendor/autoload.php");

/**
 * This function will generate an authenticated google client with the given scopes.
 *
 * @param array $scopes The scopes required by the client.
 * @return Google_Client The generated and authenticated client.
 */
function getGoogleClient($scopes){
    //The client email for the Calendar Authentication
    $client_email = "675839723730-fcmhkmip6n89m8faq5rljqcn55039vvj@developer.gserviceaccount.com";
    //Read the contents of the private key under the locked resources folder.
    $private_key = file_get_contents("../../lockedres/keys/calendar_key.p12");

    //Then create the credentials for the authentication
    $credentials = new Google_Auth_AssertionCredentials(
        $client_email,
        $scopes,
        $private_key
    );

    //And then create the actual google client
    $client = new Google_Client();
    //Next we authenticate with the auth we just created
    $client->setAssertionCredentials($credentials);
    //Check if the access token we currently have is expired.
    //The method does exist but PhpStorm cannot see it. This is why we need both of the suppressions.
    /** @noinspection PhpUndefinedMethodInspection */
    if ($client->getAuth()->isAccessTokenExpired()) {
        //If so then refresh it using the assertion auth
        /** @noinspection PhpUndefinedMethodInspection */
        $client->getAuth()->refreshTokenWithAssertion();
    }

    return $client;
}

function getCalendarEventsCount(){
    $client = getGoogleClient(array(Google_Service_Calendar::CALENDAR_READONLY));

    //Create the calendar service off out google client
    $calendar = new Google_Service_Calendar($client);
    //And list the events
    $events = $calendar->events->listEvents('u1plvrqnij8v069m20r0noanu4@group.calendar.google.com');

    //Create an empty event string for appending later.
    $counter = 0;

    //While true (we exit out of this one with a break once page tokens die.)
    while(true) {
        //Another loop (Again we exit with a break when the current item becomes invalid)
        while (true) {
            //Specify the type of the current variable to we can access its methods and
            //variables through PhpStorm.
            /** @var Google_Service_Calendar_Event $current */
            $current = $events->current();

            if ($current instanceof Google_Service_Calendar_Event) {
                $counter++;
            }

            //Then get the next event
            $events->next();
            //If it is not valid then break.
            if (!$events->valid()) break;
        }

        //Now we want to get the next page token. This is used to access the next
        //page of events in the query.
        $pageToken = $events->getNextPageToken();
        //If the token is not null
        if ($pageToken) {
            //Create the parameters.
            $optParams = array('pageToken' => $pageToken);
            //And reset the events object with the new listed events.
            $events = $calendar->events->listEvents('u1plvrqnij8v069m20r0noanu4@group.calendar.google.com', $optParams);
        } else {
            //Otherwise (If the page token is null) then break as we are done!
            break;
        }
    }
    return $counter;
}


/**
 * This function will return a list of events in the format the FullCallendar.io can understand and display on the
 * calendar page.
 *
 * @var bool $vardumps Whether to dump all variables as we go along.
 * @returns string The internals of the event string.
 */
function getCalendarEventsString($fileDepth){
    $client = getGoogleClient(array(Google_Service_Calendar::CALENDAR_READONLY));

    //Create the calendar service off out google client
    $calendar = new Google_Service_Calendar($client);
    //And list the events
    $events = $calendar->events->listEvents('u1plvrqnij8v069m20r0noanu4@group.calendar.google.com');

    //Create an empty event string for appending later.
    $eventString = "";

    //While true (we exit out of this one with a break once page tokens die.)
    while(true) {
        //Another loop (Again we exit with a break when the current item becomes invalid)
        while (true) {
            //Specify the type of the current variable to we can access its methods and
            //variables through PhpStorm.
            /** @var Google_Service_Calendar_Event $current */
            $current = $events->current();

            if ($current instanceof Google_Service_Calendar_Event) {
                //Get the name of the event to display on the calendar
                //It is called a summary by google.
                $name = $current->getSummary();


                //Next get the start and end times and set their type for PhpStorm.
                /** @var Google_Service_Calendar_EventDateTime $startTimeObj */
                $startTimeObj = $current->getStart();
                /** @var Google_Service_Calendar_EventDateTime $endTimeObj */
                $endTimeObj = $current->getEnd();

                $start = null;
                $end = null;
                //If they are not null then set the start and end times.
                if ($startTimeObj != null) $start = $startTimeObj->getDateTime();
                if ($endTimeObj != null) $end = $endTimeObj->getDateTime();

                $recurData = processRecurrenceData($current->getRecurrence());

                //Next we need to construct the event section. I could have used
                //things such as \t here but I did it like this because if gives
                //much nicer visualization of the data.
                $eventString .=                         "    {\n";
                $eventString .=                         "        title: '" . getGatheringNameWithoutID($name) . "',\n";
                if($start != null)$eventString .=       "        start: '" . $start . "',\n";
                $eventString .=                         "        url: '" . getSystemGatheringLink($name, $fileDepth) . "',\n";
                if($recurData != "") $eventString .=    $recurData . ",\n";
                if($end != null)$eventString .=         "        end: '" . $end . "'\n";
                $eventString .=                         "    },\n";
            }

            //Then get the next event
            $events->next();
            //If it is not valid then break.
            if (!$events->valid()) break;
        }

        //Now we want to get the next page token. This is used to access the next
        //page of events in the query.
        $pageToken = $events->getNextPageToken();
        //If the token is not null
        if ($pageToken) {
            //Create the parameters.
            $optParams = array('pageToken' => $pageToken);
            //And reset the events object with the new listed events.
            $events = $calendar->events->listEvents('u1plvrqnij8v069m20r0noanu4@group.calendar.google.com', $optParams);
        } else {
            //Otherwise (If the page token is null) then break as we are done!
            break;
        }
    }

    //The last item in the event string shouldn't contain a comma so we need to
    //take the last 2 characters off (The new line and the comma). We do this
    //through substring here.
    $eventString = substr($eventString, 0, strlen($eventString) - 2);
    //And then return the generated event string.
    return $eventString;
}

/**
 * This function will convert the recurrence string given by the google API into a DOW flag ready to put into the events
 * string.
 *
 * @param string $data The recurrence data string from Google.
 * @return string The processed DOW data.
 */
function processRecurrenceData($data){
    //If the data string is null then just return an empty string.
    if($data == null)return "";
    //Otherwise grab the first value only.
    $data = $data[0];

    //If it is not weekly then return a blank string (Full calendar only supports weekly recurrences.)
    if(strpos($data, "FREQ=WEEKLY") === false) return "";
    //And if it doesn't contain the BYDAY keyword then also return an empty string.
    if(strpos($data, "BYDAY") === false) return "";

    //Get everything after the BYDAY= section in the string.
    $byDay = substr($data, strpos($data, "BYDAY=") + 6);
    //Explode it into an array of each day of the week it repeats.
    $dayArray = explode(',', $byDay);

    //Create the basic day of week string.
    $dataString = "dow: [";

    //And for each day in the array
    foreach($dayArray as $day){
        //Then add it's day of the week ID to the string from the two letter code.
        switch($day){
            case "MO":
                $dataString .= "1, ";
                break;
            case "TU":
                $dataString .= "2, ";
                break;
            case "WE":
                $dataString .= "3, ";
                break;
            case "TH":
                $dataString .= "4, ";
                break;
            case "FR":
                $dataString .= "5, ";
                break;
            case "SA":
                $dataString .= "6, ";
                break;
            case "SU":
                $dataString .= "7, ";
                break;
        }
    }

    //Remove the last space and comma
    $dataString = substr($dataString, 0, strlen($dataString) - 2);
    //And add the close square bracket.
    $dataString .= "]";

    //Then return the data string.
    return $dataString;
}

/**
 * This function will create the link for a gathering given the correct title. This function doesn't check whether the
 * title ends in [#] so if it doesn't it will break.
 *
 * @param string $eventTitle The title of the event.
 * @param int $fileDepth The depth of the file
 * @return string The link with the gathering ID with {@link htmlentities()} run on it so if the ending is malicious
 * some how it won't cause any damage.
 */
function getSystemGatheringLink($eventTitle, $fileDepth){
    //If the title is blank then return the empty string.
    if($eventTitle == null)return "";

    //Get everything after the last square bracket.
    $blockedIndex = substr(strchr($eventTitle, '['), 1);
    //And remove the last character.
    $index = substr($blockedIndex, 0, strlen($blockedIndex) - 1);

    //Then return the special character escaped version of the id with the link to the view page attached.
    //I know I REALLY shouldn't use hardcoded strings here but as this page will be referenced from elsewhere I can't
    //use relative strings.
    return generateDepthString($fileDepth) . "pages/gathering/view/view.php?id=" . htmlentities($index);
}

/**
 * This function will take a file depth and create a string of ../../'s from it.
 *
 * @param int $fileDepth The depth of the file.
 * @returns String the depth string
 */
function generateDepthString($fileDepth){
    $depthString = "";
    for($i = 0; $i < $fileDepth; $i++){
        $depthString .= "../";
    }
    return $depthString;
}

/**
 * This function will return the events name without the [#] at the end for showing on the calendar.
 *
 * @param string $eventTitle The events title
 * @return string The title without the id at the end.
 */
function getGatheringNameWithoutID($eventTitle){
    //If the title is blank then return the empty string.
    if($eventTitle == null)return "";

    //Then get everything before the last [
    $name = strchr($eventTitle, '[', true);
    //And html encode it.
    return htmlentities($name);
}