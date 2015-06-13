<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Cheltenham Hackspace > Admin Console > Permissions</title>

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

if (!isset($_SESSION['user-id']) && !isset($_SESSION['agent']) && !isset($_SESSION['count']) && !isset
    ($_SESSION['user'])
) {

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
if (!$userRequest[0]) {
    redirect("There was an error retrieving your user. Please try again later.", "../../pages/gathering/gathering.php");
    return;
}

$user = $userRequest[3];

if (!SecurityUtils::userHavePermission($user, Permission::getDefaultPermissions()["PERM_ALL_ADMIN"])) {
    redirect("You are not an admin! Please contact an authorized admin if you feel this is in error..", "../.
    ./pages/gathering/gathering.php");
    return;
}

function redirect($error, $address = "../../pages/login/login.php", $replace = true, $code = 303)
{
    if ($error != null) $_SESSION['error'] = $error;
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
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                        aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a id="logo" class="pull-left" href=""><img alt="logo" src="../../res/images/base/logos/logo.png"
                                                            style="width: 50px; height: 50px;"></a>
                <a class="navbar-brand" href="http://cheltenhamhackspace.org/#" style="color:#000000;">
                    &nbsp;
                    <span id="colapse-span-1">Cheltenham Hackspace</span> <strong>&gt;</strong>
                    <span id="colapse-span-2">Admin Console</span> <strong>&gt;</strong>
                    <span>Permissions</span></a>
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
                            <li>
                                <a href="#" id="logout">Logout</a>
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
                <h1 class="page-header">Permissions</h1>
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
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Active Permissions
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
                                    return;
                                }

                                $rows = $stmt->get_result();
                                $toggle = "even";

                                foreach ($rows as $record) {
                                    echo "<tr class=\"" . $toggle . "\">";
                                    echo "<td>" . $record['id'] . "</td>";
                                    echo "<td>" . $record['name'] . "</td>";
                                    echo "<td>" . $record['description'] . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.table-responsive -->
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!--/.row-->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add Permission
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <form method="post" action="../../php/admin/permission-create.php">
                            <div class="form-group">
                                <label for="pm-frm-name">Name: </label>
                                <input class="form-control" type="text" id="pm-frm-name" name="pm-frm-name">
                            </div>
                            <div class="form-group">
                                <label for="pm-frm-description">Name: </label>
                                <textarea class="form-control" id="pm-frm-description"
                                          name="pm-frm-description" style="resize: vertical;"></textarea>
                            </div>
                            <button type="submit" class="form-control btn btn-block btn-primary">Submit</button>
                        </form>
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!--/.row-->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Remove Permission
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <form method="post" action="../../php/admin/permission-remove.php">
                            <div class="form-group">
                                <label for="pm-frm-id">Id: </label>
                                <select class="form-control" id="pm-frm-id" name="pm-frm-id">
                                    <?php
                                    foreach ($rows as $record) {
                                        echo "<option>" . $record['id'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="form-control btn btn-block btn-primary">Submit</button>
                        </form>
                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!--/.row-->
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

<!-- Custom Theme JavaScript -->
<script src="../dist/js/sb-admin-2.js"></script>

<!-- Page-Level Demo Scripts - Tables - Use for reference -->
<script>
    $(document).ready(function () {
        $("#logout").click(function(){
            var jForm = $('<form></form>');
            jForm.attr('action', "../../php/login/logout.php");
            jForm.attr('method', 'post');
            jForm.submit();
        });

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
            console.log(componentPos);

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
