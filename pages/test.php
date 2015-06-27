<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <script src="../js/jquery-1.11.3.min.js"></script>
</head>
<body>
<?php
//phpinfo();
include_once("../php/variables/Gathering.php");

include("../api/models/database/DatabaseModel.php");
include("../api/models/gatherings/GatheringsDataModel.php");
include("../api/JSONHelper.php");
$model = new GatheringsDataModel();
$model->initFromAuthFile("../lockedres/auth/database-details.txt");
$model->connect();

var_dump($model->switchTable(5, GatheringsDataModel::GATHERINGS, GatheringsDataModel::PAST_GATHERINGS));
var_dump($model->getError());
var_dump($model->GetErrorString($model->getError()));
?>
<form method="post" action="../api/calls/user/get-user.php" id="js-ajax-php-json">
    <label for="id">ID: </label>
    <input type="text" id="id" name="id">
    <button type="submit">Go</button>
</form>
<p id="return"></p>
<script>
    $("document").ready(function () {
        $("#js-ajax-php-json").submit(function () {
            console.log("Submit");
            var data = {
                "action": "test"
            };
            console.log("Data");
            data = $(this).serialize() + "&" + $.param(data);
            console.log("Serialized: " + data);
            $.ajax({
                type: "POST",
                dataType: "html",
                url: "../api/calls/user/get-user.php", //Relative or absolute path to response.php file
                data: data,
                success: function (data) {
                    console.log("Success.");
                    $("#return").html(
                        data.toString()
                    );

                    alert("Form submitted successfully.\nReturned json: " + data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log("Failed: " + jqXHR + ", " + textStatus + ", " + errorThrown);
                }
            });
            console.log("Ajaxed.");
            return false;
        });
    });
</script>
</body>
</html>