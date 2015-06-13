<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Cheltenham Hackspace > Admin Console > Dashboard</title>

    <!-- Bootstrap Core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

    <!-- Timeline CSS -->
    <link href="../dist/css/timeline.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../../res/libraries/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="../../res/libraries/fontawesome-iconpicker-1.0.0/dist/css/fontawesome-iconpicker.min.css" rel="stylesheet" type="text/css">
    <link href="../../res/libraries/jquery-ui-1.11.4.custom/jquery-ui.min.css" rel="stylesheet">
    <link href="../../css/admin/index.css" rel="stylesheet" type="text/css">

    <style>
        span.mandatory{
            color: #8c0000;
        }
    </style>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<?php
include_once("../../php/mysql/GatheringMySQLConnection.php");
include_once("../../php/variables/Gathering.php");
include_once("../../php/variables/User.php");
include_once("../../php/security/SecurityUtils.php");
include_once("../../res/libraries/Michelf/Markdown.inc.php");

session_start();

if(!isset($_SESSION['user-id']) && !isset($_SESSION['agent']) && !isset($_SESSION['count']) && !isset
    ($_SESSION['user'])){

    redirect("You must be logged in to access this page.", "../../pages/gathering/gathering.php");
    return;
}

//Connect to the database
$mysqlConnection = GatheringMySQLConnection::createDefault("../../");
$connectionResult = $mysqlConnection->connect();

if (!$connectionResult[0]) {
    echo "<div class=\"ui-widget\">";
    echo "    <div class=\"ui-state-error ui-corner-all\">";
    echo "        <p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right:
                        .3em;\"></span>";
    echo "        <strong>Alert:</strong> [" . $connectionResult[1] . "]: " . $connectionResult[2] . "</p>";
    echo "    </div>";
    echo "</div>";
    return;
}

$userRequest = User::createFromID($_SESSION['user-id'], $mysqlConnection);
if(!$userRequest[0]){
    redirect("There was an error retrieving your user. Please try again later.", "../../pages/gathering/gathering.php");
    return;
}

$user = $userRequest[3];

if(!SecurityUtils::userHavePermission($user, Permission::getDefaultPermissions()["PERM_ALL_ADMIN"])){
    redirect("You are not an admin! Please contact an authorized admin if you feel this is in error..", "../.
    ./pages/gathering/gathering.php");
    return;
}

function redirect($error, $address = "../../pages/login/login.php", $replace = true, $code = 303){
    if($error != null) $_SESSION['error'] = $error;
    header('Location: ' . $address, $replace, $code);
}

?>
<body>

<div id="wrapper">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0; border-color:
    #F9D345; border-width: 0 0 3px;">
        <div class="container-fluid">
            <div class="navbar-header">
                <a id="logo" class="pull-left" href=""><img alt="logo" src="../../res/images/base/logos/logo.png" 
                                                            style="width: 50px; height: 50px;"></a>
                <a class="navbar-brand" href="http://cheltenhamhackspace.org/#" style="color:#000000;">
                    &nbsp;
                    <span id="colapse-span-1">Cheltenham Hackspace</span> <strong>&gt;</strong>
                    <span id="colapse-span-2">Admin Console</span> <strong>&gt;</strong>
                    <span>Overview</span></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse" aria-expanded="false" style="height: 1px;">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="http://cheltenhamhackspace.org/#">Home</a></li>
                    <li><a href="http://www.cheltenhamhackspace.org/wiki" target="_blank">Wiki</a></li>
                    <li><a href="http://groups.google.com/forum/?hl=en#!forum/cheltenham_hackspace"
                           target="_blank">Forum</a></li>
                    <li><a href="http://www.meetup.com/Cheltenham-Hackspace" target="_blank">Events</a></li>
                    <li><a href="http://www.cheltenhamhackspace.org/wiki/Category:Members"
                           target="_blank">Organisation</a>
                    </li>
                    <!--Dropdown menu for the gatherings-->
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            Gatherings
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="../../pages/gathering/gathering.php">Browse</a>
                            </li>

                            <li>
                                <a href="../../pages/login/login.php">Login</a>
                            </li>

                            <li>
                                <a href="../../pages/gathering/edit/create.php">Create</a>
                            </li>
                            <li>
                                <a href="../../pages/calendar/calendar.php">Calendar</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse" style="margin-top: -48px;">
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="index.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="permissions.php"><i class="fa fa-lock fa-fw"></i> Permissions</a>
                    </li>
                    <li>
                        <a href="users.php"><i class="fa fa-user fa-fw"></i> Users</a>
                    </li>
                    <li>
                        <a href="gatherings.php"><i class="fa fa-users fa-fw"></i> Gatherings</a>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-bell-o fa-fw"></i> Events<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="events.php"><i class="fa fa-info-circle fa-fw"></i> General Events</a>
                            </li>
                            <li>
                                <a href="timeline.php"><i class="fa fa-circle-o-notch fa-fw"></i> Timeline Events</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    </nav>

    <div id="page-wrapper" style="margin-top: 25px;">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Dashboard</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <div class="row">
            <?php
            if (isset($_SESSION['success'])) {
                echo "<div class=\"ui-widget\">";
                echo "    <div class=\"ui-state-success ui-corner-all\">";
                echo "        <p class='ui-para-text'><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right:
                        .3em;\"></span>";
                echo "        <strong>Success:</strong>" . $_SESSION['success'] . "</p>";
                echo "    </div>";
                echo "</div>";
                $_SESSION['success'] = null;
            }

            if (isset($_SESSION['error'])) {
                echo "<div class=\"ui-widget\">";
                echo "    <div class=\"ui-state-error ui-corner-all\">";
                echo "        <p class='ui-para-text'><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right:
                        .3em;\"></span>";
                echo "        <strong>Alert:</strong>" . $_SESSION['error'] . "</p>";
                echo "    </div>";
                echo "</div>";
                $_SESSION['error'] = null;
            }
            ?>
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-users fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">
                                    <?php
                                    echo count($mysqlConnection->getAllGatherings());
                                    ?>
                                </div>
                                <div>Gatherings!</div>
                            </div>
                        </div>
                    </div>
                    <a href="gatherings.php">
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-green">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-user fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">
                                    <?php
                                    $sql = "SELECT count(*) FROM `users`;";
                                    $stmt = $mysqlConnection->getMysqli()->prepare($sql);

                                    $result = $stmt->execute();
                                    if (!$result) {
                                        echo "&#8734;";
                                        return;
                                    }
                                    $output = $stmt->get_result();
                                    echo $output->fetch_array(MYSQLI_NUM)[0];
                                    ?>
                                </div>
                                <div>Users!</div>
                            </div>
                        </div>
                    </div>
                    <a href="users.php">
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-yellow">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-lock fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">
                                    <?php
                                    $sql = "SELECT count(*) FROM `permissions`;";
                                    $stmt = $mysqlConnection->getMysqli()->prepare($sql);

                                    $result = $stmt->execute();
                                    if (!$result) {
                                        echo "&#8734;";
                                        return;
                                    }
                                    $output = $stmt->get_result();
                                    echo $output->fetch_array(MYSQLI_NUM)[0];
                                    ?>
                                </div>
                                <div>Permissions!</div>
                            </div>
                        </div>
                    </div>
                    <a href="permissions.php">
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-red">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-calendar-o fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">
                                    <?php
                                    include_once("../../php/calendar/CalendarUtils.php");
                                    echo getCalendarEventsCount();
                                    ?>
                                </div>
                                <div>Calendar Events!</div>
                            </div>
                        </div>
                    </div>
                    <a href="https://www.google.com/calendar/embed?src=dTFwbHZycW5pajh2MDY5bTIwcjBub2FudTRAZ3JvdXAuY2FsZW5kYXIuZ29vZ2xlLmNvbQ">
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Users
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <div class="dataTable_wrapper">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Real name</th>
                                    <th>Permissions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM `users`;";
                                $stmt = $mysqlConnection->getMysqli()->prepare($sql);

                                $result = $stmt->execute();
                                if (!$result) {
                                    echo "&#8734;";
                                    return;
                                }
                                $output = $stmt->get_result();

                                $toggle = "even";

                                foreach ($output as $row) {
                                    echo "<tr class=\"" . $toggle . "\">";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . $row['username'] . "</td>";
                                    echo "<td>" . $row['name_first'] . " " . $row['name_middle'] . " " .
                                        $row['name_last'] . "</td>";
                                    echo "<td>" . $row['permissions'] . "</td>";
                                }
                                echo $output->fetch_array(MYSQLI_NUM)[0];
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Permissions
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <div class="dataTable_wrapper">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM `permissions`;";
                                $stmt = $mysqlConnection->getMysqli()->prepare($sql);

                                $result = $stmt->execute();
                                if (!$result) {
                                    echo "&#8734;";
                                    return;
                                }
                                $output = $stmt->get_result();

                                $toggle = "even";

                                foreach ($output as $row) {
                                    echo "<tr class=\"" . $toggle . "\">";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . $row['name'] . "</td>";
                                    echo "<td>" . $row['description'] . "</td>";
                                }
                                echo $output->fetch_array(MYSQLI_NUM)[0];
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-clock-o fa-fw"></i> Responsive Timeline
                        <div class="pull-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle"
                                        data-toggle="dropdown">
                                    Actions
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li>
                                        <a href="#" onclick="triggerModal();">Post new event</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <ul class="timeline">
                            <?php
                            //SELECT all timeline events and order them by their timestamps in descending order.
                            // (Newest first)
                            $stmt = $mysqlConnection->getMysqli()->prepare("SELECT * FROM `timeline` ORDER BY
                            `timestamp` DESC;");
                            //Execute the query and get the result
                            $result = $stmt->execute();
                            //If it failed then just return out. We won't show any events.
                            if (!$result) {
                                return;
                            }

                            //Get the result from the statement and store it.
                            $rows = $stmt->get_result();

                            //Get the path to the timeline template
                            $timelinePath = "../../res/templates/admin/timeline/timeline-event.txt";
                            //And read it in.
                            $timelineData = fread(fopen($timelinePath, "r"), filesize($timelinePath));

                            //For each record in the result
                            foreach ($rows as $record) {
                                //Set output to the timeline data
                                $output = $timelineData;
                                //And replace all the templated data with the correct data.
                                $output = str_replace("{{SIDE}}", $record['side'] == "left" ? "" :
                                    "class=\"timeline-inverted\"", $output);
                                $output = str_replace("{{COLOR}}", $record['color'], $output);
                                $output = str_replace("{{ICON}}", $record['icon'], $output);
                                $output = str_replace("{{TITLE}}", $record['name'], $output);
                                $output = str_replace("{{DESCRIPTION}}", $record['description'], $output);
                                $output = str_replace("{{TIME}}", date("d-m-Y \\a\\t H:i:s", $record['timestamp']),
                                    $output);

                                //Request the author
                                $userRequest = User::createFromID($record['author'], $mysqlConnection);
                                //If it was a success
                                if ($userRequest[0]) {
                                    //Then get the user and tell PhpStorm it's type.
                                    /** @var User $user */
                                    $user = $userRequest[3];
                                    //And replace user with their username
                                    $output = str_replace("{{USER}}", "<a href=\"../../pages/user/user.php?name=" .
                                        $user->getUsername() . "\">" . $user->getUsername() . "</a>",
                                        $output);
                                } else {
                                    //Otherwise set it to 'unkown user'
                                    $output = str_replace("{{USER}}", "Unknown User", $output);
                                }
                                //And finally echo out the template.
                                echo $output;
                            }

                            ?>
                        </ul>
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-8 -->
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bell fa-fw"></i> Notifications Panel
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <div class="list-group">
                            <?php

                            $sql = "SELECT * FROM `events` LIMIT 10;";
                            $stmt = $mysqlConnection->getMysqli()->prepare($sql);

                            $result = $stmt->execute();

                            if (!$result) {
                                return;
                            }

                            $rows = $stmt->get_result();

                            $filePath = "../../res/templates/admin/notifications/notification-main.txt";
                            $fileData = fread(fopen($filePath, "r"), filesize($filePath));

                            foreach ($rows as $row) {
                                $output = $fileData;
                                $output = str_replace("%ICON%", $row['icon'], $output);
                                $output = str_replace("%TITLE%", $row['message'], $output);
                                $output = str_replace("%TIME%", date('m/d/Y H:i:s', $row['timestamp']), $output);
                                echo $output;
                            }


                            ?>
                        </div>
                        <!-- /.list-group -->
                        <a href="#" class="btn btn-default btn-block">View All Alerts</a>
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-4 -->
        </div>
        <!-- /.row -->
        <!-- Modal -->
        <div class="modal fade" id="timelinePostModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="../../php/admin/timeline-submit.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Post new timeline event</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="tln-frm-timestamp">Timestamp: <span class="mandatory">*</span></label>
                                <input class="form-control" readonly id="tln-frm-timestamp"
                                       name="tln-frm-timestamp"
                                    <?php
                                    echo "value=\"" . time() . "\"";
                                    ?>
                                    >
                            </div>
                            <div class="form-group">
                                <label for="tln-frm-name">Name: <span class="mandatory">*</span></label>
                                <input class="form-control" type="text" id="tln-frm-name" name="tln-frm-name">
                            </div>
                            <div class="form-group">
                                <label for="tln-frm-description">Description: <span class="mandatory">*</span></label>
                                <textarea class="form-control" id="tln-frm-description"
                                       name="tln-frm-description" style="resize: vertical;"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="tln-frm-icon">Icon: <span class="mandatory">*</span></label>
                                <input class="form-control" type="text" id="tln-frm-icon" name="tln-frm-icon">
                            </div>
                            <div class="form-group">
                                <label for="tln-frm-color">Color: <span class="mandatory">*</span></label>
                                <input class="form-control" type="text" id="tln-frm-color" name="tln-frm-color">
                            </div>
                            <div class="form-group">
                                <label for="tln-frm-author">Author: <span class="mandatory">*</span></label>
                                <input class="form-control" type="text" readonly id="tln-frm-author"
                                       name="tln-frm-author"
                                    <?php
                                    echo "value=\"" . $_SESSION['user-id'] . "\"";
                                    ?>
                                    >
                            </div>
                            <div class="form-group">
                                <label for="tln-frm-side">Side: <span class="mandatory">*</span></label>
                                <select class="form-control" id="tln-frm-side" name="tln-frm-side">
                                    <option>Left</option>
                                    <option>Right</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-wrapper -->


</div>
<!-- /#wrapper -->

<!-- jQuery -->
<script src="../../js/jquery-1.11.3.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="../bower_components/metisMenu/dist/metisMenu.min.js"></script>

<!-- Morris Charts JavaScript -->
<script src="../bower_components/raphael/raphael-min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="../dist/js/sb-admin-2.js"></script>
<script src="../../res/libraries/fontawesome-iconpicker-1.0.0/dist/js/fontawesome-iconpicker.min.js"></script>

<script>
    function triggerModal() {
        $("#timelinePostModal").modal();
    }

    $(document).ready(function () {
        $("#tln-frm-icon").iconpicker();

        $(document).scroll(function () {
            $('#colapse-span-1').each(function () {
                collapse(50, "Cheltenham Hackspace", "CH", $(this));
            });
            $('#colapse-span-2').each(function () {
                collapse(100, "Admin Console", "AC", $(this));
            });
        });

        /**
         * This function will check whether the current component's offset from the top of the screen is greater than
         * the <code>pos</code> variable. If so it will set the text value of <code>component</code> to the
         * <code>shrink</code> value. Otherwise it will set it to the <code>full</code> value.
         * @param pos int The position the value must be at to shrink.
         * @param full String the value the component will have when above pos
         * @param shrink String the value the component will have when below pos
         * @param component The component to apply the effect to.
         */
        function collapse(pos, full, shrink, component) {
            var componentPos = component.offset().top;

            if (componentPos > pos) {
                component.text(shrink);
            } else {
                component.text(full);
            }
        }
    })
</script>

</body>

</html>
