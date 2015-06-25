<!DOCTYPE html>
<!-- saved from url=(0031)http://cheltenhamhackspace.org/ -->
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../../res/images/base/logos/favicon.ico" type="image/x-icon">
    <title>
        Hackspace Cheltenham
    </title>
    <link href="../../res/libraries/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <script src="../../js/jquery-1.11.3.min.js" rel="script" type="text/javascript"></script>

    <script src="../../js/bootstrap.min.js" rel="script" type="text/javascript"></script>
    <link href="../../css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <link href="../../css/style.css" rel="stylesheet" type="text/css">
    <link href="../../css/pages/gathering/gathering.css" rel="stylesheet" type="text/css">
    <link href="../../css/pages/gathering/gathering.base.css" rel="stylesheet" type="text/css">
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top full-width">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a id="logo" class="pull-left" href="">
                <img alt="logo" src="../../res/images/base/logos/logo.png" class="main-logo">
            </a>
            <a class="navbar-brand" href="http://cheltenhamhackspace.org/#">&nbsp;Cheltenham Hackspace</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false" style="height: 1px;">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="http://cheltenhamhackspace.org/#">Home</a>
                </li>
                <li>
                    <a href="http://www.cheltenhamhackspace.org/wiki" target="_blank">Wiki</a>
                </li>
                <li>
                    <a href="http://groups.google.com/forum/?hl=en#!forum/cheltenham_hackspace"
                       target="_blank">Forum</a>
                </li>
                <li>
                    <a href="http://www.meetup.com/Cheltenham-Hackspace" target="_blank">Events</a>
                </li>
                <li>
                    <a href="http://www.cheltenhamhackspace.org/wiki/Category:Members" target="_blank">Organisation</a>
                </li>
                <!--Dropdown menu for the gatherings-->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        Gatherings
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="../gathering/gathering.php">Browse</a>
                        </li>
                        <li>
                            <a href="../calendar/calendar.php">Calendar</a>
                        </li>
                        <li class="divider"></li>
                        <?php
                        {
                            session_start();
                            if (isset($_SESSION['user-id']) && isset($_SESSION['agent']) && isset($_SESSION['count']) &&
                                isset($_SESSION['user']) && isset($_SESSION['admin'])) {
                                echo "<li><a href='#' id='logout'>Logout</a></li>";
                                echo "<li><a href='../gathering/edit/create.php'>Create</a></li>";
                                if($_SESSION['admin'])echo "<li class='divider'></li><li><a href='../../admin/pages/index.php'>Admin
                                Console</a></li>";
                            }else{
                                echo "<li><a href='../login/login.php'>Login</a></li>";
                                echo "<li><a href='../user/edit/create.php'>Register</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="page-container container">

    <?php
    //Include all the necessary php files required in here. We will be handling Gatherings, MySQL connections and Users.
    //We need SecurityUtils as we use it to check whether a user has the correct permission easily.
    include_once("../../php/variables/Gathering.php");
    include_once("../../php/variables/User.php");
    include_once("../../php/Utils.php");
    include_once("../../php/security/SecurityUtils.php");
    include_once("../../php/mysql/GatheringMySQLConnection.php");
    include_once("../../res/libraries/Michelf/Markdown.inc.php");

    //Connect to the database with a GatheringMySQLConnection so we can access the Gathering exclusive methods.
    $mysqlConnection = GatheringMySQLConnection::createDefault("../../");
    if ($mysqlConnection === false) {
        echo "<section>";
        echo "Authenticating the database connection failed. Please try again later but if the problem persists then
        inform an admin.";
        echo "</section>";
    }
    //Then connect to the database.
    $connectionResult = $mysqlConnection->connect();

    if(!$connectionResult[0]){
        echo "<section>";
        echo "Failed to connect to the database.";
        echo "</section>";
        return;
    }

    //Put in a request for all the gatherings in the database. We have to do it like this so we can pass objects, codes
    //and errors between methods.
    $gatheringResult = $mysqlConnection->getAllGatherings();

    //if the request failed
    if (!$gatheringResult[0]) {
        //Then output and error and return thereby stopping the rest of this script.
        echo "<p>";
        echo "There was an error retrieving the gatherings. Please try again later but if the problem persists
        then inform an admin with the following details:";
        echo "</p>";
        echo "<p>";
        echo "[" . $gatheringResult[1] . "]: " . $gatheringResult[2];
        echo "</p>";
        return;
    }

    //If it was successful (we would have returned by this point otherwise) then port the results into their own
    //variable for easier access
    $gatherings = $gatheringResult[3];

    //Now we need to load all the file templates. This comment will cover both. Get the paths to the file using relative
    //paths (../) and store them in a variable because we need them to open the file and get the file size. Next open
    //the file as read only as we will never need to open them. Using the just opened file, read in the file and close
    //it.

    $sectionFileTemplate = Utils::correctlyLoadFile("../../res/templates/gatherings/section_template.txt");
    if (is_array($sectionFileTemplate) && !$sectionFileTemplate[0]) {
        echo "<section>";
        echo "There was an error loading this page: " . $sectionFileTemplate[1];
        echo "</section>";
        return;
    }

    $userTemplate = Utils::correctlyLoadFile("../../res/templates/gatherings/user/user_template.txt");
    if (is_array($userTemplate) && !$userTemplate[0]) {
        echo "<section>";
        echo "There was an error loading this page: " . $sectionFileTemplate[1];
        echo "</section>";
        return;
    }

    //We can tell whether a user is logged in through checking whether the 'user' value is set in the session. As users
    //cannot alter their sessions this is secure.
    $userLoggedIn = isset($_SESSION['user']);
    //Set the user id to -1 as a base. If they are logged in we will be setting it in a minute.
    $userId = -1;

    //Check whether the user is logged in using the variable we just set
    if($userLoggedIn){
        //If they are the put in a request for their user ID.
        $userIdRequest = User::getUserID($_SESSION['user'], $mysqlConnection);
        //If there was an error getting their user ID from the user name then log them out by clearing the login based
        //session variables.
        if(!$userIdRequest[0]){
            $userLoggedIn = false;

            $_SESSION['user'] = null;
            $_SESSION['agent'] = null;
            $_SESSION['count'] = null;
        }else{
            //Otherwise (We got their user ID successfully) then set it to the user id (It is the third record as the
            //array will return array(success, error code, error string, value)
            $userId = $userIdRequest[3];
        }
    }


    /**
     * This function will take a gathering and a template (as well as some others for calling other methods within this
     * function) and return a formatted HTML template ready for echo-ing directly into the document. All strings should
     * have already had {@link htmlentities()} run on them to prevent HTML/JS injection from the database..
     *
     * <hr>
     *
     * Notes:
     * <h1>[1]: Why we do it this way:</h1>
     * <p>We need to pass the template to it because, as far as I know, this function cannot access the variables
     * declared outside of it. We could open the file inside the method and eliminate the template passing; however,
     * if we decided to do that, we would have to open the file for reading, get it's size, read the file and close
     * the file for every call of the method. If the database had many records this would become very inefficient very
     * fast. This way we only need to read the file once and just pass the template each time.
     *
     * <h1>[2]: Why we pass the user info like this:</h1>
     * <p> Like the first one we do this because we cannot access this files variables from the function. Silly PHP.
     * Instead of making a request for the same user over and over again every time a gathering was generated from the
     * database, we can grab the user id and template once and just pass it each time. The reason we do the template
     * like this as well is the exact same reasoning as <strong><i>Note[1]</i></strong>. It would be inefficient to
     * close and reopen a file many times just for new gatherings.
     *
     * <hr>
     *
     * @param Gathering $gathering       The gathering object to create the template from.
     *
     * @param string $template           The template to use. This should always $sectionTemplate. See <strong><i>
     *                                   Note[1]</i></strong>
     *
     * @param string $userDataTemplate   The user data template (This is the template for the users image and name under
     *                                   attendees. We do it this way for the same reason listed in <strong><i>Note[1]
     *                                   </i></strong>.
     *
     * @param bool $userLoggedIn         Whether the user is currently logged into the system. We need this to handle
     *                                   the RSVP buttons. See <strong><i>Note[2]</i></strong>.
     *
     * @param int $userId                The currently logged in users ID. If they are not logged in then -1 is passed.
     *                                   See <strong><i>Note[2]</i></strong>.
     *
     * @return string string - The generated template.
     *
     *
     */
    function generateTemplate($gathering, $template, $userDataTemplate, $userLoggedIn, $userId)
    {
        $data = str_replace("%NAME%", $gathering->getName(), $template);
        $data = str_replace("%DESCRIPTION%", $gathering->getDescription(), $data);
        $data = str_replace("%DATE%", date("l jS \of F Y", $gathering->getOccurring()), $data);
        $data = str_replace("%TIME%", date("h:i A", $gathering->getOccurring()), $data);
        $data = str_replace("%LOCATION%", $gathering->getLocationAddress(), $data);
        $data = str_replace("%HOUR_CLOSE%", "RSVP responses will stop being accepted " .
                ($gathering->getAcceptTimeout() == 0 ? "when the event starts." :
                    ($gathering->getAcceptTimeout() > 0 ? $gathering->getAcceptTimeout() .
                        " hour(s) after the event starts." : //TODO(Ryan <vitineth@gmail.com>): Convert this to a function so it isn' as messy.
                        ($gathering->getAcceptTimeout() * -1) . " hour(s) before the event.")), $data);
        $data = str_replace("%USER_DATA%", generateUserData($gathering, $userDataTemplate), $data);
        $data = str_replace("%EVENT_ID%", $gathering->getId(), $data);
        if(!$gathering->getActive()){
            $data = str_replace("%DIS%", "(CANCELLED)", $data);
            $data = str_replace("%DIS_CLASS%", "class=\"gathering-non-active\"", $data);
            $data = str_replace("%DIS_BOOL%", "disabled", $data);
        }else{
            $data = str_replace("%DIS%", "", $data);
            $data = str_replace("%DIS_CLASS%", "", $data);
            $data = str_replace("%DIS_BOOL%", "", $data);
        }
        $data = checkLoginData($gathering, $data, $userLoggedIn, $userId);
        return $data;
    }


    /**
     *
     * This function will take a gathering, template and the current user details and either enable or disable the RSVP
     * buttons depending on whether they are logged in and already [not] attending.
     *
     * <hr>
     *
     * Notes:
     *
     * <h1>[1]: Why we pass the user info like this:</h1>
     * <p> Like the first one we do this because we cannot access this files variables from the function. Silly PHP.
     * Instead of making a request for the same user over and over again every time a gathering was generated from the
     * database, we can grab the user id and template once and just pass it each time. The reason we do the template
     * like this is because it would be inefficient to close and reopen a file many times just for new gatherings.
     *
     * <hr>
     *
     * @param Gathering $gathering  The gathering object from which to get the list of attendees and absentees.
     *
     * @param string $data          The current state of the data template so we can insert in whether buttons are
     *                              disabled directly..
     *
     * @param bool $userLoggedIn    Whether the user is currently logged into the system. We need this to handle
     *                              the RSVP buttons. See <strong><i>Note[2]</i></strong>.
     *
     * @param int $userId           The currently logged in users ID. If they are not logged in then -1 is passed.
     *                              See <strong><i>Note[2]</i></strong>.
     *
     * @return string               string - The data template that was passed with the RSVP buttons either enabled
     *                              (blank strings) or disabled.
     */
    function checkLoginData($gathering, $data, $userLoggedIn, $userId){
        if($userLoggedIn){
            if(in_array($userId, explode(",", $gathering->getAttending())) ||
                in_array($userId, explode(",", $gathering->getNotAttending()))){
                $data = str_replace("%DIS%", "disabled", $data);
                return $data;
            }
        }
        $data = str_replace("%DIS%", "", $data);
        return $data;
    }

    /**
     * This function will take a user and the default template and format it into valid HTML. All strings that will be
     * inserted should have had {@link htmlentities()} run on them when they were created from the database in the
     * {@link User} class. That should stop HTML/JS injection from the database.
     *
     * <hr>
     *
     * Notes:
     * <h1>[1]: Why we do it this way:</h1>
     * <p>We need to pass the template to it because, as far as I know, this function cannot access the variables
     * declared outside of it. We could open the file inside the method and eliminate the template passing; however,
     * if we decided to do that, we would have to open the file for reading, get it's size, read the file and close
     * the file for every call of the method. If the database had many records this would become very inefficient very
     * fast. This way we only need to read the file once and just pass the template each time.
     *
     * <hr>
     *
     * @param User $user        This user object from which to get the details.
     *
     * @param string $template  The user template. Should always be $userDataTemplate. See <strong><i>Note[1]</i>
     *                          </strong>
     *
     * @returns string string - The formatted user template as valid HTML.
     */
    function generateUserTemplate($user, $template){
        $data = str_replace("%IMAGE%", $user->getRealUserProfile(), $template);
        $data = str_replace("%USERNAME%", $user->getNameFirst(), $data);
        $data = str_replace("%USER_ADDRESS%", "../user/user.php?name=" . $user->getUsername(), $data);
        return $data;
    }

    /**
     * This function will take a {@link Gathering} object and the user template and create the attendee user list and
     * then return it as HTML.
     *
     * <hr>
     *
     * Notes:
     * <h1>[1]: Why we do it this way:</h1>
     * <p>We need to pass the template to it because, as far as I know, this function cannot access the variables
     * declared outside of it. We could open the file inside the method and eliminate the template passing; however,
     * if we decided to do that, we would have to open the file for reading, get it's size, read the file and close
     * the file for every call of the method. If the database had many records this would become very inefficient very
     * fast. This way we only need to read the file once and just pass the template each time.
     *
     * <hr>
     *
     * @param Gathering $gathering  The gathering object from which to retrieve the attendee user list.
     *
     * @param string $template      The user data template. Should always be $userDataTemplate. See <strong><i>Note[1]</i>
     *                              </strong>
     *
     * @returns string string - The attending user list as valid HTML.
     */
    function generateUserData($gathering, $template){
        $data = "";
        foreach ($gathering->getAttendingUserList() as $user) {
            $data = $data . generateUserTemplate($user, $template);
        }
        return $data;
    }

    ?>

    <div id="home-page-container" class="row">
        <?php
        //If we were redirected here by an error.
        if(isset($_SESSION['error'])){
            //Then echo out the error string with the error message patched in.
            echo "<div class=\"ui-widget\">";
            echo "    <div class=\"ui-state-error ui-corner-all\">";
            echo "        <p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span>";
            echo "        <strong>Alert:</strong> " . $_SESSION['error'] . "</p>";
            echo "    </div>";
            echo "</div>";

            //And then unset the error message so it won't be seen again.
            $_SESSION['error'] = null;
        }
        ?>
        <div class="col-md-4 col-md-push-4"> <!-- SECOND COLUMN -->

            <?php

            //If the list of gatherings is not null (It might happen if there was an error).
            if($gatherings != null) {
                //If the number of gatherings is greater than one (2 or more) then continue. We have to do this because
                //this is the second column. We only want to show every third record starting from number 2.
                if (count($gatherings) > 1) {
                    //For every third record ($i += 3) from 1 to the last applicable record
                    for ($i = 1; $i < count($gatherings); $i += 3) {
                        //Store it
                        $currentGathering = $gatherings[$i];
                        //And echo the generated template.
                        echo generateTemplate($currentGathering, $sectionTemplate, $userTemplate, $userLoggedIn,
                            $userId);
                    }
                }
            }

            ?>



        </div>

        <div class="col-md-4 col-md-push-4"> <!-- THIRD COLUMN -->
            <?php

            //If the list of gatherings is not null (It might happen if there was an error).
            if($gatherings != null) {
                //If the number of gatherings is greater than two (3 or more) then continue. We have to do this because
                //this is the third column. We only want to show every third record starting from number 3.
                if (count($gatherings) > 2) {
                    //For every third record ($i += 3) from 2 to the last applicable record
                    for ($i = 2; $i < count($gatherings); $i += 3) {
                        //Store it
                        $currentGathering = $gatherings[$i];
                        //And echo the generated template.
                        echo generateTemplate($currentGathering, $sectionTemplate, $userTemplate, $userLoggedIn,
                            $userId);
                    }
                }
            }

            ?>
        </div>

        <div class="col-md-4 col-md-pull-8"> <!-- FIRST COLUMN -->
            <?php

            //If the list of gatherings is not null (It might happen if there was an error).
            if($gatherings != null) {
                //If the number of gatherings is greater than zero (1 or more) then continue. We have to do this because
                //this is the first column. We only want to show every third record starting from number 1.
                if (count($gatherings) > 0) {
                    //For every third record ($i += 3) from 0 to the last applicable record
                    for ($i = 0; $i < count($gatherings); $i += 3) {
                        //Store it
                        $currentGathering = $gatherings[$i];
                        //And echo the generated template.
                        echo generateTemplate($currentGathering, $sectionTemplate, $userTemplate, $userLoggedIn,
                            $userId);
                    }
                }
            }

            ?>
        </div>
    </div>
    <footer id="ft">
        <p>© Cheltenham Hackspace 2014</p>
        <a href="mailto:michaelkent.theellipsis@googlemail.com">Website developed by Michael Kent</a>
    </footer>
</div>
<script rel="script" type="text/javascript" src="../../js/logout.js"></script>
</body>
</html>