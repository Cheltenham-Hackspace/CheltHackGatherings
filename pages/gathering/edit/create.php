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
    <link href="../../../res/libraries/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <script src="../../../js/jquery-1.11.3.min.js" rel="script" type="text/javascript"></script>

    <script src="../../../res/libraries/jquery-ui-1.11.4.custom/jquery-ui.min.js" rel="script" type="text/javascript"></script>
    <link href="../../../res/libraries/jquery-ui-1.11.4.custom/jquery-ui.min.css" rel="stylesheet" type="text/css">

    <script src="../../../js/bootstrap.min.js" rel="script" type="text/javascript"></script>
    <link href="../../../css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <link href="../../../css/style.css" rel="stylesheet" type="text/css">
    <link href="../../../css/pages/gathering/edit/create.css" rel="stylesheet" type="text/css">
    <style type="text/css"></style>
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
            <a id="logo" class="pull-left" href=""><img alt="logo" class="main-logo" src="../../../res/images/base/logos/logo.png"></a>
            <a class="navbar-brand" href="http://cheltenhamhackspace.org/#" style="color:#000000;">&nbsp;Cheltenham
                Hackspace</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false" style="height: 1px;">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="http://cheltenhamhackspace.org/#">Home</a></li>
                <li><a href="http://www.cheltenhamhackspace.org/wiki" target="_blank">Wiki</a></li>
                <li><a href="http://groups.google.com/forum/?hl=en#!forum/cheltenham_hackspace"
                       target="_blank">Forum</a></li>
                <li><a href="http://www.meetup.com/Cheltenham-Hackspace" target="_blank">Events</a></li>
                <li><a href="http://www.cheltenhamhackspace.org/wiki/Category:Members" target="_blank">Organisation</a>
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
            <section>
                <?php

                include_once("../../../php/variables/User.php");
                include_once("../../../php/mysql/MySQLConnection.php");
                include_once("../../../php/security/SecurityUtils.php");
                include_once("../../../res/libraries/Michelf/Markdown.inc.php");

                if(!isset($_SESSION['user'])){
                    redirect("You must be logged in to create a gathering!");
                    return;
                }

                if(($_SESSION['count'] -= 1) == 0) {
                    session_regenerate_id();
                    $_SESSION['count'] = 5;
                }

                $mysqlConnection = new MySQLConnection("vit-mysql.ddns.net", "chelthacktesting", "remote_admin",
                                                       "S*@qEnl6k2HpoVvyRqYeNA@4Tp8TXm");
                $connectionResult = $mysqlConnection->connect();

                if(!$connectionResult[0]){
                    if($connectionResult[1] == $mysqlConnection->getErrorCodes()["ERR_TIME_OUT"]) {
                        redirect("The connection to the database timed out! Please try again later.", "../../.
                        ./pages/gathering/gathering.php");
                    }
                }

                $userIdRequest = User::getUserID($_SESSION['user'], $mysqlConnection);
                if(!$userIdRequest[0]){
                    redirect("Error: [" . $userIdRequest[1] . "]: " . $userIdRequest[2] . ". Try again later. If this
                     problem persists please let one of the admin staff know.", "../../../pages/gathering/edit/create
                     .php");
                    return;
                }

                $userID = $userIdRequest[3];
                $userRequest = User::createFromID($userID, $mysqlConnection);

                if(!$userRequest[0]){
                    redirect("Error: [" . $userRequest[1] . "]: " . $userRequest[2] . ". Try again later. If this
                    problem persists please let one of the admin staff know.", "../../../pages/gathering/edit/create
                    .php");
                    return;
                }
                /**@var User $user*/
                $user = $userRequest[3];

                $admin = Permission::getDefaultPermissions()['PERM_ALL_ADMIN'];
                $create = Permission::getDefaultPermissions()['PERM_GATHERING_CREATE'];
                $modify = Permission::getDefaultPermissions()['PERM_GATHERING_MODIFY_ALL'];
                if(!in_array($admin, $user->getPermissionsArray()) &&
                    !in_array($create, $user->getPermissionsArray()) &&
                    !in_array($modify, $user->getPermissionsArray())){
                    redirect("Error: You don't have the necessary permissions to create a gathering.
                     If you feel this is in error contact an admin.", "../../../pages/gathering/gathering.php");
                }

                function redirect($error, $address = "../../../pages/login/login.php", $replace = true, $code = 303){
                    $_SESSION['error'] = $error;
                    header('Location: ' . $address, $replace, $code);
                }
                ?>
                <h3>Create gathering</h3>
                <form method="post" action="../../../php/gatherings/edit/create.php" id="createGathering" onsubmit="updateOccurring()">
                    <div class="form-group">
                        <label for="gatheringName">Name: </label>
                        <input id="gatheringName" name="gatheringName" type="text" placeholder="Gathering name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="gatheringDescription">Description: (Markdown Supported)</label>
                        <textarea id="gatheringDescription" name="gatheringDescription" placeholder="Gathering description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="gatheringCreated">Created: </label>
                        <input id="gatheringCreated" name="gatheringCreated" disabled class="form-control" type="text" <?php
                        echo "value=\"" . date("d/m/Y h:i:s a") . "\"";
                        ?>>
                        <input id="gatheringCreatedReal" name="gatheringCreatedReal" class="form-control" type="hidden" <?php
                        echo "value=\"" . time() . "\"";
                        ?>>
                    </div>
                    <div class="form-group">
                        <label for="gatheringOccurring">Occurring: </label>
                        <input id="gatheringOccurring" type="text" name="gatheringOccurring" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="gatheringTime">Time: </label>
                        <input type="text" name="gatheringTime" id="gatheringTime" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="gatheringTimeout">RSVP Timeout: </label>
                        <p class="form-inline form-help">When RSVPs should stop being accepted (In hours. -1 for 1 hour before)</p>
                        <input type="text" name="gatheringTimeout" id="gatheringTimeout" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="gatheringLocationAddress">Address: </label>
                        <input type="text" name="gatheringLocationAddress" id="gatheringLocationAddress" class="form-control">
                    </div>
                    <div class="form-group">
                        <div class="form-group">
                            <label for="gatheringLocationLatitude">Latitude: (Optional)</label>
                            <input id="gatheringLocationLatitude" name="gatheringLocationLatitude" type="text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="gatheringLocationLongitude">Longitude: (Optional)</label>
                            <input id="gatheringLocationLongitude" name="gatheringLocationLongitude" type="text" class="form-control">
                        </div>
                    </div>
                    <button id="submitButton" class="btn btn-block btn-success form-control" type="submit">Submit</button>
                </form>
            </section>
    </div>
    <footer id="ft">
        <p>© Cheltenham Hackspace 2014</p>
        <a href="mailto:michaelkent.theellipsis@googlemail.com">Website developed by Michael Kent</a>
    </footer>
</div>

<script>
    var datePicker = $("#gatheringOccurring");
    datePicker.datepicker();
    datePicker.datepicker("option", "dateFormat", "dd/mm/yy");
    var name = false, description= false, time= false, timeout= false, address = false;
    $("#gatheringName").keyup(function(){
        name = !!$(this).val();
        checkButton();
    });
    $("#gatheringDescription").keyup(function(){
        description = !!$(this).val();
        checkButton();
    });
    $("#gatheringTime").keyup(function(){
        time = !!$(this).val();
        checkButton();
    });
    $("#gatheringTimeout").keyup(function(){
        timeout = !!$(this).val();
        checkButton();
    });
    $("#gatheringLocationAddress").keyup(function(){
        address = !!$(this).val();
        checkButton();
    });
    function checkButton(){
        console.log(name);
        console.log(description);
        console.log(time);
        console.log(timeout);
        console.log(address);
        $("#submitButton").prop("disabled", !(name && description && time && timeout && address));
    }

    function updateOccurring(){
        var calendar = $("#gatheringCalender");
        console.log(calendar.datepicker("getDate"));
        $("#gatheringOccurringReal").value = calendar.datepicker("getDate");
    }
</script>
    <script rel="script" type="text/javascript" src="../../../js/logout.js"></script>
</div>
</body>
</html>