<!DOCTYPE html>
<!-- saved from url=(0031)http://cheltenhamhackspace.org/ -->
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="../../../res/images/base/logos/favicon.ico" type="image/x-icon">

    <title>
        Hackspace Cheltenham
    </title>

    <script src="../../../js/jquery-1.11.3.min.js" rel="script" type="text/javascript"></script>
    <script src="../../../js/bootstrap.min.js" rel="script" type="text/javascript"></script>
    <link href="../../../css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../../../res/libraries/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="../../../css/style.css" rel="stylesheet" type="text/css">
    <link href="../../../css/pages/gathering/view/view.css" rel="stylesheet" type="text/css">
    <link href="../../../css/pages/gathering/gathering.base.css" rel="stylesheet" type="text/css">
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
                <img alt="logo" src="../../../res/images/base/logos/logo.png" class="main-logo">
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
                            <a href="../../gathering/gathering.php">Browse</a>
                        </li>
                        <li>
                            <a href="../../calendar/calendar.php">Calendar</a>
                        </li>
                        <li class="divider"></li>
                        <?php
                        {
                            session_start();
                            if (isset($_SESSION['user-id']) && isset($_SESSION['agent']) && isset($_SESSION['count']) &&
                                isset($_SESSION['user']) && isset($_SESSION['admin'])) {
                                echo "<li><a href='#' id='logout'>Logout</a></li>";
                                echo "<li><a href='../../gathering/edit/create.php'>Create</a></li>";
                                if($_SESSION['admin'])echo "<li class='divider'></li><li><a href='../../../admin/pages/index.php'>Admin
                                Console</a></li>";
                            }else{
                                echo "<li><a href='../../login/login.php'>Login</a></li>";
                                echo "<li><a href='../../user/edit/create.php'>Register</a></li>";
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
    <div id="home-page-container" class="row">
        <div class="col-lg-12">
                <?php
                if(!isset($_GET['id'])){
                    printError("No gathering id supplied");
                    return;
                }

                include_once("../../../php/mysql/GatheringMySQLConnection.php");
                include_once("../../../php/variables/Gathering.php");
                include_once("../../../php/variables/User.php");
                include_once("../../../php/variables/Permission.php");
                include_once("../../../php/security/SecurityUtils.php");
                include_once("../../../res/libraries/Michelf/Markdown.inc.php");

                $mysqlConnection = new GatheringMySQLConnection("vit-mysql.ddns.net", "chelthacktesting", "remote_admin", "S*@qEnl6k2HpoVvyRqYeNA@4Tp8TXm");
                $mysqlConnection->connect();

                $queryRequest =Gathering::doesGatheringExist($_GET['id'], $mysqlConnection);

                if(!$queryRequest[0]){
                    printError("There was an error getting the gathering ([" . $gatheringRequest[1] . "]: " . $gatheringRequest[2] . "). Please try again later. If the error persists please contact an admin.");
                    return;
                }

                if(!$queryRequest[3]){
                    printError("Unknown gathering ID: " . $_GET['id']);
                    return;
                }

                $gatheringRequest = Gathering::createFromID($_GET['id'], $mysqlConnection);

                if(!$gatheringRequest[0]){
                    printError("There was an error getting the gathering ([" . $gatheringRequest[1] . "]: " . $gatheringRequest[2] . "). Please try again later. If the error persists please contact an admin.");
                    return;
                }

                /** @var Gathering $gathering */
                $gathering = $gatheringRequest[3];

                $templatePath = "../../../res/templates/gatherings/view_template.txt";
                $templateFile = fopen($templatePath, "r");
                $templateData = fread($templateFile, filesize($templatePath));

                $userName = "../../../res/templates/gatherings/user/user_template.txt";
                $userFile = fopen($userName, "r");
                $userData = fread($userFile, filesize($userName));

                $data = str_replace("%NAME%", $gathering->getName(), $templateData);
                $data = str_replace("%LOCATION%", getLocation($gathering), $data);
                $data = str_replace("%DATE%", date("l jS \of F Y", $gathering->getOccurring()), $data);
                $data = str_replace("%TIME%", date("h:i A", $gathering->getOccurring()), $data);
                $data = str_replace("%DESC%", $gathering->getDescription(), $data);
                $data = str_replace("%ATTENDEE_DATA%", generateUserData($gathering, $userData, true), $data);
                $data = str_replace("%NOT_ATTENDEE_DATA%", generateUserData($gathering, $userData, false), $data);
                $data = str_replace("%ORG_IMAGE%", $gathering->getCreatedByUser()->getRealUserProfile(), $data);
                $data = str_replace("%ORG%", $gathering->getCreatedByUser()->getUsername(), $data);
                $data = str_replace("%ORG_NAME%", $gathering->getCreatedByUser()->getNameFirst(), $data);

                if(!$gathering->getActive()){
                    $data = str_replace("%DIS%", "(CANCELLED)", $data);
                    $data = str_replace("%DIS_CLASS%", "class=\"gathering-non-active\"", $data);
                }else{
                    $data = str_replace("%DIS%", "", $data);
                    $data = str_replace("%DIS_CLASS%", "", $data);
                }

                echo $data;

                /**
                 * @param Gathering $gathering
                 * @return string
                 */
                function getLocation($gathering){
                    $locationString = $gathering->getLocationAddress();
                    if($gathering->getLocationLatitude() != null) $locationString = $locationString . " [" . $gathering->getLocationLatitude();
                    if($gathering->getLocationLongitude() != null) $locationString = $locationString . ", " . $gathering->getLocationLongitude();
                    if($gathering->getLocationLatitude() != null) $locationString = $locationString . "]";

                    return $locationString;
                }

                /**
                 * @param Gathering $gathering
                 * @param string $template
                 * @param bool $attending
                 * @returns string
                 */
                function generateUserData($gathering, $template, $attending){
                    $data = "";
                    if($attending) {
                        foreach ($gathering->getAttendingUserList() as $user) {
                            $data = $data . generateUserTemplate($user, $template);
                        }
                    }else{
                        foreach ($gathering->getNotAttendingUserList() as $user) {
                            $data = $data . generateUserTemplate($user, $template);
                        }
                    }
                    return $data;
                }

                /**
                 * @param User $user The user from which to alter the template
                 * @param string $template
                 * @returns string
                 */
                function generateUserTemplate($user, $template){
                    $data = str_replace("%IMAGE%", $user->getRealUserProfile(), $template);
                    $data = str_replace("%USERNAME%", $user->getNameFirst(), $data);
                    $data = str_replace("%USER_ADDRESS%", "../user/user.php?name=" . $user->getUsername(), $data);
                    return $data;
                }

                function printError($message){
                    echo "<section>";
                    echo "<h3>Gathering</h3>";
                    echo "<p>" . $message . "</p>";
                    echo "</section>";
                }
                ?>
        </div>
    </div>
    <footer id="ft" style="margin-top: 100px">
        <p>© Cheltenham Hackspace 2014</p>
        <a href="mailto:michaelkent.theellipsis@googlemail.com">Website developed by Michael Kent</a>
    </footer>
</div>
<script rel="script" type="text/javascript" src="../../../js/logout.js"></script>
</body>
</html>