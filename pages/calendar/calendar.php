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

    <script src="../../res/libraries/jquery-ui-1.11.4.custom/jquery-ui.min.js" rel="script" type="text/javascript"></script>
    <link href="../../res/libraries/jquery-ui-1.11.4.custom/jquery-ui.min.css" rel="stylesheet" type="text/css">

    <script src="../../js/bootstrap.min.js" rel="script" type="text/javascript"></script>
    <link href="../../css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <link href="../../css/style.css" rel="stylesheet" type="text/css">
    <link href="../../css/pages/gathering/edit/create.css" rel="stylesheet" type="text/css">

    <link rel="stylesheet" type="text/css" href="../../res/libraries/fullcalendar-2.3.1/fullcalendar.css">
    <script rel="script" type="text/javascript" src="../../res/libraries/fullcalendar-2.3.1/lib/moment.min.js"></script>
    <script rel="script" type="text/javascript" src="../../res/libraries/fullcalendar-2.3.1/fullcalendar.min.js"></script>
    <script rel="script" type="text/javascript" src="../../res/libraries/fullcalendar-2.3.1/gcal.js"></script>
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
            <a class="navbar-brand" href="http://cheltenhamhackspace.org/#" style="color:#000000;">
                &nbsp;Cheltenham Hackspace
            </a>
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
                <h3>Gathering Calendar</h3>
                <p>Click on an event to go to it's page. All recurring events will link to the same page.</p>
                <div id="calendar"></div>
            </section>
        </div>
        <footer id="ft">
            <p>© Cheltenham Hackspace 2014</p>
            <a href="mailto:michaelkent.theellipsis@googlemail.com">Website developed by Michael Kent</a>
        </footer>
    </div>
</div>
</body>
<script>

    $(document).ready(function(){
        $("#calendar").fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,basicWeek,basicDay'
            },
            editable: false,
            eventLimit: true,
            events: [
                <?php
                    include_once("../../php/calendar/CalendarUtils.php");

                    echo getCalendarEventsString(2);
                ?>
            ]
        })
    });

</script>
</html>
