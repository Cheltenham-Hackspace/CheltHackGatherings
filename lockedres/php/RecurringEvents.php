<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 11/06/15
 * Time: 17:56
 */

include_once("../../php/mysql/GatheringMySQLConnection.php");

//Connect to the database with a GatheringMySQLConnection so we can access the Gathering exclusive methods.
$mySQLConnection = new GatheringMySQLConnection("vit-mysql.ddns.net",
    "chelthacktesting", "remote_admin", "S*@qEnl6k2HpoVvyRqYeNA@4Tp8TXm");
//Then connect to the database.
$connectionResult = $mySQLConnection->connect();
