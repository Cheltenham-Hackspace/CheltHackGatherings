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

    <script src="../../js/jquery-1.11.3.min.js" rel="script" type="text/javascript"></script>
    <script src="../../js/bootstrap.min.js" rel="script" type="text/javascript"></script>
    <link href="../../css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <link href="../../res/libraries/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <link href="../../css/style.css" rel="stylesheet" type="text/css">
    <link href="../../css/pages/user/user.css" rel="stylesheet" type="text/css">
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
            <a id="logo" class="pull-left" href="http://cheltenhamhackspace.org/">
                <img alt="logo" src="../../res/images/base/logos/logo.png" class="main-logo">
            </a>
            <a class="navbar-brand" href="http://cheltenhamhackspace.org/#" style="color:#000000;">
                &nbsp;Cheltenham
                Hackspace
            </a>
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
    <div id="home-page-container" class="row">
        <div class="col-lg-12">
            <section>
                <?php
                include_once("../../php/mysql/MySQLConnection.php");
                include_once("../../php/variables/User.php");
                include_once("../../php/security/SecurityUtils.php");
                include_once("../../res/libraries/Michelf/Markdown.inc.php");

                if(!isset($_GET['name'])){
                    echo "<h3>User profile</h3>";
                    echo "<p>No user supplied</p>";
                    return;
                }

                $mysqlConnection = MySQLConnection::createDefault("../../");

                $connectionResult = $mysqlConnection->connect();

                if(!$connectionResult[0]){
                    if($connectionResult[1] == $mysqlConnection->getErrorCodes()["ERR_TIME_OUT"]) {
                        echo "<div class=\"ui-widget\">";
                        echo "    <div class=\"ui-state-error ui-corner-all\">";
                        echo "        <p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right:
                        .3em;\"></span>";
                        echo "        <strong>Alert:</strong> " . "The connection to the database server timed out.
                        Please try again later." . "</p>";
                        echo "    </div>";
                        echo "</div>";
                    }
                }

                $userIdRequest = User::getUserID($_GET['name'], $mysqlConnection);
                
                if(!$userIdRequest[0]){
                    echo "<h3>User profile</h3>";
                    echo "<p>Unknown user</p>";
                    return;
                }
                
                $id = $userIdRequest[3];
                $user = getUser($id, $mysqlConnection);
                $templates = getTemplates();

                $profileData = $templates[3];
                $profileData = str_replace("%NAME%", $user->getUsername(), $profileData);
                
                if($user->isPrivateFullName()) {
                    if (strlen($user->getNameFirst()) > 0){
                        $profileData = str_replace("%FULL-NAME%", $user->getNameFirst() . " ." .
                            substr($user->getNameLast(), 0, 1), $profileData);
                    }else{
                        $profileData = str_replace("%FULL-NAME%", $user->getNameFirst(), $profileData);
                    }
                }else{
                    if (strlen($user->getNameMiddle()) > 0) {
                        $profileData = str_replace("%FULL-NAME%", $user->getNameFirst() . " " .
                            substr($user->getNameMiddle(), 0, 1) . ". " . $user->getNameLast(), $profileData);
                    }else{
                        $profileData = str_replace("%FULL-NAME%", $user->getNameFirst() . " " .
                            $user->getNameLast(), $profileData);
                    }
                }
                
                $profileData = str_replace("%HEADER%", $user->getRealUserHeader(), $profileData);
                $profileData = str_replace("%PROFILE%", $user->getRealUserProfile(), $profileData);
                
                $descriptionData = "";
                
                if($user->getDescriptionPersonal() != null){
                    $personalDesc = str_replace("%QUESTION%", "Who am I?", $templates[2]);
                    $personalDesc = str_replace("%DESC%", $user->getDescriptionPersonal(), $personalDesc);
                    $descriptionData = $descriptionData . $personalDesc;
                }

                if($user->getDescriptionUse() != null){
                    $useDesc = str_replace("%QUESTION%", "What would I use the space for?", $templates[2]);
                    $useDesc = str_replace("%DESC%", $user->getDescriptionUse(), $useDesc);
                    $descriptionData = $descriptionData . $useDesc;
                }

                if($user->getDescriptionOffer() != null){
                    $offerDesc = str_replace("%QUESTION%", "What can I offer to the space?", $templates[2]);
                    $offerDesc = str_replace("%DESC%", $user->getDescriptionOffer(), $offerDesc);
                    $descriptionData = $descriptionData . $offerDesc;
                }

                $profileData = str_replace("%DESCRIPTIONS%", $descriptionData, $profileData);

                $contactData = $user->isPrivatePhone() && $user->isPrivateEmail() && $user->isPrivateAddress() ? ""
                    : $templates[0];
                $contactElements = "";

                if(!$user->isPrivateEmail()){
                    $offerDesc = str_replace("%TYPE%", "Email", $templates[1]);
                    $offerDesc = str_replace("%DATA%", $user->getDecryptedEmail(), $offerDesc);
                    $contactElements = $contactElements . $offerDesc;
                }

                if(!$user->isPrivatePhone()){
                    $offerDesc = str_replace("%TYPE%", "Telephone", $templates[1]);
                    $offerDesc = str_replace("%DATA%", $user->getDecryptedTelephone(), $offerDesc);
                    $contactElements = $contactElements . $offerDesc;
                }

                if(!$user->isPrivateAddress()){
                    $offerDesc = str_replace("%TYPE%", "Address", $templates[1]);
                    $offerDesc = str_replace("%DATA%", $user->getDecryptedAddress(), $offerDesc);
                    $contactElements = $contactElements . $offerDesc;
                }

                $contactData = str_replace("%DATA%", $contactElements, $contactData);
                $profileData = str_replace("%CONTACT%", $contactData, $profileData);

                echo $profileData;

                /**
                 * @param int $id
                 * @param MySQLConnection $mysqlConnection
                 * @return User
                 */
                function getUser($id, $mysqlConnection){
                    $userRequest = User::createFromID($id, $mysqlConnection);

                    if(!$userRequest[0]){
                        echo "<h3>User profile</h3>";
                        echo "<p>There was an unknown error. Please try again later</p>";
                    }

                    return $userRequest[3];
                }
                    
                function getTemplates(){
                    $contactTemplate = "../../res/templates/user/data/contact_template.txt";
                    $dataTemplate = "../../res/templates/user/data/data_template.txt";
                    $descriptionTemplate = "../../res/templates/user/description/description_template.txt";
                    $userProfileTemplate = "../../res/templates/user/user_profile.txt";
                    
                    $contactFile = fopen($contactTemplate, "r");
                    $dataFile = fopen($dataTemplate, "r");
                    $descriptionFile = fopen($descriptionTemplate, "r");
                    $userProfileFile = fopen($userProfileTemplate, "r");
                    
                    $contactData = fread($contactFile, filesize($contactTemplate));
                    $dataData = fread($dataFile, filesize($dataTemplate));
                    $descriptionData = fread($descriptionFile, filesize($descriptionTemplate));
                    $userProfileData = fread($userProfileFile, filesize($userProfileTemplate));
                    
                    return array($contactData, $dataData, $descriptionData, $userProfileData);
                }
                ?>

            </section>
        </div>
    </div>
    <footer id="ft" style="margin-top: 100px">
        <p>© Cheltenham Hackspace 2014</p>
        <a href="mailto:michaelkent.theellipsis@googlemail.com">Website developed by Michael Kent</a>
    </footer>
</div>
<script rel="script" type="text/javascript" src="../../js/logout.js"></script>
</body>
</html>