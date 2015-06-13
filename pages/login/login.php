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
    <link href="../../res/libraries/jquery-ui-1.11.4.custom/jquery-ui.min.css" rel="stylesheet">

    <script src="../../js/bootstrap.min.js" rel="script" type="text/javascript"></script>
    <link href="../../css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <link href="../../res/libraries/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <link href="../../css/style.css" rel="stylesheet" type="text/css">
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
            <a id="logo" class="pull-left" href="http://cheltenhamhackspace.org/">
                <img alt="logo" class="main-logo" src="../../res/images/base/logos/logo.png">
            </a>
            <a class="navbar-brand" href="http://cheltenhamhackspace.org/#" style="color:#000000;">&nbsp;Cheltenham Hackspace</a>
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
                <h3>Login</h3>
                <?php
                if(isset($_SESSION['error'])){
                    echo "<div class=\"ui-widget\">";
	                echo "    <div class=\"ui-state-error ui-corner-all\">";
		            echo "        <p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span>";
		            echo "        <strong>Alert:</strong> " . $_SESSION['error'] . "</p>";
                	echo "    </div>";
                    echo "</div>";

                    $_SESSION['error'] = null;
                }
                ?>
                <form class="form-horizontal" method="post" action="../../php/login/login.php">
                    <div class="form-group">
                        <label for="username" class="col-sm-2 control-label">Username</label>
                        <div class="col-sm-9">
                            <input id="username" type="text" class="form-control" name="username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="col-sm-2 control-label">Password</label>
                        <div class="col-sm-9">
                            <input id="password" type="password" class="form-control" name="password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg btn-block" id="loginButton">Login</button>

                </form>
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
<script>
    var usernameContent = false;
    var passwordContent = false;
    $(document).ready(function(){
        $("#username").keyup(function(){
            usernameContent = $(this).val();
            updateButton();
        });
        $("#password").keyup(function(){
            passwordContent = $(this).val();
            updateButton();
        });
    });

    function updateButton(){
        $('#loginButton').prop('disabled', !(usernameContent && passwordContent));
    }
</script>
</html>