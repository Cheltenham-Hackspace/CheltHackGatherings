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
    <link href="../../../res/libraries/jquery-ui-1.11.4.custom/jquery-ui.min.css" rel="stylesheet">

    <script src="../../../js/bootstrap.min.js" rel="script" type="text/javascript"></script>
    <link href="../../../css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <link href="../../../res/libraries/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <link href="../../../css/style.css" rel="stylesheet" type="text/css">
    <link href="../../../css/pages/user/edit/usercreate.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        textarea.form-control {
            resize: vertical;
        }

        span.mandatory {
            color: #8c0000;
        }
    </style>
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
                <img alt="logo" src="../../../res/images/base/logos/logo.png" class="main-logo">
            </a>
            <a class="navbar-brand" href="http://cheltenhamhackspace.org/#" style="color:#000000;">&nbsp;Cheltenham
                Hackspace</a>
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
                                isset($_SESSION['user']) && isset($_SESSION['admin'])
                            ) {
                                echo "<li><a href='#' id='logout'>Logout</a></li>";
                                echo "<li><a href='../../gathering/edit/create.php'>Create</a></li>";
                                if ($_SESSION['admin']) echo "<li class='divider'></li><li><a href='../../../admin/pages/index.php'>Admin
                                Console</a></li>";
                            } else {
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
                <h3>Create User</h3>
                <?php
                include_once("../../../php/mysql/MySQLConnection.php");
                include_once("../../../php/variables/User.php");
                include_once("../../../php/security/SecurityUtils.php");

                //Connect to the database
                $mysqlConnection = MySQLConnection::createDefault("../../../");
                $connectionResult = $mysqlConnection->connect();

                if (!$connectionResult[0]) {
                    if ($connectionResult[1] == $mysqlConnection->getErrorCodes()["ERR_TIME_OUT"]) {
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
                <span id="errorZone"></span>

                <form id="js-submit-ajax" method="post" action="">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 20px;">
                        <li class="active"><a href="#basic" role="tab" data-toggle="tab">Home<span
                                    class="mandatory">*</span></a></li>
                        <li><a href="#names" role="tab" data-toggle="tab">Names<span class="mandatory">*</span></a></li>
                        <li><a href="#contact" role="tab" data-toggle="tab">Contact<span class="mandatory">*</span></a>
                        </li>
                        <li><a href="#images" role="tab" data-toggle="tab">Images<span class="mandatory">*</span></a>
                        </li>
                        <li><a href="#about-you" role="tab" data-toggle="tab">About You</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="basic">
                            <label for="userUsername">Username: <span class="mandatory">*</span></label>
                            <input type="text" class="form-control" id="userUsername" name="userUsername">

                            <label for="userPassword">Password: <span class="mandatory">*</span></label>
                            <input type="password" class="form-control" id="userPassword" name="userPassword">

                            <label for="userConfirmPass">Confirm Password: <span class="mandatory">*</span></label>
                            <input type="password" class="form-control" id="userConfirmPass" name="userConfirmPass">
                        </div>
                        <div class="tab-pane" id="names">
                            <label for="userFirstName">First: <span class="mandatory">*</span></label>
                            <input type="text" class="form-control" id="userFirstName" name="userFirstName">

                            <label for="userMiddleName">Middle: </label>
                            <input type="text" class="form-control" id="userMiddleName" name="userMiddleName">

                            <label for="userLastName">Surname: <span class="mandatory">*</span></label>
                            <input type="text" class="form-control" id="userLastName" name="userLastName">

                            <label for="userContactFullnamePrivate">Private: </label>
                            <input id="userContactFullnamePrivate" name="userContactFullnamePrivate" type="checkbox">
                        </div>
                        <div class="tab-pane" id="contact">
                            <label for="userContactEmail">Email address: <span class="mandatory">*</span></label>
                            <input id="userContactEmail" class="form-control" type="text" name="userContactEmail">

                            <label for="userContactEmailPrivate">Private: </label>
                            <input id="userContactEmailPrivate" type="checkbox">

                            <hr>
                            <label for="userContactPhone">Telephone number: </label>
                            <input id="userContactPhone" class="form-control" type="text" name="userContactPhone">

                            <label for="userContactPhonePrivate">Private: </label>
                            <input id="userContactPhonePrivate" type="checkbox" name="userContactPhonePrivate">

                            <hr>

                            <label for="userContactAddress">Address: </label>
                            <input id="userContactAddress" class="form-control" type="text" name="userContactAddress">

                            <label for="userContactAddressPrivate">Private: </label>
                            <input id="userContactAddressPrivate" type="checkbox" name="userContactAddressPrivate">
                        </div>
                        <div class="tab-pane" id="images">
                            <label for="userImageProfile">Profile Image: <span class="mandatory">*</span></label>

                            <p>(For best fit use 250x250)</p>
                            <input type="url" class="form-control" id="userImageProfile" name="userImageProfile">

                            <label for="userImageHeader">Header Image: <span class="mandatory">*</span></label>

                            <p>(For best fit use 500x100)</p>
                            <input type="url" class="form-control" id="userImageHeader" name="userImageHeader">
                        </div>
                        <div class="tab-pane" id="about-you">
                            <div class="form-group">
                                <label for="userDescriptionPersonal">Tell us a bit about yourself: </label>

                                <p>(Leave blank if you would prefer not to share)</p>
                                <textarea class="form-control" id="userDescriptionPersonal"
                                          name="userDescriptionPersonal"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="userDescriptionUse">What would you like to use Cheltenham Hackspace (and a
                                    possible future dedicated workspace) for: </label>

                                <p>(Leave blank if you would prefer not to share)</p>
                                <textarea class="form-control" id="userDescriptionUse"
                                          name="userDescriptionUse"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="userDescriptionOffer">Do you have any awesome skills or interests that you'd
                                    like to share with the rest of us?: </label>

                                <p>(Leave blank if you would prefer not to share)</p>
                                <textarea class="form-control" id="userDescriptionOffer"
                                          name="userDescriptionOffer"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-block btn-primary" style="margin-top: 10px;">Register!</button>
                </form>
            </section>
        </div>
    </div>
    <footer id="ft" style="margin-top: 100px">
        <p>© Cheltenham Hackspace 2014</p>
        <a href="mailto:michaelkent.theellipsis@googlemail.com">Website developed by Michael Kent</a>
    </footer>
</div>

<script rel="script" type="text/javascript" src="../../../js/logout.js"></script>
<script>
    $("document").ready(function () {
        $("#js-submit-ajax").submit(function () {
            var data = $(this).serialize();
            $.ajax({
                type: "POST",
                dataType: "html",
                url: "../../../api/calls/user/registration/register-user.php", //Relative or absolute path to
                // response.php file
                data: data,
                success: function (data) {
                    console.log(data);
                    try {
                        var parsedData = jQuery.parseJSON(data);
                        if (parsedData.success) {
                            $("#errorZone").html(
                                "<div class=\"ui-widget\">" +
                                "   <div class=\"ui-state-success ui-corner-all\">" +
                                "       <p class='ui-para-text'><span class=\"ui-icon ui-icon-alert\"></span>" +
                                "       <strong>Success:</strong>" + "The user was created successfully!" + "</p>" +
                                "   </div>" +
                                "</div>");
                            if (parsedData.mail) {
                                triggerMailAjax(parsedData.id)
                            } else {
                                $("#errorZone").html(
                                    "<div class=\"ui-widget\">" +
                                    "   <div class=\"ui-state-error ui-corner-all\">" +
                                    "       <p class='ui-para-text'><span class=\"ui-icon ui-icon-alert\"></span>" +
                                    "       <strong>Success:</strong>" + "The user was created successfully but the " +
                                    "email could not be sent. You will need to contact an admin and get your account " +
                                    "activated." + "</p>" +
                                    "   </div>" +
                                    "</div>");
                            }
//                            $("#js-submit-ajax").trigger("reset");
                        } else {
                            $("#errorZone").html(
                                "<div class=\"ui-widget\">" +
                                "   <div class=\"ui-state-error ui-corner-all\">" +
                                "       <p class='ui-para-text'><span class=\"ui-icon ui-icon-alert\"></span>" +
                                "       <strong>Alert:</strong>" + "There was an error registering: " + parsedData
                                    .error.message + "</p>" +
                                "   </div>" +
                                "</div>");
                        }
                    } catch (exception) {
                        console.log(exception);
                    }
//                    $("#return").html(
//                        data.toString()
//                    );
//
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log("Failed: " + jqXHR + ", " + textStatus + ", " + errorThrown);
                }
            });
            return false;
        });

        function triggerMailAjax(uniqueID) {
            console.log(uniqueID);
            $.ajax({
                type: "POST",
                dataType: "html",
                url: "../../../api/calls/user/registration/mail-user.php", //Relative or absolute path to response
                // .php file
                data: {
                    "uid": uniqueID
                },
                success: function (data) {
                    console.log("Success: " + data);
                    try {
                        var parsedData = jQuery.parseJSON(data);
                        if (parsedData.success) {
                            $("#errorZone").html(
                                "<div class='ui-widget'>" +
                                "   <div class='ui-state-success ui-corner-all'>" +
                                "       <p class='ui-para-text'><span class='ui-icon ui-icon-alert'></span>" +
                                "       <strong>Success:</strong>" + "The user was emailed successfully!" +
                                "</p>" +
                                "   </div>" +
                                "</div>");
                        } else {
                            $("#errorZone").html(
                                "<div class='ui-widget'>" +
                                "   <div class='ui-state-error ui-corner-all'>" +
                                "       <p class='ui-para-text'><span class='ui-icon ui-icon-alert'></span>" +
                                "       <strong>Alert:</strong>" + "There was an error mailing: " + parsedData
                                    .error.message + "</p>" +
                                "   </div>" +
                                "</div>");
                        }
                    } catch (error) {
                        console.log(error);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log("Failed: " + jqXHR + ", " + textStatus + ", " + errorThrown);
                }
            });
            console.log("Ajaxed");
        }
    });
</script>
</body>
</html>